<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */

$typeMap = $this->config( 'controller/jobs/product/export/google/types', [] );

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
	// Absolute product detail URL
	$slug = \Aimeos\Base\Str::slug( $item->getName( 'url' ) );
	$params = ['path' => $slug, 'd_name' => $slug, 'd_prodid' => $id, 'd_pos' => ''];
	$url = $this->url( $item->getTarget() ?: $urlTarget, $urlCntl, $urlAction, array_diff_key( $params, $urlFilter ), [], $urlConfig );

	// Google product category from catalog item config key 'google', fallback to category name
	$catItem = $item->getRefItems( 'catalog', 'default', 'default' )->first();

	$articles = $item->getType() === 'select' ? $item->getRefItems( 'product', null, 'default' ) : map();
	$articles->push( $item, $item->getId() ); // include main product

	foreach( $articles as $article )
	{
		// Media: first image as primary, remainder as additional
		$imageUrls = $article->getRefItems( 'media', 'default', 'default' )
			->map( fn( $m ) => $this->content( $m->getUrl(), $m->getFileSystem() ) );

		if( $imageUrls->isEmpty() )
		{
			$imageUrls = $item->getRefItems( 'media', 'default', 'default' )
				->map( fn( $m ) => $this->content( $m->getUrl(), $m->getFileSystem() ) );
		}

		// Availability
		$now = date( 'Y-m-d H:i:s' );
		$dateStart = $article->getDateStart();
		$dateEnd = $article->getDateEnd();

		if( $dateStart && $dateStart > $now ) {
			$availability = 'preorder';
		} elseif( $article->inStock() ) {
			$availability = 'in stock';
		} else {
			$availability = 'out of stock';
		}

		$availabilityDate = $dateStart ? str_replace( ' ', 'T', $dateStart ) . '+00:00' : '';
		$expirationDate   = $dateEnd   ? str_replace( ' ', 'T', $dateEnd )   . '+00:00' : '';

		// Price: regular price and sale price (when rebate applies)
		$price = $salePrice = $priceDate = $delivery = '';

		$priceItem = $article->getRefItems( 'price', 'default', 'default' )->first()
			?? $item->getRefItems( 'price', 'default', 'default' )->first();

		if( $priceItem )
		{
			$currency  = $priceItem->getCurrencyId();
			$value     = $priceItem->getValue();
			$rebate    = $priceItem->getRebate();
			$costs     = $priceItem->getCosts();

			if( !empty( $value ) ) {
				$salePrice = $value . ' ' . $currency;
			}

			if( $rebate > 0 ) {
				$price     = $this->number( $value + $rebate ) . ' ' . $currency;
			}

			$deliveryCost = (float)( $article->delivery?->getValue() ?? 0 );
			$delivery = $this->number( $deliveryCost + $costs ) . ' ' . $currency;
		}

		// supplier/brand: from supplier relation
		$brand = $article->getRefItems( 'supplier', 'default', 'default' )->first()?->getName()
			?: $item->getRefItems( 'supplier', 'default', 'default' )->first()?->getName();

		// Product attributes and properties from product and its sub-articles
		$map = map( [] );

		foreach( $typeMap as $googleType => $type )
		{
			if( !$type ) {
				continue;
			}

			$props = $article->getProperties( $type )
				->merge( $articles->getProperties( $type )->flat( 1 ) );

			$attrs = $article->getRefItems( 'attribute', $type, 'default' )
				->merge( $articles->getRefItems( 'attribute', $type, 'default' )->flat( 1 ) )
				->getCode();

			$map[$googleType] = $props->merge( $attrs );
		}

		// item_group_id: parent product code for variant grouping
		$parentSku = $item->getType() === 'select' ? $item->getCode() : '';

		echo implode( ',', [
			$csv( $article->getCode() ),
			$csv( $article->getName( 'name' ) ),
			$csv( $article->getName( 'long' ) ?: $item->getName( 'long' ) ), // description
			$csv( $url ),
			$csv( $imageUrls->first() ), // image_link
			$csv( $imageUrls->skip( 1 )->join( ',' ) ), // additional_image_link
			$csv( $availability ),
			$csv( $availabilityDate ),
			$csv( $delivery ), // cost_of_goods_sold
			$csv( $expirationDate ),
			$csv( $price ), // former price
			$csv( $salePrice ), // current price
			$csv( $priceDate ), // sale_price_effective_date
			$csv( $catItem?->getConfigValue( 'google-merchant' ) ), // google_product_category
			$csv( $catItem?->getName() ), // product_type (category path)
			$csv( $brand ),
			$csv( $map->get( 'gtin' )?->join( ',' ) ),
			$csv( $map->get( 'mpn' )?->join( ',' ) ),
			$csv( $map->get( 'gtin' )?->isNotEmpty() || $map->get( 'mpn' )?->isNotEmpty() ? 'yes' : 'no' ), // identifier_exists
			$csv( $map->get( 'condition' )?->first() ),
			$csv( $map->get( 'adult' )?->first() ),
			$csv( '' ), // multipack
			$csv( $article->getType() === 'bundle' ? 'yes' : '' ), // is_bundle
			$csv( $map->get( 'certification' )?->join( ',' ) ),
			$csv( $map->get( 'energy_efficiency_class' )?->first() ),
			$csv( $map->get( 'age_group' )?->first() ),
			$csv( $map->get( 'color' )?->join( ',' ) ),
			$csv( $map->get( 'gender' )?->join( ',' ) ),
			$csv( $map->get( 'material' )?->join( ',' ) ),
			$csv( $map->get( 'pattern' )?->join( ',' ) ),
			$csv( $map->get( 'size' )?->join( ',' ) ),
			$csv( $map->get( 'size_type' )?->first() ),
			$csv( $map->get( 'size_system' )?->first() ),
			$csv( $parentSku ), // item_group_id
			$csv( $map->get( 'product_length' )?->first() ),
			$csv( $map->get( 'product_width' )?->first() ),
			$csv( $map->get( 'product_height' )?->first() ),
			$csv( $map->get( 'product_weight' )?->first() ),
			$csv( $article->getName( 'highlight' ) ?: $item->getName( 'highlight' ) ),
		] ) . "\n";
	}
}
