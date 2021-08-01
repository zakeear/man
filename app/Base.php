<?php
/*
* 基础控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.1.1
* @time 2019-07-18
*/
declare (strict_types = 1);

namespace app;

use app\common\logic\Curl;
use app\common\logic\Email;
use app\common\logic\Enum;
use app\common\logic\Logs;
use app\common\logic\Money;
use think\App;
use think\cache\Driver;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Cache;
use think\Model;
use think\Response;
use think\Validate;
use think\facade\Db;
use think\facade\Config;

abstract class Base
{
	/**
	 * Request实例
	 * @var \think\Request
	 */
	protected $request;

	/**
	 * 应用实例
	 * @var App
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
	 * @var mixed
	 */
	protected $params;

	/**
	 * @var array|Db|Model|null
	 */
	protected $user;

	/**
	 * @var array|mixed
	 */
	protected $admin;

	/**
	 * @var array|mixed|Db|Model
	 */
	protected $config;

	protected $appName;

	/**
	 * @var array
	 */
	protected $ticket;

	/**
	 * 构造方法
	 * @access public
	 * @param App $app 应用对象
	 * @throws DataNotFoundException
	 * @throws DbException
	 * @throws ModelNotFoundException
	 */
	public function __construct(App $app)
	{
		// 应用对象
		$this->app = $app;
		// 请求对象
		$this->request = $this->app->request;
		// 传参
		$this->params = $this->request->param();
		// 用户
		$this->user = session('user.id') ? Db::name('user')->cache(true, 5)->where(['id' => session('user.id')])->find() : [];
		// 管理员
		$this->admin = session('admin') ? session('admin') : [];
		// 工单
		$this->ticket = $this->user ? Db::name('ticket')->cache(true, 5)->where('uid', '=', $this->user['id'])->where('status', '<>', 3)->select()->toArray() : [];
		// 应用名
		$this->appName = app('http')->getName();
		// 登录状态
		if ($this->appName == 'admin') {
			if ($this->request->controller() <> 'User') {
				if (session('?admin') !== true) {
					$this->redirect('./admin/user/login.html');
				}
				if (session('admin_lock') == 1) {
					$this->redirect('./admin/user/screen.html');
				}
			}
		} else {
			if ($this->request->controller() <> 'User') {
				if (session('?user') !== true) {
					$this->redirect('./index/user/login.html');
				}
				if (session('user_lock') == 1) {
					$this->redirect('./index/user/screen.html');
				}
			}
		}
		// 配置
		$config_db = Db::name('config')->field('rate,month,is_buy,vultr_api,vultr_keys,web_name,web_icon,time')->cache(true, 5)->where('status', '=', 1)->order('time', 'desc')->find();
		$this->config = $config_db ?: Config::get('config');
		// 控制器初始化
		$this->initialize();
	}

	// 初始化
	protected function initialize()
	{
	}

	/**
	 * 验证数据
	 * @access protected
	 * @param array $data 数据
	 * @param string|array $validate 验证器名或者验证规则数组
	 * @param array $message 提示信息
	 * @param bool $batch 是否批量验证
	 * @return array|string|true
	 * @throws ValidateException
	 */
	protected function validate(array $data, $validate, array $message = [], bool $batch = false)
	{
		if (is_array($validate)) {
			$v = new Validate();
			$v->rule($validate);
		} else {
			if (strpos($validate, '.')) {
				// 支持场景
				[$validate, $scene] = explode('.', $validate);
			}
			$class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
			$v = new $class();
			if (!empty($scene)) {
				$v->scene($scene);
			}
		}
		$v->message($message);
		// 是否批量验证
		if ($batch || $this->batchValidate) {
			$v->batch(true);
		}
		return $v->failException(true)->check($data);
	}

