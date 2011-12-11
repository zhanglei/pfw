<?php
/**
 * 框架核心文件
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
class App {

	public function __construct() {

	}

	public static function init() {
		static $is_init;
		if ($is_init) {
			return true;
		}
		App::_initConfig();
	}

	/**
	 * 初始化配置
	 * @return void
	 */
	public static function _initConfig() {
		// 标识当前请求是否是 API ， JS 请求，此值将决定如何输出错误信息 等
		define('IS_IN_API_REQUEST', V(V_API_REQUEST_ROUTE, FALSE));
		define('IS_IN_JS_REQUEST', V(V_JS_REQUEST_ROUTE, FALSE));
	}

	/**
	 * 处理用户请求
	 * @param $halt		执行完请求后是否退出
	 * @return void
	 */
	public static function request($halt = false) {
		if ($halt) {
			exit ;
		}
	}

	/**
	 * 从当前请求中取得模块路由信息
	 * @param $is_acc			是否以数组的形式返回
	 * @return  requestRoute
	 */
	public static function getRequestRoute($is_acc = false) {

	}

	/**
	 * V($vRoute,$def_v=NULL);
	 * app:V($vRoute,$def_v=NULL);
	 * 获取还原后的  $_GET ，$_POST , $_FILES $_COOKIE $_REQUEST $_SERVER $_ENV
	 * 同名全局函数： V($vRoute,$def_v=NULL);
	 * @param $vRoute	变量路由，规则为：“<第一个字母>[：变量索引/[变量索引]]
	 * 					例:	V('G:TEST/BB'); 表示获取 $_GET['TEST']['BB']
	 * 						V('p'); 		表示获取 $_POST
	 * 						V('c:var_name');表示获取 $_COOKIE['var_name']
	 * @param $def_v
	 * @param $setVar	是否设置一个变量
	 * @return $mixed
	 */
	public static function V($vRoute, $def_v = NULL, $setVar = false) {
		static $v;
		if (empty($v)) {
			$v = array();
		}
		$vRoute = trim($vRoute);

		//强制初始化值
		if ($setVar) {
			$v[$vRoute] = $def_v;
			return true;
		}

		if (!isset($v[$vRoute])) {
			$vKey = array('C' => $_COOKIE, 'G' => $_GET, 'P' => $_POST, 'R' => $_REQUEST, 'F' => $_FILES, 'S' => $_SERVER, 'E' => $_ENV, '-' => $GLOBALS[V_CFG_GLOBAL_NAME]);
			if (empty($vKey['R'])) {
				$vKey['R'] = array_merge($_COOKIE, $_GET, $_POST);
			}
			if (!preg_match("#^([cgprfse-])(?::(.+))?\$#sim", $vRoute, $m) || !isset($vKey[strtoupper($m[1])])) {
				trigger_error("Can't parse var from vRoute: $vRoute ", E_USER_ERROR);
				return NULL;
			}

			//----------------------------------------------------------
			$m[1] = strtoupper($m[1]);
			$tv = $vKey[$m[1]];

			//----------------------------------------------------------
			if (empty($m[2])) {
				$v[$vRoute] = ($m[1] == '-' || $m[1] == 'F' || $m[1] == 'S' || $m[1] == 'E') ? $tv : APP::_magic_var($tv);
			} elseif (empty($tv)) {
				return $def_v;
			} else {
				$vr = explode('/', $m[2]);
				while (count($vr) > 0) {
					$vk = array_shift($vr);
					if (!isset($tv[$vk])) {
						return $def_v;
						break;
					}
					$tv = $tv[$vk];
				}
			}
			$v[$vRoute] = ($m[1] == '-' || $m[1] == 'F' || $m[1] == 'S' || $m[1] == 'E') ? $tv : App::_magic_var($tv);
		}
		return $v[$vRoute];
	}

	/**
	 * 根据用户服务器环境配置，递归还原变量
	 * @param $mixed
	 * @return 还原后的值
	 */
	private static function _magic_var($mixed) {
		if ((function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) || @ini_get('magic_quotes_sybase')) {
			if (is_array($mixed))
				return array_map(array('APP', '_magic_var'), $mixed);
			return stripslashes($mixed);
		} else {
			return $mixed;
		}
	}

	/**
	 * App::redirect($mRoute,$type=1);
	 * 重定向 并退出程序
	 * @param $mRoute
	 * @param $type 	1 : 默认 ， 内部模块跳转 ,2 : 给定模块路由，通过浏览器跳转 ,3 : 给定URL  ,4 : 给定URL，用JS跳
	 * @return 无返回值
	 */
	public static function redirect($mRoute, $type = 1) {
		//if(ENTRY_SCRIPT_NAME=='wap') {}
		switch ($type) {
			case 1 :
				App::M($mRoute);
				break;
			case 2 :
				$url = App::mkModuleUrl($mRoute);
				header("Location: " . $url);
				break;
			case 3 :
				header("Location: " . $mRoute);
				break;
			case 4 :
				echo '<script>window.location.href="' . addslashes($mRoute) . '";</script>';
				break;
			default :
				break;
		}
		exit ;
	}

	/**
	 * App::F($fRoute);
	 * 执行 $fRoute 指定的函数 第二个以及以后的参数 将传递给此函数
	 * 例：APP::F('test.func',1,2); 表示执行  func(1,2);
	 * @param $fRoute 函数路由，规则与模块规则一样
	 * @return 函数执行结果
	 */
	public static function F($fRoute) {
		static $_fTree = array();
		$p = func_get_args();
		array_shift($p);

		if (isset($_fTree[$fRoute])) {
			return call_user_func_array($_fTree[$fRoute], $p);
		}

		$cFile = APP::_getIncFile($fRoute, 'func');
		require_once ($cFile);

		$pp = preg_match("#^([a-z_][a-z0-9_\./]*/|)([a-z0-9_]+)(?:\.([a-z_][a-z0-9_]*))?\$#sim", $fRoute, $m);
		if (!$pp) { trigger_error("fRoute : [ $fRoute  ] is  invalid ", E_USER_ERROR);
			return false;
		}
		$_fTree[$fRoute] = empty($m[3]) ? $m[2] : $m[3];
		if (!function_exists($_fTree[$fRoute])) {
			trigger_error("Can't find function [ {$_fTree[$fRoute]} ] in file [ $cFile ]", E_USER_ERROR);
		}

		return call_user_func_array($_fTree[$fRoute], $p);
	}

	/**
	 * APP::O($oRoute);
	 * 根据类路由 和 类初始化参数获取一个单例
	 * 第二个以及以后的参数 将传递给类的构造函数
	 * 如： APP::O('test/classname','a','b'); 实例化时执行的是 new classname('a','b');
	 * @param $oRoute 类路由，规则与模块规则一样
	 * @return 类实例
	 */
	public static function & O($oRoute) {
		static $oArr;
		if (isset($oArr[$oRoute]) && is_object($oArr[$oRoute])) {
			return $oArr[$oRoute];
		}

		$p = func_get_args();
		array_shift($p);
		array_unshift($p, $oRoute, 'cls', false);
		$oArr[$oRoute] = call_user_func_array(array('APP', '_cls'), $p);
		return $oArr[$oRoute];
	}

	/**
	 * APP::N($oRoute);
	 * 根据类路由 和 类初始化参数获取一个类实例
	 * 第二个以及以后的参数 将传递给类的构造函数
	 * 如： APP::N('test/classname','a','b'); 实例化时执行的是 new classname('a','b');
	 * @param $oRoute 类路由，规则与模块规则一样
	 * @return 类实例
	 */
	public static function N($oRoute) {
		$p = func_get_args();
		array_shift($p);
		array_unshift($p, $oRoute, 'cls', false);
		return call_user_func_array(array('APP', '_cls'), $p);
	}

	//对当前GET请求，生成一个唯一标识串,可以作为控制器缓存HOOK的　缓存　KEY
	public static function requestKey() {
		return App::getRequestRoute() . "###" . md5(serialize(V('g')));
	}

	//控制器缓存选项　有参数时－设置，无参数时－获取
	public static function xcacheOpt($data = false) {
		static $opt = false;
		if (func_num_args() == 0) {
			return $opt;
		} else {
			return $opt = $data;
		}
	}

	/// 收集或者 设置控制器缓存内容，有参数时收集，无参数时更新或者设置
	public static function xcache($buf = '') {
		static $html = '';

		//无参数时，是 shutdown　后调用的,缓冲堆栈中的数据是倒序的
		if (func_num_args() == 0) {
			$noFlushHtml = '';
			$opt = APP::xcacheOpt();
			if (!is_array($opt) || !isset($opt['K']) || !isset($opt['T'])) {
				return true;
			}

			while (ob_get_level() > 0) {
				$noFlushHtml = ob_get_clean() . $noFlushHtml;
			}

			CACHE::set($opt['K'], $html . $noFlushHtml, $opt['T']);
			echo $noFlushHtml;
			exit ;
		} else {
			$html .= $buf;
		}
		return true;
	}

	//------------------------------------------------------------------
	/**
	 * APP::M($mRoute);
	 * 执行一个模块
	 * @param $mRoute
	 * @return no nreturn
	 */
	public static function M($mRoute) {
		$r = APP::_parseRoute($mRoute);
		APP::setData('RuningRoute', array('path' => $r[1], 'class' => $r[2], 'function' => $r[3]));

		$p = func_get_args();
		array_shift($p);
		array_unshift($p, $mRoute, 'mod', true);
		$m = call_user_func_array(array('APP', '_cls'), $p);

		if (!is_object($m)) {
			//trigger_error("Can't instance mRoute  [ $mRoute ] ", E_USER_ERROR);
			F('err404', "Can't instance mRoute  [ $mRoute ] ");
		}

		if (substr($r[3], 0, 1) == '_') {
			//trigger_error("Module method: [ ".$r[3]." ]  start with '_' is private !  ", E_USER_ERROR);
			F('err404', "Module method: [ " . $r[3] . " ]  start with '_' is private !  ");
		}

		//检查缓存HOOK　HOOK方法将返回 array('K'=>'keystr'/*缓存KEY*/,'T'=>300/*缓存时间*/);
		$cacheOptFunc = X_CACHE_HOOK_PREFIX . $r[3];
		if (CTRL_CACHE_HOOK_ENABLE && method_exists($m, $cacheOptFunc)) {
			$cacheOpt = $m -> $cacheOptFunc();
			if ($cacheOpt === false || V('g:_no_xcache', false)) {
				$cacheContent = false;
			} else {
				//检查返回
				if (!is_array($cacheOpt) || !isset($cacheOpt['K']) || !isset($cacheOpt['T'])) {
					F('err404', "Cache HOOK return data is Error, " . $r[1] . $r[2] . $cacheOptFunc);
				}
				$cacheContent = CACHE::get($cacheOpt['K']);
			}

			if ($cacheContent) {
				echo $cacheContent;
				exit ;
			} else {
				APP::xcacheOpt($cacheOpt);
				register_shutdown_function(array('APP', 'xcache'));
			}
		}
		//-------

		if (!method_exists($m, $r[3])) {
			//trigger_error("Can't find method  [ ".$r[3]." ]  in  [ ".$r[2]." ] ", E_USER_ERROR);
			F('err404', "Can't find method  [ " . $r[3] . " ]  in  [ " . $r[2] . " ] ");
		}

		/// before hook
		$beforeAct = ACTION_BEFORE_PREFIX . $r[3];
		if (defined('ENABLE_ACTION_HOOK') && ENABLE_ACTION_HOOK && method_exists($m, $beforeAct)) {
			$m -> $beforeAct();
		}

		/// call action
		if ($r[3] != $r[2]) { $m -> $r[3]();
		}

		/// after hook
		$afterAct = ACTION_AFTER_PREFIX . $r[3];
		if (defined('ENABLE_ACTION_HOOK') && ENABLE_ACTION_HOOK && method_exists($m, $afterAct)) {
			$m -> $afterAct();
		}
	}

	//------------------------------------------------------------------
	/// 获取一个类名,在此定义类的后缀
	private static function _className($className, $type) {
		$tCfg = array('cls' => '', 'mod' => '_mod', );
		return isset($tCfg[$type]) ? $className . $tCfg[$type] : $className;
	}

	//------------------------------------------------------------------
	private static function & _cls($iRoute, $type, $is_single) {
		static $clsArr = array();
		$iRoute = trim($iRoute);
		$type = trim($type);

		$clsKey = $type . ":" . $iRoute;
		if ($is_single && isset($clsArr[$clsKey]) && is_object($clsArr[$clsKey])) {
			return $clsArr[$clsKey];
		} else {

			$cFile = APP::_getIncFile($iRoute, $type);
			require_once ($cFile);
			$r = APP::_parseRoute($iRoute);
			$class = APP::_className($r[2], $type);
			$func = $r[3];

			if (!class_exists($class)) {
				trigger_error("class [ $class ]  is not exists in file [ $cFile ] ", E_USER_ERROR);
			}
			$p = func_get_args();
			array_shift($p);
			array_shift($p);
			array_shift($p);
			if (!empty($p)) {
				$prm = array();
				foreach ($p as $i => $v) {
					$prm[] = "\$p[" . $i . "]";
				}
				eval("\$retClass = new " . $class . " (" . implode(",", $prm) . ");");
				if ($is_single) { $clsArr[$clsKey] = $retClass;
				}
				return $retClass;
			} else {
				if ($is_single) {
					$clsArr[$clsKey] = new $class;
					return $clsArr[$clsKey];
				} else {
					$c = new $class;
					return $c;
				}
			}
		}
	}

	//------------------------------------------------------------------
	public static function _parseRoute($route) {
		/*
		 static $staticRoute=array();
		 if (isset($staticRoute[$route])){
		 return $staticRoute[$route];
		 }*/
		$route = trim($route);
		$p = preg_match("#^([a-z_][a-z0-9_\./]*/|)([a-z0-9_]+)(?:\.([a-z_][a-z0-9_]*))?\$#sim", $route, $m);
		if (!$p) { trigger_error("route : [ $route  ] is  invalid ", E_USER_ERROR);
			return false;
		}
		if (empty($m[3]))
			$m[3] = R_DEF_MOD_FUNC;
		return $m;
	}

	//------------------------------------------------------------------
	/**
	 * APP::L($k);
	 * 根据语言索引返回信息信息
	 * 如果存在二个以上的参数，将以语言信息为格式 返回格式化后的字符串
	 * 如：APP::L($k,'a','b');
	 * 假设语言信息数据为 $_LANG,上例将返回 sprintf($_LANG[$k],'a','b');
	 * @param $k
	 * @return 格式化后的语言信息
	 */
	public static function L($k) {
		if (!is_array($GLOBALS[V_GLOBAL_NAME]['LANG'])) {
			trigger_error("Can't find any lang data ", E_USER_ERROR);
		}
		$s = isset($GLOBALS[V_GLOBAL_NAME]['LANG'][$k]) ? $GLOBALS[V_GLOBAL_NAME]['LANG'][$k] : false;
		if (!$s) {
			return false;
		}
		$p = func_get_args();
		array_shift($p);
		if (!empty($p)) {
			array_unshift($p, $s);
			$s = call_user_func_array('sprintf', $p);
		}
		return $s;
	}

	/**
	 * 获取当前系统使用的语言
	 *
	 * @param mixed $ext
	 */
	public static function getLang() {
		$lang = V('-:sysConfig/wb_lang_type');
		return $lang ? $lang : SITE_LANG;
	}

	//------------------------------------------------------------------
	/**
	 * APP::importLang($lRoute);
	 * 导入一个语言信息文件
	 * @param $lRoute	语言信息路由 规则与模块路由一样
	 * @return 成功 true 失败 false;
	 */
	public static function importLang($lRoute, $ext = false) {
		$ext = $ext ? $ext : APP::getLang();
		if (!defined('WB_LANG_TYPE_CSS')) {
			if ($ext == 'zh_cn') {
				define('WB_LANG_TYPE_CSS', '');
			} else {
				define('WB_LANG_TYPE_CSS', $ext);
			}
		}
		$lf = APP::_getIncFile($lRoute, 'lang', $ext);
		include_once $lf;
		if (!is_array($_LANG)) {
			trigger_error("Can't find lang array var \$_LANG in file [ $lf ] ", E_USER_ERROR);
		}

		$g = &$GLOBALS[V_GLOBAL_NAME];
		if (!isset($g['LANG']) || !is_array($g['LANG'])) {
			$g['LANG'] = array();
		}
		foreach ($_LANG as $k => $v) {
			$g['LANG'][$k] = $v;
		}
		return true;
	}

	/**
	 * App::mkModuleUrl($mRoute, $qData=false, $entry=false);
	 * 根据模块路由，query 数据 ，入口程序，生成URL，
	 * @param $mRoute		模块路由，如 demo/index.show
	 * @param $qData		添加在URL后面的参数，可以是数组或者字符串，
	 * 						如  array('a'=>'a_var') 或者  "a=a_var&b=b_var"
	 * @param $entry		入口程序名，默认获取当前入口程序，如： index.php admin.php
	 * @return 生成的URL
	 */
	public static function mkModuleUrl($mRoute, $qData = false, $entry = false) {
		$baseUrl = $entry ? W_BASE_URL . $entry : W_BASE_URL . W_BASE_FILENAME;
		$basePath = W_BASE_URL;
		//--------------------------------------------------------------
		//锚点
		$aName = "";
		//把参数统一转换为数组
		if (!is_array($qData)) {
			if (!empty($qData)) {
				if (strpos($qData, '#') !== false) {
					$aName = substr($qData, strpos($qData, '#'));
					$qData = substr($qData, 0, strpos($qData, '#'));
				}
				parse_str($qData, $qData);
			} else {
				$qData = array();
			}
		}
		//--------------------------------------------------------------
		//wap URL特殊处理，增加SESSIONID
		if (ENTRY_SCRIPT_NAME == 'wap' && (!isset($_COOKIE) || empty($_COOKIE))) {
			$qData[WAP_SESSION_NAME] = session_id();
		}
		//--------------------------------------------------------------
		//处理 APACHE 中 类 /index.php/sdfdsf 地址，在 ？ 之前出现 %2f 时无法使用的BUG
		//可用于 rewrite 优化的数据
		$qStr1 = "";
		//不可用于 rewrite 优化的数据 值 或者 名 中，包含 / 字符
		$qStr2 = "";
		if (!empty($qData)) {
			$kv1 = array();
			$kv2 = array();
			foreach ($qData as $k => $v) {
				if (strpos($k . $v, '/') === false) {
					$kv1[] = $k . "=" . urlencode($v);
				} else {
					$kv2[] = $k . "=" . urlencode($v);
				}
			}

			$qStr1 = empty($kv1) ? "" : implode("&", $kv1);
			$qStr2 = empty($kv2) ? "" : implode("&", $kv2);
		}
		//--------------------------------------------------------------
		if (R_MODE == 0) {
			$rStr = R_GET_VAR_NAME . '=' . $mRoute;
			if ($qStr1)
				$rStr .= "&" . $qStr1;
			if ($qStr2)
				$rStr .= "&" . $qStr2;
			return $baseUrl . '?' . $rStr . $aName;
		}
		//--------------------------------------------------------------
		if (R_MODE == 1) {
			return empty($qData) ? $baseUrl . "/" . trim($mRoute, '/ ') : $baseUrl . "/" . trim($mRoute, '/ ') . "?" . trim($qStr1 . '&' . $qStr2, '& ');
		}
		//--------------------------------------------------------------
		if (R_MODE == 2 || R_MODE == 3) {

			$rStr = $qStr1 ? preg_replace("#(?:^|&)([a-z0-9_]+)=#sim", "/\\1-", $qStr1) : '/';
			$rStr .= $qStr2 ? "?" . $qStr2 : '';
			return empty($qData) ? $basePath . trim($mRoute, '/ ') : $basePath . trim($mRoute, '/ ') . $rStr;
		}
		//--------------------------------------------------------------
		trigger_error("Unknow route type: [ " . R_MODE . " ]", E_USER_ERROR);
		return false;
	}

}// END

