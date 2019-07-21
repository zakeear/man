<?php
/*
* 卡密
* @author zakeear <zakeear@86dede.com>
* @version v0.0.5
* @time 2019-07-10
*/
namespace app\admin\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Card extends Base{
	//首页
	public function index(){
		$where=[];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//状态
		if(isset($this->params['status']) && $this->params['status']){
			$where['status']=$this->params['status'];
		}
		//用户
		if(isset($this->params['uid']) && $this->params['uid']){
			$where['uid']=$this->params['uid'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('card')->where($where)->where('num','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('card')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		$list->each(function($item,$key){
			//用户
			if($item['uid']){
				$member=Db::name('user')->field('id,username,nickname,realname')->where(['id'=>$item['uid']])->find();
				$item['username']=$member['username'];
				$item['nickname']=$member['nickname'];
				$item['realname']=$member['realname'];
			}else{
				$item['username']='';
				$item['nickname']='';
				$item['realname']='';
			}
			return $item;
		});
		//变量
		View::assign(['list'=>$list,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
	}
	//生成卡密
	public function add(){
		if(!$this->request->isPost()){
			return View::fetch();
		}
		//张数
		$limit=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=1 ? intval($this->params['limit']) : 1;//生成数量
		//金额
		$money=isset($this->params['money']) && $this->params['money'] && $this->params['money']<=1000 && $this->params['money']>=0 ? $this->params['money'] : 1;//面额
		Db::startTrans();
		try{
			for($i=0;$i<=$limit-1;$i++){
				$data=['num'=>$this->nums(),'money'=>$money,'keys'=>\think\helper\Str::random(8),'create'=>$this->timestamp(10)];
				Db::name('card')->insertGetId($data);
				Db::commit();
			}
		}catch(\Exception $e){
			Db::rollback();
			$this->error('生成失败，请重试');
		}
		//日志
		$this->logs()->database(1,5,$this->request->ip(),'生成卡密',$this->admin['id'],0);
		$this->success('生成成功',$this->app->route->buildUrl('index'));
	}
	//生成唯一的卡号
	private function nums(){
		$nums=mt_rand(100000000000,999999999999);
		$check=Db::name('card')->where(['num'=>$nums])->find();
		if($check){
			$nums=mt_rand(100000000000,999999999999);
		}
		return $nums;
	}
}