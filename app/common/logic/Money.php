<?php
/*
* 账户流水
* @author zakeear <zakeear@86dede.com>
* @version v0.0.8
* @time 2019-06-10
*/
namespace app\common\logic;
use think\facade\Db;
class Money{
	/**
	 * 直接加余额
	 * @access public
	 * @param integer $uid 用户id
	 * @param integer $class 分类
	 * @param float $money 金额
	 * @param integer $op 操作人
	 * @param string $content 备注
	 * @return boolean
	 * @throws \think\exception\DbException
	 */
	public function setInc(int $uid=0,int $class=1,float $money=0.000,int $op=1,string $content=''){
		//重组
		$data=['uid'=>$uid,'type'=>1,'class'=>$class,'money'=>sprintf("%.3f",$money),'way'=>1,'style'=>1,'op'=>$op,'time'=>time(),'timestamp'=>'system_inc_'.$op.'_'.time().'_'.rand(10000,99999),'content'=>$content];
		//流水
		$account=Db::name('account')->insertGetId($data);
		if(!$account){
			return false;
		}
		//会员
		$user=Db::name('user')->where(['id'=>$uid])->inc('money',$data['money'])->update();
		if(!$user){
			return false;
		}
		return true;
	}
	/**
	 * 直接扣余额
	 * @access public
	 * @param integer $uid 用户id
	 * @param integer $class 分类
	 * @param float $money 金额
	 * @param integer $op 操作人
	 * @param string $content 备注
	 * @return boolean
	 * @throws \think\exception\DbException
	 */
	public function setDec(int $uid=0,int $class=1,float $money=0.000,int $op=1,string $content=''){
		//重组
		$data=['uid'=>$uid,'type'=>2,'classs'=>$class,'money'=>sprintf("%.3f",$money),'way'=>1,'style'=>1,'op'=>$op,'time'=>time(),'timestamp'=>'system_dec_'.$op.'_'.time().'_'.rand(10000,99999),'content'=>$content];
		//流水
		$account=Db::name('account')->insertGetId($data);
		if(!$account){
			return false;
		}
		//会员
		$user=Db::name('user')->where(['id'=>$uid])->dec('money',$data['money'])->update();
		if(!$user){
			return false;
		}
		return true;
	}
	/**
	 * 主机加余额
	 * @access public
	 * @param integer $uid 用户id
	 * @param integer $class 分类
	 * @param float $money 金额
	 * @param integer $op 操作人
	 * @param integer $subid 主机id
	 * @param string $content 备注
	 * @return boolean
	 * @throws \think\exception\DbException
	 */
	public function hostInc(int $uid=0,int $class=1,float $money=0.000,int $op=1,int $subid=0,string $content=''){
		//重组
		$data=['uid'=>$uid,'type'=>1,'class'=>$class,'money'=>sprintf("%.3f",$money),'way'=>4,'style'=>1,'op'=>$op,'time'=>time(),'timestamp'=>'server_inc_'.$op.'_'.time().'_'.rand(10000,99999),'subid'=>$subid,'content'=>$content];
		//流水
		$account=Db::name('account')->insertGetId($data);
		if(!$account){
			return false;
		}
		//会员
		$user=Db::name('user')->where(['id'=>$uid])->inc('money',$data['money'])->update();
		if(!$user){
			return false;
		}
		return true;
	}
	/**
	 * 主机扣余额
	 * @access public
	 * @param integer $uid 用户id
	 * @param integer $class 分类
	 * @param float $money 金额
	 * @param integer $op 操作人
	 * @param integer $subid 主机id
	 * @param string $content 备注
	 * @return boolean
	 * @throws \think\exception\DbException
	 */
	public function hostDec(int $uid=0,int $class=1,float $money=0.000,int $op=1,int $subid=0,string $content=''){
		//重组
		$data=['uid'=>$uid,'type'=>2,'class'=>$class,'money'=>sprintf("%.3f",$money),'way'=>4,'style'=>1,'op'=>$op,'time'=>time(),'timestamp'=>'server_dec_'.$op.'_'.time().'_'.rand(10000,99999),'subid'=>$subid,'content'=>$content];
		//流水
		$account=Db::name('account')->insertGetId($data);
		if(!$account){
			return false;
		}
		//会员
		$user=Db::name('user')->where(['id'=>$uid])->dec('money',$data['money'])->update();
		if(!$user){
			return false;
		}
		return true;
	}
	/**
	 * 充值加余额
	 * @access public
	 * @param integer $uid 用户id
	 * @param integer $class 分类
	 * @param float $money 金额
	 * @param integer $style 充值方式
	 * @param integer $op 操作人
	 * @param integer $recharge_id 充值id
	 * @param string $trade 交易流水号
	 * @param string $content 备注
	 * @return boolean
	 * @throws \think\exception\DbException
	 */
	public function recharge(int $uid=0,int $class=1,float $money=0.000,int $style=1,int $op=1,int $recharge_id=0,string $trade='',string $content=''){
		//重组
		$data=['uid'=>$uid,'type'=>1,'class'=>$class,'money'=>sprintf("%.3f",$money),'way'=>2,'style'=>$style,'op'=>$op,'time'=>time(),'timestamp'=>'recharge_inc_'.$style.'_'.time().'_'.rand(10000,99999),'recharge_id'=>$recharge_id,'trade'=>$trade,'content'=>$content];
		//流水
		$account=Db::name('account')->insertGetId($data);
		if(!$account){
			return false;
		}
		//充值
		$recharge=Db::name('recharge')->where(['id'=>$recharge_id])->update(['status'=>2,'pay_time'=>$data['time'],'trade'=>$trade]);
		if(!$recharge){
			return false;
		}
		//会员
		$user=Db::name('user')->where(['id'=>$uid])->inc('money',$data['money'])->update();
		if(!$user){
			return false;
		}
		return true;
	}
	/**
	 * 卡密加余额
	 * @access public
	 * @param integer $uid 用户id
	 * @param integer $class 分类
	 * @param integer $op 操作人
	 * @param integer $card_id 卡密id
	 * @param string $content 备注
	 * @return boolean
	 * @throws \think\exception\DbException
	 */
	public function cardInc(int $uid=0,int $class=1,int $card_id=0,string $content=''){
		//卡密
		$card=Db::name('card')->where(['id'=>$card_id])->find();
		if(!$card){
			return false;
		}
		$use=Db::name('card')->where(['id'=>$card['id']])->update(['status'=>2,'uid'=>$uid,'use'=>time()]);
		if(!$use){
			return false;
		}
		//重组
		$data=['uid'=>$uid,'type'=>1,'class'=>$class,'money'=>$card['money'],'way'=>2,'style'=>4,'op'=>1,'time'=>time(),'timestamp'=>'card_inc_'.$card_id.'_'.time().'_'.rand(10000,99999),'card_id'=>$card_id,'content'=>$content];
		//流水
		$account=Db::name('account')->insertGetId($data);
		if(!$account){
			return false;
		}
		//会员
		$user=Db::name('user')->where(['id'=>$uid])->inc('money',$data['money'])->update();
		if(!$user){
			return false;
		}
		return true;
	}
}