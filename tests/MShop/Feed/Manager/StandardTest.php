<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\MShop\Feed\Manager;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;


	protected function setUp() : void
	{
		$this->context = \TestHelper::context();

		$this->object = new \Aimeos\MShop\Feed\Manager\Standard( $this->context );
		$this->object = new \Aimeos\MShop\Common\Manager\Decorator\Lists( $this->object, $this->context );
		$this->object = new \Aimeos\MShop\Common\Manager\Decorator\Site( $this->object, $this->context );
		$this->object->setObject( $this->object );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context );
	}


	public function testClear()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Manager\Iface::class, $this->object->clear( [-1] ) );
	}


	public function testDelete()
	{
		$item = ( new \Aimeos\MShop\Feed\Item\Standard( 'feed.' ) )->setId( -1 );

		$this->assertInstanceOf( \Aimeos\MShop\Common\Manager\Iface::class, $this->object->delete( [-1] ) );
		$this->assertInstanceOf( \Aimeos\MShop\Common\Manager\Iface::class, $this->object->delete( [$item] ) );
	}


	public function testCreate()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Feed\Item\Iface::class, $this->object->create() );
	}


	public function testCreateType()
	{
		$item = $this->object->create( ['feed.type' => 'google'] );
		$this->assertEquals( 'google', $item->getType() );
	}


	public function testCreateListItem()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Lists\Iface::class, $this->object->createListItem() );
	}


	public function testFilter()
	{
		$search = $this->object->filter( true );
		$this->assertInstanceOf( \Aimeos\Base\Criteria\SQL::class, $search );
	}


	public function testGetSearchAttributes()
	{
		foreach( $this->object->getSearchAttributes() as $attribute ) {
			$this->assertInstanceOf( \Aimeos\Base\Criteria\Attribute\Iface::class, $attribute );
		}
	}


	public function testGet()
	{
		$expected = $this->object->search( $this->object->filter() )->first();

		$this->assertEquals( $expected, $this->object->get( $expected?->getId() ) );
	}


	public function testSave()
	{
		$search = $this->object->filter();
		$search->setConditions( $search->compare( '==', 'feed.label', 'google-en' ) );
		$items = $this->object->search( $search )->toArray();

		$this->assertTrue( is_map( $this->object->save( $items ) ) );
	}


	public function testSaveUpdateDelete()
	{
		$item = $this->object->search( $this->object->filter()->add( 'feed.label', '==', 'google-en' ) )->first();

		$item->setId( null );
		$item->setLabel( 'google-test' );
		$resultSaved = $this->object->save( $item );
		$itemSaved = $this->object->get( $item->getId() );

		$itemExp = clone $itemSaved;
		$itemExp->setLabel( 'google-updated' );
		$resultUpd = $this->object->save( $itemExp );
		$itemUpd = $this->object->get( $itemExp->getId() );

		$this->object->delete( $itemUpd );

		$this->assertNotNull( $item->getId() );
		$this->assertNotNull( $itemSaved->getType() );
		$this->assertEquals( $item->getId(), $itemSaved->getId() );
		$this->assertEquals( $item->getSiteId(), $itemSaved->getSiteId() );
		$this->assertEquals( $item->getLabel(), $itemSaved->getLabel() );
		$this->assertEquals( $item->getType(), $itemSaved->getType() );
		$this->assertEquals( $item->getLanguageId(), $itemSaved->getLanguageId() );
		$this->assertEquals( $item->getCurrencyId(), $itemSaved->getCurrencyId() );
		$this->assertEquals( $item->getStock(), $itemSaved->getStock() );
		$this->assertEquals( $item->getStatus(), $itemSaved->getStatus() );
		$this->assertEquals( $item->getConfig(), $itemSaved->getConfig() );

		$this->assertEquals( $this->context->editor(), $itemSaved->editor() );
		$this->assertMatchesRegularExpression( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeCreated() );
		$this->assertMatchesRegularExpression( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemSaved->getTimeModified() );

		$this->assertEquals( $itemExp->getId(), $itemUpd->getId() );
		$this->assertEquals( $itemExp->getLabel(), $itemUpd->getLabel() );
		$this->assertEquals( $itemExp->getTimeCreated(), $itemUpd->getTimeCreated() );
		$this->assertMatchesRegularExpression( '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $itemUpd->getTimeModified() );

		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $resultSaved );
		$this->assertInstanceOf( \Aimeos\MShop\Common\Item\Iface::class, $resultUpd );

		$this->expectException( \Aimeos\MShop\Exception::class );
		$this->object->get( $itemSaved->getId() );
	}


	public function testSaveRefItems()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'feed' );

		$item = $manager->create()->setLabel( 'feed-listtest' )->setType( 'google' );

		$listManager = $manager->getSubManager( 'lists' );
		$listItem = $listManager->create()->setType( 'default' );

		$textManager = \Aimeos\MShop::create( $this->context, 'text' );
		$textItem = $textManager->create()->setType( 'name' );

		$item->addListItem( 'text', $listItem, $textItem );

		$item = $manager->save( $item );
		$item2 = $manager->get( $item->getId(), ['text'] );

		$item->deleteListItem( 'text', $listItem, $textItem );

		$item = $manager->save( $item );
		$item3 = $manager->get( $item->getId(), ['text'] );

		$manager->delete( $item->getId() );

		$this->assertEquals( 0, count( $item->getRefItems( 'text', 'name', 'default', false ) ) );
		$this->assertEquals( 1, count( $item2->getRefItems( 'text', 'name', 'default', false ) ) );
		$this->assertEquals( 0, count( $item3->getRefItems( 'text', 'name', 'default', false ) ) );
	}


	public function testSearch()
	{
		$total = 0;
		$search = $this->object->filter();

		$expr = [];
		$expr[] = $search->compare( '!=', 'feed.id', null );
		$expr[] = $search->compare( '!=', 'feed.siteid', null );
		$expr[] = $search->compare( '==', 'feed.label', 'google-en' );
		$expr[] = $search->compare( '==', 'feed.type', 'google' );
		$expr[] = $search->compare( '==', 'feed.languageid', 'en' );
		$expr[] = $search->compare( '==', 'feed.currencyid', 'EUR' );
		$expr[] = $search->compare( '==', 'feed.stock', true );
		$expr[] = $search->compare( '==', 'feed.status', 1 );
		$expr[] = $search->compare( '>=', 'feed.ctime', '1970-01-01 00:00:00' );
		$expr[] = $search->compare( '-', 'feed.mtime', '1970-01-01 00:00 - 2100-01-01 00:00' );
		$expr[] = $search->compare( '>=', 'feed.editor', '' );

		$search->setConditions( $search->and( $expr ) );
		$results = $this->object->search( $search, [], $total );

		$this->assertEquals( 1, count( $results ) );
		$this->assertEquals( 1, $total );

		if( ( $item = $results->first() ) === null ) {
			throw new \RuntimeException( 'No feed item "google-en" found' );
		}

		$this->assertEquals( $results->firstKey(), $item->getId() );
		$this->assertEquals( 'google-en', $item->getLabel() );
		$this->assertEquals( 'google', $item->getType() );
		$this->assertEquals( 'en', $item->getLanguageId() );
		$this->assertEquals( 'EUR', $item->getCurrencyId() );
		$this->assertTrue( $item->getStock() );
		$this->assertEquals( 1, $item->getStatus() );
		$this->assertEquals( ['format' => 'csv'], $item->getConfig() );
	}


	public function testSearchAll()
	{
		$total = 0;
		$search = $this->object->filter()->slice( 0, 10 );
		$results = $this->object->search( $search, [], $total );

		$this->assertEquals( 3, count( $results ) );
		$this->assertEquals( 3, $total );
	}


	public function testSearchBase()
	{
		$search = $this->object->filter( true );
		$expr = [
			$search->compare( '==', 'feed.label', ['google-en', 'idealo-de'] ),
			$search->getConditions(),
		];
		$search->setConditions( $search->and( $expr ) );
		$result = $this->object->search( $search );

		$this->assertEquals( 2, count( $result ) );
	}


	public function testGetSubManager()
	{
		$this->assertInstanceOf( \Aimeos\MShop\Common\Manager\Iface::class, $this->object->getSubManager( 'lists' ) );
		$this->assertInstanceOf( \Aimeos\MShop\Common\Manager\Iface::class, $this->object->getSubManager( 'lists', 'Standard' ) );

		$this->expectException( \LogicException::class );
		$this->object->getSubManager( 'unknown' );
	}


	public function testGetSubManagerInvalidName()
	{
		$this->expectException( \LogicException::class );
		$this->object->getSubManager( 'lists', 'unknown' );
	}


	public function testType()
	{
		$this->assertEquals( ['feed'], array_values( $this->object->type() ) );
	}
}
