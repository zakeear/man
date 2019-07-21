<?php
/*
* 主机扣费类
* @author zakeear <zakeear@86dede.com>
* @version v0.2.0
* @time 2019-06-13
*/
namespace app\queue\job;
use think\queue\Job;
use think\facade\Db;
class Money{
	public function fire(Job $job,$data){
		//job
		$isJobDone=$this->doJob($job,$data);
		$attempts=$job->attempts()+1;
		if($isJobDone){
			print('<info>['.date('Y-m-d H:i:s',time())."] 主机".$data['hostname']."扣费任务完成，任务销毁</info>\n");
			$job->delete();
		}else{
			$release=strtotime(date('Y-m-d H:',time()).'00')+3599+date('i',$data['submit'])*60+date('s',$data['submit'])-time();
			print('<info>['.date('Y-m-d H:i:s',time())."] ".$release."秒后执行主机".$data['hostname']."第".$attempts."次扣费任务</info>\n");
			$job->release($release);
		}
	}
	private function doJob($job,$data){
		//job
		$attempts=$job->attempts();
		print('<info>['.date('Y-m-d H:i:s',time())."] 主机".$data['hostname']."第".$attempts."次扣费</info>\n");
		//主机
		$server=Db::name('server')->field('id,uid,subid,month,money,hostname,deduction')->where(['subid'=>$data['subid'],'status'=>2])->find();
		if(!$server){
			print('<info>['.date('Y-m-d H:i:s')."] 主机".$data['hostname']."已经不存在或者被删除!"."\n");
			return true;
		}
		//日志
		$logs=new \app\common\logic\Logs();
		//配置
		$this->config = Db::name('config')->field('rate,month,is_buy,vultr_api,vultr_keys,web_name,web_icon,time')->where(['status'=>1])->order('time','desc')->find();
		if($server['deduction']==$this->config['month']){
			//扣费
			$logs->database($server['uid'],5,'','主机【'.$server['hostname'].'】达到月付限额，本月不再扣费',1,$server['subid']);
			//计数
			Db::name('server')->where(['subid'=>$data['subid']])->update(['deduction'=>0,'deduction_time'=>0]);
			//job
			$attempts=$attempts-1;
			print('<info>['.date('Y-m-d H:i:s')."] 主机".$data['hostname']."已经达到月付上限".$attempts."次\n");
			//队列
			$job=new \app\queue\controller\Host();
			//删除
			$job->addTask($data['subid'],'destroy',0);
			//创建
			$release=\app\Timer::nextMonth()[0]+date('i',$data['submit'])*60+date('s',$data['submit'])-time();//下月重新计费
			$job->addTask($data['subid'],'server',$release);
			//返回
			return true;
		}
		//用户
		$user=Db::name('user')->field('id,money')->where(['id'=>$server['uid']])->find();
		if($user['money']<$server['money']){
			//日志
			$logs->database($server['uid'],5,'','主机【'.$server['hostname'].'】不足于支付：【'.$server['money'].'】',1,$server['subid']);
			//删除
			Db::name('server')->where(['subid'=>$data['subid']])->update(['status'=>5,'destroy'=>time()]);
			//日志
			$logs->database($server['uid'],5,'','主机【'.$server['hostname'].'】删除',1,$server['subid']);
			//队列
			$job=new \app\queue\controller\Host();
			//删除
			$job->addTask($data['subid'],'destroy',0);
			//job
			print('<info>['.date('Y-m-d H:i:s')."] 用户余额不足于支付".$user['money']."元\n");
			//返回
			return true;
		}
		//费用
		$money=new \app\common\logic\Money();
		//扣费
		$money->hostDec($server['uid'],1,$server['money'],1,$server['subid'],'主机【'.$server['hostname'].'】支付费用');
		//日志
		$logs->database($server['uid'],5,'','主机【'.$server['hostname'].'】支付费用：【'.$server['money'].'】',1,$server['subid']);
		//计数
		Db::name('server')->where(['subid'=>$data['subid']])->inc('deduction',1)->update(['deduction_time'=>time()]);
	}
}