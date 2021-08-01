<?php

namespace app\common\validate;

use think\Validate;

class Email extends Validate
{
	protected $rule = [
		'email' => 'require|email',
		'subject' => 'require|min:6',
		'message' => 'require|min:6',
	];

	protected $message = [
		'email.require' => '请输入接受邮箱',
		'email.email' => '邮箱输入错误',
		'subject.require' => '请输入邮件标题',
		'subject.min' => '邮件标题必须大于6个字符',
		'message.require' => '请输入邮件内容',
		'message.min' => '邮件内容必须大于6个字符',
	];

	protected $scene = [
		'send' => ['email', 'subject', 'message'],
	];
}