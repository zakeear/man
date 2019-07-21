<?php
/*
* 主机控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.1.6
* @time 2019-06-10
*/
namespace app\index\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Server extends Base{
	//首页
	public function index(){
		//默认只显示当前用户
		$where=[['uid','=',$this->user['id']]];
		//默认不显示已经删除
		array_push($where,['status','<>',5]);
		//状态
		if(isset($this->params['status']) && $this->params['status']){
			array_pop($where);
			array_push($where,['status','=',$this->params['status']]);
		}
		//分页
		$pageSize=isset($this->params['limit']) && $this->params['limit'] && $this->params['limit']<=100 && $this->params['limit']>=5 ? $this->params['limit'] : 20;//每页记录数
		if(isset($this->params['keywords']) && $this->params['keywords']){
			//关键字搜索
			array_pop($where);
			$list=Db::name('server')->where($where)->where('instances|hostname','like','%'.$this->params['keywords'].'%')->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}else{
			//列表
			$list=Db::name('server')->where($where)->order('id desc')->paginate($pageSize,false,['query'=>$this->params]);
		}
		//变量
		View::assign(['list'=>$list,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
		//视图
		return View::fetch();
	}
	//添加
	public function add(){
		if($this->user['status']<>1){
			$this->error('您的账户被限制使用!');
		}
		if($this->config['is_buy']<>1){
			$this->error('系统已经关闭购买!');
		}
		if($this->request->isPost()){
			//权限检查
			if($this->user['status']<>1){
				$this->error('您的账户被限制使用!');
			}
			//验证数据
			$result=$this->validate($this->params,'app\common\validate\Server.add');
			if($result !== true){
				$this->error($result);
			}
			//快照和系统二选一
			if(!isset($this->params['OSID']) && !isset($this->params['SNAPSHOTID']) ){
				$this->error('请选择操作系统或者快照');
			}
			$this->params['OSID']=isset($this->params['SNAPSHOTID']) && $this->params['SNAPSHOTID'] ? 164 : $this->params['OSID'];
			//创建台数限制
			$servers=Db::name('server')->where('uid='.$this->user['id'].' and status<>5')->count('id');
			if($servers>=$this->user['server']){
				$this->error('您账户目前只能创建'.$this->user['server'].'台主机');
			}
			//组装
			$data=['dcid'=>$this->params['DCID'],'osid'=>$this->params['OSID'],'vpsplanid'=>$this->params['VPSPLANID'],'enable_ipv6'=>$this->params['enable_ipv6'],'uid'=>$this->user['id'],'time'=>$this->timestamp(10),'money'=>0,'deduction'=>0];
			if(isset($this->params['SNAPSHOTID']) && $this->params['SNAPSHOTID']){
				$data['snapshotid']=$this->params['SNAPSHOTID'];
				$snapshot=Db::name('snapshot')->cache(true,6)->where(['snapshotid'=>$this->params['SNAPSHOTID']])->find();
				$data['password']=$snapshot['password'];
				$data['port']=$snapshot['port'];
			}
			//计算主机配置费用
			$host=Db::name('host')->where(['vpsplanid'=>$data['vpsplanid']])->find();
			if($host['hour'] && $host['hour']>0){
				$data['money']=$data['money']+$host['hour'];
			}
			//主机名
			if(isset($this->params['hostname']) && $this->params['hostname']){
				$data['hostname']=$this->params['hostname'];
			}
			//汇率
			$data['money']=$data['money'] * $this->config['rate'];
			//会员
			$user=Db::name('user')->where(['id'=>$this->user['id']])->find();
			//24小时费用
			$dayMoney=round($data['money']*24,2);
			if($user['money']<$dayMoney){
				$this->error('您账户余额不足于支付该主机24小时费用了...所需费用：'.$dayMoney.'元');
			}
			//组装数据
			$server=['DCID'=>intval($data['dcid']),'OSID'=>intval($data['osid']),'VPSPLANID'=>intval($data['vpsplanid']),'enable_ipv6'=>$data['enable_ipv6'],'label'=>$data['hostname'],'hostname'=>$data['hostname']];
			if(isset($this->params['SNAPSHOTID']) && $this->params['SNAPSHOTID']){
				$server['SNAPSHOTID']=$this->params['SNAPSHOTID'];
			}
			//请求Vultr
			$result=$this->curl()->send($this->config['vultr_api'].'/v1/server/create','POST',$this->config['vultr_keys'],$server);
			$json=json_decode($result,true);
			if(!$json){
				$this->error('添加失败，原因:'.$result);
			}
			//$data['subid']=mt_rand(100000,999999);
			$data['subid']=$json['SUBID'];
			//请求Vultr
			$result=json_decode($this->curl()->send($this->config['vultr_api'].'/v1/server/list?SUBID='.$json['SUBID'],'GET',$this->config['vultr_keys']),true);
			$data['ip_address']=$result['main_ip'];
			if(!isset($this->params['SNAPSHOTID'])){
				$data['password']=$result['default_password'];
			}
			$data['password']=\think\helper\Str::random(12);
			//插入数据库
			$add=Db::name('server')->insertGetId($data);
			//日志
			$this->logs()->database($this->user['id'],4,$this->request->ip(),'添加主机【'.$data['hostname'].'】',0,$data['subid']);
			//队列
			$job=new \app\queue\controller\Host();
			$job->addTask($data['subid'],'server',0);
			$this->success('添加成功，请等待系统配分配IP地址和root密码...',url('detail',['id'=>$add]),20);
		}else{
			//变量
			View::assign(['hostname'=>'host_'.\think\helper\Str::random(10),'dc'=>dc_online(),'os'=>os_online(),'host'=>host_online(),'snapshot'=>snapshot_online(),'rate'=>$this->config['rate'],'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
			//视图
			return View::fetch();
		}
	}
	//详情
	public function detail(){
		if($this->request->isPost()){
			//权限检查
			if($this->user['status']<>1){
				$this->error('您的账户被限制使用!');
			}
			//验证数据
			$result=$this->validate($this->params,'app\common\validate\Server.detail');
			if($result !== true){
				$this->error($result);
			}
			//验证会员
			$server=Db::name('server')->where(['id'=>$this->params['id']])->find();
			if(!$server){
				$this->error('主机不存在!');
			}
			if($server['uid']<>$this->user['id']){
				$this->error('主机不属于您!');
			}
			if(isset($this->params['instances']) && $this->params['instances']){
				$data['instances']=$this->params['instances'];
			}
			if(isset($this->params['hostname']) && $this->params['hostname']){
				$data['hostname']=$this->params['hostname'];
			}
			Db::startTrans();
			try{
				Db::name('server')->where(['id'=>$this->params['id']])->update($data);
				Db::commit();
				//记录日志
				$this->logs()->database(1,2,$this->request->ip(),'修改【'.$server['hostname'].'】资料');
			}catch(\Exception $e){
				Db::rollback();
				$this->error('修改失败，请重试');
			}
			$this->success('修改成功',url('index'));
		}else{
			//验证数据
			$result=$this->validate($this->params,'app\common\validate\Server.detail');
			if($result !== true){
				$this->error($result);
			}
			//主机
			$server=Db::name('server')->where(['id'=>$this->params['id']])->find();
			if(!$server){
				$this->error('主机不存在!');
			}
			if($server['uid']<>$this->user['id']){
				$this->error('主机不属于您!');
			}
			//用户
			$user=Db::name('user')->where(['id'=>$server['uid']])->find();
			if(!$user){
				$this->error('用户不存在!');
			}
			$server['user']=$user;
			//IP地址
			if(!$server['ip_address'] || $server['ip_address'] == '0.0.0.0' || !$server['password']){
				//请求Vultr
				$result=json_decode($this->curl()->send($this->config['vultr_api'].'/v1/server/list?SUBID='.$server['subid'],'GET',$this->config['vultr_keys']),true);
				if(!$result){
					$this->error('该主机信息异常：'.$result);
				}
				if(!$server['ip_address'] || $server['ip_address'] == '0.0.0.0' ){
					Db::name('server')->where(['id'=>$this->params['id']])->update(['ip_address'=>$result['main_ip']]);
				}
				$server['ip_address']=$result['main_ip'];
				if(!$server['password']){
					Db::name('server')->where(['id'=>$this->params['id']])->update(['password'=>$result['default_password']]);
					$server['password']=$result['default_password'];
				}
			}
			//变量
			View::assign(['server'=>$server,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
			//视图
			return View::fetch();
		}
	}
	//启动
	public function start(){
		if($this->user['status']<>1){
			$this->result(0,'您的账户被限制使用!');
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\Server.detail');
		if($result !== true){
			$this->result(0,$result);
		}
		//主机
		$server=Db::name('server')->where(['id'=>$this->params['id']])->find();
		if(!$server){
			$this->result(0,'主机不存在!');
		}
		if($server['uid']<>$this->user['id']){
			$this->result(0,'主机不属于您!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'].'/v1/server/start','POST',$this->config['vultr_api'],['SUBID'=>$server['subid']]);
		//日志
		$this->logs()->database($this->user['id'],4,$this->request->ip(),'启动主机【'.$server['hostname'].'】',0,$server['subid']);
		//主机
		$start=Db::name('server')->where(['id'=>$this->params['id']])->update(['status'=>2,'op'=>$this->user['id'],'op_time'=>$this->timestamp(10)]);
		if(!$start){
			$this->result(0,'操作失败!');
		}
		$this->result(1,'操作成功，请等待启动完成!');
	}
	//停止
	public function halt(){
		//权限检查
		if($this->user['status']<>1){
			$this->result(0,'您的账户被限制使用!');
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\Server.detail');
		if($result !== true){
			$this->result(0,$result);
		}
		//主机
		$server=Db::name('server')->where(['id'=>$this->params['id']])->find();
		if(!$server){
			$this->result(0,'主机不存在!');
		}
		if($server['uid']<>$this->user['id']){
			$this->result(0,'主机不属于您!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'].'/v1/server/halt','POST',$this->config['vultr_keys'],['SUBID'=>$server['subid']]);
		//日志
		$this->logs()->database($this->user['id'],4,$this->request->ip(),'停止主机【'.$server['hostname'].'】',0,$server['subid']);
		//主机
		$halt=Db::name('server')->where(['id'=>$this->params['id']])->update(['status'=>1,'op'=>$this->user['id'],'op_time'=>$this->timestamp(10)]);
		if(!$halt){
			$this->result(0,'操作失败!');
		}
		$this->result(1,'操作成功，请等待停止完成!');
	}
	//重启
	public function reboot(){
		if($this->user['status']<>1){
			$this->result(0,'您的账户被限制使用!');
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\Server.detail');
		if($result !== true){
			$this->result(0,$result);
		}
		//主机
		$server=Db::name('server')->where(['id'=>$this->params['id']])->find();
		if(!$server){
			$this->result(0,'主机不存在!');
		}
		if($server['uid']<>$this->user['id']){
			$this->result(0,'主机不属于您!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'].'/v1/server/reboot','POST',$this->config['vultr_keys'],['SUBID'=>$server['subid']]);
		//日志
		$this->logs()->database($this->user['id'],4,$this->request->ip(),'重启主机【'.$server['hostname'].'】',0,$server['subid']);
		//主机
		$reboot=Db::name('server')->where(['id'=>$this->params['id']])->update(['status'=>1,'op'=>$this->user['id'],'op_time'=>$this->timestamp(10)]);
		if(!$reboot){
			$this->result(0,'操作失败!');
		}
		$this->result(1,'操作成功，请等待重启完成!');
	}
	//重装
	public function reinstall(){
		if($this->user['status']<>1){
			$this->result(0,'您的账户被限制使用!');
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\Server.detail');
		if($result !== true){
			$this->result(0,$result);
		}
		//主机
		$server=Db::name('server')->where(['id'=>$this->params['id']])->find();
		if(!$server){
			$this->result(0,'主机不存在!');
		}
		if($server['uid']<>$this->user['id']){
			$this->result(0,'主机不属于您!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'].'/v1/server/reinstall','POST',$this->config['vultr_keys'],['SUBID'=>$server['subid']]);
		//日志
		$this->logs()->database($this->user['id'],4,$this->request->ip(),'重装主机【'.$server['hostname'].'】',0,$server['subid']);
		//主机
		$reinstall=Db::name('server')->where(['id'=>$this->params['id']])->update(['status'=>1,'op'=>$this->user['id'],'op_time'=>$this->timestamp(10)]);
		if(!$reinstall){
			$this->result(0,'操作失败!');
		}
		$this->result(1,'操作成功，请等待重装完成!');
	}
	//删除
	public function destroy(){
		if($this->user['status']<>1){
			$this->result(0,'您的账户被限制使用!');
		}
		//验证数据
		$result=$this->validate($this->params,'app\common\validate\Server.detail');
		if($result !== true){
			$this->result(0,$result);
		}
		//主机
		$server=Db::name('server')->where(['id'=>$this->params['id']])->find();
		if(!$server){
			$this->result(0,'主机不存在!');
		}
		if($server['uid']<>$this->user['id']){
			$this->result(0,'主机不属于您!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'].'/v1/server/destroy','POST',$this->config['vultr_keys'],['SUBID'=>$server['subid']]);
		//日志
		$this->logs()->database($this->user['id'],4,$this->request->ip(),'删除主机【'.$server['hostname'].'】',0,$server['subid']);
		//主机
		$destroy=Db::name('server')->where(['id'=>$this->params['id']])->update(['status'=>5,'op_time'=>$this->timestamp(10),'destroy'=>$this->timestamp(10)]);
		if(!$destroy){
			$this->result(0,'操作失败!');
		}
		$this->result(1,'删除成功!');
	}
}