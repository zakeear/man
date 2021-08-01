<?php
/*
* 主机
* @author zakeear <zakeear@86dede.com>
* @version v0.0.9
* @time 2019-06-10
*/

namespace app\admin\controller;

use app\Base;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\View;

class Server extends Base
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
		//用户
		if (isset($this->params['uid']) && $this->params['uid']) {
			$where['uid'] = $this->params['uid'];
		}
		//位置
		if (isset($this->params['dc_id']) && $this->params['dc_id']) {
			$where['dc_id'] = $this->params['dc_id'];
		}
		//系统
		if (isset($this->params['os_id']) && $this->params['os_id']) {
			$where['os_id'] = $this->params['os_id'];
		}
		//配置
		if (isset($this->params['vps_plan_id']) && $this->params['vps_plan_id']) {
			$where['vps_plan_id'] = $this->params['vps_plan_id'];
		}
		//状态
		if (isset($this->params['status']) && $this->params['status']) {
			$where['status'] = $this->params['status'];
		}
		if (isset($this->params['keywords']) && $this->params['keywords']) {
			//关键字搜索
			$list = Db::name('server')->where($where)->where('hostname', 'like', '%' . $this->params['keywords'] . '%')->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		} else {
			//列表
			$list = Db::name('server')->where($where)->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		}
		$list->each(function ($item) {
			//用户
			if ($item['uid']) {
				$member = Db::name('user')->field('id,user_name,nick_name,real_name')->where(['id' => $item['uid']])->find();
				$item['user_name'] = $member['user_name'];
			} else {
				$item['username'] = '';
			}
			return $item;
		});
		//变量
		View::assign(['list' => $list, 'enum' => $this->enum(), 'dc' => dc_all(), 'os' => os_all()]);
		//视图
		return View::fetch();
	}

	//详情

	/**
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function detail(): string
	{
		if ($this->request->isPost()) {
			//验证数据
			$result = $this->validate($this->params, 'app\common\validate\User.detail');
			if ($result !== true) {
				$this->error($result);
			}
			//验证会员
			$server = Db::name('server')->where(['id' => $this->params['id']])->find();
			if (!$server) {
				$this->error('主机不存在!');
			}
			if (isset($this->params['hostname']) && $this->params['hostname']) {
				$data['hostname'] = $this->params['hostname'];
			}
			//Trans
			Db::startTrans();
			try {
				Db::name('server')->where(['id' => $this->params['id']])->update($data);
				//日志
				$this->logs()->database(0, 4, $this->request->ip(), '修改【' . $server['hostname'] . '】资料', $this->admin['id'], $server['sub_id']);
				Db::commit();
			} catch (Exception $e) {
				Db::rollback();
				$this->error('修改失败，请重试');
			}
			$this->success('修改成功', 'index');
		}
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\Server.detail');
		if ($result !== true) {
			$this->error($result);
		}
		//主机
		$server = Db::name('server')->where(['id' => $this->params['id']])->find();
		if (!$server) {
			$this->error('主机不存在!');
		}
		//用户
		$user = Db::name('user')->where(['id' => $server['uid']])->find();
		if (!$user) {
			$this->error('用户不存在!');
		}
		$server['user'] = $user;
		//IP地址
		if (!$server['ip_address'] || $server['ip_address'] == '0.0.0.0' || !$server['password']) {
			//请求Vultr
			$result = json_decode($this->curl()->send($this->config['vultr_api'] . '/v1/server/list?SUBID=' . $server['sub_id'], 'GET', $this->config['vultr_keys']), true);
			if (!$result) {
				$this->error('该主机信息异常');
			}
			if (!$server['ip_address'] || $server['ip_address'] == '0.0.0.0') {
				Db::name('server')->where(['id' => $this->params['id']])->update(['ip_address' => $result['main_ip']]);
			}
			$server['ip_address'] = $result['main_ip'];
			if (!$server['password']) {
				Db::name('server')->where(['id' => $this->params['id']])->update(['password' => $result['default_password']]);
			}
			$server['password'] = $result['default_password'];
		}
		//变量
		View::assign(['server' => $server, 'enum' => $this->enum()]);
		//视图
		return View::fetch();
	}

	//启动

	/**
	 * @throws ModelNotFoundException
	 * @throws DataNotFoundException
	 * @throws DbException
	 */
	public function start()
	{
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\Server.detail');
		if ($result !== true) {
			$this->result(0, $result);
		}
		//主机
		$server = Db::name('server')->where(['id' => $this->params['id']])->find();
		if (!$server) {
			$this->result(0, '主机不存在!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'] . '/v1/server/start', 'POST', $this->config['vultr_keys'], ['SUBID' => $server['sub_id']]);
		//日志
		$this->logs()->database(0, 4, $this->request->ip(), '启动主机【' . $server['hostname'] . '】', $this->admin['id'], $server['sub_id']);
		//主机
		$start = Db::name('server')->where(['id' => $this->params['id']])->update(['status' => 2, 'op' => $this->admin['id'], 'op_time' => $this->timestamp()]);
		if (!$start) {
			$this->result(0, '操作失败!');
		}
		$this->result(1, '操作成功，请等待启动完成!');
	}

	//停止

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function halt()
	{
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\Server.detail');
		if ($result !== true) {
			$this->error($result);
		}
		//主机
		$server = Db::name('server')->where(['id' => $this->params['id']])->find();
		if (!$server) {
			$this->result(0, '主机不存在!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'] . '/v1/server/halt', 'POST', $this->config['vultr_keys'], ['SUBID' => $server['sub_id']]);
		//日志
		$this->logs()->database(0, 4, $this->request->ip(), '停止主机【' . $server['hostname'] . '】', $this->admin['id'], $server['sub_id']);
		//主机
		$halt = Db::name('server')->where(['id' => $this->params['id']])->update(['status' => 1, 'op' => $this->admin['id'], 'op_time' => $this->timestamp()]);
		if (!$halt) {
			$this->result(0, '操作失败!');
		}
		$this->result(1, '操作成功，请等待停止完成!');
	}

	//重启

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function reboot()
	{
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\Server.detail');
		if ($result !== true) {
			$this->result(0, $result);
		}
		//主机
		$server = Db::name('server')->where(['id' => $this->params['id']])->find();
		if (!$server) {
			$this->result(0, '主机不存在!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'] . '/v1/server/reboot ', 'POST', $this->config['vultr_keys'], ['SUBID' => $server['sub_id']]);
		//日志
		$this->logs()->database(0, 4, $this->request->ip(), '重启主机【' . $server['hostname'] . '】', $this->admin['id'], $server['sub_id']);
		//主机
		$reboot = Db::name('server')->where(['id' => $this->params['id']])->update(['op' => $this->admin['id'], 'op_time' => $this->timestamp()]);
		if (!$reboot) {
			$this->result(0, '操作失败!');
		}
		$this->result(1, '操作成功，请等待重启完成!');
	}

	//重装

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	public function reinstall()
	{
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\Server.detail');
		if ($result !== true) {
			$this->result(0, $result);
		}
		//主机
		$server = Db::name('server')->where(['id' => $this->params['id']])->find();
		if (!$server) {
			$this->result(0, '主机不存在!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'] . '/v1/server/reinstall', 'POST', $this->config['vultr_keys'], ['SUBID' => $server['sub_id']]);
		//日志
		$this->logs()->database(0, 4, $this->request->ip(), '重装主机【' . $server['hostname'] . '】', $this->admin['id'], $server['sub_id']);
		//主机
		$reinstall = Db::name('server')->where(['id' => $this->params['id']])->update(['op' => $this->admin['id'], 'op_time' => $this->timestamp()]);
		if (!$reinstall) {
			$this->result(0, '操作失败!');
		}
		$this->result(1, '操作成功，请等待重装完成!');
	}

	//删除

	/**
	 * @throws ModelNotFoundException
	 * @throws DataNotFoundException
	 * @throws DbException
	 */
	public function destroy()
	{
		//验证数据
		$result = $this->validate($this->params, 'app\common\validate\Server.detail');
		if ($result !== true) {
			$this->result(0, $result);
		}
		//主机
		$server = Db::name('server')->where(['id' => $this->params['id']])->find();
		if (!$server) {
			$this->result(0, '主机不存在!');
		}
		//请求Vultr
		$this->curl()->send($this->config['vultr_api'] . '/v1/server/destroy', 'POST', $this->config['vultr_keys'], ['SUBID' => $server['sub_id']]);
		//日志
		$this->logs()->database(0, 4, $this->request->ip(), '删除主机【' . $server['hostname'] . '】', $this->admin['id'], $server['sub_id']);
		//主机
		$destroy = Db::name('server')->where(['id' => $this->params['id']])->update(['status' => 5, 'op' => $this->admin['id'], 'op_time' => $this->timestamp(), 'destroy' => $this->timestamp()]);
		if (!$destroy) {
			$this->result(0, '操作失败!');
		}
		$this->result(1, '删除成功!');
	}
}