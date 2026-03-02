<?php

return [
	'graphql' => [
		'domains' => [
			'feed' => 'feed',
		],
		'resource' => [
			'feed' => [
				/** admin/graphql/resource/feed/delete
				 * List of user groups that are allowed to delete feed items
				 *
				 * @param array List of user group names
				 * @since 2026.01
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/feed/save
				 * List of user groups that are allowed to create and update feed items
				 *
				 * @param array List of user group names
				 * @since 2026.01
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/feed/get
				 * List of user groups that are allowed to retrieve feed items
				 *
				 * @param array List of user group names
				 * @since 2026.01
				 */
				'get' => ['admin', 'super'],
			],
		]
	],
	'jqadm' => [
		'navbar' => [
			60 => [
				95 => 'feed',
			],
		],
		'resource' => [
			'feed' => [
				/** admin/jqadm/resource/feed/groups
				 * List of user groups that are allowed to access the Feed panel
				 *
				 * @param array List of user group names
				 * @since 2026.01
				 */
				'groups' => ['admin', 'super'],

				/** admin/jqadm/resource/feed/key
				 * Shortcut key to switch to the Feed panel by using the keyboard
				 *
				 * @param string Single character in upper case
				 * @since 2026.01
				 */
				'key' => 'F',
			],
		]
	],
];
