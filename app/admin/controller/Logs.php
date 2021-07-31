<?php
/*
* 系统日志
* @author zakeear <zakeear@86dede.com>
* @version v0.0.6
* @time 2019-06-05
*/

namespace app\admin\controller;

use app\Base;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\View;

class Logs extends Base
{
	//首页
	/**
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function index(): string
	{
		$where = [];
		//分页
		$pageSize = isset($this->params['limit']) && $this->params['limit'] && $this->params['limit'] <= 100 && $this->params['limit'] >= 5 ? $this->params['limit'] : 20;//每页记录数
		//类型
		if (isset($this->params['type']) && $this->params['type']) {
			$where['type'] = $this->params['type'];
		}
		//用户
		if (isset($this->params['uid']) && $this->params['uid']) {
			$where['uid'] = $this->params['uid'];
		}
		//管理
		if (isset($this->params['op_id']) && $this->params['op_id']) {
			$where['op_id'] = $this->params['op_id'];
		}
		//主机
		if (isset($this->params['sub_id']) && $this->params['sub_id']) {
			$where['sub_id'] = $this->params['sub_id'];
		}
		if (isset($this->params['keywords']) && $this->params['keywords']) {
			//关键字搜索
			$list = Db::name('logs')->where($where)->where('content', 'like', '%' . $this->params['keywords'] . '%')->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		} else {
			//列表
			$list = Db::name('logs')->where($where)->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		}
		$list->each(function ($item) {
			if ($item['op_id']) {
				//管理
				$member = Db::name('admin')->field('id,user_name')->cache(true)->where(['id' => $item['op_id']])->find();
				$item['user_name'] = $member['user_name'];
				$item['nick_name'] = $member['user_name'];
				$item['real_name'] = $member['user_name'];
			} else {
				//用户
				$member = Db::name('user')->field('id,user_name,nick_name,real_name')->where(['id' => $item['uid']])->find();
				$item['user_name'] = $member['username'];
				$item['nick_name'] = $member['nick_name'];
				$item['real_name'] = $member['real_name'];
			}
			return $item;
		});
		//变量
		View::assign(['list' => $list, 'enum' => $this->enum()]);
		//视图
		return View::fetch();
	}
}