<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Controller\Jobs\Product\Export\Google;


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

		$this->object = new \Aimeos\Controller\Jobs\Product\Export\Google\Standard( $this->context, $this->aimeos );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::cache( false );
		$this->object = null;
	}


	public function testGetName()
	{
		$this->assertEquals( 'Google shopping export', $this->object->getName() );
	}


	public function testGetDescription()
	{
		$this->assertEquals( 'Exports products for Google Shopping', $this->object->getDescription() );
	}


	public function testRun()
	{
		$exfile = 'tmp' . DIRECTORY_SEPARATOR . 'google-exclude.csv';
		$infile = 'tmp' . DIRECTORY_SEPARATOR . 'google-include.csv';

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
			$this->assertEquals( 11, count( explode( "\n", trim( $excontent ) ) ) );

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
