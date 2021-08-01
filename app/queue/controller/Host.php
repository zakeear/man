<?php
/*
* 队列任务
* @author zakeear <zakeear@86dede.com>
* @version v0.1.5
* @time 2019-06-10
*/

namespace app\queue\controller;

use think\Exception;
use think\facade\Db;
use think\facade\Queue;

class Host
{
	/**
	 * 添加队列
	 * @access public
	 * @param int $sub_id 主机id
	 * @param string $type 任务名
	 * @param int $times 延时秒数
	 * @throws Exception
	 */
	public function addTask(int $sub_id = 0, string $type = 'server', int $times = 0)
	{
		$server = Db::name('server')->where(['sub_id' => $sub_id])->find();
		if (!$server) {
			exit;
		}
		switch ($type) {
			case 'server':
				$jobHandlerClassName = 'app\queue\job\Money@fire';
				$jobDataArr = ['submit' => time(), 'doit' => time() + $times, 'sub_id' => $server['sub_id'], 'hostname' => $server['hostname']];
				$jobQueueName = "Money";
				break;
			case 'destroy':
				$jobHandlerClassName = 'app\queue\job\Destroy@fire';
				$jobDataArr = ['submit' => time(), 'doit' => time() + $times, 'sub_id' => $server['sub_id'], 'hostname' => $server['hostname']];
				$jobQueueName = "Destroy";
				break;
			default:
				break;
		}
		if ($times == 0) {
			Queue::push($jobHandlerClassName, $jobDataArr, $jobQueueName);
		} else {
			Queue::later($times, $jobHandlerClassName, $jobDataArr, $jobQueueName);
		}
	}
}