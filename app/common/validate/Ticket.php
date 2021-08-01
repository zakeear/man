<?php

namespace app\common\validate;

use think\Validate;

class Ticket extends Validate
{
	protected $rule = [
		'id' => 'require',
		'type' => 'require|number|in:1,2,3,4,5',
		'title' => 'require|chsDash',
		'content' => 'require',
	];

	protected $message = [
		'type.require' => '请选择类型',
		'type.number' => '类型必须是一个数字',
		'type.in' => '类型错误',
		'title.require' => '请输入标题',
		'title.chsDash' => '标题不能有特殊字符',
		'content.require' => '请输入内容',
	];

	protected $scene = [
		'detail' => ['id'],
		'replay' => ['id', 'title', 'content'],
		'add' => ['type', 'title', 'content'],
	];
}