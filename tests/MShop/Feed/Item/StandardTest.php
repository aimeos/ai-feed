<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\MShop\Feed\Item;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $values;


	protected function setUp() : void
	{
		$this->values = [
			'feed.id' => 1,
			'feed.siteid' => '1.',
			'feed.label' => 'test-feed',
			'feed.type' => 'google',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => true,
			'feed.status' => 1,
			'feed.config' => ['format' => 'csv'],
			'feed.ctime' => '2026-01-01 00:00:00',
			'feed.mtime' => '2026-01-02 00:00:00',
			'feed.editor' => 'unittest',
			'additional' => 'value',
		];

		$this->object = new \Aimeos\MShop\Feed\Item\Standard( 'feed.', $this->values );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->values );
	}


	public function testGetId()
	{
		$this->assertEquals( '1', $this->object->getId() );
	}


	public function testSetId()
	{
		$return = $this->object->setId( null );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertNull( $this->object->getId() );
		$this->assertTrue( $this->object->isModified() );

		$return = $this->object->setId( 1 );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertEquals( '1', $this->object->getId() );
		$this->assertFalse( $this->object->isModified() );
	}


	public function testGetSiteId()
	{
		$this->assertEquals( '1.', $this->object->getSiteId() );
	}


	public function testGetLabel()
	{
		$this->assertEquals( 'test-feed', $this->object->getLabel() );
	}


	public function testSetLabel()
	{
		$this->assertFalse( $this->object->isModified() );

		$return = $this->object->setLabel( 'other-feed' );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertEquals( 'other-feed', $this->object->getLabel() );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testGetType()
	{
		$this->assertEquals( 'google', $this->object->getType() );
	}


	public function testSetType()
	{
		$this->assertFalse( $this->object->isModified() );

		$return = $this->object->setType( 'idealo' );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertEquals( 'idealo', $this->object->getType() );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testGetLanguageId()
	{
		$this->assertEquals( 'en', $this->object->getLanguageId() );
	}


	public function testSetLanguageId()
	{
		$this->assertFalse( $this->object->isModified() );

		$return = $this->object->setLanguageId( 'de' );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertEquals( 'de', $this->object->getLanguageId() );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testSetLanguageIdNull()
	{
		$return = $this->object->setLanguageId( null );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertNull( $this->object->getLanguageId() );
	}


	public function testGetCurrencyId()
	{
		$this->assertEquals( 'EUR', $this->object->getCurrencyId() );
	}


	public function testSetCurrencyId()
	{
		$this->assertFalse( $this->object->isModified() );

		$return = $this->object->setCurrencyId( 'USD' );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertEquals( 'USD', $this->object->getCurrencyId() );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testSetCurrencyIdNull()
	{
		$return = $this->object->setCurrencyId( null );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertNull( $this->object->getCurrencyId() );
	}


	public function testGetStock()
	{
		$this->assertTrue( $this->object->getStock() );
	}


	public function testSetStock()
	{
		$this->assertFalse( $this->object->isModified() );

		$return = $this->object->setStock( false );

		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $return );
		$this->assertFalse( $this->object->getStock() );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testGetStatus()
	{
		$this->assertEquals( 1, $this->object->getStatus() );
	}


	public function testSetStatus()
	{
		$return = $this->object->setStatus( 0 );

		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $return );
		$this->assertEquals( 0, $this->object->getStatus() );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testGetConfig()
	{
		$this->assertEquals( ['format' => 'csv'], $this->object->getConfig() );
	}


	public function testGetConfigValue()
	{
		$this->assertEquals( 'csv', $this->object->getConfigValue( 'format' ) );
	}


	public function testSetConfig()
	{
		$this->assertFalse( $this->object->isModified() );

		$return = $this->object->setConfig( ['key' => 'value'] );

		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $return );
		$this->assertEquals( ['key' => 'value'], $this->object->getConfig() );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testGetTimeCreated()
	{
		$this->assertEquals( '2026-01-01 00:00:00', $this->object->getTimeCreated() );
	}


	public function testGetTimeModified()
	{
		$this->assertEquals( '2026-01-02 00:00:00', $this->object->getTimeModified() );
	}


	public function testGetEditor()
	{
		$this->assertEquals( 'unittest', $this->object->editor() );
	}


	public function testGetResourceType()
	{
		$this->assertEquals( 'feed', $this->object->getResourceType() );
	}


	public function testIsAvailable()
	{
		$this->assertTrue( $this->object->isAvailable() );
		$this->object->setAvailable( false );
		$this->assertFalse( $this->object->isAvailable() );
	}


	public function testIsAvailableOnStatus()
	{
		$this->assertTrue( $this->object->isAvailable() );

		$this->object->setStatus( 0 );
		$this->assertFalse( $this->object->isAvailable() );

		$this->object->setStatus( -1 );
		$this->assertFalse( $this->object->isAvailable() );
	}


	public function testIsModified()
	{
		$this->assertFalse( $this->object->isModified() );
	}


	public function testIsModifiedTrue()
	{
		$this->object->setLabel( 'changed-feed' );
		$this->assertTrue( $this->object->isModified() );
	}


	public function testFromArray()
	{
		$item = new \Aimeos\MShop\Feed\Item\Standard( 'feed.' );

		$list = $entries = [
			'feed.id' => 1,
			'feed.label' => 'test-feed',
			'feed.type' => 'google',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => true,
			'feed.status' => 1,
			'feed.config' => ['format' => 'csv'],
			'additional' => 'value',
		];

		$item = $item->fromArray( $entries, true );

		$this->assertEquals( ['additional' => 'value'], $entries );
		$this->assertEquals( $list['feed.id'], $item->getId() );
		$this->assertEquals( $list['feed.label'], $item->getLabel() );
		$this->assertEquals( $list['feed.type'], $item->getType() );
		$this->assertEquals( $list['feed.languageid'], $item->getLanguageId() );
		$this->assertEquals( $list['feed.currencyid'], $item->getCurrencyId() );
		$this->assertEquals( $list['feed.stock'], $item->getStock() );
		$this->assertEquals( $list['feed.status'], $item->getStatus() );
		$this->assertEquals( $list['feed.config'], $item->getConfig() );
	}


	public function testToArray()
	{
		$arrayObject = $this->object->toArray( true );
		$this->assertEquals( count( $this->values ), count( $arrayObject ) );

		$this->assertEquals( $this->object->getId(), $arrayObject['feed.id'] );
		$this->assertEquals( $this->object->getSiteId(), $arrayObject['feed.siteid'] );
		$this->assertEquals( $this->object->getLabel(), $arrayObject['feed.label'] );
		$this->assertEquals( $this->object->getType(), $arrayObject['feed.type'] );
		$this->assertEquals( $this->object->getLanguageId(), $arrayObject['feed.languageid'] );
		$this->assertEquals( $this->object->getCurrencyId(), $arrayObject['feed.currencyid'] );
		$this->assertEquals( $this->object->getStock(), $arrayObject['feed.stock'] );
		$this->assertEquals( $this->object->getStatus(), $arrayObject['feed.status'] );
		$this->assertEquals( $this->object->getConfig(), $arrayObject['feed.config'] );
		$this->assertEquals( $this->object->getTimeCreated(), $arrayObject['feed.ctime'] );
		$this->assertEquals( $this->object->getTimeModified(), $arrayObject['feed.mtime'] );
		$this->assertEquals( $this->object->editor(), $arrayObject['feed.editor'] );
	}
}
