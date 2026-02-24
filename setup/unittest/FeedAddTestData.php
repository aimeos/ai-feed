<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Upscheme\Task;


/**
 * Adds feed test data
 */
class FeedAddTestData extends BaseAddTestData
{
	/**
	 * Returns the list of task names which this task depends on
	 *
	 * @return string[] List of task names
	 */
	public function after() : array
	{
		return ['Feed', 'TypeAddTestData'];
	}


	/**
	 * Adds feed test data
	 */
	public function up()
	{
		$this->info( 'Adding feed test data', 'vv' );
		$this->context()->setEditor( 'ai-feed' );

		$this->process( $this->getData() );
	}


	/**
	 * Returns the test data array
	 *
	 * @return array Multi-dimensional array of test data
	 */
	protected function getData() : array
	{
		$path = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'feed.php';

		if( ( $testdata = include( $path ) ) == false ) {
			throw new \RuntimeException( sprintf( 'No file "%1$s" found for feed domain', $path ) );
		}

		return $testdata;
	}


	/**
	 * Adds the feed data from the given array
	 *
	 * @param array $testdata Multi-dimensional array of test data
	 */
	protected function process( array $testdata )
	{
		$manager = $this->getManager( 'feed' );

		foreach( $testdata['feed'] as $entry )
		{
			$item = $manager->create()->fromArray( $entry );
			$manager->save( $item );
		}
	}
}
