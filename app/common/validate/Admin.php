<?php

namespace app\common\validate;

use think\Validate;

class Admin extends Validate
{
	protected $rule = [
		'username' => 'require',
		'password' => 'require|min:6|alphaDash',
	];
	protected $message = [
		'username.require' => '请输入账号',
		'password.require' => '请输入登录密码',
		'password.min' => '登录密码必须大于6个字符',
		'password.alphaDash' => '登录密码只能是字母、数字、下划线和破折号',
		'repass.require' => '请确认登录密码',
		'repass.min' => '确认密码必须大于6个字符',
		'repass.alphaDash' => '确认密码只能是字母、数字、下划线和破折号',
		'repass.confirm' => '确认密码必须和登录密码一致',
	];
	protected $scene = [
		'login' => ['username', 'password'],
		'lock' => ['password'],
	];
}