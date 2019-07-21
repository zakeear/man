<?php
/*
* 基础控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.1.1
* @time 2019-07-18
*/
declare (strict_types = 1);
namespace app;
use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use think\Validate;
use think\facade\Route;
use think\facade\Session;
use think\facade\Db;
use think\facade\Config;
abstract class Base{
	/**
	 * Request实例
	 * @var \think\Request
	 */
	protected $request;
	/**
	 * 应用实例
	 * @var \think\App
	 */
	protected $app;
	/**
	 * 是否批量验证
	 * @var bool
	 */
	protected $batchValidate = false;
	/**
	 * 控制器中间件
	 * @var array
	 */
	protected $middleware = [];
	/**
	 * 构造方法
	 * @access public
	 * @param App $app 应用对象
	 */
	public function __construct(App $app){
		//应用对象
		$this->app = $app;
		//请求对象
		$this->request = $this->app->request;
		//传参
		$this->params = $this->request->param();
		//用户
		$this->user=Session::get('user.id') ? Db::name('user')->cache(true,5)->where(['id'=>Session::get('user.id')])->find() : [];
		//管理员
		$this->admin=Session::get('admin') ? Session::get('admin') : [];
		//工单
		$this->ticket=$this->user ? Db::name('ticket')->cache(true,5)->where('uid','=',$this->user['id'])->where('status','<>',3)->select()->toArray() : [];
		//登录状态
		if($this->request->app()=='admin'){
			if($this->request->controller() <> 'User'){
				if(Session::has('admin') !== true){
					$this->redirect('user/login');
				}
				if(Session::get('admin_lock') == 1){
					$this->redirect('user/screen');
				}
			}
		}else{
			if($this->request->controller() <> 'User'){
				if(Session::has('user') !== true){
					$this->redirect('user/login');
				}
				if(Session::get('user_lock') == 1){
					$this->redirect('user/screen');
				}
			}
		}
		//配置
		$config_db=Db::name('config')->field('rate,month,is_buy,vultr_api,vultr_keys,web_name,web_icon,time')->cache(true,5)->where('status','=',1)->order('time','desc')->find();
		$this->config = $config_db ? $config_db : Config::get('config');
		//控制器初始化
		$this->initialize();
	}
	//初始化
	protected function initialize(){}
	/**
	 * 验证数据
	 * @access protected
	 * @param array $data 数据
	 * @param string|array $validate 验证器名或者验证规则数组
	 * @param array $message 提示信息
	 * @param bool $batch 是否批量验证
	 * @return string|true
	 * @throws ValidateException
	 */
	protected function validate(array $data, $validate, array $message = [], bool $batch = false){
		if (is_array($validate)) {
			$v = new Validate();
			$v->rule($validate);
		} else {
			if (strpos($validate, '.')) {
				//支持场景
				list($validate, $scene) = explode('.', $validate);
			}
			$class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
			$v = new $class();
			if (!empty($scene)) {
				$v->scene($scene);
			}
		}
		$v->message($message);
		//是否批量验证
		if ($batch || $this->batchValidate) {
			$v->batch(true);
		}
		try {
			return $v->failException(true)->check($data);
		}catch (ValidateException $e) {
			return $e->getError();
		}
	}
	/**
	 * 操作成功跳转的快捷方法
	 * @access protected
	 * @param string $msg 提示信息
	 * @param string|object $url 跳转的url地址
	 * @param integer $wait 跳转等待时间
	 * @param array $filter 内容过滤
	 * @param array|string $data 返回数据
	 * @return void
	 */
	protected function success(string $msg = '',$url = null, int $wait = 3, $filter = null, $data = null){
		if (is_null($url)) {
			$url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
		} elseif (is_string($url)) {
			$url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
		}
		$vars = [
			'code' => 1,
			'msg' => $msg,
			'data' => $data,
			'url' => $url,
			'wait' => $wait
		];
		$response = Response::create($this->app->config->get('app.dispatch_success_tmpl') ? $this->app->config->get('app.dispatch_success_tmpl') : '', 'view', 200)->assign($vars)->filter($filter);
		throw new HttpResponseException($response);
	}
	/**
	 * 操作错误跳转的快捷方法
	 * @access protected
	 * @param string $msg 提示信息
	 * @param string|object $url 跳转的url地址
	 * @param integer $wait 跳转等待时间
	 * @param array $filter 内容过滤
	 * @param array|string $data 返回数据
	 * @return void
	 */
	protected function error(string $msg = '', $url = null, int $wait = 3, $filter = null, $data = null){
		if (is_null($url)) {
			$url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
		} elseif (is_string($url)) {
			$url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
		}
		$vars = [
			'code' => 0,
			'msg' => $msg,
			'data' => $data,
			'url' => $url,
			'wait' => $wait
		];
		$response = Response::create($this->app->config->get('app.dispatch_error_tmpl') ? $this->app->config->get('app.dispatch_error_tmpl') : '', 'view', 200)->assign($vars)->filter($filter);
		throw new HttpResponseException($response);
	}
	/**
	 * 重定向
	 * @access protected
	 * @param string|array $url 跳转的url表达式
	 * @param array $params 其它url参数
	 * @param integer $code http code
	 * @return void
	 */
	protected function redirect($url,array $params = [],int $code = 302){
		if (is_integer($params)) {
			$code = $params;
			$params = [];
		}
		$response = Response::create($url, 'redirect', $code)->params($params);
		throw new HttpResponseException($response);
	}
	/**
	 * 返回封装后的json数据到客户端
	 * @access protected
	 * @param array $data 要返回的数据
	 * @param integer $code 返回的code
	 * @param string $msg 提示信息
	 * @param string|object $url 跳转的url地址
	 * @param array $header 发送的Header信息
	 * @return void
	 */
	protected function result(int $code = 0,string $msg = '',array $data=[], $url = null, array $header = []){
		$result = [
			'code' => $code,
			'msg' => $msg,
			'time' => time(),
			'data' => $data,
			'url' => $url
		];
		$response = Response::create($result, 'json' ,200)->header($header);
		throw new HttpResponseException($response);
	}
	/**
	 * 获取时间戳
	 * @access protected
	 * @param integer $length 时间戳长度
	 * @return float
	 */
	protected function timestamp(int $length=10){
		$length=$length==10 ? 10 : 13;
		if($length==10){
			return time();
		}else{
			list($msec,$sec)=explode(' ',microtime());
			return (float)sprintf('%.0f',(floatval($msec)+floatval($sec))*1000);
		}
	}
	/**
	 * 实例化日志类
	 * @access protected
	 * @return void
	 */
	protected function logs(){
		return new \app\common\logic\Logs();
	}
	/**
	 * 实例化CURL类
	 * @access protected
	 * @return void
	 */
	protected function curl(){
		return new \app\common\logic\Curl();
	}
	/**
	 * 实例化流水类
	 * @access protected
	 * @return void
	 */
	protected function money(){
		return new \app\common\logic\Money();
	}
	/**
	 * 实例化枚举类
	 * @access protected
	 * @return void
	 */
	protected function enum(){
		return new \app\common\logic\Enum();
	}
	/**
	 * 实例化邮件类
	 * @access protected
	 * @return void
	 */
	protected function sendEmail(){
		return new \app\common\logic\Email();
	}
	/**
	 * 初始化Redis类
	 * @access protected
	 * @return void
	 */
	protected function redis(){
		return \think\facade\Cache::store('redis');
	}
	/**
	 * 重载方法
	 * @access public
	 * @param string $method 控制器名
	 * @param array $args 参数
	 * @throws mixed
	 */
	public function __call(string $method,array $args){
		$this->redirect('/../pages/404.html');
	}
}