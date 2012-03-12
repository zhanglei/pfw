<?php
/**
 * 系统配置文件
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */

//定义框架版本
define('VERSION', '0.0.1');

/// 默认时区
define('APP_TIMEZONE_OFFSET', 8);

// 站点语言名称（目录）
define('SITE_LANG', 'zh_cn');

// 站点皮肤  CSS 文件目录名称的 前缀
define('SITE_SKIN_CSS_PRE', 'skin_');

// 站点皮肤 CSS 自定义皮肤目录
define('SITE_SKIN_CSS_CUSTOM', 'skin_define');

// 当用户和系统都没有设置,且不能从预览变量路由中取得CSS皮肤值的时候即为当前值
define('SITE_SKIN_TYPE', 'default');

// 本地时间，与标准时间的差，单位为秒，当本地时钟较快时为　负数　，较慢时为　正数　, 默认为　０　即本地时间是准确的
define('LOCAL_TIME_OFFSET', 0);

// 经过较准的，本地时间戳　所有使用APP_LOCAL_TIMESTAMP　的地方用这个常替代，防止，无法更改服务器时间导致的问题
define('APP_LOCAL_TIMESTAMP', time() + LOCAL_TIME_OFFSET);

//Base Site URL
$config['base_url'] = '';

//
$config['charset'] = 'UTF-8';

//数据库配置
$dbConfig = array();
$dbConfig['adapter'] = 'mysql';
$dbConfig['table_prefix'] = 'pf_';
$dbConfig['charset'] = 'utf8';
$dbConfig['persitent'] = false;
$dbConfig['master'][] = array(
	'dbhost' => '127.0.0.1', 
	'dbuser' => 'root', 
	'dbpasswd' => '123456', 
	'dbport' => '3306', 
	'dbname' => 'pfw',
);
$dbConfig['slave'][] = array(
	'dbhost' => '127.0.0.1', 
	'dbuser' => 'root', 
	'dbpasswd' => '123456', 
	'dbport' => '3306', 
	'dbname' => 'pfw',
);
?>
