<?php
/**
 * 框架配置文件
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */

if (!defined('IS_DEBUG')) {
	define('IS_DEBUG', '1');
}

// 根据调试状态打开错误信息
if (defined('IS_DEBUG') && IS_DEBUG) {
	if (version_compare(PHP_VERSION, '5.0', '>=')) {
		error_reporting(E_ALL & ~E_STRICT);
	} else {
		error_reporting(E_ALL);
	}

	@ini_set('display_errors', 1);
} else {
	error_reporting(0);
	//? E_ERROR | E_WARNING | E_PARSE
	@ini_set('display_errors', 0);
}

// 应用程序目录
define('P_ROOT', dirname(__FILE__));
// function	扩展文件的存放目录
define('P_FUNCTION', P_ROOT . "/function");
// class		扩展文件的存放目录
define('P_CLASS', P_ROOT . "/class");
//系统模块文件的存放目录
if (defined('ENTRY_SCRIPT_NAME') && ENTRY_SCRIPT_NAME == 'wap') {
	define('P_MODULES', P_ROOT . "/controllers/wap");
} else {
	define('P_MODULES', P_ROOT . "/controllers");
}
// 系统模板文件的存放目录
define('P_TEMPLATE', P_ROOT . "/../templates/default");

// 系统语言文件的存放目录
define('P_LANG', P_ROOT . "/languages");

// 第三方库
define('P_THIRDPARTY', P_ROOT . "/third-party");
// adodb库存放目录
define('P_THIRDPARTY_ADODB', P_THIRDPARTY . "/adodb");
// phpexcel库存放目录
define('P_THIRDPARTY_PHPEXCEL', P_THIRDPARTY . "/phpexcel");
// smarty库存放目录
define('P_THIRDPARTY_SMARTY', P_THIRDPARTY . "/smarty");
// wso2-wsf库存放目录
define('P_THIRDPARTY_WSF', P_THIRDPARTY . "/wso2-wsf");
// xhprof库存放目前
define('P_THIRDPARTY_XHPROF', P_THIRDPARTY . "/xhprof");

// 存放可变数据的目录名
define('P_VAR_NAME', 'var');
// 系统文件数据（上传数据，缓存数据）的存放目录
define('P_VAR', P_ROOT . "/../" . P_VAR_NAME);
// 系统永久存储的数据目录
define('P_VAR_DATA', P_VAR . "/data");
// 系统文件缓存的数据目录
define('P_VAR_CACHE', P_VAR . "/cache");
// 系统上传文件的数据目录
define('P_VAR_UPLOAD', P_VAR . "/upload");
// 锁文件存放目录
define('P_VAR_LOCK', P_VAR . '/lock');
// 备份目录
define('P_VAR_BACKUP', P_VAR . '/backup');
// 数据库备份目录
define('P_VAR_BACKUP_SQL', P_VAR_BACKUP . '/sql');
// 用于组合URL 的 VAR 路径
define('P_URL_UPLOAD', P_VAR_NAME . "/upload");

define('P_TEMPLATE_C', P_VAR_CACHE . "/templates_c");

// 扩展函数文件扩展名
define('EXT_FUNCTION', ".func.php");
// 扩展类文件扩展名
define('EXT_CLASS', ".class.php");
// 系统模块文件扩展名
define('EXT_MODULES', ".mod.php");
// 系统语言文件扩展名
define('EXT_LANG', ".lang.php");
// 系统模板文件扩展名
define('EXT_TPL', ".tpl.html");

// 判断当前请求是否是 API 请求的变量理由; AJAX请求同样被认为是API请求
define('V_API_REQUEST_ROUTE', "R:api_sign");
// 判断当前请求是否是 JS 请求的变量理由; 通常 AJAX请求同样被认为是API请求
define('V_JS_REQUEST_ROUTE', "R:_");

// 用于存储全局数据的变量名
define('V_GLOBAL_NAME', "__GG");
// 用于存储用户配置的全局变量名
define('V_CFG_GLOBAL_NAME', "cfg");

// 模块路由的变量名 , 当 R_MODE 为 0 时 可用
define('R_GET_VAR_NAME',		"mod");
// 方法路由的变量名 
define('F_GET_VAR_NAME',		"act");
// 系统默认的模块方法
define('R_DEF_MOD_FUNC', "index");

// 前置模块HOOK 前缀 ， ACTION_BEFORE_PREFIX+模块方法名 命名的成员方法 将在模块执行前 预先被执行
define('ACTION_BEFORE_PREFIX',	"_before_");
// 后置模块HOOK 前缀 ， ACTION_AFTER_PREFIX+模块方法名 命名的成员方法 将在模块执行完成后被执行
define('ACTION_AFTER_PREFIX',	"_after_");
?>