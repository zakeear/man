<?php
/*
* 用户控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.0.4
* @time 2019-06-05
*/

namespace app\admin\controller;

use app\Base;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\View;

class Member extends Base
{
	//首页
	/**
	 * @throws DbException
	 */
	public function index(): string
	{
		$where = [];
		//分页
		$pageSize = isset($this->params['limit']) && $this->params['limit'] && $this->params['limit'] <= 100 && $this->params['limit'] >= 5 ? $this->params['limit'] : 20;//每页记录数
		//用户组
		if (isset($this->params['group']) && $this->params['group']) {
			$where['group'] = $this->params['group'];
		}
		//状态
		if (isset($this->params['status']) && $this->params['status']) {
			$where['status'] = $this->params['status'];
		}
		if (isset($this->params['keywords']) && $this->params['keywords']) {
			//关键字搜索
			$list = Db::name('user')->where($where)->where('user_name|nick_name|real_name', 'like', '%' . $this->params['keywords'] . '%')->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		} else {
			//列表
			$list = Db::name('user')->where($where)->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		}
		//变量
		View::assign(['list' => $list, 'enum' => $this->enum()]);
		//视图
		return View::fetch();
	}
	//恢复

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function open()
	{
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\User.detail');
		if ($result !== true) {
			$this->result(0, $result);
		}
		$user = Db::name('user')->field('id,username')->where(['id' => $this->params['id']])->find();
		if (!$user) {
			$this->result(0, '用户不存在!');
		}
		Db::startTrans();
		try {
			Db::name('user')->where(['id' => $this->params['id']])->update(['status' => 1, 'lock_uid' => 1, 'lock_time' => $this->timestamp()]);
			Db::commit();
			$this->logs()->database(0, 2, $this->request->ip(), '恢复【' . $user['username'] . '】', $this->admin['id']);
		} catch (Exception $e) {
			Db::rollback();
			$this->result(0, '操作失败，请重试!');
		}
		$this->result(0, '恢复成功!');
	}
	//禁用

	/**
	 * @throws ModelNotFoundException
	 * @throws DataNotFoundException
	 * @throws DbException
	 */
	public function close()
	{
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\User.detail');
		if ($result !== true) {
			$this->result(0, $result);
		}
		$user = Db::name('user')->field('id,username')->where(['id' => $this->params['id']])->find();
		if (!$user) {
			$this->result(0, '用户不存在!');
		}
		Db::startTrans();
		try {
			Db::name('user')->where(['id' => $this->params['id']])->update(['status' => 2, 'lock_uid' => 1, 'lock_time' => $this->timestamp()]);
			Db::commit();
			$this->logs()->database(1, 2, $this->request->ip(), '禁用【' . $user['username'] . '】', $this->admin['id']);
		} catch (Exception $e) {
			Db::rollback();
			$this->result(0, '操作失败，请重试!');
		}
		$this->result(0, '禁用成功!');
	}

	//详情

	/**
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function detail()
	{
		if ($this->request->isPost()) {
			//验证数据
			$result = $this->validate($this->params, 'app\common\validate\User.detail');
			if ($result !== true) {
				$this->error($result);
			}
			//验证会员
			$user = Db::name('user')->where(['id' => $this->params['id']])->find();
			if (!$user) {
				$this->error('用户不存在!');
			}
			if (isset($this->params['nick_name']) && $this->params['nick_name']) {
				$data['nick_name'] = $this->params['nick_name'];
			}
			if (isset($this->params['real_name']) && $this->params['real_name']) {
				$data['real_name'] = $this->params['real_name'];
			}
			if (isset($this->params['status']) && $this->params['status']) {
				$data['status'] = $this->params['status'];
			}
			if (isset($this->params['status']) && $this->params['status']) {
				$data['status'] = $this->params['status'];
			}
			if (isset($this->params['server']) && $this->params['server']) {
				$data['server'] = $this->params['server'];
			}
			Db::startTrans();
			try {
				Db::name('user')->where(['id' => $this->params['id']])->update($data);
				Db::commit();
				//记录日志
				$this->logs()->database(1, 2, $this->request->ip(), '修改【' . $user['username'] . '】资料', $this->admin['id']);
			} catch (Exception $e) {
				Db::rollback();
				$this->error('修改失败，请重试');
			}
			$this->success('修改成功', $this->app->route->buildUrl('index'));
		} else {
			//验证数据
			$result = $this->validate($this->params, 'app\common\validate\User.detail');
			if ($result !== true) {
				$this->error($result);
			}
			//会员
			$user = Db::name('user')->where(['id' => $this->params['id']])->find();
			if (!$user) {
				$this->error('用户不存在!');
			}
			//变量
			View::assign(['user' => $user, 'enum' => $this->enum()]);
			//视图
			return View::fetch();
		}
	}
}