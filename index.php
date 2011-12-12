<?php
/**
 * 程序入口文件
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */
//phpinfo();exit;
// 入口名称
define('ENTRY_SCRIPT_NAME', 'index');
// 当前入口的默认模块路由
define('R_DEF_MOD', "index");
// 强制的路由模式　如果你尝试使用 rewrite功能失败，可以通过此选项快速恢复网站正常
//define('R_FORCE_MODE', 0);

/// 初始化框架
require_once 'application/init.php';

if (App::F('is_robot')) {
	App::deny();
}

// 初始化应用程序
App::init();

// 处理当前HTTP请求
App::request();
?>
