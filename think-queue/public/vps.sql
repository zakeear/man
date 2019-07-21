/*
 Navicat Premium Data Transfer

 Source Server : localhost
 Source Server Type : MySQL
 Source Server Version : 100138
 Source Host : localhost:3306
 Source Schema : vps

 Target Server Type : MySQL
 Target Server Version : 100138
 File Encoding : 65001

 Date: 13/06/2019 11:10:55
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for account
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account`	(
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
	`type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型1:收入2:支出',
	`class` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '种类1:余额',
	`money` decimal(10, 3) UNSIGNED NULL DEFAULT 0.000 COMMENT '金额',
	`way` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '来源1:系统2:充值3:提现4:主机',
	`style` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '分类1:系统2:支付宝3:微信4:卡密',
	`timestamp` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '时间锁',
	`subid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '实例id',
	`rechargeid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '充值id',
	`trade` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '流水号',
	`card_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '卡密id',
	`op` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '操作人',
	`content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '内容',
	`time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间',
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `idx_timestamp`(`timestamp`) USING BTREE COMMENT '时间锁',
	INDEX `idx_uid_type_class_way_style_subid_rechargeid`(`uid`, `type`, `class`, `way`, `style`, `subid`, `rechargeid`, `trade`, `money`) USING BTREE COMMENT '联合索引'
) ENGINE = InnoDB AUTO_INCREMENT = 4436 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '流水表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`	(
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
	`password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
	`status` tinyint(1) UNSIGNED NULL DEFAULT 0,
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES (1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 0);

-- ----------------------------
-- Table structure for card
-- ----------------------------
DROP TABLE IF EXISTS `card`;
CREATE TABLE `card`	(
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`num` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	`keys` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	`money` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00,
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
	`create` int(10) UNSIGNED NOT NULL DEFAULT 0,
	`uid` int(10) UNSIGNED NOT NULL DEFAULT 0,
	`use` int(10) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `idx_num`(`num`) USING BTREE COMMENT '卡号唯一'
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '卡密表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of card
-- ----------------------------
INSERT INTO `card` VALUES (1, '266697181021', 'mYVsW0eQ', 10.00, 1, 1560220031, 0, 0);
INSERT INTO `card` VALUES (2, '894447554827', 'UdzJZS7s', 2.00, 1, 1560220044, 0, 0);

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config`	(
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`rate` decimal(6, 3) UNSIGNED NOT NULL DEFAULT 7.600 COMMENT '汇率',
	`month` int(4) UNSIGNED NOT NULL DEFAULT 500 COMMENT '月付上限',
	`is_buy` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '购买开关',
	`vultr_api` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'vultr网关',
	`vultr_keys` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'vultr密钥',
	`web_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '网站名称',
	`web_icon` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '网站icon',
	`time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '配置状态',
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO `config` VALUES (10, 7.600, 500, 1, 'https://api.vultr.com', '', '', '', 1560165916, 2);

-- ----------------------------
-- Table structure for dc
-- ----------------------------
DROP TABLE IF EXISTS `dc`;
CREATE TABLE `dc`	(
	`id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
	`fid` int(4) UNSIGNED NOT NULL DEFAULT 0,
	`dcid` int(6) UNSIGNED NULL DEFAULT 0,
	`name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
	`time` int(10) UNSIGNED NOT NULL DEFAULT 0,
	`flags` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 21 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '主机位置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dc
-- ----------------------------
INSERT INTO `dc` VALUES (1, 0, 0, '北美', 1, 1555928212, '');
INSERT INTO `dc` VALUES (2, 0, 0, '亚洲', 1, 1555928212, '');
INSERT INTO `dc` VALUES (3, 0, 0, '欧洲', 1, 1555928212, '');
INSERT INTO `dc` VALUES (4, 0, 0, '澳大利亚', 1, 1555928212, '');
INSERT INTO `dc` VALUES (5, 1, 1, '纽约', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (6, 1, 2, '芝加哥', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (7, 1, 6, '亚特兰大', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (8, 1, 3, '达拉斯', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (9, 1, 5, '洛杉矶', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (10, 1, 39, '迈阿密', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (11, 1, 4, '西雅图', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (12, 1, 12, '硅谷', 1, 1555928212, '/static/images/flags/xs_us.png');
INSERT INTO `dc` VALUES (13, 1, 22, '多伦多', 1, 1555928212, '/static/images/flags/xs_ca.png');
INSERT INTO `dc` VALUES (14, 2, 25, '东京', 1, 1555928212, '/static/images/flags/xs_jp.png');
INSERT INTO `dc` VALUES (15, 2, 40, '新加坡', 1, 1555928212, '/static/images/flags/xs_sg.png');
INSERT INTO `dc` VALUES (16, 3, 7, '阿姆斯特丹', 1, 1555928212, '/static/images/flags/xs_nl.png');
INSERT INTO `dc` VALUES (17, 3, 8, '伦敦', 1, 1555928212, '/static/images/flags/xs_gb.png');
INSERT INTO `dc` VALUES (18, 3, 24, '巴黎', 1, 1555928212, '/static/images/flags/xs_fr.png');
INSERT INTO `dc` VALUES (19, 3, 9, '法兰克福', 1, 1555928212, '/static/images/flags/xs_de.png');
INSERT INTO `dc` VALUES (20, 4, 19, '悉尼', 1, 1555928212, '/static/images/flags/xs_au.png');

-- ----------------------------
-- Table structure for host
-- ----------------------------
DROP TABLE IF EXISTS `host`;
CREATE TABLE `host`	(
	`id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
	`vpsplanid` int(6) UNSIGNED NOT NULL DEFAULT 0,
	`cpu` int(2) UNSIGNED NOT NULL DEFAULT 0,
	`ram` int(4) UNSIGNED NOT NULL DEFAULT 0,
	`ssd` int(4) UNSIGNED NOT NULL DEFAULT 0,
	`bandwidth` int(10) UNSIGNED NOT NULL DEFAULT 0,
	`month` decimal(10, 3) NOT NULL,
	`hour` decimal(10, 3) NOT NULL,
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
	`time` int(10) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '配置规格表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of host
-- ----------------------------
INSERT INTO `host` VALUES (7, 201, 1, 1, 25, 1000, 5.000, 0.010, 1, 1555928212);
INSERT INTO `host` VALUES (8, 202, 1, 2, 55, 2000, 10.000, 0.020, 1, 1555928212);
INSERT INTO `host` VALUES (9, 203, 2, 4, 80, 3000, 20.000, 0.040, 1, 1555928212);
INSERT INTO `host` VALUES (10, 204, 4, 8, 160, 4000, 40.000, 0.080, 1, 1555928212);
INSERT INTO `host` VALUES (11, 205, 6, 16, 320, 5000, 80.000, 0.160, 1, 1555928212);
INSERT INTO `host` VALUES (12, 206, 8, 32, 640, 6000, 160.000, 0.320, 1, 1555928212);
INSERT INTO `host` VALUES (13, 207, 16, 64, 1280, 10000, 320.000, 0.640, 1, 1555928212);

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs`	(
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
	`subid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '主机id',
	`op_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '操作人id',
	`type` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型1:会员2:管理3:系统4:主机5:财务',
	`time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间',
	`ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '登录ip',
	`content` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '日志内容',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `idx_uid_type_time`(`uid`, `type`, `time`, `op_id`, `subid`) USING BTREE COMMENT '联合索引'
) ENGINE = InnoDB AUTO_INCREMENT = 4633 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for os
-- ----------------------------
DROP TABLE IF EXISTS `os`;
CREATE TABLE `os`	(
	`id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT,
	`fid` int(4) UNSIGNED NOT NULL DEFAULT 0,
	`osid` int(6) UNSIGNED NULL DEFAULT 0,
	`name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	`month` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00,
	`hour` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00,
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
	`time` int(10) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '操作系统表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of os
-- ----------------------------
INSERT INTO `os` VALUES (1, 0, 0, '64位', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (2, 0, 0, '32位', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (3, 1, 127, 'CentOS 6', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (4, 1, 167, 'CentOS 7', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (5, 1, 302, 'Ubuntu 18.10', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (6, 1, 270, 'Ubuntu 18.04', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (7, 1, 215, 'Ubuntu 16.04', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (8, 1, 160, 'Ubuntu 14.04', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (9, 1, 193, 'Debian 8', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (10, 1, 244, 'Debian 9', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (11, 1, 271, 'Fedora 28', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (12, 1, 322, 'Fedora 29', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (13, 1, 124, 'Windows 2012 R2', 16.00, 1.00, 2, 1555928212);
INSERT INTO `os` VALUES (14, 1, 240, 'Windows 2016', 16.00, 1.00, 2, 1555928212);
INSERT INTO `os` VALUES (15, 2, 147, 'CentOS 6', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (16, 2, 194, 'Debian 8', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (17, 2, 216, 'Ubuntu 16.04', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (18, 2, 161, 'Ubuntu 14.04', 0.00, 0.00, 1, 1555928212);
INSERT INTO `os` VALUES (19, 1, 164, 'SNAPSHOT', 0.00, 0.00, 3, 1555928212);

-- ----------------------------
-- Table structure for recharge
-- ----------------------------
DROP TABLE IF EXISTS `recharge`;
CREATE TABLE `recharge`	(
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
	`out_trade_no` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '单号',
	`subid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '实例id',
	`class` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '种类1:余额',
	`money` decimal(10, 3) UNSIGNED NULL DEFAULT 0.000 COMMENT '金额',
	`pay_type` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付方式2:支付宝3:微信',
	`time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间',
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态1:待支付2:已支付3:已取消',
	`pay_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付时间',
	`trade` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '交易流水号',
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `idx_out_trade_no`(`out_trade_no`) USING BTREE COMMENT '唯一索引',
	INDEX `idx_uid_subid_class_pay-type_time`(`uid`, `out_trade_no`, `subid`, `class`, `pay_type`, `time`, `status`, `pay_time`, `trade`) USING BTREE COMMENT '联合索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '充值表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for server
-- ----------------------------
DROP TABLE IF EXISTS `server`;
CREATE TABLE `server`	(
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uid` int(10) UNSIGNED NOT NULL COMMENT '所属用户',
	`status` int(2) UNSIGNED NOT NULL DEFAULT 2 COMMENT '状态1:已停止2:运行中3:已过期4:需续费5:已删除6:异常',
	`time` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
	`op` int(4) UNSIGNED NULL DEFAULT 0 COMMENT '操作人',
	`op_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '操作时间',
	`subid` bigint(11) UNSIGNED NULL DEFAULT 0 COMMENT '实例id',
	`ip_address` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'IPv4',
	`password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'root密码',
	`snapshotid` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '快照id',
	`port` int(10) UNSIGNED NULL DEFAULT 22 COMMENT '端口',
	`ips` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '高防IP',
	`enable_ipv6` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'IPv6',
	`ipv6` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'IPv6地址',
	`dcid` int(5) UNSIGNED NULL DEFAULT 0 COMMENT '位置',
	`osid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '操作系统',
	`arch` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '系统类型',
	`vpsplanid` double(32, 0) UNSIGNED NULL DEFAULT 0 COMMENT '配置规格',
	`hostname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '自定义名称',
	`ddos` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'DDOS',
	`appid` int(6) UNSIGNED NULL DEFAULT 0 COMMENT '预装应用',
	`destroy` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '删除时间',
	`month` int(3) UNSIGNED NULL DEFAULT 0 COMMENT '购买时长',
	`deduction` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '扣费次数',
	`money` decimal(10, 3) UNSIGNED NULL DEFAULT 0.000 COMMENT '费用',
	`deduction_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '扣费时间',
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 64 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '主机表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for snapshot
-- ----------------------------
DROP TABLE IF EXISTS `snapshot`;
CREATE TABLE `snapshot`	(
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`snapshotid` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	`name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	`password` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	`port` int(10) UNSIGNED NULL DEFAULT 0,
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
	`time` int(10) UNSIGNED NULL DEFAULT 0,
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of snapshot
-- ----------------------------
INSERT INTO `snapshot` VALUES (1, '105bb633cde41', 'windows2008', '76f56f39e631a@007', 10086, 1, 1555928212);
INSERT INTO `snapshot` VALUES (2, '9a75abdd9b3e8', '08英文-电脑管家版-20180330', 'e2556f3a2e6be@007', 3389, 1, 1556971766);

-- ----------------------------
-- Table structure for ticket
-- ----------------------------
DROP TABLE IF EXISTS `ticket`;
CREATE TABLE `ticket`	(
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`uid` int(10) UNSIGNED NOT NULL,
	`subid` int(10) UNSIGNED NOT NULL DEFAULT 0,
	`type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
	`title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
	`content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`status` tinyint(1) UNSIGNED NULL DEFAULT 1,
	`time` int(10) UNSIGNED NULL DEFAULT 0,
	`op_id` int(4) UNSIGNED NULL DEFAULT 0,
	`op_time` int(10) UNSIGNED NULL DEFAULT 0,
	`update_time` int(10) UNSIGNED NULL DEFAULT 0,
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '工单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ticket_log
-- ----------------------------
DROP TABLE IF EXISTS `ticket_log`;
CREATE TABLE `ticket_log`	(
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`uid` int(10) UNSIGNED NOT NULL DEFAULT 0,
	`tid` int(10) UNSIGNED NOT NULL DEFAULT 0,
	`type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
	`content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`status` tinyint(1) UNSIGNED NULL DEFAULT 1,
	`time` int(10) UNSIGNED NULL DEFAULT 0,
	`op_id` int(4) UNSIGNED NULL DEFAULT 0,
	PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '工单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`	(
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
	`nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '昵称',
	`realname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '姓名',
	`password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '密码',
	`create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册时间',
	`update_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '更新时间',
	`login_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '登陆时间',
	`login_count` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '登陆次数',
	`login_ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '登录ip',
	`vip` tinyint(2) UNSIGNED NULL DEFAULT 0 COMMENT 'vip等级',
	`vip_join` int(10) UNSIGNED NULL DEFAULT 0 COMMENT 'vip加入时间',
	`vip_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT 'vip过期时间',
	`ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '注册ip',
	`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态1:正常2:禁用3:临时',
	`lock_uid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '封禁人',
	`lock_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '封禁时间',
	`lock_tips` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '封禁原因',
	`back_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '解封时间',
	`group` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '身份1:普通2:管理员3:代理4:合作方5:渠道商',
	`group_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '身份过期',
	`safe_level` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '安全等级',
	`safe_code` char(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '密保安全码',
	`safe_token` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '密保令牌1:未2:是',
	`safe_device` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '密保设备1:未2:是',
	`safe_phone` bigint(11) UNSIGNED NULL DEFAULT 0 COMMENT '密保手机',
	`safe_email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '密保邮箱',
	`verify_code` char(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '找回校验码',
	`verify_lock` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '找回锁定',
	`verify_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '校验码过期',
	`money` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000 COMMENT '余额',
	`give` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000 COMMENT '增送',
	`brokerage` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000 COMMENT '佣金',
	`server` int(10) UNSIGNED NOT NULL DEFAULT 10 COMMENT '服务器',
	`gold` tinyint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '金币',
	`credits` tinyint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分',
	`union_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '微信',
	`unionid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'QQ',
	`inviters` tinyint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '邀请次数',
	`inviter_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '邀请人',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `id`(`id`, `username`, `realname`, `vip`, `group`, `money`, `create_time`) USING BTREE COMMENT '联合索引'
) ENGINE = InnoDB AUTO_INCREMENT = 36 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (25, '48256609@qq.com', 'snipoer', 'zakeear', 'e10adc3949ba59abbe56e057f20f883e', 1553736260, 0, 1560395213, 100, '114.219.138.236', 0, 0, 0, '221.225.237.163', 1, 1, 1556942647, '', 0, 1, 0, 1, '08cwCQSX', 0, 1, 15997531225, '48256609@qq.com', NULL, 1553736260, 1553737130, 100.473, 0.000, 0.000, 10, 0, 0, '', '', 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
