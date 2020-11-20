<?php

return [
	'testShouldBailoutWhenBothValuesAreEmpty' => [
			'config' => [
				'cdn_url'   => '',
				'cdn_token' => '',
			],
			'expected' => [
				'empty' => true,
				'data'  => [
					'message' => 'cdn_values_empty',
				],
			],
	],
	'testShouldBailoutWhenCDNTokenIsEmpty' => [
		'config' => [
			'cdn_url'   => 'https://rocketcdn.me',
			'cdn_token' => '',
		],
		'expected' => [
			'empty' => true,
			'data'  => [
				'message' => 'cdn_values_empty',
			],
		],
	],
	'testShouldBailoutWhenCDNUrlIsEmpty' => [
		'config' => [
			'cdn_url'   => '',
			'cdn_token' => 'TOKEN',
		],
		'expected' => [
			'empty' => true,
			'data'  => [
				'message' => 'cdn_values_empty',
			],
		],
	],
	'testShouldFailAtValidatingCDNUrl' => [
		'config' => [
			'cdn_url'   => 'not_valid_cdn_url',
			'cdn_token' => 'TOKEN',
		],
		'expected' => [
			'empty'     => false,
			'not_valid' => true,
			'data'      => [
				'message' => 'cdn_url_invalid_format',
			],
		],
	],
	'testShouldFailAtValidatingToken' => [
		'config' => [
			'cdn_url'   => 'https://rocketcdn.me',
			'cdn_token' => 'not40charslong',
		],
		'expected' => [
			'empty'     => false,
			'not_valid' => true,
			'data'      => [
				'message' => 'invalid_token_length',
			],
		],
	],
	'testShouldDueToCurrentTokenSetAndCNameSet' => [
		'config' => [
			'cdn_url'       => 'https://rocketcdn.me',
			'cdn_token'     => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
			'current_token' => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
			'current_cname' => 'https://rocketcdn.me',
		],
		'expected' => [
			'empty'     => false,
			'not_valid' => true,
			'get_option' => true,
			'data'      => [
				'message' => 'token_already_set',
			],
		],
	],
	'testShouldDueToCurrentTokenSetAndCNameNotSet' => [
		'config' => [
			'cdn_url'       => 'https://rocketcdn.me',
			'cdn_token'     => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
			'current_token' => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
			'current_cname' => '',
		],
		'expected' => [
			'empty'      => false,
			'not_valid'  => true,
			'get_option' => true,
			'data'       => [
				'message' => 'token_already_set',
			],
		],
	],
	'testShouldUpdateTokenAndCname' => [
		'config' => [
			'cdn_url'       => 'https://rocketcdn.me',
			'cdn_token'     => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
			'current_token' => '',
			'current_cname' => '',
		],
		'expected' => [
			'empty'      => false,
			'not_valid'  => false,
			'success'    => true,
			'get_option' => true,
			'data'       => [
				'message' => 'token_updated_successfully',
			],
		],
	],
];
