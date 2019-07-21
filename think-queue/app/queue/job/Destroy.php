<?php
/*
* 删除主机
* @author zakeear <zakeear@86dede.com>
* @version v0.0.7
* @time 2019-06-22
*/
namespace app\queue\job;
use think\queue\Job;
use think\facade\Db;
class Destroy{
	public function fire(Job $job,$data){
		//任务执行
		$isJobDone=$this->doJob($job,$data);
		if($isJobDone){
			print('<info>['.date('Y-m-d H:i:s',time())."] 主机".$data['hostname']."已删除，任务销毁</info>\n");
			$job->delete();
		}
	}
	private function doJob($job,$data){
		//job
		$id=json_decode($job->getRawBody(),true)['id'];
		$attempts=$job->attempts();
		print('<info>['.date('Y-m-d H:i:s',time())."] [".$id."] 主机：".$data['hostname']."。第".$attempts."次删除</info>"."\n");
		//配置
		$this->config = Db::name('config')->field('rate,month,is_buy,vultr_api,vultr_keys,web_name,web_icon,time')->where(['status'=>1])->order('time','desc')->find();
		//请求Vultr
		$curl=new \app\common\logic\Curl();
		$curl->send($this->config['vultr_api'].'/v1/server/destroy','POST',$this->config['vultr_keys'],['SUBID'=>$data['subid']]);
		return true;
	}
}