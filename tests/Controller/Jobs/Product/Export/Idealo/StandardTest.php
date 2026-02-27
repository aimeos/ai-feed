<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Controller\Jobs\Product\Export\Idealo;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private $aimeos;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );

		$this->context = \TestHelper::context();
		$this->aimeos = \TestHelper::getAimeos();

		$this->object = new \Aimeos\Controller\Jobs\Product\Export\Idealo\Standard( $this->context, $this->aimeos );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		$this->object = null;
	}


	public function testGetName()
	{
		$this->assertEquals( 'Idealo product export', $this->object->getName() );
	}


	public function testGetDescription()
	{
		$this->assertEquals( 'Exports products for Idealo', $this->object->getDescription() );
	}


	public function testRun()
	{
		$exfile = 'tmp' . DIRECTORY_SEPARATOR . 'idealo-exclude.csv';
		$infile = 'tmp' . DIRECTORY_SEPARATOR . 'idealo-include.csv';

		try
		{
			$this->object->run();

			$this->assertFileExists( $exfile );
			$this->assertFileExists( $infile );

			$excontent = file_get_contents( $exfile );
			$incontent = file_get_contents( $infile );

			$this->assertStringNotContainsString( 'ABCD/16 discs', $excontent );
			$this->assertStringNotContainsString( 'CNC', $excontent );
			$this->assertStringNotContainsString( 'CNE', $excontent );
			$this->assertEquals( 8, count( explode( "\n", trim( $excontent ) ) ) ); // no selection products, only variants

			$this->assertStringContainsString( 'ABCD/16 discs', $incontent );
			$this->assertStringContainsString( 'CNC', $incontent );
			$this->assertStringContainsString( 'CNE', $incontent );
			$this->assertEquals( 4, count( explode( "\n", trim( $incontent ) ) ) );
		}
		finally
		{
			unlink( $exfile );
			unlink( $infile );
		}
	}
}
