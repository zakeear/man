<?php
/*
* 首页
* @author zakeear <zakeear@86dede.com>
* @version v0.0.6
* @time 2019-06-10
*/
namespace app\admin\controller;
use think\facade\Db;
use think\facade\View;
use app\Base;
class Index extends Base{
	//首页
	public function index(){
		//最新用户
		$user=Db::name('user')->field('id,username,nickname,create_time')->order('id desc')->paginate(8)->each(function($item,$key){
			return $item;
		});
		//最新主机
		$server=Db::name('server')->field('id,uid,time,ip_address,hostname')->order('id desc')->paginate(8)->each(function($item,$key){
			return $item;
		});
		//统计
		$data=[
			'total_user'=>Db::name('user')->count('id'),//总用户
			'week_user'=>Db::name('user')->whereTime('create_time','week')->count('id'),//一周新增
			'total_server'=>Db::name('server')->count('id'),//总主机
			'week_server'=>Db::name('server')->whereTime('time','week')->count('id'),//一周新增
			'total_destroy'=>Db::name('server')->where('destroy','>',0)->count(),//总删除
			'week_destroy'=>Db::name('server')->whereTime('destroy','week')->where('destroy','>',0)->count('id'),//一周删除
			'total_money'=>Db::name('account')->where('type','=',2)->sum('money'),//总账单
			'week_money'=>Db::name('account')->where('type','=',2)->whereTime('time','week')->sum('money'),//一周账单
		];
		//变量
		View::assign(['data'=>$data,'user'=>$user,'server'=>$server]);
		//视图
		return View::fetch();
	}
	//账户余额
	public function api(){
		return $this->curl()->send($this->config['vultr_api'].'/v1/account/info','GET',$this->config['vultr_keys']);
	}
	//添加主机
	public function server_add(){
		return $this->curl()->send($this->config['vultr_api'].'/v1/server/create','POST',$this->config['vultr_keys'],['DCID'=>127]);
	}
	//主机列表
	public function server(){
		print_r(json_decode($this->curl()->send($this->config['vultr_api'].'/v1/server/list','GET',$this->config['vultr_keys']),true));
	}
	//主机列表
	public function server_del(){
		return $this->curl()->send($this->config['vultr_api'].'/v1/server/destroy','POST',$this->config['vultr_keys'],['SUBID'=>$this->params['subid']]);
	}
}