<?php
// [ 应用入口文件 ]
namespace think;
header_remove('x-powered-by');
require __DIR__ . '/../vendor/autoload.php';
// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);