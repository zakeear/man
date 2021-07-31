<?php
// 全局中间件定义文件
use think\middleware\AllowCrossDomain;
use think\middleware\LoadLangPack;
use think\middleware\SessionInit;

return [
	// 全局请求缓存
	// \think\middleware\CheckRequestCache::class,
	// 多语言加载
	LoadLangPack::class,
	// Session初始化
	SessionInit::class,
	// 跨域请求
	AllowCrossDomain::class
];
