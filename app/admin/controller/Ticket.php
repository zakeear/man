<?php
/*
* 工单
* @author zakeear <zakeear@86dede.com>
* @version v0.0.2
* @time 2019-06-05
*/
namespace app\admin\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Ticket extends Base{
	//首页
	public function index(){
		$where=[];
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
		//用户
		if(isset($this->params['uid']) && $this->params['uid']){
			$where['uid']=$this->params['uid'];
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
				//管理
				$member=Db::name('admin')->field('id,username')->cache(true)->where(['id'=>$item['op_id']])->find();
				$item['admin']=$member['username'];
			}else{
				$item['admin']='';
			}
			if($item['uid']){
				//用户
				$member=Db::name('user')->field('id,username,nickname,realname')->where(['id'=>$item['uid']])->find();
				$item['username']=$member['username'];
			}else{
				$item['username']='';
			}
			return $item;
		});
		//变量
		View::assign(['list'=>$list,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
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
		View::assign(['ticket'=>$ticket,'enum'=>$this->enum()]);
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
		if(Request::instance()->isPost()){
			if($ticket['status']==3){
				$this->error('工单已经关闭');
			}
			//组装
			$data=['op_id'=>$this->admin['id'],'tid'=>$this->params['id'],'type'=>$this->params['type'],'content'=>$this->params['content'],'time'=>$this->timestamp(10)];
			Db::startTrans();
			try{
				Db::name('ticket')->where(['id'=>$this->params['id']])->update(['status'=>$this->params['type'],'update_time'=>$this->timestamp(10),'op_id'=>$this->admin['id'],'op_time'=>$this->timestamp(10)]);
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
			View::assign(['ticket'=>$ticket,'enum'=>$this->enum()]);
			//视图
			return View::fetch();
		}
	}
}