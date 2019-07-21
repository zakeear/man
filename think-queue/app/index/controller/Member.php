<?php
/*
* 用户控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.0.4
* @time 2019-06-04
*/
namespace app\index\controller;
use app\Base;
use think\facade\Db;
use think\facade\View;
class Member extends Base{
	//详情
	public function detail(){
		if($this->request->isPost()){
			//验证会员
			$user=Db::name('user')->where(['id'=>$this->user['id']])->find();
			if(!$user){
				$this->error('用户不存在!');
			}
			if(isset($this->params['nickname']) && $this->params['nickname']){
				$data['nickname']=$this->params['nickname'];
			}
			if(isset($this->params['realname']) && $this->params['realname']){
				$data['realname']=$this->params['realname'];
			}
			if(isset($this->params['safe_phone']) && $this->params['safe_phone']){
				$data['safe_phone']=$this->params['safe_phone'];
			}
			Db::startTrans();
			try{
				Db::name('user')->where(['id'=>$this->user['id']])->update($data);
				Db::commit();
				//记录日志
				$this->logs()->database($this->user['id'],1,$this->request->ip(),'【'.$user['username'].'】修改资料',0,0);
			}catch(\Exception $e){
				Db::rollback();
				$this->error('修改失败，请重试');
			}
			$this->success('修改成功',url('detail'));
		}else{
			//会员
			$user=Db::name('user')->where(['id'=>$this->user['id']])->find();
			if(!$user){
				$this->error('用户不存在!');
			}
			//变量
			View::assign(['user'=>$user,'users'=>$this->user,'tickets'=>$this->ticket,'enum'=>$this->enum()]);
			//视图
			return View::fetch();
		}
	}
}