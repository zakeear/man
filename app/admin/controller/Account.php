<?php
/*
* 财务流水
* @author zakeear <zakeear@86dede.com>
* @version v0.0.6
* @time 2019-07-10
*/
namespace app\admin\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Account extends Base{
	//首页
	public function index(){
		$where=[];
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
			$list=Db::name('account')->field('id,uid,type,way,style,money,time,trade')->where($where)->where('trade','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('account')->field('id,uid,type,way,style,money,time,trade')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
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
	//手动入账
	public function add(){
		if(!$this->request->isPost()){
			return View::fetch();
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\User.account');
		if($result !== true){
			$this->error($result);
		}
		//验证用户
		$user=Db::name('user')->field('id,username,status,money')->where(['username'=>$this->params['username']])->find();
		if(!$user){
			$this->error('用户不存在');
		}
		if($user['status']<>1){
			$this->error('用户被禁用');
		}
		//组装数据
		$data=[
			'uid'=>$user['id'],
			'type'=>$this->params['type'],
			'money'=>$this->params['money'],
			'time'=>$this->timestamp(10),
			'timestamp'=>'admin_op_'.$this->admin['id'].'_'.$this->timestamp(10).'_'.number_code(6)
		];
		if(isset($this->params['subid']) && $this->params['subid']){
			$data['subid']=$this->params['subid'];
		}
		if(isset($this->params['rechargeid']) && $this->params['rechargeid']){
			$data['rechargeid']=$this->params['rechargeid'];
		}
		if(isset($this->params['trade']) && $this->params['trade']){
			$data['trade']=$this->params['trade'];
		}
		//金额验证
		if($user['money'] < $this->params['money'] && $this->params['type'] == 2){
			$this->error('用户余额不足扣除');
		}
		//流水
		$account=Db::name('account')->insertGetId($data);
		if(!$account){
			$this->error('流水失败');
		}
		//用户
		if($this->params['type']==1){
			$money=Db::name('user')->where(['username'=>$this->params['username']])->inc('money',$this->params['money'])->update();
		}else{
			$money=Db::name('user')->where(['username'=>$this->params['username']])->dec('money',$this->params['money'])->update;
		}
		if(!$money){
			$this->error('入账失败');
		}
		//日志
		if($this->params['type']==1){
			$this->logs()->database($user['id'],5,$this->request->ip(),'手动入账【'.$this->params['money'].'】,操作人：【'.$this->admin['username'].'】',$this->admin['id'],0);
		}else{
			$this->logs()->database($user['id'],5,$this->request->ip(),'手动扣款【'.$this->params['money'].'】,操作人：【'.$this->admin['username'].'】',$this->admin['id'],0);
		}
		$this->success('操作成功',url('index'));
	}
}