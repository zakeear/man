<?php
namespace app\index\controller;
use app\Base;
class Index extends Base{
	//首页
	public function index(){
		$this->redirect('server/index');
	}
	public function hello(int $year = 2019,int $month = 06,int $day = 03){
		$this->result(1,'success',['year'=>$year,'month'=>$month,'day'=>$day]);
	}
	public function api(){
		$this->result(1,'api');
	}
	public function redirects(){
		$this->redirect('halt',['url'=>'hehe']);
	}
	public function halt(){
		$this->error('error',null,30);
	}
	public function log(){
		return $this->logs()->files('user','login','demo',['uid'=>342,'phone'=>15997531225,'time'=>$this->timestamp(13)]);
	}
	public function times(){
		return $this->timestamp(13);
	}
	public function server(){
		return $this->curl()->send($this->config['vultr_api'].'/v1/server/list','GET',$this->config['vultr_keys']);
	}
	public function destroy(){
		return $this->curl()->send($this->config['vultr_api'].'/v1/server/destroy','POST',$this->config['vultr_keys'],['SUBID'=>$this->params['subid']]);
	}
	public function queue(){
		$job=new \app\queue\controller\Host();
		$job->addTask(25123521,'server',0);
	}
	public function cache(){
		return \think\facade\Cache::store('redis')->remember('name','value',3600);
	}
	public function rediss(){
		$this->redis()->select(0);
		$this->redis()->set('set','values',3600);
		$this->redis()->delete('set');
		$this->redis()->hset('hset',12562,time());
		$this->redis()->hDel('hset',12562);
	}
	public function helper(){
		$helper=\app\Timer::nextMonth()[0];
		var_dump($helper);
	}
	public function get_config(){
		var_dump($this->config);
	}
}