<?php
use think\facade\Env;
return [
	'channels' => [
		'file' => [
			'path' => app()->getRuntimePath() . 'log/index',
		],
	],
];