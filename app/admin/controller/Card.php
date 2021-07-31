<?php
/*
* 卡密
* @author zakeear <zakeear@86dede.com>
* @version v0.0.5
* @time 2019-07-10
*/

namespace app\admin\controller;

use app\Base;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\View;
use think\helper\Str;

class Card extends Base
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
		//状态
		if (isset($this->params['status']) && $this->params['status']) {
			$where['status'] = $this->params['status'];
		}
		//用户
		if (isset($this->params['uid']) && $this->params['uid']) {
			$where['uid'] = $this->params['uid'];
		}
		if (isset($this->params['keywords']) && $this->params['keywords']) {
			//关键字搜索
			$list = Db::name('card')->where($where)->where('num', 'like', '%' . $this->params['keywords'] . '%')->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		} else {
			//列表
			$list = Db::name('card')->where($where)->order('id desc')->paginate($pageSize, false, ['query' => $this->params]);
		}
		$list->each(function ($item) {
			//用户
			if ($item['uid']) {
				$member = Db::name('user')->field('id,user_name,nick_name,real_name')->where(['id' => $item['uid']])->find();
				$item['user_name'] = $member['user_name'];
				$item['nick_name'] = $member['nick_name'];
				$item['real_name'] = $member['real_name'];
			} else {
				$item['user_name'] = '';
				$item['nick_name'] = '';
				$item['real_name'] = '';
			}
			return $item;
		});
		//变量
		View::assign(['list' => $list, 'enum' => $this->enum()]);
		//视图
		return View::fetch();
	}

	//生成卡密
	public function add()
	{
		if (!$this->request->isPost()) {
			return View::fetch();
		}
		//张数
		$limit = isset($this->params['limit']) && $this->params['limit'] && $this->params['limit'] <= 100 && $this->params['limit'] >= 1 ? intval($this->params['limit']) : 1;//生成数量
		//金额
		$money = isset($this->params['money']) && $this->params['money'] && $this->params['money'] <= 1000 && $this->params['money'] >= 0 ? $this->params['money'] : 1;//面额
		Db::startTrans();
		try {
			for ($i = 0; $i <= $limit - 1; $i++) {
				$data = ['num' => $this->nums(), 'money' => $money, 'keys' => Str::random(8), 'create' => $this->timestamp()];
				Db::name('card')->insertGetId($data);
				Db::commit();
			}
		} catch (Exception $e) {
			Db::rollback();
			$this->error('生成失败，请重试');
		}
		//日志
		$this->logs()->database(1, 5, $this->request->ip(), '生成卡密', $this->admin['id']);
		$this->success('生成成功', $this->app->route->buildUrl('index'));
	}

	//生成唯一的卡号

	/**
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws DataNotFoundException
	 */
	private function nums(): int
	{
		$nums = mt_rand(100000000000, 999999999999);
		$check = Db::name('card')->where(['num' => $nums])->find();
		if ($check) {
			$nums = mt_rand(100000000000, 999999999999);
		}
		return $nums;
	}
}