<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


return [
	'feed' => [
		[
			'feed.label' => 'google-en',
			'feed.type' => 'google',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => true,
			'feed.status' => 1,
			'feed.config' => ['format' => 'csv'],
		],
		[
			'feed.label' => 'idealo-de',
			'feed.type' => 'idealo',
			'feed.languageid' => 'de',
			'feed.currencyid' => 'EUR',
			'feed.stock' => false,
			'feed.status' => 1,
			'feed.config' => [],
		],
		[
			'feed.label' => 'google-all',
			'feed.type' => 'google',
			'feed.languageid' => null,
			'feed.currencyid' => null,
			'feed.stock' => false,
			'feed.status' => 0,
			'feed.config' => [],
		],
	],
];
