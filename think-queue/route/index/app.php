<?php
use think\facade\Route;
Route::get('think', function () {
	return json(['code'=>0,'msg'=>'hello,ThinkPHP6!','time'=>time(),'data'=>[]]);
});
Route::get('hello/<year>/<month>/<day>','Index/hello');