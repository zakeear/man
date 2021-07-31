<?php
/*
* CURL操作类
* @author zakeear <zakeear@86dede.com>
* @version v0.0.3
* @time 2019-06-04
*/

namespace app\common\logic;

use think\exception\HttpException;

class Curl
{
	/**
	 * 发送数据
	 * @access public
	 * @param string $url 发送目标URL
	 * @param string $method 发送方式
	 * @param string $keys header密钥
	 * @param array $data 数据内容
	 * @return string
	 * @throws HttpException
	 */
	public function send(string $url, string $method = 'POST', string $keys = '', array $data = []): string
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($keys) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['API-KEY:' . $keys]);
		}
		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			return curl_error($ch);
		} else {
			curl_close($ch);
			return $result;
		}
	}
}