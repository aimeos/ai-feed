<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


return [
	'manager' => [
		'decorators' => [
			'global' => [
				'Lists' => 'Lists',
				'Site' => 'Site',
			],
		],
		'lists' => [
			'decorators' => [
				'global' => [
					'Site' => 'Site',
				],
			],
		],
		'resource' => 'db',
		'submanagers' => [
			'lists' => 'lists',
		],
	],
];
