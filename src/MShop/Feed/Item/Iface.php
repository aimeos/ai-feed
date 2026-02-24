<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 * @package MShop
 * @subpackage Feed
 */


namespace Aimeos\MShop\Feed\Item;


/**
 * Generic interface for feed items.
 *
 * @package MShop
 * @subpackage Feed
 */
interface Iface
	extends \Aimeos\MShop\Common\Item\Iface, \Aimeos\MShop\Common\Item\Config\Iface,
		\Aimeos\MShop\Common\Item\ListsRef\Iface, \Aimeos\MShop\Common\Item\Status\Iface,
		\Aimeos\MShop\Common\Item\TypeRef\Iface
{
	/**
	 * Returns the label of the feed item.
	 *
	 * @return string Label of the feed item
	 */
	public function getLabel() : string;

	/**
	 * Sets the label of the feed item.
	 *
	 * @param string $label Label of the feed item
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setLabel( string $label ) : \Aimeos\MShop\Feed\Item\Iface;

	/**
	 * Returns the language ID of the feed item.
	 *
	 * @return string|null ISO language code (e.g. "en" or "en_US") or null for all languages
	 */
	public function getLanguageId() : ?string;

	/**
	 * Sets the language ID of the feed item.
	 *
	 * @param string|null $langid ISO language code (e.g. "en" or "en_US") or null for all languages
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setLanguageId( ?string $langid ) : \Aimeos\MShop\Feed\Item\Iface;

	/**
	 * Returns the currency ID of the feed item.
	 *
	 * @return string|null Three letter ISO currency code (e.g. "EUR") or null for all currencies
	 */
	public function getCurrencyId() : ?string;

	/**
	 * Sets the currency ID of the feed item.
	 *
	 * @param string|null $currencyid Three letter ISO currency code (e.g. "EUR") or null for all currencies
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setCurrencyId( ?string $currencyid ) : \Aimeos\MShop\Feed\Item\Iface;

	/**
	 * Returns whether the feed should be limited to in-stock products.
	 *
	 * @return bool TRUE to include only in-stock products, FALSE for all products
	 */
	public function getStock() : bool;

	/**
	 * Sets whether the feed should be limited to in-stock products.
	 *
	 * @param bool $stock TRUE to include only in-stock products, FALSE for all products
	 * @return \Aimeos\MShop\Feed\Item\Iface Feed item for chaining method calls
	 */
	public function setStock( bool $stock ) : \Aimeos\MShop\Feed\Item\Iface;
}
