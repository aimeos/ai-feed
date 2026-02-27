<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 * @package Controller
 * @subpackage Jobs
 */


namespace Aimeos\Controller\Jobs\Product\Export\Idealo;


/**
 * Job controller for product exports for Idealo Shopping.
 *
 * @package Controller
 * @subpackage Jobs
 */
class Standard
	extends \Aimeos\Controller\Jobs\Base
	implements \Aimeos\Controller\Jobs\Iface
{
	/** controller/jobs/product/export/idealo/name
	 * Class name of the used Idealo product export controller implementation
	 *
	 * Each default job controller can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the controller factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Controller\Jobs\Product\Export\Idealo\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Controller\Jobs\Product\Export\Myalgorithm
	 *
	 * then you have to set the this configuration option:
	 *
	 *  controller/jobs/product/export/idealo/name = Myalgorithm
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyOptimizer"!
	 *
	 * @param string Last part of the class name
	 * @since 2026.01
	 */

	/** controller/jobs/product/export/idealo/decorators/excludes
	 * Excludes decorators added by the "common" option from the product export job controller
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to remove a decorator added via
	 * "controller/jobs/common/decorators/default" before they are wrapped
	 * around the job controller.
	 *
	 *  controller/jobs/product/export/idealo/decorators/excludes = array( 'decorator1' )
	 *
	 * This would remove the decorator named "decorator1" from the list of
	 * common decorators ("\Aimeos\Controller\Jobs\Common\Decorator\*") added via
	 * "controller/jobs/common/decorators/default" to the job controller.
	 *
	 * @param array List of decorator names
	 * @since 2026.01
	 * @see controller/jobs/common/decorators/default
	 * @see controller/jobs/product/export/idealo/decorators/global
	 * @see controller/jobs/product/export/idealo/decorators/local
	 */

	/** controller/jobs/product/export/idealo/decorators/global
	 * Adds a list of globally available decorators only to the product export job controller
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap global decorators
	 * ("\Aimeos\Controller\Jobs\Common\Decorator\*") around the job controller.
	 *
	 *  controller/jobs/product/export/idealo/decorators/global = array( 'decorator1' )
	 *
	 * This would add the decorator named "decorator1" defined by
	 * "\Aimeos\Controller\Jobs\Common\Decorator\Decorator1" only to the job controller.
	 *
	 * @param array List of decorator names
	 * @since 2026.01
	 * @see controller/jobs/common/decorators/default
	 * @see controller/jobs/product/export/idealo/decorators/excludes
	 * @see controller/jobs/product/export/idealo/decorators/local
	 */

	/** controller/jobs/product/export/idealo/decorators/local
	 * Adds a list of local decorators only to the product export job controller
	 *
	 * Decorators extend the functionality of a class by adding new aspects
	 * (e.g. log what is currently done), executing the methods of the underlying
	 * class only in certain conditions (e.g. only for logged in users) or
	 * modify what is returned to the caller.
	 *
	 * This option allows you to wrap local decorators
	 * ("\Aimeos\Controller\Jobs\Product\Export\Decorator\*") around the job
	 * controller.
	 *
	 *  controller/jobs/product/export/idealo/decorators/local = array( 'decorator2' )
	 *
	 * This would add the decorator named "decorator2" defined by
	 * "\Aimeos\Controller\Jobs\Product\Export\Decorator\Decorator2"
	 * only to the job controller.
	 *
	 * @param array List of decorator names
	 * @since 2026.01
	 * @see controller/jobs/common/decorators/default
	 * @see controller/jobs/product/export/idealo/decorators/excludes
	 * @see controller/jobs/product/export/idealo/decorators/global
	 */

	use \Aimeos\Macro\Macroable;


	/**
	 * Returns the localized name of the job.
	 *
	 * @return string Name of the job
	 */
	public function getName() : string
	{
		return $this->context()->translate( 'controller/jobs', 'Idealo product export' );
	}


	/**
	 * Returns the localized description of the job.
	 *
	 * @return string Description of the job
	 */
	public function getDescription() : string
	{
		return $this->context()->translate( 'controller/jobs', 'Exports products for Idealo' );
	}


	/**
	 * Executes the job.
	 *
	 * @throws \Aimeos\Controller\Jobs\Exception If an error occurs
	 */
	public function run()
	{
		$context = $this->context();
		$logger = $context->logger();
		$sitecode = $context->locale()->getSiteCode();

		$manager = \Aimeos\MShop::create( $context, 'feed' );
		$localeManager = \Aimeos\MShop::create( $context, 'locale' );

		$filter = $manager->filter( true )
			->add( 'feed.type', '==', 'idealo' )
			->order( 'feed.id' );

		$items = $manager->search( $filter, ['catalog', 'product'] );

		foreach( $items as $item )
		{
			try
			{
				$locale = $localeManager->bootstrap( $sitecode, $item->getLanguageId(), $item->getCurrencyId() );
				$this->export( $item, $locale );
			}
			catch( \Exception $e )
			{
				$logger->error( 'Idealo product export error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'product/export/idealo' );
				$this->mail( 'Idealo product export error', $e->getMessage() . "\n" . $e->getTraceAsString() );
			}
		}
	}


	/**
	 * Returns the domain names whose items should be exported too
	 *
	 * @return array List of domain names
	 */
	protected function domains() : array
	{
		/** controller/jobs/product/export/idealo/domains
		 * List of associated items from other domains that should be exported too
		 *
		 * Products consist not only of the base data but also of texts, media
		 * files, prices, attrbutes and other details. Those information is
		 * associated to the products via their lists. Using the "domains" option
		 * you can make more or less associated items available in the template.
		 *
		 * @param array List of domain names
		 * @since 2026.01
		 * @see controller/jobs/product/export/idealo/filename
		 * @see controller/jobs/product/export/idealo/max-items
		 */
		$default = ['attribute', 'catalog', 'media', 'price', 'product', 'text'];

		return $this->context()->config()->get( 'controller/jobs/product/export/idealo/domains', $default );
	}


	/**
	 * Exports the given product feed for the specified locale
	 *
	 * @param \Aimeos\MShop\Feed\Item\Iface $feedItem Product feed item to export
	 * @param \Aimeos\MShop\Locale\Item\Iface $locale Locale to use for the export
	 * @throws \Aimeos\Controller\Jobs\Exception If an error occurs during export
	 */
	protected function export( \Aimeos\MShop\Feed\Item\Iface $feedItem, \Aimeos\MShop\Locale\Item\Iface $locale ) : void
	{
		$context = ( clone $this->context() )->setLocale( $locale );
		$manager = \Aimeos\MShop::create( $context, 'index' );
		$filter = $this->filter( $manager->filter( true ), $feedItem );
		$excludes = $feedItem->getListItems( 'catalog', 'exclude' )->getRefId();

		$cursor = $manager->cursor( $filter );
		$domains = $this->domains();

		if( ( $fh = tmpfile() ) === false ) {
			throw new \Aimeos\Controller\Jobs\Exception( sprintf( 'Unable to create temporary file for Idealo export' ) );
		}

		try
		{
			if( fwrite( $fh, $this->header() ) === false ) {
				throw new \Aimeos\Controller\Jobs\Exception( sprintf( 'Unable to write header for Idealo export to temporary file' ) );
			}

			while( $items = $manager->iterate( $cursor, $domains ) )
			{
				$items = $items->filter( fn( $item ) => $item->getListItems( 'catalog' )->getRefId()->intersect( $excludes )->isEmpty() );
				$items = $this->call( 'hydrate', $items );

				if( fwrite( $fh, $this->render( $items ) ) === false ) {
					throw new \Aimeos\Controller\Jobs\Exception( sprintf( 'Unable to write products for Idealo export to temporary file' ) );
				}
			}

			rewind( $fh );

			$filename = sprintf( $this->call( 'filename' ), $feedItem->getLabel() );
			$this->fs()->writes( $filename, $fh );
		}
		finally
		{
			fclose( $fh );
		}
	}


	/**
	 * Returns the file name template for the exported feed file
	 *
	 * @return string File name template with one %s placeholder for the feed label
	 */
	protected function filename() : string
	{
		/** controller/jobs/product/export/idealo/filename
		 * Template for the generated file names
		 *
		 * The generated export files will be named according to the given
		 * string which can contain one place holder: The feed label of the exported feed.
		 *
		 * @param string File name template
		 * @since 2026.01
		 * @see controller/jobs/product/export/idealo/max-items
		 * @see controller/jobs/product/export/idealo/domains
		 */
		return $this->context()->config()->get( 'controller/jobs/product/export/idealo/filename', '%s.csv' );
	}


	/**
	 * Adds filters based on the feed item to the given filter
	 *
	 * @param \Aimeos\Base\Criteria\Iface $filter Filter to add conditions to
	 * @param \Aimeos\MShop\Feed\Item\Iface $item Feed item to get the conditions from
	 * @return \Aimeos\Base\Criteria\Iface Filter with added conditions
	 */
	protected function filter( \Aimeos\Base\Criteria\Iface $filter, \Aimeos\MShop\Feed\Item\Iface $item ) : \Aimeos\Base\Criteria\Iface
	{
		$excludes = $includes = [];

		$filter->add( 'index.catalog.id', '!=', null )
			->order( 'product.id' )
			->slice( 0, $this->max() );

		if( $item->getStock() ) {
			$filter->add( 'product.instock', '>', 0 );
		}

		if( !( $ids = $item->getListItems( 'catalog', 'include' )->getRefId()->values() )->isEmpty() ) {
			$includes[] = $filter->is( 'index.catalog.id', '==', $ids );
		}

		if( !( $ids = $item->getListItems( 'product', 'exclude' )->getRefId()->values() )->isEmpty() ) {
			$excludes[] = $filter->is( 'product.id', '!=', $ids );
		}

		if( !( $ids = $item->getListItems( 'product', 'include' )->getRefId()->values() )->isEmpty() ) {
			$includes[] = $filter->is( 'product.id', '==', $ids );
		}

		return $filter->add( $filter->and( [
			$filter->and( $excludes ),
			$filter->or( $includes )
		] ) );
	}


	/**
	 * Returns the file system for storing the exported files
	 *
	 * @return \Aimeos\Base\Filesystem\Iface File system to store files to
	 */
	protected function fs() : \Aimeos\Base\Filesystem\Iface
	{
		return $this->context()->fs( 'fs-export' );
	}


	/**
	 * Renders the CSV header for the exported products
	 *
	 * @return string Rendered header
	 */
	protected function header() : string
	{
		/** controller/jobs/product/export/idealo/template-header
		 * Relative path to the CSV header template of the product export job controller.
		 *
		 * The template file contains the CSV code and processing instructions
		 * to generate the export files. The configuration string is the path
		 * to the template file relative to the templates directory (usually in
		 * templates/controller/jobs).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating CSV code for the export items
		 * @since 2026.01
		 * @see controller/jobs/product/export/idealo/domains
		 * @see controller/jobs/product/export/idealo/filename
		 * @see controller/jobs/product/export/idealo/max-items
		 */
		$tplconf = 'controller/jobs/product/export/idealo/template-header';
		$default = 'product/export/idealo/items-header-standard';

		$context = $this->context();
		$view = $context->view();

		return $view->render( $context->config()->get( $tplconf, $default ) );
	}


	/**
	 * Hydrates the given list of items
	 *
	 * @param \Aimeos\Map $items List of items to hydrate
	 * @return \Aimeos\Map Hydrated list of items
	 */
	protected function hydrate( \Aimeos\Map $items ) : \Aimeos\Map
	{
		$order = \Aimeos\MShop::create( $this->context(), 'order' )->create()->off();
		$orderProductManager = \Aimeos\MShop::create( $this->context(), 'order/product' );
		$manager = \Aimeos\MShop::create( $this->context(), 'service' );

		$filter = $manager->filter( true )->add( 'service.type', '==', 'delivery' )->order( 'service.position' );
		$providers = $manager->search( $filter )->map( fn( $item ) => $manager->getProvider( $item, $item->getType() ) );

		return $items->map( function( $item ) use ( $order, $orderProductManager, $providers ) {
			$orderProduct = $orderProductManager->create()->copyFrom( $item );
			$basket = (clone $order)->addProduct( $orderProduct );

			$item->delivery = $providers->find( fn( $provider ) => $provider->isAvailable( $basket ) )?->calcPrice( $basket );
			return $item;
		} );
	}


	/**
	 * Returns the maximum number of fetched products at once
	 *
	 * @return int Maximum number of fetched products at once
	 */
	protected function max() : int
	{
		/** controller/jobs/product/export/idealo/max-items
		 * Maximum number of fetched products at once
		 *
		 * Limits the number of fetched products at once as the memory
		 * consumption of fetching large result sets is rather high. Splitting
		 * the data into several files that can also be processed in
		 * parallel is able to speed up importing the files again.
		 *
		 * @param integer Number of products
		 * @since 2026.01
		 * @see controller/jobs/product/export/idealo/filename
		 * @see controller/jobs/product/export/idealo/domains
		 */
		return $this->context()->config()->get( 'controller/jobs/product/export/idealo/max-items', 1000 );
	}


	/**
	 * Renders the output for the given items
	 *
	 * @param \Aimeos\Map $items List of product items implementing \Aimeos\MShop\Product\Item\Iface
	 * @return string Rendered content
	 */
	protected function render( \Aimeos\Map $items ) : string
	{
		/** controller/jobs/product/export/idealo/template-items
		 * Relative path to the CSV items template of the product export job controller.
		 *
		 * The template file contains the CSV code and processing instructions
		 * to generate the export files. The configuration string is the path
		 * to the template file relative to the templates directory (usually in
		 * templates/controller/jobs).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating CSV code for the export items
		 * @since 2026.01
		 * @see controller/jobs/product/export/idealo/domains
		 * @see controller/jobs/product/export/idealo/filename
		 * @see controller/jobs/product/export/idealo/max-items
		 */
		$tplconf = 'controller/jobs/product/export/idealo/template-items';
		$default = 'product/export/idealo/items-body-standard';

		$context = $this->context();
		$view = $context->view();

		$view->urlConfig = $context->config()->get( 'client/html/catalog/detail/url', [] );
		$view->exportItems = $items;

		return $view->render( $context->config()->get( $tplconf, $default ) );
	}
}
