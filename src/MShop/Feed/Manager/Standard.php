<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 * @package MShop
 * @subpackage Feed
 */


namespace Aimeos\MShop\Feed\Manager;


/**
 * Default feed manager.
 *
 * @package MShop
 * @subpackage Feed
 */
class Standard
	extends \Aimeos\MShop\Common\Manager\Base
	implements \Aimeos\MShop\Feed\Manager\Iface, \Aimeos\MShop\Common\Manager\Factory\Iface
{
	/**
	 * Creates a new empty item instance
	 *
	 * @param array $values Values the item should be initialized with
	 * @return \Aimeos\MShop\Feed\Item\Iface New feed item object
	 */
	public function create( array $values = [] ) : \Aimeos\MShop\Common\Item\Iface
	{
		$values['feed.siteid'] = $values['feed.siteid'] ?? $this->context()->locale()->getSiteId();

		return new \Aimeos\MShop\Feed\Item\Standard( 'feed.', $values );
	}


	/**
	 * Creates a filter object.
	 *
	 * @param bool|null $default Add default criteria or NULL for relaxed default criteria
	 * @param bool $site TRUE for adding site criteria to limit items by the site of related items
	 * @return \Aimeos\Base\Criteria\Iface Returns the filter object
	 */
	public function filter( ?bool $default = false, bool $site = false ) : \Aimeos\Base\Criteria\Iface
	{
		return $this->filterBase( 'feed', $default );
	}


	/**
	 * Returns the additional column/search definitions
	 *
	 * @return array Associative list of column names as keys and items implementing \Aimeos\Base\Criteria\Attribute\Iface
	 */
	public function getSaveAttributes() : array
	{
		return $this->createAttributes( [
			'feed.label' => [
				'label' => 'Label',
				'internalcode' => 'label',
			],
			'feed.type' => [
				'label' => 'Type',
				'internalcode' => 'type',
			],
			'feed.languageid' => [
				'label' => 'Language ID',
				'internalcode' => 'langid',
			],
			'feed.currencyid' => [
				'label' => 'Currency ID',
				'internalcode' => 'currencyid',
			],
			'feed.stock' => [
				'label' => 'Stock filter',
				'internalcode' => 'stock',
				'type' => 'bool',
			],
			'feed.status' => [
				'label' => 'Status',
				'internalcode' => 'status',
				'type' => 'int',
			],
			'feed.config' => [
				'label' => 'Configuration',
				'internalcode' => 'config',
				'type' => 'json',
				'public' => false,
			],
		] );
	}


	/**
	 * Returns the prefix for the item properties and search keys.
	 *
	 * @return string Prefix for the item properties and search keys
	 */
	protected function prefix() : string
	{
		return 'feed.';
	}


	/** mshop/feed/manager/resource
	 * Name of the database connection resource to use
	 *
	 * You can configure a different database connection for each data domain
	 * and if no such connection name exists, the "db" connection will be used.
	 * It's also possible to use the same database connection for different
	 * data domains by configuring the same connection name using this setting.
	 *
	 * @param string Database connection name
	 * @since 2026.01
	 */

	/** mshop/feed/manager/name
	 * Class name of the used feed manager implementation
	 *
	 * Each default manager can be replace by an alternative imlementation.
	 * To use this implementation, you have to set the last part of the class
	 * name as configuration value so the manager factory knows which class it
	 * has to instantiate.
	 *
	 * For example, if the name of the default class is
	 *
	 *  \Aimeos\MShop\Feed\Manager\Standard
	 *
	 * and you want to replace it with your own version named
	 *
	 *  \Aimeos\MShop\Feed\Manager\Mymanager
	 *
	 * then you have to set the this configuration option:
	 *
	 *  mshop/feed/manager/name = Mymanager
	 *
	 * The value is the last part of your own class name and it's case sensitive,
	 * so take care that the configuration value is exactly named like the last
	 * part of the class name.
	 *
	 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
	 * characters are possible! You should always start the last part of the class
	 * name with an upper case character and continue only with lower case characters
	 * or numbers. Avoid chamel case names like "MyManager"!
	 *
	 * @param string Last part of the class name
	 * @since 2026.01
	 */

	/** mshop/feed/manager/decorators/excludes
	 * Excludes decorators added by the "common" option from the feed manager
	 *
	 * @param array List of decorator names
	 * @since 2026.01
	 * @see mshop/common/manager/decorators/default
	 * @see mshop/feed/manager/decorators/global
	 * @see mshop/feed/manager/decorators/local
	 */

	/** mshop/feed/manager/decorators/global
	 * Adds a list of globally available decorators only to the feed manager
	 *
	 * @param array List of decorator names
	 * @since 2026.01
	 * @see mshop/common/manager/decorators/default
	 * @see mshop/feed/manager/decorators/excludes
	 * @see mshop/feed/manager/decorators/local
	 */

	/** mshop/feed/manager/decorators/local
	 * Adds a list of local decorators only to the feed manager
	 *
	 * @param array List of decorator names
	 * @since 2026.01
	 * @see mshop/common/manager/decorators/default
	 * @see mshop/feed/manager/decorators/excludes
	 * @see mshop/feed/manager/decorators/global
	 */

	/** mshop/feed/manager/submanagers
	 * List of manager names that can be instantiated by the feed manager
	 *
	 * @param array List of sub-manager names
	 * @since 2026.01
	 */

	/** mshop/feed/manager/delete/ansi
	 * Deletes the items matched by the given IDs from the database
	 *
	 * @param string SQL statement for deleting items
	 * @since 2026.01
	 * @see mshop/feed/manager/insert/ansi
	 * @see mshop/feed/manager/update/ansi
	 * @see mshop/feed/manager/newid/ansi
	 * @see mshop/feed/manager/search/ansi
	 * @see mshop/feed/manager/count/ansi
	 */

	/** mshop/feed/manager/insert/ansi
	 * Inserts a new feed record into the database table
	 *
	 * @param string SQL statement for inserting records
	 * @since 2026.01
	 * @see mshop/feed/manager/update/ansi
	 * @see mshop/feed/manager/newid/ansi
	 * @see mshop/feed/manager/delete/ansi
	 * @see mshop/feed/manager/search/ansi
	 * @see mshop/feed/manager/count/ansi
	 */

	/** mshop/feed/manager/update/ansi
	 * Updates an existing feed record in the database
	 *
	 * @param string SQL statement for updating records
	 * @since 2026.01
	 * @see mshop/feed/manager/insert/ansi
	 * @see mshop/feed/manager/newid/ansi
	 * @see mshop/feed/manager/delete/ansi
	 * @see mshop/feed/manager/search/ansi
	 * @see mshop/feed/manager/count/ansi
	 */

	/** mshop/feed/manager/newid/ansi
	 * Retrieves the ID generated by the database when inserting a new record
	 *
	 * @param string SQL statement for retrieving the last inserted record ID
	 * @since 2026.01
	 * @see mshop/feed/manager/insert/ansi
	 * @see mshop/feed/manager/update/ansi
	 * @see mshop/feed/manager/delete/ansi
	 * @see mshop/feed/manager/search/ansi
	 * @see mshop/feed/manager/count/ansi
	 */

	/** mshop/feed/manager/sitemode
	 * Mode how items from levels below or above in the site tree are handled
	 *
	 * @param int Constant from Aimeos\MShop\Locale\Manager\Base class
	 * @since 2026.01
	 * @see mshop/locale/manager/sitelevel
	 */

	/** mshop/feed/manager/search/ansi
	 * Retrieves the records matched by the given criteria in the database
	 *
	 * @param string SQL statement for searching items
	 * @since 2026.01
	 * @see mshop/feed/manager/insert/ansi
	 * @see mshop/feed/manager/update/ansi
	 * @see mshop/feed/manager/newid/ansi
	 * @see mshop/feed/manager/delete/ansi
	 * @see mshop/feed/manager/count/ansi
	 */

	/** mshop/feed/manager/count/ansi
	 * Counts the number of records matched by the given criteria in the database
	 *
	 * @param string SQL statement for counting items
	 * @since 2026.01
	 * @see mshop/feed/manager/insert/ansi
	 * @see mshop/feed/manager/update/ansi
	 * @see mshop/feed/manager/newid/ansi
	 * @see mshop/feed/manager/delete/ansi
	 * @see mshop/feed/manager/search/ansi
	 */
}
