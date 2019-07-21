<?php
/*
* 系统日志控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.0.5
* @time 2019-05-04
*/
namespace app\index\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Logs extends Base{
	//首页
	public function index(){
		$where['uid']=$this->user['id'];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//类型
		if(isset($this->params['type']) && $this->params['type']){
			$where['type']=$this->params['type'];
		}
		//用户
		if(isset($this->params['uid']) && $this->params['uid']){
			$where['uid']=$this->params['uid'];
		}
		//主机
		if(isset($this->params['subid']) && $this->params['subid']){
			$where['subid']=$this->params['subid'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('logs')->where($where)->where('content','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('logs')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		$list->each(function($item,$key){
			//用户
			if($item['uid']==1){
				$item['username']='admin';
				$item['nickname']='admin';
				$item['realname']='admin';
			}else{
				$member=Db::name('user')->field('id,username,nickname,realname')->where(['id'=>$item['uid']])->find();
				$item['username']=$member['username'];
				$item['nickname']=$member['nickname'];
				$item['realname']=$member['realname'];
			}
			return $item;
		});
		//变量
		View::assign(['list'=>$list,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
	}
}