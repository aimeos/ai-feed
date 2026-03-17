<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 * @package Admin
 * @subpackage JQAdm
 */


namespace Aimeos\Admin\JQAdm\Feed;

sprintf( 'feed' ); // for translation


/**
 * Default implementation of feed JQAdm client.
 *
 * @package Admin
 * @subpackage JQAdm
 */
class Standard
	extends \Aimeos\Admin\JQAdm\Common\Admin\Factory\Base
	implements \Aimeos\Admin\JQAdm\Common\Admin\Factory\Iface
{
	/** admin/jqadm/feed/name
	 * Class name of the used feed panel implementation
	 *
	 * Each default admin client can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the client factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\Admin\JQAdm\Feed\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\Admin\JQAdm\Feed\Myfavorite
	 *
	 * then you have to set the this configuration option:
	 *
	 *  admin/jqadm/feed/name = Myfavorite
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyFavorite"!
	 *
	 * @param string Last part of the class name
	 * @since 2026.01
	 */


	/**
	 * Adds the required data used in the template
	 *
	 * @param \Aimeos\Base\View\Iface $view View object
	 * @return \Aimeos\Base\View\Iface View object with assigned parameters
	 */
	public function data( \Aimeos\Base\View\Iface $view ) : \Aimeos\Base\View\Iface
	{
		$context = $this->context();
		$config = $context->config();

		$localeManager = \Aimeos\MShop::create( $context, 'locale' );
		$filter = $localeManager->filter( true );

		$view->itemSubparts = $this->getSubClientNames();
		$view->itemLocales = $localeManager->search( $filter );
		$view->itemExportTypes = array_keys( $config->get( 'controller/jobs/product/export', [] ) );
		$view->itemAttrTypes = map( $config->get( 'controller/jobs/product/export', [] ) )
			->map( fn( $config ) => $config['types'] ?? [] );

		return $view;
	}


	/**
	 * Batch update of a resource
	 *
	 * @return string|null Output to display
	 */
	public function batch() : ?string
	{
		return $this->batchBase( 'feed' );
	}


	/**
	 * Copies a resource
	 *
	 * @return string|null HTML output
	 */
	public function copy() : ?string
	{
		$view = $this->object()->data( $this->view() );

		try
		{
			if( ( $id = $view->param( 'id' ) ) === null )
			{
				$msg = $this->context()->translate( 'admin', 'Required parameter "%1$s" is missing' );
				throw new \Aimeos\Admin\JQAdm\Exception( sprintf( $msg, 'id' ) );
			}

			$manager = \Aimeos\MShop::create( $this->context(), 'feed' );

			$view->item = $manager->get( $id, ['catalog', 'product', 'supplier'] );
			$view->itemData = $this->toArray( $view->item, true );
			$view->itemBody = parent::copy();
		}
		catch( \Exception $e )
		{
			$this->report( $e, 'copy' );
		}

		return $this->render( $view );
	}


	/**
	 * Creates a new resource
	 *
	 * @return string|null HTML output
	 */
	public function create() : ?string
	{
		$view = $this->object()->data( $this->view() );

		try
		{
			$data = $view->param( 'item', [] );

			if( !isset( $view->item ) ) {
				$view->item = \Aimeos\MShop::create( $this->context(), 'feed' )->create();
			}

			$data['feed.siteid'] = $view->item->getSiteId();

			$view->itemData = array_replace_recursive( $this->toArray( $view->item ), $data );
			$view->itemBody = parent::create();
		}
		catch( \Exception $e )
		{
			$this->report( $e, 'create' );
		}

		return $this->render( $view );
	}


	/**
	 * Deletes a resource
	 *
	 * @return string|null HTML output
	 */
	public function delete() : ?string
	{
		$view = $this->view();

		$manager = \Aimeos\MShop::create( $this->context(), 'feed' );
		$manager->begin();

		try
		{
			if( ( $ids = $view->param( 'id' ) ) === null )
			{
				$msg = $this->context()->translate( 'admin', 'Required parameter "%1$s" is missing' );
				throw new \Aimeos\Admin\JQAdm\Exception( sprintf( $msg, 'id' ) );
			}

			$search = $manager->filter()->add( 'feed.id', '==', $ids )->slice( 0, count( (array) $ids ) );
			$items = $manager->search( $search );

			foreach( $items as $item )
			{
				$view->item = $item;
				parent::delete();
			}

			$manager->delete( $items );
			$manager->commit();

			return $this->redirect( 'feed', 'search', null, 'delete' );
		}
		catch( \Exception $e )
		{
			$manager->rollback();
			$this->report( $e, 'delete' );
		}

		return $this->search();
	}


	/**
	 * Returns a single resource
	 *
	 * @return string|null HTML output
	 */
	public function get() : ?string
	{
		$view = $this->object()->data( $this->view() );

		try
		{
			if( ( $id = $view->param( 'id' ) ) === null )
			{
				$msg = $this->context()->translate( 'admin', 'Required parameter "%1$s" is missing' );
				throw new \Aimeos\Admin\JQAdm\Exception( sprintf( $msg, 'id' ) );
			}

			$manager = \Aimeos\MShop::create( $this->context(), 'feed' );

			$view->item = $manager->get( $id, ['catalog', 'product', 'supplier'] );
			$view->itemData = $this->toArray( $view->item );
			$view->itemBody = parent::get();
		}
		catch( \Exception $e )
		{
			$this->report( $e, 'get' );
		}

		return $this->render( $view );
	}


	/**
	 * Saves the data
	 *
	 * @return string|null HTML output
	 */
	public function save() : ?string
	{
		$view = $this->view();

		$manager = \Aimeos\MShop::create( $this->context(), 'feed' );
		$manager->begin();

		try
		{
			$item = $this->fromArray( $view->param( 'item', [] ) );
			$view->item = $item->getId() ? $item : $manager->save( $item );
			$view->itemBody = parent::save();

			$manager->save( clone $view->item );
			$manager->commit();

			return $this->redirect( 'feed', $view->param( 'next' ), $view->item->getId(), 'save' );
		}
		catch( \Exception $e )
		{
			$manager->rollback();
			$this->report( $e, 'save' );
		}

		return $this->create();
	}


	/**
	 * Returns a list of resources according to the conditions
	 *
	 * @return string|null HTML output
	 */
	public function search() : ?string
	{
		$view = $this->view();

		try
		{
			$total = 0;
			$params = $this->storeFilter( $view->param(), 'feed' );
			$manager = \Aimeos\MShop::create( $this->context(), 'feed' );

			$search = $manager->filter()->order( ['feed.type', 'feed.label'] );
			$search = $this->initCriteria( $search, $params );

			$view->items = $manager->search( $search, [], $total );
			$view->filterAttributes = $manager->getSearchAttributes( true );
			$view->filterOperators = $search->getOperators();
			$view->itemExportTypes = array_keys( $this->context()->config()->get( 'controller/jobs/product/export', [] ) );
			$view->itemBody = parent::search();
			$view->total = $total;
		}
		catch( \Exception $e )
		{
			$this->report( $e, 'search' );
		}

		/** admin/jqadm/feed/template-list
		 * Relative path to the HTML body template for the feed list.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in templates/admin/jqadm).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "default" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "default"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the HTML code
		 * @since 2026.01
		 */
		$tplconf = 'admin/jqadm/feed/template-list';
		$default = 'feed/list';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Admin\JQAdm\Iface Sub-client object
	 */
	public function getSubClient( string $type, ?string $name = null ) : \Aimeos\Admin\JQAdm\Iface
	{
		/** admin/jqadm/feed/decorators/excludes
		 * Excludes decorators added by the "common" option from the feed JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "client/jqadm/common/decorators/default" before they are wrapped
		 * around the JQAdm client.
		 *
		 *  admin/jqadm/feed/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Admin\JQAdm\Common\Decorator\*") added via
		 * "client/jqadm/common/decorators/default" to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2026.01
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/feed/decorators/global
		 * @see admin/jqadm/feed/decorators/local
		 */

		/** admin/jqadm/feed/decorators/global
		 * Adds a list of globally available decorators only to the feed JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Admin\JQAdm\Common\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/feed/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Admin\JQAdm\Common\Decorator\Decorator1" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2026.01
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/feed/decorators/excludes
		 * @see admin/jqadm/feed/decorators/local
		 */

		/** admin/jqadm/feed/decorators/local
		 * Adds a list of local decorators only to the feed JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Admin\JQAdm\Feed\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/feed/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Admin\JQAdm\Feed\Decorator\Decorator2" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2026.01
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/feed/decorators/excludes
		 * @see admin/jqadm/feed/decorators/global
		 */
		return $this->createSubClient( 'feed/' . $type, $name );
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of JQAdm client names
	 */
	protected function getSubClientNames() : array
	{
		/** admin/jqadm/feed/subparts
		 * List of JQAdm sub-clients rendered within the feed section
		 *
		 * The output of the frontend is composed of the code generated by the JQAdm
		 * clients. Each JQAdm client can consist of several (or none) sub-clients
		 * that are responsible for rendering certain sub-parts of the output. The
		 * sub-clients can contain JQAdm clients themselves and therefore a
		 * hierarchical tree of JQAdm clients is composed. Each JQAdm client creates
		 * the output that is placed inside the container of its parent.
		 *
		 * At first, always the JQAdm code generated by the parent is printed, then
		 * the JQAdm code of its sub-clients. The order of the JQAdm sub-clients
		 * determines the order of the output of these sub-clients inside the parent
		 * container. If the configured list of clients is
		 *
		 *  array( "subclient1", "subclient2" )
		 *
		 * you can easily change the order of the output by reordering the subparts:
		 *
		 *  admin/jqadm/<clients>/subparts = array( "subclient1", "subclient2" )
		 *
		 * You can also remove one or more parts if they shouldn't be rendered:
		 *
		 *  admin/jqadm/<clients>/subparts = array( "subclient1" )
		 *
		 * As the clients only generates structural JQAdm, the layout defined via CSS
		 * should support adding, removing or reordering content by a fluid like
		 * design.
		 *
		 * @param array List of sub-client names
		 * @since 2026.01
		 */
		return $this->context()->config()->get( 'admin/jqadm/feed/subparts', [] );
	}


	/**
	 * Creates new and updates existing items using the data array
	 *
	 * @param array $data Data array
	 * @return \Aimeos\MShop\Feed\Item\Iface New or updated feed item object
	 */
	protected function fromArray( array $data ) : \Aimeos\MShop\Feed\Item\Iface
	{
		$manager = \Aimeos\MShop::create( $this->context(), 'feed' );

		if( !empty( $data['feed.id'] ) ) {
			$item = $manager->get( $data['feed.id'], ['catalog', 'product', 'supplier'] );
		} else {
			$item = $manager->create();
		}

		$data['feed.stock'] = $data['feed.stock'] ?? '0';
		$item->fromArray( $data, true );

		// Build attribute mapping and store it in config['attributes']
		$attrData = (array) ( $data['config']['attributes'] ?? [] );
		$attributes = array_column( $attrData, 'val', 'key' );
		$attributes = array_filter( array_map( fn( $v ) => trim( (string) $v ), $attributes ) );

		// Build attribute excludes and store in config['attribute_excludes']
		$exclData = (array) ( $data['config']['attribute_excludes'] ?? [] );
		$excludes = [];

		foreach( $exclData as $entry )
		{
			$type = trim( (string) ( $entry['attribute.type'] ?? '' ) );
			$id = trim( (string) ( $entry['attribute.id'] ?? '' ) );

			if( $type !== '' && $id !== '' ) {
				$excludes[] = ['type' => $type, 'id' => $id];
			}
		}

		$item->setConfig( ['attributes' => $attributes, 'attribute_excludes' => $excludes] );

		// Handle included and excluded categories (catalog domain)
		$this->fromArrayListItems( $item, 'catalog', array_values( $data['category']['include'] ?? [] ), 'include' );
		$this->fromArrayListItems( $item, 'catalog', array_values( $data['category']['exclude'] ?? [] ), 'exclude' );

		// Handle included and excluded products (product domain)
		$this->fromArrayListItems( $item, 'product', array_values( $data['product']['include'] ?? [] ), 'include' );
		$this->fromArrayListItems( $item, 'product', array_values( $data['product']['exclude'] ?? [] ), 'exclude' );

		// Handle included and excluded suppliers (supplier domain)
		$this->fromArrayListItems( $item, 'supplier', array_values( $data['supplier']['include'] ?? [] ), 'include' );
		$this->fromArrayListItems( $item, 'supplier', array_values( $data['supplier']['exclude'] ?? [] ), 'exclude' );

		return $item;
	}


	/**
	 * Updates list items of the given domain and type from posted data
	 *
	 * @param \Aimeos\MShop\Feed\Item\Iface $item Feed item
	 * @param string $domain Domain name (catalog or product)
	 * @param array $entries Posted entries for this domain/type combination
	 * @param string $type List item type (include or exclude)
	 */
	protected function fromArrayListItems( \Aimeos\MShop\Feed\Item\Iface $item, string $domain, array $entries, string $type ) : void
	{
		$context = $this->context();
		$listItems = $item->getListItems( $domain, $type );

		$refIds = array_filter( array_column( $entries, $domain . '.id' ) );
		$refItems = map();

		if( !empty( $refIds ) )
		{
			$refManager = \Aimeos\MShop::create( $context, $domain );
			$filter = $refManager->filter()->add( $domain . '.id', '==', $refIds )->slice( 0, count( $refIds ) );
			$refItems = $refManager->search( $filter );
		}

		$feedManager = \Aimeos\MShop::create( $context, 'feed' );

		foreach( $entries as $entry )
		{
			if( !( $refid = $this->val( $entry, $domain . '.id' ) ) ) {
				continue;
			}

			$listid = $this->val( $entry, 'feed.lists.id' );
			$litem = $listItems->pull( $listid ) ?: $feedManager->createListItem();
			$litem->setType( $type )->setRefId( $refid );

			$item->addListItem( $domain, $litem, $refItems->get( $refid ) );
		}

		$item->deleteListItems( $listItems );
	}


	/**
	 * Constructs the data array for the view from the given item
	 *
	 * @param \Aimeos\MShop\Feed\Item\Iface $item Feed item object
	 * @param bool $copy True if items should be copied, false if not
	 * @return array Multi-dimensional associative list of item data
	 */
	protected function toArray( \Aimeos\MShop\Feed\Item\Iface $item, bool $copy = false ) : array
	{
		$siteId = $this->context()->locale()->getSiteId();
		$data = $item->toArray( true );

		// Flatten attribute mapping for the config-table Vue component
		$config = $item->getConfig();
		$data['config'] = ['attributes' => $this->flatten( $config['attributes'] ?? [] )];

		// Flatten attribute excludes for the Vue component, resolve attribute labels
		$excludes = [];
		$attrIds = array_filter( array_column( $config['attribute_excludes'] ?? [], 'id' ) );

		if( !empty( $attrIds ) )
		{
			$attrManager = \Aimeos\MShop::create( $this->context(), 'attribute' );
			$filter = $attrManager->filter()->add( 'attribute.id', '==', $attrIds )->slice( 0, count( $attrIds ) );
			$attrItems = $attrManager->search( $filter );
		}

		foreach( $config['attribute_excludes'] ?? [] as $entry )
		{
			$id = $entry['id'] ?? '';
			$label = isset( $attrItems ) && ( $ref = $attrItems->get( $id ) ) ? $ref->getLabel() : '';

			$excludes[] = [
				'attribute.type' => $entry['type'] ?? '',
				'attribute.id' => $id,
				'attribute.label' => $label,
			];
		}
		$data['config']['attribute_excludes'] = $excludes;

		// Build category list data (included and excluded)
		$includeCategories = [];
		$excludeCategories = [];

		foreach( $item->getListItems( 'catalog' ) as $listItem )
		{
			if( ( $refItem = $listItem->getRefItem() ) === null ) {
				continue;
			}

			$entry = [
				'feed.lists.id'     => $copy ? '' : $listItem->getId(),
				'feed.lists.type'   => $listItem->getType(),
				'feed.lists.siteid' => $copy ? $siteId : $listItem->getSiteId(),
				'catalog.id'        => $refItem->getId(),
				'catalog.label'     => $refItem->getLabel() . ' (' . $refItem->getCode() . ')',
				'catalog.code'      => $refItem->getCode(),
			];

			if( $listItem->getType() === 'include' ) {
				$includeCategories[] = $entry;
			} else {
				$excludeCategories[] = $entry;
			}
		}

		// Build product list data (included and excluded)
		$includeProducts = [];
		$excludeProducts = [];

		foreach( $item->getListItems( 'product' ) as $listItem )
		{
			if( ( $refItem = $listItem->getRefItem() ) === null ) {
				continue;
			}

			$entry = [
				'feed.lists.id'     => $copy ? '' : $listItem->getId(),
				'feed.lists.type'   => $listItem->getType(),
				'feed.lists.siteid' => $copy ? $siteId : $listItem->getSiteId(),
				'product.id'        => $refItem->getId(),
				'product.label'     => $refItem->getLabel() . ' (' . $refItem->getCode() . ')',
				'product.code'      => $refItem->getCode(),
			];

			if( $listItem->getType() === 'include' ) {
				$includeProducts[] = $entry;
			} else {
				$excludeProducts[] = $entry;
			}
		}

		// Build supplier list data (included and excluded)
		$includeSuppliers = [];
		$excludeSuppliers = [];

		foreach( $item->getListItems( 'supplier' ) as $listItem )
		{
			if( ( $refItem = $listItem->getRefItem() ) === null ) {
				continue;
			}

			$entry = [
				'feed.lists.id'     => $copy ? '' : $listItem->getId(),
				'feed.lists.type'   => $listItem->getType(),
				'feed.lists.siteid' => $copy ? $siteId : $listItem->getSiteId(),
				'supplier.id'       => $refItem->getId(),
				'supplier.label'    => $refItem->getLabel() . ' (' . $refItem->getCode() . ')',
				'supplier.code'     => $refItem->getCode(),
			];

			if( $listItem->getType() === 'include' ) {
				$includeSuppliers[] = $entry;
			} else {
				$excludeSuppliers[] = $entry;
			}
		}

		$data['category']['include'] = $includeCategories;
		$data['category']['exclude'] = $excludeCategories;
		$data['product']['include'] = $includeProducts;
		$data['product']['exclude'] = $excludeProducts;
		$data['supplier']['include'] = $includeSuppliers;
		$data['supplier']['exclude'] = $excludeSuppliers;

		if( $copy === true )
		{
			$data['feed.id'] = '';
			$data['feed.siteid'] = $siteId;
			$data['feed.ctime'] = '';
		}

		return $data;
	}


	/**
	 * Returns the rendered template including the view data
	 *
	 * @param \Aimeos\Base\View\Iface $view View object with data assigned
	 * @return string HTML output
	 */
	protected function render( \Aimeos\Base\View\Iface $view ) : string
	{
		/** admin/jqadm/feed/template-item
		 * Relative path to the HTML body template for the feed item.
		 *
		 * @param string Relative path to the template creating the HTML code
		 * @since 2026.01
		 */
		$tplconf = 'admin/jqadm/feed/template-item';
		$default = 'feed/item';

		return $view->render( $view->config( $tplconf, $default ) );
	}
}
