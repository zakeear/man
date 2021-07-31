<?php
/*
* 系统日志控制器
* @author zakeear <zakeear@86dede.com>
* @version v0.0.5
* @time 2019-06-05
*/
namespace app\common\logic;
use think\facade\Db;
class Logs{
	/**
	 * 日志记录到数据库
	 * @access public
	 * @param integer $uid 用户id
	 * @param integer $type 日志类型
	 * @param string $ip 操作IP地址
	 * @param string $content 日志内容
	 * @param integer|object $op 管理员id
	 * @param integer $subid 主机id
	 * @return integer
	 * @throws \think\exception\DbException
	 */
	public function database(int $uid=0,int $type=1,string $ip='',string $content='', $op=0,int $subid=0){
		return Db::name('logs')->insertGetId(['uid'=>$uid,'type'=>$type,'time'=>time(),'ip'=>$ip,'content'=>$content,'op_id'=>$op,'subid'=>$subid]);
	}
	/**
	 * 记录到文件
	 * @access public
	 * @param string $type 一级目录
	 * @param string $way 二级目录
	 * @param string $filename 文件名
	 * @param string|array $content 日志内容
	 * @throws \think\exception
	 */
	public function files(string $type=null,string $way=null,string $filename='',$content=''){
		//缺失参数
		if(!$type || !$way || !$content ){
			return false;
		}
		//创建目录
		$frist="./";
		$path="logs/".$type."/".$way."/".strftime("%Y",time())."/".strftime("%m",time())."/".strftime("%d",time());
		foreach(explode('/',$path) as $v){
			$path=$frist.=$v.'/';
			if(!is_readable($path)){
				is_file($path) or mkdir($path,0777,true);
			}
		}
		//文件名
		$filename=$filename ? $filename : strftime("%Y",time())."-".strftime("%m",time())."-".strftime("%d",time());
		//日志内容
		$content=is_array($content) || is_object($content) ? json_encode($content,JSON_UNESCAPED_UNICODE) : $content;
		//写入日志
		$fp=fopen("".$path.$filename.".log","a");
		flock($fp,LOCK_EX);
		fwrite($fp,strftime("%Y-%m-%d %H:%M:%S",time())."\n".$content."\n");
		flock($fp,LOCK_UN);
		fclose($fp);
	}
}