<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */

$typeMap = $this->config( 'controller/jobs/product/export/idealo/types', [] );

$urlTarget = $this->get( 'urlConfig/target' );
$urlCntl = $this->get( 'urlConfig/controller' );
$urlAction = $this->get( 'urlConfig/action' );
$urlFilter = array_flip( $this->get( 'urlConfig/filter' ) ?? ['d_prodid'] );
$urlConfig = $this->get( 'urlConfig/config' ) ?? [];
$urlConfig['absoluteUri'] = true;

// Encodes a value as a properly escaped CSV field
$csv = function( ?string $value ) : string {
	$value ??= '';
	if( strpbrk( $value, ',"' . "\n\r" ) !== false ) {
		return '"' . str_replace( '"', '""', $value ) . '"';
	}
	return $value;
};


foreach( $this->get( 'exportItems', [] ) as $id => $item )
{
	// Absolute product url URL
	$slug = \Aimeos\Base\Str::slug( $item->getName( 'url' ) );
	$params = ['path' => $slug, 'd_name' => $slug, 'd_prodid' => $id, 'd_pos' => ''];
	$url = $this->url( $item->getTarget() ?: $urlTarget, $urlCntl, $urlAction, array_diff_key( $params, $urlFilter ), [], $urlConfig );

	// Category path
	$catItem = $item->getRefItems( 'catalog', 'default', 'default' )->first();
	$catPath = $catItem?->getConfigValue( 'idealo' ) ?: $catItem?->getName();

	$articles = $item->getType() === 'select' ? $item->getRefItems( 'product', null, 'default' ) : map( [$item] );

	foreach( $articles as $article )
	{
		// All media items as pipe-separated URLs
		$imageUrls = $article->getRefItems( 'media', 'default', 'default' )
			->map( fn( $m ) => $this->content( $m->getUrl(), $m->getFileSystem() ) );

		if( $imageUrls->isEmpty() )
		{
			$imageUrls = $item->getRefItems( 'media', 'default', 'default' )
				->map( fn( $m ) => $this->content( $m->getUrl(), $m->getFileSystem() ) );
		}

		// Price: current selling price; formerPrice is original when a rebate applies
		$price = $formerPrice = $delivery = '';

		$priceItem = $article->getRefItems( 'price', 'default', 'default' )->first()
			?? $item->getRefItems( 'price', 'default', 'default' )->first();

		if( $priceItem )
		{
			$currency  = $priceItem->getCurrencyId();
			$value     = $priceItem->getValue();
			$rebate    = $priceItem->getRebate();
			$costs     = $priceItem->getCosts();

			if( !empty( $value ) ) {
				$price = $value . ' ' . $currency;
			}

			if( $rebate > 0 ) {
				$formerPrice = $this->number($value + $rebate) . ' ' . $currency;
			}

			$deliveryCost = (float)( $article->delivery?->getValue() ?? 0 );
			$delivery = $this->number( $deliveryCost + $costs ) . ' ' . $currency;
		}

		// supplier/brand: from supplier relation
		$brand = $article->getRefItems( 'supplier', 'default', 'default' )->first()?->getName()
			?: $item->getRefItems( 'supplier', 'default', 'default' )->first()?->getName();

		// Product attributes and properties from articles and selection products
		$map = map();

		foreach( $typeMap as $idealoType => $type )
		{
			if( !$type ) {
				continue;
			}

			$props = $article->getProperties( $type )
				->merge( $articles->getProperties( $type )->flat( 1 ) );

			$attrs = $article->getRefItems( 'attribute', $type, 'default' )
				->merge( $articles->getRefItems( 'attribute', $type, 'default' )->flat( 1 ) )
				->getCode();

			$map[$idealoType] = $props->merge( $attrs );
		}


		echo implode( ',', [
			$csv( $article->getCode() ), // sku
			$csv( $brand ),
			$csv( $article->getName( 'name' ) ), // title
			$csv( $catPath ), // categoryPath
			$csv( $url ),
			$csv( $map->get( 'hans' )?->first() ),
			$csv( $article->getName( 'long' ) ?: $item->getName( 'long' ) ), // description
			$csv( $imageUrls->first() ),
			$csv( $price ),
			$csv( $formerPrice ),
			$csv( $delivery ), // shipping costs
			$csv( $map->get( 'delivery' )?->first() ), // delivery time
			$csv( $map->get( 'eans' )?->first() ),
			$csv( $map->get( 'size' )?->first() ),
			$csv( $map->get( 'colour' )?->join( '/' ) ),
			$csv( $map->get( 'gender' )?->join( ',' ) ),
			$csv( $map->get( 'material' )?->join( '/' ) ),
			$csv( $map->get( 'eec_efficiencyClass' )?->first() ),
			$csv( $map->get( 'eec_labelUrl' )?->first() ),
			$csv( $map->get( 'eec_dataSheetUrl' )?->first() ),
			$csv( $map->get( 'eec_version' )?->first() ),
		] ) . "\n";
	}
}
