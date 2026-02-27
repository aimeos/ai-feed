<?php

return [
	'jobs' => [
		'product' => [
			'export' => [
				'google' => [
					'types' => [
						'gtin',
						'mpn',
						'material',
						'pattern',
						'colour',
						'size',
						'size_type',
						'size_system',
						'gender',
						'adult',
						'age_group',
						'condition',
						'certification',
						'product_length',
						'product_width',
						'product_height',
						'product_weight',
					],
				],
				'idealo' => [
					'types' => [
						'hans',
						'eans',
						'material',
						'colour',
						'size',
						'gender',
						'delivery',
						'eec_efficiencyClass',
						'eec_labelUrl',
						'eec_dataSheetUrl',
						'eec_version',
					],
				]
			]
		]
	],
];
