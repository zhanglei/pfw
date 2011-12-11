<?php
/**
 * 程序入口文件
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */
//phpinfo();exit;

/// 初始化框架
require_once 'application/init.php';

// 初始化应用程序
App::init();

// 处理当前HTTP请求
App::request();
?>
