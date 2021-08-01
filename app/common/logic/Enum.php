<?php
/*
* 枚举数组
* @author zakeear <zakeear@86dede.com>
* @version v0.1.0
* @time 2019-06-04
*/

namespace app\common\logic;
class Enum
{
	//颜色A
	protected $colorsA = ['1' => '#33b86c', '2' => '#ef5350', '3' => 'blue', '4' => '#7e57c2', '5' => '#317eeb', '6' => '#d90000', '7' => '#8c008c', '8' => '#ff00ff', '9' => '#800000', '10' => '#004dca', '11' => '#f15822', '12' => 'purple', '13' => '#044c68', '14' => '#f26a4d', '15' => '#ff007f', '16' => '#00ca30', '17' => '#941b6f', '18' => '#f15822', '19' => '#c34956', '20' => '#28b779'];
	//颜色B
	protected $colorsB = ['1' => '#ef5350', '2' => '#33b86c', '3' => 'blue', '4' => '#f15822', '5' => '#7e57c2', '6' => '#28b779', '7' => '#ff00ff', '8' => '#8c008c', '9' => '#004dca', '10' => '#800000', '11' => '#4a84b7', '12' => '044c68', '13' => '#purple', '14' => '#ff007f', '15' => '#f26a4d', '16' => '#00ca30', '17' => '#00ca30', '18' => '#4a84b7', '19' => '#28b779', '20' => '#c34956'];
	//用户身份
	protected $userGroup = ['1' => '会员', '2' => '管理员', '3' => '代理商', '4' => '合作方', '5' => '渠道商'];
	//用户状态
	protected $userStatus = ['1' => '正常', '2' => '禁用', '3' => '限制'];
	//日志类型
	protected $logsGroup = ['1' => '会员', '2' => '管理', '3' => '系统', '4' => '主机', '5' => '财务'];
	//流水来源
	protected $accountWay = ['1' => '系统', '2' => '充值', '3' => '提现', '4' => '订单'];
	//流水收支
	protected $accountType = ['1' => '收入', '2' => '支出'];
	//流水分支
	protected $accountStyle = ['1' => '系统', '2' => '支付宝', '3' => '微信', '4' => '卡密'];
	//支付类型
	protected $payType = ['2' => '支付宝', '3' => '微信', '4' => '卡密'];
	//支付状态
	protected $payStatus = ['1' => '待支付', '2' => '已支付', '3' => '已取消'];
	//主机状态
	protected $hostStatus = ['1' => '已停止', '2' => '运行中', '3' => '已过期', '4' => '需续费', '5' => '已删除', '6' => '异常'];
	//主机状态
	protected $dcStatus = ['1' => '正常', '2' => '下架', '3' => ''];
	//卡密状态
	protected $cardStatus = ['1' => '正常', '2' => '已使用'];
	//工单状态
	protected $ticketStatus = ['1' => '待处理', '2' => '处理中', '3' => '已关闭'];
	//工单处理
	protected $ticketDoUser = ['2' => '补充', '3' => '关闭'];
	//工单处理
	protected $ticketDoAdmin = ['2' => '处理', '3' => '关闭'];
	//工单状态
	protected $ticketType = ['1' => '咨询', '2' => '反馈', '3' => '建议', '4' => '投诉', '5' => '申诉', '6' => '故障'];

	//检查
	protected function checkArr($arr)
	{
	}

	//键值
	public function value($arr = '', $keys = 0): string
	{
		if (!isset($this->$arr[$keys])) {
			return '';
		}
		return $this->$arr[$keys];
	}

	//颜色
	public function color($arr = '', $keys = 0, $color = 'colorsA', $array = []): string
	{
		if (!isset($this->$arr[$keys])) {
			return '';
		}
		$color = isset($this->$color) && $this->$color ? $color : 'colorsA';
		$num = $keys >= 1 && $keys <= 20 ? $keys : 1;
		if ($array) {
			return "<span style='color:" . $this->$color[$num] . "'>" . $arr[$keys] . "</span>";
		} else {
			return "<span style='color:" . $this->$color[$num] . "'>" . $this->$arr[$keys] . "</span>";
		}
	}

	//遍历一维数组
	public function arrays($arr = '')
	{
		return $this->$arr;
	}

	//遍历二维数组
	public function withIdName($arr = ''): array
	{
		$i = 0;
		$result = [];
		foreach ($this->$arr as $k => $v) {
			$result[$i] = ['id' => $k, 'name' => $v];
			$i++;
		}
		return $result;
	}

	//自定义遍历二维数组
	public function ArrKeys($arr = '', $keys = ''): array
	{
		$str = explode(',', $keys);
		$i = 0;
		$result = [];
		foreach ($str as $v) {
			if (isset($this->$arr[$v])) {
				$result[$i]['id'] = $v;
				$result[$i]['name'] = $this->$arr[$v];
			}
			$i++;
		}
		return $result;
	}
}