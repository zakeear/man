<?php
/*
* 登录
* @author zakeear <zakeear@86dede.com>
* @version v0.0.5
* @time 2019-07-10
*/
namespace app\index\controller;
use app\Base;
use phpmailerException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Session;
use think\facade\Db;
use think\facade\View;
use think\helper\Str;

class User extends Base{
	//注册
	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function register(){
		if(!$this->request->isPost()){
			//试图
			return View::fetch();
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\User.register');
		if($result !== true){
			$this->error($result);
		}
		//验证用户
		$user=Db::name('user')->where('username',$this->params['username'])->value('id');
		if($user){
			$this->error('用户已经注册');
		}
		$data=['username'=>$this->params['username'],'safe_email'=>$this->params['username'],'password'=>md5($this->params['password']),'create_time'=>$this->timestamp(),'ip'=>$this->request->ip(),'safe_code'=> Str::random(8)];
		$add=Db::name('user')->insertGetId($data);
		if(!$add){
			$this->error('注册失败');
		}
		//发送邮件
		$this->email()->send(['email'=>$this->params['username'],'subject'=>'欢迎注册成为我们的会员，请勿删除此封邮件。','message'=>'您的账号安全码是：'.$data['safe_code'].'，请妥善保管安全码和此封邮件。',]);
		if($result!==true){
			$this->logs->files('error','register',$this->params['username'],$result);
			$this->success('注册成功！',$this->app->route->buildUrl('login'));
		}else{
			$this->logs()->database($add,1,$data['ip'],'注册会员'.$data['safe_code']);
			$this->success('注册成功，请注意查收邮件！',$this->app->route->buildUrl('login'));
		}
	}
	//登录

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function login(){
		if(!$this->request->isPost()){
			//视图
			return View::fetch();
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\User.login');
		if($result !== true){
			$this->error($result);
		}
		//验证用户
		$user=Db::name('user')->field('id,username,password,status,login_count,login_time,vip')->where('username',$this->params['username'])->find();
		if(!$user){
			$this->error('用户不存在');
		}
		if(md5($this->params['password']) <> $user['password']){
			$this->error('登录密码错误');
		}
		if($user['status'] <> 1){
			$this->error('用户被禁止登录');
		}
		if($user['login_time'] && $this->timestamp()-$user['login_time']<=3){
			$this->error('请勿频繁登录');
		}
		//登录
		$data=['username'=>$user['username'],'login_count'=>$user['login_count']+1,'login_time'=>$this->timestamp(),'login_ip'=>$this->request->ip()];
		$update=Db::name('user')->where('username',$this->params['username'])->update($data);
		if(!$update){
			$this->error('登录失败');
		}
		$data['id']=$user['id'];
		//日志
		$this->logs()->database($user['id'],1,$data['login_ip'],'登录系统');
		//Session
		Session::set('user',$data);
		$this->success('欢迎回来，'.$user['username'],$this->app->route->buildUrl('server/index'));
	}
	//找回

	/**
	 * @throws DataNotFoundException
	 * @throws phpmailerException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function forget(){
		if(!$this->request->isPost()){
			//视图
			return View::fetch();
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\User.forget');
		if($result <> true){
			$this->error($result);
		}
		//验证用户
		$user=Db::name('user')->field('id,username,safe_email,verify_lock')->where('username',$this->params['username'])->find();
		if(!$user){
			$this->error('用户不存在');
		}
		if(!$user['verify_lock'] && $this->timestamp()-$user['verify_lock']<60){
			$this->error('请勿重复操作');
		}
		$verify_code=number_code(6);
		$add=Db::name('user')->where('username',$this->params['username'])->update(['verify_code'=>$verify_code,'verify_lock'=>$this->timestamp(),'verify_time'=>$this->timestamp()+900]);
		if(!$add){
			$this->error('找回失败');
		}
		//发送邮件
		$this->sendEmail()->send(['email'=>$user['username'],'subject'=>'用户邮件找回密码，请勿删除此封邮件。','message'=>'您的邮件确认码是：'.$verify_code.'[15分钟后过期]，请妥善保管安全码和此封邮件。']);
		//记录日志
		$this->logs()->files('success','verify',$user['username'],$verify_code);
		$this->logs()->database($user['id'],1,$this->request->ip(),'发送邮件找回码【'.$verify_code.'】');
		$this->success('邮件已经发送',$this->app->route->buildUrl('email',['user'=>$this->params['username']]));
	}
	//验证邮箱

	/**
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function email(){
		if($this->request->isPost()){
			//验证数据
			$result=$this->validate($this->params,'app\common\validate\User.verify_email');
			if($result !== true){
				$this->error($result);
			}
			//验证用户
			$user=Db::name('user')->field('id,username,verify_code,verify_time')->where('username',$this->params['username'])->find();
			if(!$user){
				$this->error('用户不存在');
			}
			if($this->timestamp()>$user['verify_time']){
				$this->error('已经过期','login');
			}
			if($this->params['verify_code']<>$user['verify_code']){
				$this->error('邮件确认码不对');
			}
			$add=Db::name('user')->where('username',$this->params['username'])->update(['password'=>md5($this->params['password']),'verify_lock'=>$this->timestamp(),'verify_code'=>null,'create_time'=>$this->timestamp()]);
			if(!$add){
				$this->error('修改失败');
			}
			$this->logs()->database($user['id'],1,$this->request->ip(),'修改登录密码');
			$this->success('修改成功',$this->app->route->buildUrl('login'));
		}else{
			//验证参数
			if(!isset($this->params['user']) || !$this->params['user']){
				$this->redirect('user/login');
			}
			//验证用户
			$user=Db::name('user')->field('id,username,verify_time')->where('username',$this->params['user'])->find();
			if(!$user){
				$this->redirect('user/login');
			}
			if($this->timestamp() > $user['verify_time']){
				$this->error('已经过期',$this->app->route->buildUrl('forget'));
			}
			//变量
			View::assign(['get'=>$user]);
			//视图
			return View::fetch();
		}
	}
	//注销
	public function logout(){
		$this->logs()->database(Session::get('user.id'),1,$this->request->ip(),'退出登录');
		Session::delete('user');
		$this->redirect('user/login');
	}
	//上锁
	public function lock(){
		Session::set('user_lock',1);
		$this->logs()->database(Session::get('user.id'),1,$this->request->ip(),'操作锁屏');
		$this->redirect('user/screen');
	}
	//锁屏
	public function screen(){
		if(Session::get('user_lock') == 1){
			//视图
			return View::fetch();
		}else{
			$this->redirect('index/index');
		}
	}
	//解锁

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function unlock(){
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\User.lock');
		if($result !== true){
			$this->error($result);
		}
		$user=Db::name('user')->field('id,password')->where(['id'=>Session::get('user.id')])->find();
		if($user['password']==md5($this->params['password'])){
			Session::set('user_lock',0);
			$this->logs()->database($user['id'],1,$this->request->ip(),'解除锁屏');
			$this->redirect('index/index');
		}else{
			$this->error('密码错误');
		}
	}
}