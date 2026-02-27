<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


return [
	'feed' => [
		[
			'feed.label' => 'google-exclude',
			'feed.type' => 'google',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => false,
			'feed.status' => 1,
			'feed.config' => [
				'attribute' => [
					'colour' => 'color',
					'size' => 'size',
				]
			],
			'lists' => [
				'catalog' => [[
					'feed.lists.type' => 'exclude',
					'feed.lists.domain' => 'catalog',
					'ref' => 'Kaffee',
				], [
					'feed.lists.type' => 'exclude',
					'feed.lists.domain' => 'catalog',
					'ref' => 'Neu',
				]],
				'product' => [[
					'feed.lists.type' => 'exclude',
					'feed.lists.domain' => 'product',
					'ref' => 'ABCD/16 discs',
				]],
			],
		],
		[
			'feed.label' => 'google-include',
			'feed.type' => 'google',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => true,
			'feed.status' => 1,
			'feed.config' => [
				'attribute' => [
					'colour' => 'color',
					'size' => 'size',
				]
			],
			'lists' => [
				'catalog' => [[
					'feed.lists.type' => 'include',
					'feed.lists.domain' => 'catalog',
					'ref' => 'Kaffee',
				]],
				'product' => [[
					'feed.lists.type' => 'include',
					'feed.lists.domain' => 'product',
					'ref' => 'ABCD/16 discs',
				]],
			],
		],
		[
			'feed.label' => 'idealo-exclude',
			'feed.type' => 'idealo',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => false,
			'feed.status' => 1,
			'feed.config' => [
				'attribute' => [
					'colour' => 'color',
					'size' => 'size',
				]
			],
			'lists' => [
				'catalog' => [[
					'feed.lists.type' => 'exclude',
					'feed.lists.domain' => 'catalog',
					'ref' => 'Kaffee',
				], [
					'feed.lists.type' => 'exclude',
					'feed.lists.domain' => 'catalog',
					'ref' => 'Neu',
				]],
				'product' => [[
					'feed.lists.type' => 'exclude',
					'feed.lists.domain' => 'product',
					'ref' => 'ABCD/16 discs',
				]],
			],
		],
		[
			'feed.label' => 'idealo-include',
			'feed.type' => 'idealo',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => true,
			'feed.status' => 1,
			'feed.config' => [
				'attribute' => [
					'colour' => 'color',
					'size' => 'size',
				]
			],
			'lists' => [
				'catalog' => [[
					'feed.lists.type' => 'include',
					'feed.lists.domain' => 'catalog',
					'ref' => 'Kaffee',
				]],
				'product' => [[
					'feed.lists.type' => 'include',
					'feed.lists.domain' => 'product',
					'ref' => 'ABCD/16 discs',
				]],
			],
		],
		[
			'feed.label' => 'google-disabled',
			'feed.type' => 'google',
			'feed.languageid' => 'en',
			'feed.currencyid' => 'EUR',
			'feed.stock' => false,
			'feed.status' => 0,
			'feed.config' => [],
		],
	],
];