/**
 * 获取一个变量值  App::V 的同名函数
 * @param $vRoute	变量路由
 * @param $def		默认值
 * @return 			变量值
 */
function V($vRoute, $def = NULL, $setVar = false) {
	return App::V($vRoute, $def, $setVar);
}

// copydoc App::L
function L() {
	$p = func_get_args();
	return call_user_func_array(array('App', 'L'), $p);
}

// copydoc App::L
function LO() {
	$p = func_get_args();
	echo call_user_func_array(array('App', 'L'), $p);
}

// copydoc App::F
function F() {
	$p = func_get_args();
	return call_user_func_array(array('App', 'F'), $p);
}

//----------------------------------------------------------------------
/**
 * 获取一个url App::mkModuleUrl 的同名函数
 * @param $mRoute	模块路由
 * @param $qData	URL 参数可以是字符串如 "a=xxx&b=ooo" 或者数组 array('k'=>'k_var')
 * @param $entry	模块入口 默认为当前入口，可指定入口程序 如 admin.php
 * @return 			URL
 */
function URL($mRoute, $qData = false, $entry = false) {
	return App::mkModuleUrl($mRoute, $qData, $entry);
}

/**
 * 生成WAP兼容的URL页面链接
 *
 * @param mixed $mRoute
 * @param mixed $qData
 * @param mixed $entry
 * @return mixed
 */
function WAP_URL($mRoute, $qData = false, $entry = false) {
	$url = App::mkModuleUrl($mRoute, $qData, $entry);
	return htmlspecialchars($url, ENT_QUOTES);
}
?>