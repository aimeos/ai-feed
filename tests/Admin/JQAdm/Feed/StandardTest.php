<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Admin\JQAdm\Feed;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $view;


	protected function setUp() : void
	{
		$this->view = \TestHelper::jqadmView();
		$this->context = \TestHelper::context();

		$this->object = new \Aimeos\Admin\JQAdm\Feed\Standard( $this->context );
		$this->object = new \Aimeos\Admin\JQAdm\Common\Decorator\Page( $this->object, $this->context );
		$this->object->setAimeos( \TestHelper::getAimeos() );
		$this->object->setView( $this->view );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->view, $this->context );
	}


	public function testCreate()
	{
		$result = $this->object->create();

		$this->assertStringContainsString( 'feed', $result );
		$this->assertEmpty( $this->view->get( 'errors' ) );
	}


	public function testCreateException()
	{
		$object = $this->getClientMock( 'getSubClients' );

		$object->expects( $this->once() )->method( 'getSubClients' )
			->will( $this->throwException( new \RuntimeException() ) );

		$object->create();
	}


	public function testCopy()
	{
		$param = ['site' => 'unittest', 'id' => $this->getItem()->getId()];
		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );

		$result = $this->object->copy();

		$this->assertStringContainsString( 'google-include', $result );
		$this->assertEmpty( $this->view->get( 'errors' ) );
	}


	public function testCopyException()
	{
		$object = $this->getClientMock( 'getSubClients' );

		$object->expects( $this->once() )->method( 'getSubClients' )
			->will( $this->throwException( new \RuntimeException() ) );

		$object->copy();
	}


	public function testDelete()
	{
		$this->assertNull( $this->getClientMock( ['redirect'], false )->delete() );
	}


	public function testDeleteException()
	{
		$object = $this->getClientMock( ['getSubClients', 'search'] );

		$object->expects( $this->once() )->method( 'getSubClients' )
			->will( $this->throwException( new \RuntimeException() ) );
		$object->expects( $this->once() )->method( 'search' );

		$object->delete();
	}


	public function testGet()
	{
		$param = ['site' => 'unittest', 'id' => $this->getItem()->getId()];
		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );

		$result = $this->object->get();

		$this->assertStringContainsString( 'google-include', $result );
		$this->assertEmpty( $this->view->get( 'errors' ) );
	}


	public function testGetException()
	{
		$object = $this->getClientMock( 'getSubClients' );

		$object->expects( $this->once() )->method( 'getSubClients' )
			->will( $this->throwException( new \RuntimeException() ) );

		$object->get();
	}


	public function testSave()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'feed' );

		$param = [
			'site' => 'unittest',
			'item' => [
				'feed.id'         => '',
				'feed.type'       => 'google',
				'feed.label'      => 'test-jqadm-save',
				'feed.languageid' => 'en',
				'feed.currencyid' => 'EUR',
				'feed.status'     => '1',
				'feed.stock'      => '1',
				'config' => [
					'attributes' => [
						['key' => 'gtin', 'val' => 'ean'],
						['key' => 'colour', 'val' => 'color'],
					],
				],
			],
		];

		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );

		$result = $this->object->save();

		$saved = $this->getItem( 'test-jqadm-save' );
		$manager->delete( $saved->getId() );

		$this->assertNull( $result );
		$this->assertEmpty( $this->view->get( 'errors' ) );
		$this->assertEquals( 'google', $saved->getType() );
		$this->assertEquals( 'en', $saved->getLanguageId() );
		$this->assertEquals( 'EUR', $saved->getCurrencyId() );
		$this->assertEquals( 1, $saved->getStatus() );
		$this->assertEquals( ['gtin' => 'ean', 'colour' => 'color'], $saved->getConfig()['attributes'] );
	}


	public function testSaveWithCategories()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'feed' );

		$catManager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$catItems = $catManager->search( $catManager->filter()->slice( 0, 1 ) );

		if( $catItems->isEmpty() ) {
			$this->markTestSkipped( 'No catalog items found in test data' );
		}

		$catItem = $catItems->first();

		$param = [
			'site' => 'unittest',
			'item' => [
				'feed.id'         => '',
				'feed.type'       => 'google',
				'feed.label'      => 'test-jqadm-categories',
				'feed.languageid' => 'en',
				'feed.currencyid' => 'EUR',
				'feed.status'     => '1',
				'category' => [
					'include' => [
						['feed.lists.id' => '', 'feed.lists.type' => 'include', 'catalog.id' => $catItem->getId(), 'catalog.label' => $catItem->getLabel()],
					],
					'exclude' => [],
				],
				'product' => ['include' => [], 'exclude' => []],
			],
		];

		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );

		$result = $this->object->save();

		$saved = $this->getItem( 'test-jqadm-categories', ['catalog', 'product'] );
		$manager->delete( $saved->getId() );

		$this->assertNull( $result );
		$this->assertEmpty( $this->view->get( 'errors' ) );

		$listItems = $saved->getListItems( 'catalog', 'include' );
		$this->assertCount( 1, $listItems );
		$this->assertEquals( $catItem->getId(), $listItems->first()->getRefId() );
	}


	public function testSaveWithProducts()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'feed' );

		$prodManager = \Aimeos\MShop::create( $this->context, 'product' );
		$prodSearch = $prodManager->filter()->add( ['product.code' => 'ABCD/16 discs'] )->slice( 0, 1 );
		$prodItems = $prodManager->search( $prodSearch );

		if( $prodItems->isEmpty() ) {
			$this->markTestSkipped( 'No product item with code "ABCD/16 discs" found' );
		}

		$prodItem = $prodItems->first();

		$param = [
			'site' => 'unittest',
			'item' => [
				'feed.id'         => '',
				'feed.type'       => 'idealo',
				'feed.label'      => 'test-jqadm-products',
				'feed.languageid' => 'en',
				'feed.currencyid' => 'EUR',
				'feed.status'     => '1',
				'category' => ['include' => [], 'exclude' => []],
				'product' => [
					'include' => [],
					'exclude' => [
						['feed.lists.id' => '', 'feed.lists.type' => 'exclude', 'product.id' => $prodItem->getId(), 'product.label' => $prodItem->getLabel()],
					],
				],
			],
		];

		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );

		$result = $this->object->save();

		$saved = $this->getItem( 'test-jqadm-products', ['catalog', 'product'] );
		$manager->delete( $saved->getId() );

		$this->assertNull( $result );
		$this->assertEmpty( $this->view->get( 'errors' ) );

		$listItems = $saved->getListItems( 'product', 'exclude' );
		$this->assertCount( 1, $listItems );
		$this->assertEquals( $prodItem->getId(), $listItems->first()->getRefId() );
	}


	public function testSaveException()
	{
		$object = $this->getClientMock( 'fromArray' );

		$object->expects( $this->once() )->method( 'fromArray' )
			->will( $this->throwException( new \RuntimeException() ) );

		$object->save();
	}


	public function testSearch()
	{
		$param = [
			'site'   => 'unittest',
			'filter' => [
				'key' => [0 => 'feed.label'],
				'op'  => [0 => '=~'],
				'val' => [0 => 'google-include'],
			],
			'sort' => ['feed.label'],
		];

		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );

		$result = $this->object->search();

		$this->assertStringContainsString( 'google-include', $result );
		$this->assertEmpty( $this->view->get( 'errors' ) );
	}


	public function testSearchException()
	{
		$object = $this->getClientMock( 'initCriteria' );

		$object->expects( $this->once() )->method( 'initCriteria' )
			->will( $this->throwException( new \RuntimeException() ) );

		$object->search();
	}


	public function testBatch()
	{
		$param = [
			'site' => 'unittest',
			'id' => [$this->getItem()->getId()],
			'item' => ['feed.status' => '1'],
		];

		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );

		$this->assertNull( $this->object->batch() );
		$this->assertEmpty( $this->view->get( 'errors' ) );
	}


	public function testGetSubClientInvalid()
	{
		$this->expectException( \LogicException::class );
		$this->object->getSubClient( '$unknown$' );
	}


	public function testGetSubClientUnknown()
	{
		$this->expectException( \LogicException::class );
		$this->object->getSubClient( 'unknown' );
	}


	protected function getClientMock( $methods, $real = true )
	{
		$object = $this->getMockBuilder( \Aimeos\Admin\JQAdm\Feed\Standard::class )
			->setConstructorArgs( [$this->context, \TestHelper::getTemplatePaths()] )
			->onlyMethods( (array) $methods )
			->getMock();

		$object->setAimeos( \TestHelper::getAimeos() );
		$object->setView( $this->getViewNoRender( $real ) );

		return $object;
	}


	protected function getViewNoRender( $real = true )
	{
		$view = $this->getMockBuilder( \Aimeos\Base\View\Standard::class )
			->setConstructorArgs( [[]] )
			->onlyMethods( ['render'] )
			->getMock();

		$param = ['site' => 'unittest', 'id' => $real ? $this->getItem()->getId() : -1];
		$helper = new \Aimeos\Base\View\Helper\Param\Standard( $view, $param );
		$view->addHelper( 'param', $helper );

		$helper = new \Aimeos\Base\View\Helper\Config\Standard( $view, $this->context->config() );
		$view->addHelper( 'config', $helper );

		$helper = new \Aimeos\Base\View\Helper\Access\Standard( $view, [] );
		$view->addHelper( 'access', $helper );

		return $view;
	}


	protected function getItem( string $label = 'google-include', array $domains = [] ) : \Aimeos\MShop\Feed\Item\Iface
	{
		$manager = \Aimeos\MShop::create( $this->context, 'feed' );
		$search = $manager->filter()->slice( 0, 1 )->add( ['feed.label' => $label] );
		$id = $manager->search( $search )->firstKey(
			new \Exception( sprintf( 'No feed item with label "%1$s" found', $label ) )
		);

		return $manager->get( $id, $domains );
	}
}
