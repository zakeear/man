<?php

namespace app\common\validate;

use think\Validate;

class Server extends Validate
{
	protected $rule = [
		'DCID' => 'require|integer',
		'OSID' => 'require|integer',
		'VPSPLANID' => 'require|integer',
		'hostname' => 'chsDash',
		'enable_ipv6' => 'require|lower',
		'id' => 'require|integer|min:1|max:10',
		'month' => 'require|integer|in:1,2,3,4,5,6,7,8,9,10,11,12,24,36,60,120',
	];
	protected $message = [
		'DCID.require' => '请选择服务器位置',
		'DCID.integer' => 'DCID必须是一个数字',
		'OSID.require' => '请选择操作系统',
		'OSID.integer' => 'OSID必须是一个数字',
		'VPSPLANID.require' => '请选择主机配置',
		'VPSPLANID.integer' => 'VPSPLANID必须是一个数字',
		'hostname.chsDash' => '主机名不能有非法字符',
		'enable_ipv6.require' => '请确认是否启用IPV6',
		'enable_ipv6.lower' => 'enable_ipv6必须是小写字母',
		'id.require' => 'ID错误',
		'id.integer' => 'ID错误必须是一个数字',
		'id.min' => 'ID长度必须大于1个字符',
		'id.max' => 'ID长度不能超过32个字符',
		'month.require' => '请选择购买时长',
		'month.integer' => '购买时长必须是一个数字',
		'month.in' => '购买时长错误',
	];
	protected $scene = [
		'add' => ['DCID', 'hostname', 'enable_ipv6'],
		'regitser' => ['username', 'password', 'repass'],
		'forget' => ['username'],
		'verify_email' => ['username', 'verify_code', 'password', 'repass'],
		'lock' => ['password'],
		'detail' => ['id'],
	];
}