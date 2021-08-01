<?php

namespace app\common\validate;

use think\Validate;

class User extends Validate
{
	protected $rule = [
		'username' => 'require|email|min:6|max:32',
		'safe_email' => 'require|email',
		'password' => 'require|min:6|alphaDash',
		'repass' => 'require|min:6|alphaDash|confirm:password',
		'verify_code' => 'require|chsDash',
		'id' => 'require|integer|min:1|max:10',
		'money' => 'require|number',
		'num' => 'require|chsDash',
		'keys' => 'require|chsDash',
	];
	protected $message = [
		'username.require' => '请输入账号',
		'username.email' => '账号必须是一个邮箱',
		'username.min' => '账号长度必须大于6个字符',
		'username.max' => '账号长度不能超过32个字符',
		'safe_email.require' => '请填写密保邮箱',
		'safe_email.email' => '密保邮箱格式不对',
		'password.require' => '请输入登录密码',
		'password.min' => '登录密码长度必须大于6个字符',
		'password.alphaDash' => '登录密码只能是字母、数字、下划线和破折号',
		'repass.require' => '请确认登录密码',
		'repass.min' => '确认密码长度必须大于6个字符',
		'repass.alphaDash' => '确认密码只能是字母、数字、下划线和破折号',
		'repass.confirm' => '确认密码必须和登录密码一致',
		'verify_code.require' => '请输入邮件确认码',
		'verify_code.chsDash' => '邮件确认码格式不对',
		'id.require' => '会员ID错误',
		'id.integer' => '会员ID错误必须是一个数字',
		'id.min' => '会员ID长度必须大于1个字符',
		'id.max' => '会员ID长度不能超过32个字符',
		'money.require' => '请输入金额',
		'money.number' => '金额必须是一个数字',
		'num.require' => '请输入卡号',
		'num.chsDash' => '卡号格式不对',
		'keys.require' => '请输入卡密密码',
		'keys.chsDash' => '卡密密码格式不对',
	];

	protected $scene = [
		'login' => ['username', 'password'],
		'register' => ['username', 'password', 'repass'],
		'forget' => ['username'],
		'verify_email' => ['username', 'verify_code', 'password', 'repass'],
		'lock' => ['password'],
		'detail' => ['id'],
		'account' => ['username', 'money'],
		'card' => ['num', 'keys'],
	];
}