<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 * @package MShop
 * @subpackage Feed
 */


namespace Aimeos\MShop\Feed\Item;

use \Aimeos\MShop\Common\Item\Config;
use \Aimeos\MShop\Common\Item\ListsRef;
use \Aimeos\MShop\Common\Item\TypeRef;


/**
 * Default implementation of a feed item.
 *
 * @package MShop
 * @subpackage Feed
 */
class Standard
	extends \Aimeos\MShop\Common\Item\Base
	implements \Aimeos\MShop\Feed\Item\Iface
{
	use Config\Traits, ListsRef\Traits, TypeRef\Traits {
		ListsRef\Traits::__clone as __cloneList;
	}


	/**
	 * Initializes the feed item.
	 *
	 * @param string $prefix Domain specific prefix string
	 * @param array $values Parameter for initializing the basic properties
	 */
	public function __construct( string $prefix, array $values = [] )
	{
		parent::__construct( $prefix, $values );

		$this->initListItems( $values['.listitems'] ?? [] );
	}


	/**
	 * Creates a deep clone of all objects
	 */
	public function __clone()
	{
		parent::__clone();
		$this->__cloneList();
	}


	/**
	 * Returns the label of the feed item.
	 *
	 * @return string Label of the feed item
	 */
	public function getLabel() : string
	{
		return (string) $this->get( 'feed.label', '' );
	}


	/**
	 * Sets the label of the feed item.
	 *
	 * @param string $label Label of the feed item
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setLabel( string $label ) : \Aimeos\MShop\Feed\Item\Iface
	{
		return $this->set( 'feed.label', $label );
	}


	/**
	 * Returns the language ID of the feed item.
	 *
	 * @return string|null ISO language code or null for all languages
	 */
	public function getLanguageId() : ?string
	{
		return $this->get( 'feed.languageid' );
	}


	/**
	 * Sets the language ID of the feed item.
	 *
	 * @param string|null $langid ISO language code or null for all languages
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setLanguageId( ?string $langid ) : \Aimeos\MShop\Feed\Item\Iface
	{
		return $this->set( 'feed.languageid', $langid );
	}


	/**
	 * Returns the currency ID of the feed item.
	 *
	 * @return string|null Three letter ISO currency code or null for all currencies
	 */
	public function getCurrencyId() : ?string
	{
		return $this->get( 'feed.currencyid' );
	}


	/**
	 * Sets the currency ID of the feed item.
	 *
	 * @param string|null $currencyid Three letter ISO currency code or null for all currencies
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setCurrencyId( ?string $currencyid ) : \Aimeos\MShop\Feed\Item\Iface
	{
		return $this->set( 'feed.currencyid', $currencyid );
	}


	/**
	 * Returns whether the feed is limited to in-stock products.
	 *
	 * @return bool TRUE to include only in-stock products, FALSE for all products
	 */
	public function getStock() : bool
	{
		return (bool) $this->get( 'feed.stock', false );
	}


	/**
	 * Sets whether the feed is limited to in-stock products.
	 *
	 * @param bool $stock TRUE to include only in-stock products, FALSE for all products
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setStock( bool $stock ) : \Aimeos\MShop\Feed\Item\Iface
	{
		return $this->set( 'feed.stock', $stock );
	}


	/**
	 * Returns the status of the feed item.
	 *
	 * @return int Status of the feed item
	 */
	public function getStatus() : int
	{
		return (int) $this->get( 'feed.status', 1 );
	}


	/**
	 * Sets the new status of the feed item.
	 *
	 * @param int $status New status of the feed item
	 * @return \Aimeos\MShop\Common\Item\Iface Feed item for chaining method calls
	 */
	public function setStatus( int $status ) : \Aimeos\MShop\Common\Item\Iface
	{
		return $this->set( 'feed.status', $status );
	}


	/**
	 * Returns the configuration values of the item
	 *
	 * @return array Configuration values
	 */
	public function getConfig() : array
	{
		return $this->get( 'feed.config', [] );
	}


	/**
	 * Sets the configuration values of the item.
	 *
	 * @param array $config Configuration values
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setConfig( array $config ) : \Aimeos\MShop\Common\Item\Iface
	{
		if( !$this->compareConfig( $this->getConfig(), $config ) ) {
			$this->set( 'feed.config', $config );
		}

		return $this;
	}


	/**
	 * Tests if the item is available based on status, time, language and currency
	 *
	 * @return bool True if available, false if not
	 */
	public function isAvailable() : bool
	{
		return parent::isAvailable() && $this->getStatus() > 0;
	}


	/*
	 * Sets the item values from the given array and removes that entries from the list
	 *
	 * @param array &$list Associative list of item keys and their values
	 * @param bool True to set private properties too, false for public only
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function fromArray( array &$list, bool $private = false ) : \Aimeos\MShop\Common\Item\Iface
	{
		$item = parent::fromArray( $list, $private );

		foreach( $list as $key => $value )
		{
			switch( $key )
			{
				case 'feed.label': $item->setLabel( $value ); break;
				case 'feed.type': $item->setType( $value ); break;
				case 'feed.languageid': $item->setLanguageId( $value ); break;
				case 'feed.currencyid': $item->setCurrencyId( $value ); break;
				case 'feed.stock': $item->setStock( (bool) $value ); break;
				case 'feed.status': $item->setStatus( (int) $value ); break;
				case 'feed.config': $item->setConfig( $value ); break;
				default: continue 2;
			}

			unset( $list[$key] );
		}

		return $item;
	}


	/**
	 * Returns the item values as array.
	 *
	 * @param bool True to return private properties, false for public only
	 * @return array Associative list of item properties and their values
	 */
	public function toArray( bool $private = false ) : array
	{
		$list = parent::toArray( $private );

		$list['feed.label'] = $this->getLabel();
		$list['feed.type'] = $this->getType();
		$list['feed.languageid'] = $this->getLanguageId();
		$list['feed.currencyid'] = $this->getCurrencyId();
		$list['feed.stock'] = $this->getStock();
		$list['feed.status'] = $this->getStatus();
		$list['feed.config'] = $this->getConfig();

		return $list;
	}
}
