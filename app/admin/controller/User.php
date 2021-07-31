<?php
/*
* 登录
* @author zakeear <zakeear@86dede.com>
* @version v0.0.5
* @time 2019-07-10
*/
namespace app\admin\controller;
use app\Base;
use think\facade\Session;
use think\facade\Db;
use think\facade\View;
class User extends Base{
	//登录
	public function login(){
		if(!$this->request->isPost()){
			return View::fetch();
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\Admin.login');
		if($result !== true){
			$this->error($result);
		}
		//验证码
		if(isset($this->params['captcha']) && !$this->check_verify($this->params['captcha'],1)){
			$this->error('验证码错误');
		}
		//验证用户
		$admin=Db::name('admin')->where('username',$this->params['username'])->find();
		if(!$admin){
			$this->error('用户不存在');
		}
		if(md5($this->params['password']) <> $admin['password']){
			$this->error('登录密码错误');
		}
		Session::set('admin',$admin);
		$this->logs()->database(0,2,$this->request->ip(),'登录后台',$admin['id'],0);
		$this->success('欢迎回来，'.$admin['username'],$this->app->route->buildUrl('index/index'));
	}
	//注销
	public function logout(){
		$this->logs()->database(0,2,$this->request->ip(),'退出登录',$this->admin['id'],0);
		Session::delete('admin');
		$this->redirect('user/login');
	}
	//上锁
	public function lock(){
		Session::set('admin_lock',1);
		$this->logs()->database(0,2,$this->request->ip(),'后台锁屏',$this->admin['id'],0);
		$this->redirect('user/screen');
	}
	//锁屏
	public function screen(){
		if(Session::get('admin_lock') == 1){
			return View::fetch();
		}else{
			$this->redirect('index/index');
		}
	}
	//解锁
	public function unlock(){
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\Admin.lock');
		if($result !== true){
			$this->error($result);
		}
		$admin=Db::name('admin')->where(['id'=>Session::get('admin.id')])->find();
		if($admin['password']==md5($this->params['password'])){
			Session::set('admin_lock',0);
			$this->logs()->database(0,2,$this->request->ip(),'解除锁屏',$admin['id'],0);
			$this->redirect('index/index');
		}else{
			$this->error('密码错误');
		}
	}
}