<?php
/*
* 删除主机
* @author zakeear <zakeear@86dede.com>
* @version v0.0.7
* @time 2019-06-22
*/

namespace app\queue\job;

use app\common\logic\Curl;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\queue\Job;
use think\facade\Db;

class Destroy
{

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function fire(Job $job, $data)
	{
		//任务执行
		$isJobDone = $this->doJob($job, $data);
		if ($isJobDone) {
			print('<info>[' . date('Y-m-d H:i:s', time()) . "] 主机" . $data['hostname'] . "已删除，任务销毁</info>\n");
			$job->delete();
		}
	}

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	private function doJob($job, $data): bool
	{
		//job
		$id = json_decode($job->getRawBody(), true)['id'];
		$attempts = $job->attempts();
		print('<info>[' . date('Y-m-d H:i:s', time()) . "] [" . $id . "] 主机：" . $data['hostname'] . "。第" . $attempts . "次删除</info>" . "\n");
		//配置
		$config = Db::name('config')->field('rate,month,is_buy,vultr_api,vultr_keys,web_name,web_icon,time')->where(['status' => 1])->order('time', 'desc')->find();
		//请求Vultr
		$curl = new Curl();
		$curl->send($config['vultr_api'] . '/v1/server/destroy', 'POST', $config['vultr_keys'], ['SUBID' => $data['sub_id']]);
		return true;
	}
}