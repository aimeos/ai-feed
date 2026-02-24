<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


return [
	'table' => [
		'mshop_feed' => function ( \Aimeos\Upscheme\Schema\Table $table ) {

			$table->engine = 'InnoDB';

			$table->id()->primary( 'pk_msfee_id' );
			$table->string( 'siteid' );
            $table->string( 'label' );
            $table->type();
            $table->string( 'langid', 5 )->null( true );
            $table->string( 'currencyid', 3 )->null( true );
            $table->bool( 'stock' );
            $table->smallint( 'status' );
            $table->config();
            $table->meta();
		},


		'mshop_feed_list' => function( \Aimeos\Upscheme\Schema\Table $table ) {

			$table->engine = 'InnoDB';

			$table->id()->primary( 'pk_msfeeli_id' );
			$table->string( 'siteid' );
			$table->int( 'parentid' );
			$table->string( 'key', 134 )->default( '' );
			$table->type();
			$table->string( 'domain', 32 );
			$table->refid();
			$table->startend();
			$table->config();
			$table->int( 'pos' )->default( 0 );
			$table->smallint( 'status' )->default( 1 );
			$table->meta();

			$table->unique( ['parentid', 'domain', 'type', 'refid', 'siteid'], 'unq_msfeeli_pid_dm_ty_rid_sid' );
			$table->index( ['key', 'siteid'], 'idx_msfeeli_key_sid' );

			$table->foreign( 'parentid', 'mshop_feed', 'id', 'fk_msfeeli_pid' );
		},
    ],
];
