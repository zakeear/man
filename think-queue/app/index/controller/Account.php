<?php
/*
* 流水控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.0.9
* @time 2019-06-04
*/
namespace app\index\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Account extends Base{
	//首页
	public function index(){
		$where['uid']=$this->user['id'];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//收支
		if(isset($this->params['type']) && $this->params['type']){
			$where['type']=$this->params['type'];
		}
		//来源
		if(isset($this->params['way']) && $this->params['way']){
			$where['way']=$this->params['way'];
		}
		//分类
		if(isset($this->params['style']) && $this->params['style']){
			$where['style']=$this->params['style'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('account')->field('id,uid,type,way,style,money,time,trade,content')->where($where)->where('trade|content','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('account')->field('id,uid,type,way,style,money,time,trade,content')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		//变量
		View::assign(['list'=>$list,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
	}
	//卡密
	public function add(){
		if($this->request->isPost()){
			//数据处理
			$this->params['num']=str_replace(" ","",$this->params['num']);
			$this->params['keys']=str_replace(" ","",$this->params['keys']);
			//验证数据
			$result=$this->validate($this->params,'app\common\validate\User.card');
			if($result !== true){
				$this->error($result);
			}
			//检查卡密
			$card=Db::name('card')->where(['num'=>$this->params['num']])->find();
			if(!$card){
				$this->error('卡密不存在');
			}
			if($card['status']==2){
				$this->error('卡密被使用');
			}
			if($card['keys']<>$this->params['keys']){
				$this->error('卡密的密码不对，请确认');
			}
			Db::startTrans();
			try{
				$update=Db::name('card')->where(['num'=>$this->params['num']])->update(['status'=>2,'uid'=>$this->user['id'],'use'=>$this->timestamp(10)]);
				$money=new \app\common\logic\Money();
				$add=$money->cardInc($this->user['id'],1,$card['id']);
				//记录日志
				$this->logs()->database($this->user['id'],5,$this->request->ip(),'卡密【'.$card['num'].'】成功使用，金额【'.$card['money'].'】',0,0);
				Db::commit();
			}catch(\Exception $e){
				Db::rollback();
				$this->error('充值失败，请重试');
			}
			$this->success('成功充值'.$card['money'],$this->app->route->buildUrl('index'));
		}else{
			//变量
			View::assign(['users'=>$this->user,'tickets'=>$this->ticket]);
			//视图
			return View::fetch();
		}
	}
}