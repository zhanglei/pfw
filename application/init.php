<?php
/**
 * 框架初始化文件
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */
require_once 'cfg.php';
require_once P_ROOT . '/../config/config.ini.php';
require_once 'core.php';

/// 初始化全局数据
$GLOBALS[V_GLOBAL_NAME] = array();
$GLOBALS[V_GLOBAL_NAME]['TPL'] = array();
$GLOBALS[V_GLOBAL_NAME]['LANG'] = array();
$GLOBALS[V_GLOBAL_NAME]['STATIC_STORE'] = array();
?>