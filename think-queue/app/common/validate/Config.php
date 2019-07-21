<?php
namespace app\common\validate;
use think\Validate;
class Config extends Validate{
	protected $rule=[
		'rate'=>'require|float',
		'month'=>'require|integer',
		'is_buy'=>'require|integer|in:1,2',
		'vultr_api'=>'require|url',
		'vultr_keys'=>'require|alphaNum',
	];
	protected $message=[
		'rate.require'=>'请输入汇率',
		'rate.float'=>'汇率必须是数字，可包含小数点',
		'month.require'=>'请设置月付上限',
		'month.integer'=>'月付上限必须是整数',
		'is_buy.require'=>'请设置购买开关状态',
		'is_buy.integer'=>'购买开关状态必须是整数',
		'is_buy.in'=>'购买开关状态格式不对',
		'vultr_api.require'=>'请设置vultr网关',
		'vultr_api.url'=>'vultr网关必须是URL',
		'vultr_keys.require'=>'请设置vultr密钥',
		'vultr_keys.url'=>'vultr密钥只能包含数字和字母',
	];
	protected $scene=[
		'edit'=>['rate','month','is_buy','vultr_api','vultr_keys'],
	];
}