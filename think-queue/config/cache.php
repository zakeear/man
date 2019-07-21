<?php
use think\facade\Env;
return [
	// 默认缓存驱动
	'default' => Env::get('cache.driver', 'file'),
	// 缓存连接方式配置
	'stores' => [
		// 文件缓存
		'file' => [
			// 驱动方式
			'type' => 'File',
			// 全局缓存有效期（0为永久有效）
			'expire'=> 0,
			// 缓存前缀
			'prefix'=> 'think_',
			// 缓存目录
			'path' => '../runtime/cache/',
			// 缓存标签前缀
			'tag_prefix' => 'tag:',
			// 序列化机制 例如 ['serialize', 'unserialize']
			'serialize' => [],
		],
		// redis缓存
		'redis' => [
			// 驱动方式
			'type' => 'redis',
			// 服务器地址
			'host' => '127.0.0.1',
			// 端口
			'port' => 6379,
			// 密码
			'password' => 'Xun166123',
			// 库
			'select' => 1,
			// 链接超时
			'timeout'=> 0,
			// 全局缓存有效期（0为永久有效）
			'expire' => 0,
			// 缓存前缀
			'prefix'=>'think_',
			// 缓存标签前缀
			'tag_prefix' => 'tag:',
			// 序列化机制 例如 ['serialize', 'unserialize']
			'serialize' => [],
			// 长链接
			'persistent' => false
		],
	]
];