	/**
	 * 操作成功跳转的快捷方法
	 * @access protected
	 * @param string $msg 提示信息
	 * @param string|object $url 跳转的url地址
	 * @param integer $wait 跳转等待时间
	 * @param array|null $filter 内容过滤
	 * @param array|string $data 返回数据
	 * @return void
	 */
	protected function success(string $msg = '', $url = null, int $wait = 3, array $filter = null, $data = null)
	{
		if (is_null($url)) {
			$url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
		} elseif (is_string($url)) {
			$url = (strpos($url, ':// ') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
		}
		$vars = [
			'code' => 1,
			'msg' => $msg,
			'data' => $data,
			'url' => $url,
			'wait' => $wait
		];
		$response = Response::create($this->app->config->get('app.dispatch_success_tmpl') ? $this->app->config->get('app.dispatch_success_tmpl') : '', 'view')->assign($vars)->filter($filter);
		throw new HttpResponseException($response);
	}

	/**
	 * 操作错误跳转的快捷方法
	 * @access protected
	 * @param string $msg 提示信息
	 * @param string|object $url 跳转的url地址
	 * @param integer $wait 跳转等待时间
	 * @param array|null $filter 内容过滤
	 * @param array|string $data 返回数据
	 * @return void
	 */
	protected function error(string $msg = '', $url = null, int $wait = 3, array $filter = null, $data = null)
	{
		if (is_null($url)) {
			$url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
		} elseif (is_string($url)) {
			$url = (strpos($url, ':// ') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
		}
		$vars = [
			'code' => 0,
			'msg' => $msg,
			'data' => $data,
			'url' => $url,
			'wait' => $wait
		];
		$response = Response::create($this->app->config->get('app.dispatch_error_tmpl') ? $this->app->config->get('app.dispatch_error_tmpl') : '', 'view')->assign($vars)->filter($filter);
		throw new HttpResponseException($response);
	}

	/**
	 * 重定向
	 * @access protected
	 * @param string|array $url 跳转的url表达式
	 * @param integer $code http code
	 * @return void
	 */
	protected function redirect($url = '', int $code = 302)
	{
		$response = Response::create($url, 'redirect', $code);
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
	protected function result(int $code = 0, string $msg = '', array $data = [], $url = null, array $header = [])
	{
		$result = [
			'code' => $code,
			'msg' => $msg,
			'time' => time(),
			'data' => $data,
			'url' => $url
		];
		$response = Response::create($result, 'json')->header($header);
		throw new HttpResponseException($response);
	}

	/**
	 * 获取时间戳
	 * @access protected
	 * @param integer $length 时间戳长度
	 * @return float
	 */
	protected function timestamp(int $length = 10): float
	{
		$length = $length == 10 ? 10 : 13;
		if ($length == 10) {
			return time();
		} else {
			list($msec, $sec) = explode(' ', microtime());
			return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
		}
	}

	/**
	 * 实例化日志类
	 * @access protected
	 * @return common\logic\Logs
	 */
	protected function logs(): Logs
	{
		return new Logs();
	}

	/**
	 * 实例化CURL类
	 * @access protected
	 * @return common\logic\Curl
	 */
	protected function curl(): Curl
	{
		return new Curl();
	}

	/**
	 * 实例化流水类
	 * @access protected
	 * @return common\logic\Money
	 */
	protected function money(): Money
	{
		return new Money();
	}

	/**
	 * 实例化枚举类
	 * @access protected
	 * @return common\logic\Enum
	 */
	protected function enum(): Enum
	{
		return new Enum();
	}

	/**
	 * 实例化邮件类
	 * @access protected
	 * @return common\logic\Email
	 */
	protected function sendEmail(): common\logic\Email
	{
		return new Email();
	}

	/**
	 * 初始化Redis类
	 * @access protected
	 * @return Driver
	 */
	protected function redis(): Driver
	{
		return Cache::store('redis');
	}

	/**
	 * 重载方法
	 * @access public
	 * @param string $method 控制器名
	 * @param array $args 参数
	 * @throws mixed
	 */
	/*public function __call(string $method, array $args)
	{
		$this->redirect('/../pages/404.html');
	}*/

}