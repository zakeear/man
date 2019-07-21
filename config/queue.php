<?php
return [
	'default' => 'redis',
	'connections' => [
		'sync' => [
			'driver' => 'sync',
		],
		'database' => [
			'driver' => 'database',
			'queue' => 'default',
			'table' => 'jobs',
		],
		'redis' => [
			'driver' => 'redis',
			'queue' => 'default',
			'host' => '127.0.0.1',
			'port' => 6379,
			'password' => 'Xun166123',
			'select' => 1,
			'timeout' => 0,
			'persistent' => false,
		],
	],
	'failed' => [
		'type' => 'none',
		'table' => 'failed_jobs',
	],
];