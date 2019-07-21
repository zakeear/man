<?php
use think\facade\Db;
//随机数字
function number_code(int $length=4){
	$code='';
	for($i=1;$i<=$length;$i++){
		$randcode=mt_rand(0,9);
		$code.=$randcode;
	}
	return $code;
}
//自动截取字符串
function short(string $str,int $length){
	if(!$str){
		return '';
	}
	if(!$length){
		return $str;
	}
	if(mb_strlen($str,'utf-8')<=$length){
		return $str;
	}else{
		$qian=mb_substr($str,'0',$length);
		return $qian.'...';
	}
}
//所有位置
function dc_all(){
	return Db::name('dc')->cache(true,6)->field('id,dcid,name')->where('fid','>',0)->select()->toArray();
}
//上架位置
function dc_online(){
	$dc=Db::name('dc')->cache(true)->field('id,name')->where(['status'=>1,'fid'=>0])->select()->toArray();
	foreach($dc as $k=>$v){
		$sub=Db::name('dc')->cache(true,6)->field('id,dcid,name,flags')->where(['fid'=>$v['id'],'status'=>1])->select()->toArray();
		$dc[$k]['sub']=$sub;
		if(count($sub)==0){
			unset($dc[$k]);
		}
	}
	return $dc;
}
//位置棋子
function get_flags(int $id){
	$flags=Db::name('dc')->cache(true)->where(['dcid'=>$id])->value('flags');
	$images=$flags ? "<img src=".$flags." />" : '';
	return $images;
}
//位置名称
function get_dc(int $id){
	return Db::name('dc')->cache(true)->where(['dcid'=>$id])->value('name');
}
//大区位置
function dc_fa(int $id){
	return Db::name('dc')->cache(true)->where(['id'=>$id,'fid'=>0])->value('name');
}
//所有大区
function dc_fa_all(){
	return Db::name('dc')->cache(true,6)->field('id,dcid,name')->where('fid','=',0)->select()->toArray();
}
//所有系统
function os_all(){
	$os=Db::name('os')->cache(true,6)->field('id,fid,osid,name')->where('fid','>',0)->select()->toArray();
	foreach($os as $k=>$v){
		$os[$k]['name']=$os[$k]['name'].' '.os_fa($v['fid']);
	}
	return $os;
}
//上架系统
function os_online(){
	$os=Db::name('os')->cache(true)->field('id,name')->where(['status'=>1,'fid'=>0])->select()->toArray();
	foreach($os as $k=>$v){
		$sub=Db::name('os')->cache(true,6)->field('id,osid,name')->where(['fid'=>$v['id'],'status'=>1])->select()->toArray();
		$os[$k]['sub']=$sub;
		if(count($sub)==0){
			unset($os[$k]);
		}
	}
	return $os;
}
//系统名称
function get_os(int $id){
	$os=Db::name('os')->cache(true)->field('fid,name')->where(['osid'=>$id])->find();
	return $os['name'].' '.os_fa($os['fid']);
}
//系统类型
function os_fa(int $id){
	return Db::name('os')->cache(true)->where(['id'=>$id,'fid'=>0])->value('name');
}
//所有类型
function os_fa_all(){
	return Db::name('os')->cache(true,6)->field('id,osid,name')->where('fid','=',0)->select()->toArray();
}
//所有配置
function host_all(){
	return Db::name('host')->cache(true,6)->field('id,vpsplanid,cpu,ram,ssd,bandwidth,hour')->select()->toArray();
}
//上线配置
function host_online(){
	return Db::name('host')->cache(true,6)->field('id,vpsplanid,cpu,ram,ssd,bandwidth,hour')->where(['status'=>1])->select()->toArray();
}
//配置详情
function get_host(int $id){
	$host=Db::name('host')->cache(true,6)->field('id,vpsplanid,cpu,ram,ssd,bandwidth,hour')->where(['vpsplanid'=>$id])->find();
	$result=$host['cpu'].'核'.$host['ram'].'G内存'.$host['ssd'].'G固态'.$host['bandwidth'].'G流量';
	return $result;
}
//所有快照
function snapshot_all(){
	return Db::name('snapshot')->cache(true,6)->field('id,snapshotid,name,password,port')->select()->toArray();
}
//上线快照
function snapshot_online(){
	return Db::name('snapshot')->cache(true,6)->field('id,snapshotid,name,password,port')->where(['status'=>1])->select()->toArray();
}
//快照详情
function get_snapshot(int $id){
	$snapshot=Db::name('snapshot')->cache(true)->where(['snapshotid'=>$id])->find();
	return $snapshot['name'].'_'.$snapshot['snapshotid'];
}