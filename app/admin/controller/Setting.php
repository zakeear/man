<?php
/*
* 系统设置
* @author zakeear <zakeear@86dede.com>
* @version v0.0.5
* @time 2019-07-01
*/
namespace app\admin\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Setting extends Base{
	//位置
	public function dc(){
		//定义
		$where=[];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//大区
		if(isset($this->params['fid']) && $this->params['fid']){
			$where['fid']=$this->params['fid'];
		}
		//状态
		if(isset($this->params['status']) && $this->params['status']){
			$where['status']=$this->params['status'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('dc')->where($where)->where('name','like','%'.$this->params['keywords'].'%')->where('fid','>',0)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('dc')->where($where)->where('fid','>',0)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		//变量
		View::assign(['list'=>$list,'enum'=>$this->enum(),'fa'=>dc_fa_all()]);
		//视图
		return View::fetch();
	}
	//添加位置
	public function add_dc(){
		if($this->request->isPost()){
			//名称
			if(!isset($this->params['name']) || !$this->params['name']){
				$this->error('请输入位置名称!');
			}
			//DCID
			if(!isset($this->params['dcid']) || !$this->params['dcid'] || !is_numeric($this->params['dcid'])){
				$this->error('请输入DCID!');
			}
			//组装
			$data=['name'=>$this->params['name'],'DCID'=>$this->params['dcid'],'status'=>1,'time'=>$this->timestamp(10)];
			//父级
			if(isset($this->params['fid']) && $this->params['fid']){
				$where['fid']=$this->params['fid'];
			}
			//检验
			$check=Db::name('dc')->where(['DCID'=>$this->params['dcid']])->find();
			if($check){
				$this->error('DCID已经被使用!');
			}
			//检验
			$check=Db::name('dc')->where(['name'=>$this->params['name']])->find();
			if($check){
				$this->error('名称已经被使用!');
			}
			//插入
			$add=Db::name('dc')->insertGetId($data);
			if(!$add){
				$this->error('添加失败!');
			}
			//日志
			$this->logs()->database(0,4,$this->request->ip(),'添加位置【'.$data['name'].'】',$this->admin['id'],0);
			$this->success('添加成功',$this->app->route->buildUrl('dc'));
		}else{
			//变量
			View::assign(['fa'=>dc_fa_all()]);
			//视图
			return View::fetch();
		}
	}
	//禁用位置
	public function del_dc(){
		//主机
		$dc=Db::name('dc')->where(['id'=>$this->params['id']])->find();
		if(!$dc){
			$this->result(0,'记录不存在!');
		}
		//位置
		$destroy=Db::name('dc')->where(['id'=>$this->params['id']])->update(['status'=>2]);
		if(!$destroy){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),'下架位置【'.$dc['name'].'】',$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//恢复位置
	public function back_dc(){
		//位置
		$dc=Db::name('dc')->where(['id'=>$this->params['id']])->find();
		if(!$dc){
			$this->result(0,'记录不存在!');
		}
		//操作
		$destroy=Db::name('dc')->where(['id'=>$this->params['id']])->update(['status'=>1]);
		if(!$destroy){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),'上架位置【'.$dc['name'].'】',$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//系统
	public function os(){
		//定义
		$where=[];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//类型
		if(isset($this->params['fid']) && $this->params['fid']){
			$where['fid']=$this->params['fid'];
		}
		//状态
		if(isset($this->params['status']) && $this->params['status']){
			$where['status']=$this->params['status'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('os')->where($where)->where('name','like','%'.$this->params['keywords'].'%')->where('fid','>',0)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('os')->where($where)->where('fid','>',0)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		//变量
		View::assign(['list'=>$list,'enum'=>$this->enum(),'fa'=>dc_fa_all()]);
		//视图
		return View::fetch();
	}
	//添加系统
	public function add_os(){
		if($this->request->isPost()){
			//名称
			if(!isset($this->params['name']) || !$this->params['name']){
				$this->error('请输入系统名称!');
			}
			//OSID
			if(!isset($this->params['osid']) || !$this->params['osid'] || !is_numeric($this->params['osid'])){
				$this->error('请输入OSID!');
			}
			//DCID
			if(!isset($this->params['fid']) || !$this->params['fid'] || !is_numeric($this->params['fid'])){
				$this->error('请选择系统类型!');
			}
			//组装
			$data=['fid'=>$this->params['fid'],'name'=>$this->params['name'],'OSID'=>$this->params['osid'],'status'=>1,'time'=>$this->timestamp(10)];
			//价格
			if(isset($this->params['hour']) && $this->params['hour'] && is_numeric($this->params['hour'])){
				$data['hour']=$this->params['hour'];
			}
			//检验
			$check=Db::name('os')->where(['OSID'=>$this->params['osid']])->find();
			if($check){
				$this->error('OSID已经被使用!');
			}
			//检验
			$check=Db::name('os')->where(['name'=>$this->params['name']])->find();
			if($check){
				$this->error('名称已经被使用!');
			}
			//插入
			$add=Db::name('os')->insertGetId($data);
			if(!$add){
				$this->error('添加失败!');
			}
			//日志
			$this->logs()->database(0,4,$this->request->ip(),'添加系统【'.$data['name'].'】',$this->admin['id'],0);
			$this->success('添加成功',$this->app->route->buildUrl('os'));
		}else{
			//变量
			View::assign(['fa'=>dc_fa_all()]);
			//视图
			return View::fetch();
		}
	}
	//禁用系统
	public function del_os(){
		//系统
		$os=Db::name('os')->where(['id'=>$this->params['id']])->find();
		if(!$os){
			$this->result(0,'记录不存在!');
		}
		//操作
		$destroy=Db::name('os')->where(['id'=>$this->params['id']])->update(['status'=>2]);
		if(!$destroy){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),'下架系统【'.$os['name'].'】',$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//恢复系统
	public function back_os(){
		//系统
		$os=Db::name('dc')->where(['id'=>$this->params['id']])->find();
		if(!$os){
			$this->result(0,'记录不存在!');
		}
		//操作
		$destroy=Db::name('os')->where(['id'=>$this->params['id']])->update(['status'=>1]);
		if(!$destroy){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),'上架系统【'.$os['name'].'】',$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//配置
	public function host(){
		//定义
		$where=[];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//状态
		if(isset($this->params['status']) && $this->params['status']){
			$where['status']=$this->params['status'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('host')->where($where)->where('name','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('host')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		//变量
		View::assign(['list'=>$list,'enum'=>$this->enum(),'rate'=>$this->config['rate']]);
		//视图
		return View::fetch();
	}
	//添加配置
	public function add_host(){
		if($this->request->isPost()){
			//CPU
			if(!isset($this->params['cpu']) || !$this->params['cpu']){
				$this->error('请输入CPU');
			}
			//RAM
			if(!isset($this->params['ram']) || !$this->params['ram']){
				$this->error('请输入RAM');
			}
			//SSD
			if(!isset($this->params['ssd']) || !$this->params['ssd']){
				$this->error('请输入SSD');
			}
			//带宽
			if(!isset($this->params['bandwidth']) || !$this->params['bandwidth']){
				$this->error('请输入bandwidth');
			}
			//VPSPLANID
			if(!isset($this->params['vpsplanid']) || !$this->params['vpsplanid'] || !is_numeric($this->params['vpsplanid'])){
				$this->error('请输入VPSPLANID!');
			}
			//hour
			if(!isset($this->params['hour']) || !$this->params['hour'] || !is_numeric($this->params['hour'])){
				$this->error('请输入价格');
			}
			//组装
			$data=['cpu'=>$this->params['cpu'],'ram'=>$this->params['ram'],'ssd'=>$this->params['ssd'],'bandwidth'=>$this->params['bandwidth'],'hour'=>$this->params['hour'],'vpsplanid'=>$this->params['vpsplanid'],'status'=>1,'time'=>$this->timestamp(10)];
			//检验
			$check=Db::name('host')->where(['vpsplanid'=>$this->params['vpsplanid']])->find();
			if($check){
				$this->error('VPSPLANID已经被使用!');
			}
			//插入
			$add=Db::name('host')->insertGetId($data);
			if(!$add){
				$this->error('添加失败!');
			}
			//日志
			$this->logs()->database(0,4,$this->request->ip(),'添加配置【'.$data['vpsplanid'].'】',$this->admin['id'],0);
			$this->success('添加成功',$this->app->route->buildUrl('host'));
		}else{
			return View::fetch();
		}
	}
	//禁用配置
	public function del_host(){
		//配置
		$host=Db::name('host')->where(['id'=>$this->params['id']])->find();
		if(!$host){
			$this->result(0,'记录不存在!');
		}
		//操作
		$destroy=Db::name('host')->where(['id'=>$this->params['id']])->update(['status'=>2]);
		if(!$destroy){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),"下架配置【".get_host($host['vpsplanid'])."】",$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//恢复配置
	public function back_host(){
		//配置
		$host=Db::name('host')->where(['id'=>$this->params['id']])->find();
		if(!$host){
			$this->result(0,'记录不存在!');
		}
		//操作
		$destroy=Db::name('host')->where(['id'=>$this->params['id']])->update(['status'=>1]);
		if(!$destroy){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),"上架配置【".get_host($host['vpsplanid'])."】",$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//快照
	public function snapshot(){
		//定义
		$where=[];
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		//状态
		if(isset($this->params['status']) && $this->params['status']){
			$where['status']=$this->params['status'];
		}
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			$list=Db::name('snapshot')->where($where)->where('name','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('snapshot')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		//变量
		View::assign(['list'=>$list,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
	}
	//添加快照
	public function add_snapshot(){
		if($this->request->isPost()){
			//名称
			if(!isset($this->params['name']) || !$this->params['name']){
				$this->error('请输入快照名称!');
			}
			//快照id
			if(!isset($this->params['snapshotid']) || !$this->params['snapshotid']){
				$this->error('请输入快照ID!');
			}
			//快照密码
			if(!isset($this->params['password']) || !$this->params['password']){
				$this->error('请输入系统密码!');
			}
			//端口
			if(!isset($this->params['port']) || !$this->params['port']){
				$this->error('请输入系统默认!');
			}
			//组装
			$data=['name'=>$this->params['name'],'password'=>$this->params['password'],'snapshotid'=>$this->params['snapshotid'],'port'=>$this->params['port'],'status'=>1,'time'=>$this->timestamp(10)];
			//重复检测
			$check=Db::name('snapshot')->where(['snapshotid'=>$this->params['snapshotid']])->find();
			if($check){
				$this->error('快照ID已经存在');
			}
			//插入
			$add=Db::name('snapshot')->insertGetId($data);
			if(!$add){
				$this->error('添加失败!');
			}
			$this->success('添加成功',$this->app->route->buildUrl('snapshot'));
		}else{
			//视图
			return View::fetch();
		}
	}
	//禁用快照
	public function del_snapshot(){
		//配置
		$snapshot=Db::name('snapshot')->where(['id'=>$this->params['id']])->find();
		if(!$snapshot){
			$this->result(0,'记录不存在!');
		}
		//操作
		$back=Db::name('host')->where(['id'=>$this->params['id']])->update(['status'=>2]);
		if(!$back){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),"下架快照【".get_snapshot($snapshot['snapshotid'])."】",$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//恢复快照
	public function back_snapshot(){
		//配置
		$snapshot=Db::name('snapshot')->where(['id'=>$this->params['id']])->find();
		if(!$snapshot){
			$this->result(0,'记录不存在!');
		}
		//操作
		$back=Db::name('host')->where(['id'=>$this->params['id']])->update(['status'=>1]);
		if(!$back){
			$this->result(0,'操作失败!');
		}
		//日志
		$this->logs()->database(0,4,$this->request->ip(),"上架快照【".get_snapshot($snapshot['snapshotid'])."】",$this->admin['id'],0);
		$this->result(1,'操作成功!');
	}
	//系统设置
	public function config(){
		if($this->request->isPost()){
			//验证数据
			$result=$this->validate($this->params,'app\common\validate\Config.edit');
			if($result !== true){
				$this->error($result);
			}
			//组装
			$data=['rate'=>$this->params['rate'],'month'=>$this->params['month'],'is_buy'=>$this->params['is_buy'],'vultr_api'=>$this->params['vultr_api'],'vultr_keys'=>$this->params['vultr_keys'],'status'=>1,'time'=>$this->timestamp(10)];
			Db::startTrans();
			try{
				//清理
				Db::name('config')->where('status','=',1)->update(['status'=>2]);
				//插入
				Db::name('config')->insertGetId($data);
				//提交
				Db::commit();
				//记录日志
				$this->logs()->database(0,2,$this->request->ip(),'修改系统配置',$this->admin['id'],0);
				$this->success('修改成功！',$this->app->route->buildUrl('config'));
			}catch(\Exception $e){
				Db::rollback();
				$this->success('修改成功');
			}
		}else{
			//变量
			View::assign(['config'=>$this->config]);
			//视图
			return View::fetch();
		}
	}
}