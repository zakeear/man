<?php

namespace app\common\validate;

use think\Validate;

class Account extends Validate
{
	protected $rule = [
		'type' => 'require|number|length:1',
		'way' => 'require|number|length:1',
		'style' => 'require|number|length:1',
		'money' => 'require|number',
	];
	protected $message = [
		'type.require' => '请输入收支',
		'type.number' => '必须是一个数字',
		'type.length' => '必须1个字符',
		'way.require' => '请输入来源',
		'way.number' => '必须是一个数字',
		'way.length' => '必须1个字符',
		'style.require' => '请输入分类',
		'style.number' => '必须是一个数字',
		'style.length' => '必须1个字符',
		'money.require' => '请输入金额',
		'money.number' => '金额必须是一个数字',
	];
	protected $scene = [];
}