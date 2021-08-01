<?php
/*
* 主机扣费类
* @author zakeear <zakeear@86dede.com>
* @version v0.2.0
* @time 2019-06-13
*/

namespace app\queue\job;

use app\common\logic\Logs;
use app\queue\controller\Host;
use app\Timer;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\queue\Job;
use think\facade\Db;

class Money
{

	/**
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws Exception
	 */
	public function fire(Job $job, $data)
	{
		// job
		$isJobDone = $this->doJob($job, $data);
		$attempts = $job->attempts() + 1;
		if ($isJobDone) {
			print('<info>[' . date('Y-m-d H:i:s', time()) . "] 主机" . $data['host_name'] . "扣费任务完成，任务销毁</info>\n");
			$job->delete();
		} else {
			$release = strtotime(date('Y-m-d H:', time()) . '00') + 3599 + date('i', $data['submit']) * 60 + date('s', $data['submit']) - time();
			print('<info>[' . date('Y-m-d H:i:s', time()) . "] " . $release . "秒后执行主机" . $data['host_name'] . "第" . $attempts . "次扣费任务</info>\n");
			$job->release($release);
		}
	}

	/**
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws Exception
	 */
	private function doJob($job, $data): bool
	{
		// job
		$attempts = $job->attempts();
		print('<info>[' . date('Y-m-d H:i:s', time()) . "] 主机" . $data['host_name'] . "第" . $attempts . "次扣费</info>\n");
		// 主机
		$server = Db::name('server')->field('id,uid,sub_id,month,money,host_name,deduction')->where(['sub_id' => $data['sub_id'], 'status' => 2])->find();
		if (!$server) {
			print('<info>[' . date('Y-m-d H:i:s') . "] 主机" . $data['host_name'] . "已经不存在或者被删除!" . "\n");
			return true;
		}
		// 日志
		$logs = new Logs();
		// 配置
		$config = Db::name('config')->field('rate,month,is_buy,vultr_api,vultr_keys,web_name,web_icon,time')->where(['status' => 1])->order('time', 'desc')->find();
		if ($server['deduction'] == $config['month']) {
			// 扣费
			$logs->database($server['uid'], 5, '', '主机【' . $server['host_name'] . '】达到月付限额，本月不再扣费', 1, $server['sub_id']);
			// _
			Db::name('server')->where(['sub_id' => $data['sub_id']])->update(['deduction' => 0, 'deduction_time' => 0]);
			// job
			$attempts = $attempts - 1;
			print('<info>[' . date('Y-m-d H:i:s') . "] 主机" . $data['host_name'] . "已经达到月付上限" . $attempts . "次\n");
			// 队列
			$job = new Host();
			// 删除
			$job->addTask($data['sub_id'], 'destroy');
			// 创建
			$release = Timer::nextMonth()[0] + date('i', $data['submit']) * 60 + date('s', $data['submit']) - time();// 下月重新计费
			$job->addTask($data['sub_id'], 'server', $release);
			// 返回
			return true;
		}
		// 用户
		$user = Db::name('user')->field('id,money')->where(['id' => $server['uid']])->find();
		if ($user['money'] < $server['money']) {
			// 日志
			$logs->database($server['uid'], 5, '', '主机【' . $server['host_name'] . '】不足于支付：【' . $server['money'] . '】', 1, $server['sub_id']);
			// 删除
			Db::name('server')->where(['sub_id' => $data['sub_id']])->update(['status' => 5, 'destroy' => time()]);
			// 日志
			$logs->database($server['uid'], 5, '', '主机【' . $server['host_name'] . '】删除', 1, $server['sub_id']);
			// 队列
			$job = new Host();
			// 删除
			$job->addTask($data['sub_id'], 'destroy');
			// job
			print('<info>[' . date('Y-m-d H:i:s') . "] 用户余额不足于支付" . $user['money'] . "元\n");
			// 返回
			return true;
		}
		// 费用
		$money = new \app\common\logic\Money();
		// 扣费
		$money->hostDec($server['uid'], 1, $server['money'], 1, $server['sub_id'], '主机【' . $server['host_name'] . '】支付费用');
		// 日志
		$logs->database($server['uid'], 5, '', '主机【' . $server['host_name'] . '】支付费用：【' . $server['money'] . '】', 1, $server['sub_id']);
		// 计数
		Db::name('server')->where(['sub_id' => $data['sub_id']])->inc('deduction')->update(['deduction_time' => time()]);
		// 返回
		return true;
	}
}