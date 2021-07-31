<?php
/*
* 邮件发送类
* @author zakeear <zakeear@86dede.com>
* @version v0.0.2
* @time 2019-06-04
*/

namespace app\common\logic;

use Phpmailer;
use phpmailerException;

class Email
{
	//账号配置
	protected static $config = [
		'host' => '',//地址
		'username' => '',//账号
		'password' => '',//密码
		'from' => '',//来自哪儿
		'name' => '',//发件人
		'altbody' => '邮箱验证码，如果您看见的是本条内容请与管理员联系！',//邮件默认内容，当收件人屏蔽了内容或某些意外情况时展现
	];
	//发送

	/**
	 * @throws phpmailerException
	 */
	public function send($data = [])
	{
		$result = new \app\common\validate\Email;
		if (!$result->scene('send')->check($data)) {
			return $result->getError();
		}
		$config = self::$config;
		$mailer = new Phpmailer();
		$mailer->Host = $config['host'];//smtp服务器地址
		$mailer->SMTPAuth = true;//启用smtp认证
		$mailer->SMTPSecure = 'ssl';//启用smtp加密
		$mailer->Port = '465';//smtp端口
		$mailer->Username = $config['username'];//邮箱名
		$mailer->Password = $config['password'];//邮箱密码
		$mailer->From = $config['from'];//发件人地址
		$mailer->FromName = $config['name'];//发件人
		$mailer->CharSet = 'utf-8';//编码
		$mailer->Subject = $data['subject'];//主题
		$mailer->Body = $data['message'];//内容
		$mailer->AltBody = $config['altbody'];//邮件正文不支持html的备用显示
		$mailer->WordWrap = 500;//设置每行字符长度
		$mailer->IsSMTP();//启用smtp
		$mailer->IsHTML(true);//是否html格式邮件
		$mailer->AddAddress($data['email']);
		$status = $mailer->Send();
		if ($status != true) {
			return $mailer->ErrorInfo;
		} else {
			return $status;
		}
	}
}