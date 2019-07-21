<?php
/*
* 工单
* @author zakeear <zakeear@86dede.com>
* @version v0.0.4
* @time 2019-05-04
*/
namespace app\index\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Ticket extends Base{
	//首页
	public function index(){
		$where['uid']=$this->user['id'];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//状态
		if(isset($this->params['status']) && $this->params['status']){
			$where['status']=$this->params['status'];
		}
		//类型
		if(isset($this->params['type']) && $this->params['type']){
			$where['type']=$this->params['type'];
		}
		//管理
		if(isset($this->params['op_id']) && $this->params['op_id']){
			$where['op_id']=$this->params['op_id'];
		}
		//主机
		if(isset($this->params['subid']) && $this->params['subid']){
			$where['subid']=$this->params['subid'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('ticket')->where($where)->where('title|content','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('ticket')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		$list->each(function($item,$key){
			if($item['op_id']){
				$member=Db::name('admin')->field('id,username')->cache(true)->where(['id'=>$item['op_id']])->find();
				$item['admin']=$member['username'];
			}else{
				$item['admin']='';
			}
			return $item;
		});
		//变量
		View::assign(['list'=>$list,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
	}
	//提交
	public function add(){
		if($this->request->isPost()){
			//验证数据
			$result=$this->validate($this->params,'app\common\validate\Ticket.add');
			if($result !== true){
				$this->error($result);
			}
			//组装
			$data=['uid'=>$this->user['id'],'type'=>$this->params['type'],'title'=>$this->params['title'],'content'=>$this->params['content'],'time'=>$this->timestamp(10)];
			//管理
			if(isset($this->params['hostname']) && $this->params['hostname']){
				$server=Db::name('server')->where(['hostname'=>$this->params['hostname']])->find();
				if(!$server){
					$this->error('主机不存在');
				}
				$data['subid']=$server['subid'];
			}
			//频繁提交
			$check=Db::name('ticket')->where(['uid'=>$this->user['id'],'status'=>1])->order('id desc')->find();
			if($check && $check['time']-$this->timestamp(10)>=3600){
				$this->error('一小时内仅能提交一次，谢谢配合！');
			}
			Db::startTrans();
			try{
				Db::name('ticket')->insertGetId($data);
				Db::commit();
			}catch(\Exception $e){
				Db::rollback();
				$this->error('提交失败，请重试');
			}
			$this->success('提交成功',url('index'));
		}else{
			//变量
			View::assign(['users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
			//视图
			return View::fetch();
		}
	}
	//详情
	public function detail(){
		//验证处理数据
		$result=$this->validate($this->params,'app\common\validate\Ticket.detail');
		if($result !== true){
			$this->error($result);
		}
		//验证工单
		$ticket=Db::name('ticket')->where(['id'=>$this->params['id']])->find();
		if(!$ticket){
			$this->error('工单不存在');
		}
		//关联主机
		if($ticket['subid']>0){
			$ticket['server']=Db::name('server')->where(['subid'=>$ticket['subid']])->find();
		}else{
			$ticket['server']=[];
		}
		//日志
		$ticket['log']=Db::name('ticket_log')->where(['tid'=>$ticket['id']])->select()->toArray();;
		foreach($ticket['log'] as $k=>$v){
			if($v['op_id']){
				$ticket['log'][$k]['admin']=Db::name('admin')->cache(true)->where(['id'=>$v['op_id']])->value('username');
			}else{
				$ticket['log'][$k]['admin']='';
			}
			if($v['uid']){
				$ticket['log'][$k]['user']=Db::name('user')->cache(true)->where(['id'=>$v['uid']])->value('username');
			}else{
				$ticket['log'][$k]['user']='';
			}
		}
		//变量
		View::assign(['ticket'=>$ticket,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
	}
	//处理
	public function replay(){
		//验证处理数据
		$result=$this->validate($this->params,'app\common\validate\Ticket.detail');
		if($result !== true){
			$this->error($result);
		}
		//验证工单
		$ticket=Db::name('ticket')->where(['id'=>$this->params['id']])->find();
		if(!$ticket){
			$this->error('工单不存在');
		}
		if($this->request->isPost()){
			if($ticket['status']==3){
				$this->error('工单已经关闭');
			}
			//组装
			$data=['uid'=>$this->user['id'],'tid'=>$this->params['id'],'type'=>$this->params['type'],'content'=>$this->params['content'],'time'=>$this->timestamp(10)];
			//频繁提交
			$check=Db::name('ticket')->where(['uid'=>$this->user['id'],'status'=>1])->order('id desc')->find();
			if($check && $check['time']-$this->timestamp(10)>=3600){
				$this->error('一小时内仅能提交一次，谢谢配合！');
			}
			Db::startTrans();
			try{
				Db::name('ticket')->where(['id'=>$this->params['id']])->update(['status'=>$this->params['type'],'update_time'=>$this->timestamp(10)]);
				Db::name('ticket_log')->insertGetId($data);
				Db::commit();
			}catch(\Exception $e){
				Db::rollback();
				$this->error('提交失败，请重试');
			}
			$this->success('提交成功',url('index'));
		}else{
			//关联主机
			if($ticket['subid']>0){
				$ticket['server']=Db::name('server')->where(['subid'=>$ticket['subid']])->find();
			}else{
				$ticket['server']=[];
			}
			//变量
			View::assign(['ticket'=>$ticket,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
			//视图
			return View::fetch();
		}
	}
}