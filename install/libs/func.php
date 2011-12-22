<?php
/**
 * 安装程序公用函数
 * @author qingmu
 * @version
 * Created at:  2011-12-09 
 */    

if (!defined('PFW_ACCESS')) {
	die('NOT ACCESS');
}

$pfw_isError = 1;

if(pfw_function_exists('date_default_timezone_set')) {
	date_default_timezone_set('Etc/GMT-8');
} else {
	putenv('Etc/GMT-8');
}

if (!pfw_function_exists('array_combine')) {
	function array_combine( $keys, $values ) {
	   if( !is_array($keys) || !is_array($values) || empty($keys) || empty($values) || count($keys) != count($values)) {
		 trigger_error( "array_combine() expects parameters 1 and 2 to be non-empty arrays with an equal number of elements", E_USER_WARNING );
		 return false;
	   }
	   $keys = array_values($keys);
	   $values = array_values($values);
	   $result = array();
	   foreach( $keys as $index => $key ) {
		 $result[$key] = $values[$index];
	   }
	   return $result;
	}
}

if (!pfw_function_exists('json_decode')){
	include_once "servicesJSON.class.php";
	function json_decode($s, $ass = false){
		$assoc = ($ass) ? 16 : 32;
		$gloJSON = new servicesJSON($assoc);

		return $gloJSON->decode($s);
	}
}

if (!pfw_function_exists('json_encode')){
	include_once "servicesJSON.class.php";
	function json_encode($s){
		$gloJSON = new servicesJSON();
		return $gloJSON->encode($s);
	}
}

/**
 * 环境检查
 *
 * @param unknown_type
 * @return unknown
 */
function check_env(&$result)
{
	global $_LANG;

	$env_vars = array();

	/// 检查操作系统
	$env_vars['php_os'] = array('required' => $_LANG['no_limit'], 'best' => $_LANG['unix'], 'curr' => PHP_OS, 'state' => true);

	/// 检查php版本
	$env_vars['php_vers'] = array('required' => '4.0', 'best' => '5.0', 'curr' => PHP_VERSION);
	if ((int)$env_vars['php_vers']['required'] > (int)$env_vars['php_vers']['curr']) {
		$env_vars['php_vers']['state'] = false;
		$result = false;
	} else {
		$env_vars['php_vers']['state'] = true;
	}

	/// 检查上传附件大小
	/*
	$env_vars['upload'] = array('required' => '1M', 'best' => '2M', 'curr' => ini_get('upload_max_filesize'));
	$u = substr($env_vars['upload']['curr'], -1, 1);
	$max_upload = $u == 'M' ? (int)$env_vars['upload']['curr'] : ($u == 'K' ? (int)$env_vars['upload']['curr'] / 1024 : (int)$env_vars['upload']['curr'] / (1024 * 1024));
	if ((int)$env_vars['upload']['required'] > $max_upload) {
		$env_vars['upload']['state'] = false;
		$result = false;
	} else {
		$env_vars['upload']['state'] = true;
	}
	 */

	/// 检查gd库版本
	if (pfw_function_exists('gd_info')) {
		$gd_info = gd_info();
	} else {
		$gd_info['GD Version'] = $_LANG['advice_gd'];
		$result = false;
	}
	$env_vars['gd_vers'] = array('required' => '1.0', 'best' => '2.0', 'curr' => $gd_info['GD Version']);
	$match = array();
	preg_match('/\d/', $env_vars['gd_vers']['curr'], $match);
	$gd_vers = $match[0];
	if ((int)$env_vars['gd_vers']['required'] > $gd_vers) {
		$env_vars['gd_vers']['state'] = false;
	} else {
		$env_vars['gd_vers']['state'] = true;
	}

	/// 检查可用磁盘空间
	$env_vars['disk'] = array('required' => '10M', 'best' => $_LANG['no_limit'], 'curr' => floor(diskfreespace(ROOT_PATH) / (1024 * 1024)).'M');
	if ((int)$env_vars['disk']['required'] > (int)$env_vars['disk']['curr']) {
		$env_vars['disk']['state'] = false;
		$result = false;
	} else {
		$env_vars['disk']['state'] = true;
	}

	return $env_vars;
}

/**
 * 检查目录文件
 *
 * @param unknown_type
 * @return unknown
 */
function check_dir(&$result)
{
	/// 需要检查的目录和文件
	$dir_files = array(
					'./',
					'./css/default/base.css',
					'./user_config.php',
					'./var',
					'./var/cache',
					'./var/data',
					'./img/fonts',
					'./var/data/logo',
					'./var/log',
					'./var/upload',
					'./var/upload/avatarTemp',
					'./install/data');

	$check_dir_files = array();
	foreach ($dir_files as $var) {
		if (is_dir($var)) {
			if ($fp = @fopen(ROOT_PATH.$var.'/text.txt', 'w')) {
				@fclose($fp);
				@unlink(ROOT_PATH.$var.'/text.txt');
				$check_dir_files[$var] = array('w' => 'writeable', 'state' => true);
			} else {
				$check_dir_files[$var] = array('w' => 'unwriteable', 'state' => false);
				$result = false;
			}
		} else {
			if (file_exists(ROOT_PATH.$var)) {
				if (is_writable(ROOT_PATH.$var)) {
					$check_dir_files[$var] = array('w' => 'writeable', 'state' => true);
				} else {
					$check_dir_files[$var] = array('w' => 'unwriteable', 'state' => false);
					$result = false;
				}
			} else {
				$check_dir_files[$var] = array('w' => 'unwriteable', 'state' => false);
				$result = false;
			}
		}
	}
	return $check_dir_files;
}

/**
 * 检查函数依赖
 *
 * @param unknown_type
 * @return unknown
 */
function check_func(&$result)
{
	$func_items = array('mysql' => 'mysql_connect',
						'sock' => array('fsockopen', 'curl'),
						'file' => 'file_get_contents',
						'image' => 'imagettftext',
						'mhash' => array('mhash', 'hash_hmac')
						);
	$check_func_items = array();
	foreach ($func_items as $key => $var) {
		if ('mhash' == $key) {
			if (!xwb_function_exists($var[0]) && !xwb_function_exists($var[1])) {
				$check_func_items[implode(',', $var).'( )'] = array('s' => 'unsupportted_both', 'state' => false);
				$result = false;
			} else {
				$check_func_items[$var[0].'( ),'.$var[1].'( )'] = array('s' => 'supportted', 'state' => true);
			}
		} elseif ('sock' == $key) {
			if (!xwb_function_exists($var[0]) && !xwb_function_exists($var[1], array('init', 'exec'))) {
				$check_func_items[implode(',', $var).'( )'] = array('s' => 'unsupportted_both', 'state' => false);
				$result = false;
			} else {
				$check_func_items[$var[0].'( ),'.$var[1].'( )'] = array('s' => 'supportted', 'state' => true);
			}
		} else {
			if (xwb_function_exists($var)) {
				$check_func_items[$var.'( )'] = array('s' => 'supportted', 'state' => true);
			} else {
				$check_func_items[$var.'( )'] = array('s' => 'unsupportted', 'state' => false);
				$result = false;
			}
		}
	}
	return $check_func_items;
}

/**
 * 检查方法是否可用
 *
 * @param string $func 函数名或扩展模块名
 * @param array $ext 扩展模块，具体的函数扩展名，可以多个
 *
 * @return bool
 */
function pfw_function_exists($func, $ext = false) {
	/// 获取被禁用的方法
	$disable_functions = ini_get('disable_functions');
	$result = true;
	if ($ext) {
		foreach ($ext as $var) {
			$func_name = $func.'_'.$var;
			if (strpos($disable_functions, $func) !== false || !function_exists($func_name)) {
				$result = false;
			}
		}
	} else {
		if (strpos($disable_functions, $func) !== false || !function_exists($func)) {
			$result = false;
		}
	}

	return $result;
}

/**
 * 检查mc是否可用
 *
 * @param unknown_type
 * @return unknown
 */
function check_mc_connect($mc_host, $mc_port)
{
	global $_LANG;

	/// 没有加载mc模块
	if (!xwb_function_exists('memcache', array('connect'))) {
		show_msg($_LANG['advice_memcache_connect']);
	}
	$memcache = new Memcache;
	@$memcache->connect($mc_host, $mc_port) || show_msg($_LANG['mc_connect_error']);
}

/**
 * 创建数据库
 *
 * @param unknown_type
 * @return unknown
 */
function create_db($db_host, $db_user, $db_passwd, $db_name)
{
	global $_LANG, $pfw_isError;

	/// 注册错误处理方法
	register_shutdown_function('error', $_LANG['database_exists_error']);

	$link = @mysql_connect($db_host, $db_user, $db_passwd);

	$xwb_isError = 0;
	
	if (!$link) {
		/// 错误日志
		install_log("sql:  \r\nerrno: ".mysql_errno()." \r\nerror: ".mysql_error());
		show_msg($_LANG['database_connect_error']);
	}

    if (mysql_select_db($db_name, $link) === false)
    {
        $sql = "CREATE DATABASE $db_name DEFAULT CHARACTER SET " . XWEIBO_DB_CHARSET;
        if (mysql_query($sql, $link) === false)
        {
			$errno = mysql_errno($link);
			$error = mysql_error($link);
			/// 错误日志
			install_log('sql: '.$sql." \r\nerrno: ".$errno." \r\nerror: ".$error);
			if ($errno == 1064) {
				show_msg($_LANG['database_create_1064_error']);
			} elseif ($errno == 1044 || $errno == 1045) {
				show_msg($_LANG['database_create_1044_error']);
			} else {
				show_msg($_LANG['database_create_error']);
			}
        }
    }
    mysql_close($link);
}

/**
 * 创建数据库资源
 *
 * @param unknown_type
 * @return unknown
 */
function db_resource($db_host = null, $db_user = null, $db_passwd = null, $db_name = null, $ajax = false)
{
	global $_LANG;
	$link = @mysql_connect($db_host, $db_user, $db_passwd);
	if (!$link) {
		/// 错误日志
		install_log("sql: \r\nerrno: ".mysql_errno()." \r\nerror: ".mysql_error());
		if ($ajax) {
			die($_LANG['database_connect_error']);
		}
		show_msg($_LANG['database_connect_error'], 'index.php?step=3');
	}
	if (!mysql_select_db($db_name, $link)) {
		if ($ajax) {
			return '-1';
			//die($_LANG['database_exists_error']);
		}
		show_msg($_LANG['database_exists_error']);
	}
	mysql_query('SET NAMES '.XWEIBO_DB_CHARSET, $link);
	return $link;
}

/**
 * 检测安装使用的数据是否已经存在
 *
 * @param unknown_type
 * @return unknown
 */
function db_exists($db_host, $db_user, $db_passwd, $db_name, $db_prefix = null)
{
	global $_LANG;
	$link = @mysql_connect($db_host, $db_user, $db_passwd);
	if (!$link) {
		/// 错误日志
		install_log("sql: \r\nerrno: ".mysql_errno()." \r\nerror: ".mysql_error());
		show_msg($_LANG['database_connect_error']);
	}
	$sql = 'show databases';
	$result = mysql_query($sql);
	
	if(!$result){
		install_log("sql: 'show databases' Error\nerrno: ".mysql_errno()." \r\nerror: ".mysql_error());
		show_msg($_LANG['database_sql_show_error']);
	}
	
	$list = array();
	while ($row = mysql_fetch_assoc($result)) {
		if ($db_name == $row['Database']) {
			$ret = check_app_key($link, $db_name, $db_prefix);
			if ($ret == '10001' || $ret == '10000') {
				return $ret;
			}
			return '1';
		}
	}
	return '0';
}

/**
 * 检测之前安装xweibo的版本
 *
 * @param unknown_type
 * @return unknown
 */
function check_version($db_host, $db_user, $db_passwd, $db_name, $db_prefix = null)
{
	$link = db_resource($db_host, $db_user, $db_passwd, $db_name);

	if ($db_prefix) {
		$table_name = $db_prefix.'sys_config';
	} else {
		$sql = "show tables like '%sys_config'";
		$result = mysql_query($sql, $link);
		$list = array();
		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			if (empty($row[0])) {
				return '0';
			}
			$sql = 'SELECT value FROM '.$row[0].' WHERE `key` = "wb_version"';
			$ret = mysql_query($sql, $link);
			$fields_rows = mysql_fetch_assoc($ret);
			if ($fields_rows) {
				$table_name = $row[0];
				break;
			}
			return '0';
		}
	}

	$sql = 'SELECT value FROM '.$table_name.' WHERE `key` = "wb_version"';
	$ret = mysql_query($sql, $link);
	if ($ret) {
		$row = mysql_fetch_assoc($ret);
		if ($row['value'] != XWEIBO_VERSION) {
			return $row['value'];
		}
		return '1';
	}
	return '0';

	mysql_close();
}

/**
 * 创建表并且返回表的列表
 *
 * @param unknown_type
 * @return unknown
 */
function create_tables($db_host, $db_user, $db_passwd, $db_name, $db_prefix)
{
	global $_LANG;
	$lang_type = isset($_COOKIE['xwb_install_config_lang']) ? $_COOKIE['xwb_install_config_lang'] : 'zh_cn';
	$data_sql = XWEIBO_DB_STRUCTURE_FILE_NAME.'_'.$lang_type.'.'.XWEIBO_VERSION.'.sql';
	$fp = fopen(ROOT_PATH.'/install/data/'.$data_sql, 'r');
	$sql_items = fread($fp, filesize(ROOT_PATH.'/install/data/'.$data_sql));
	fclose($fp);

	/// 删除SQL行注释
	$sql_items = preg_replace('/^\s*(?:--|#).*/m', '', $sql_items);
	/// 删除SQL块注释
	$sql_items = preg_replace('/^\s*\/\*.*?\*\//ms', '', $sql_items);
	/// 代替表前缀
	$keywords = 'CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|'
			  . 'DROP\s+TABLE(?:\s+IF\s+EXISTS)?|'
			  . 'ALTER\s+TABLE|'
			  . 'UPDATE|'
			  . 'REPLACE\s+INTO|'
			  . 'DELETE\s+FROM|'
			  . 'INSERT\s+INTO|'
			  .	'LOCK\s+TABLES';
	$pattern = '/(' . $keywords . ')(\s*)`?' . XWEIBO_SCRPIT_DB_PREFIX . '(\w+)`?(\s*)/i';
	$replacement = '\1\2`' . $db_prefix . '\3`\4';
	$sql_items = preg_replace($pattern, $replacement, $sql_items);

	$pattern = '/(UPDATE.*?WHERE)(\s*)`?' . XWEIBO_SCRPIT_DB_PREFIX . '(\w+)`?(\s*\.)/i';
	$replacement = '\1\2`' . $db_prefix . '\3`\4';
	$sql_items = preg_replace($pattern, $replacement, $sql_items);

	$sql_items = str_replace("\r", '', $sql_items);
	$query_items = explode(";\n", $sql_items);

	$link = db_resource($db_host, $db_user, $db_passwd, $db_name);
	$sign = true;
	
	foreach ($query_items as $var) {
		$var = trim($var);

		if (empty($var)) {
			continue;
		}

		$sign = mysql_query($var, $link);
		if (!$sign) {
			/// 错误日志
			install_log('sql: '.$var." \r\nerrno: ".mysql_errno($link)." \r\nerror: ".mysql_error($link));
		}
	}


	mysql_close($link);
	if (!$sign) {
		show_msg($_LANG['tables_create_error']);
	}
}

/**
 * 罗列表列表
 *
 * @param unknown_type
 * @return unknown
 */
function get_tables_list()
{
	global $_LANG;
	//$config_info = get_ini_config();
	//@extract($config_info, EXTR_SKIP);
	$db_host = DB_HOST;
	$db_user = DB_USER;
	$db_passwd = DB_PASSWD;
	$db_name = DB_NAME;
	$db_prefix = DB_PREFIX;

	$link = db_resource($db_host, $db_user, $db_passwd, $db_name);
	/// 罗列表
	$sql = 'SHOW tables';
	$result = mysql_query($sql, $link);
	$list = array();
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if (!preg_match("/$db_prefix.+/i", $row[0])) {
			continue;
		}
		$list[] = $row;
	}
	mysql_free_result($result);
	mysql_close($link);

	return $list;

}

function error($msg, $type = 1)
{
	global $_LANG, $xwb_isError;
	if ($xwb_isError) {
		header('Content-Type: text/html; charset=utf-8');
		echo $_LANG['database_host_connect_error'];
		//include 'templates/error.php';
	}
	exit;
}

/**
 * 处理数据库和数据
 *
 * @param unknown_type
 * @return unknown
 */
function action_dbs($db_host, $db_user, $db_passwd, $db_name, $db_prefix, $cover, $admin_name = null, $admin_passwd = null)
{
	if (2 == $cover) {
		/// 覆盖安装
		create_db($db_host, $db_user, $db_passwd, $db_name);

		//clear_tables($db_host, $db_user, $db_passwd, $db_name, $db_prefix);

		create_tables($db_host, $db_user, $db_passwd, $db_name, $db_prefix);

		init_site_data($db_host, $db_user, $db_passwd, $db_name, $db_prefix);
	} elseif(1 == $cover || 3 == $cover) {
		/// 升级安装或保护安装
		
		///保护安装检查
		if(3 == $cover){
			if(true == checkXweiboTableExist($db_host, $db_user, $db_passwd, $db_name, $db_prefix)){
				//立刻返回
				return '90000';
			}else{
				$ver = '0';
			}
			
		///升级安装检查	
		}else{
			/// 检查数据库是否存在, appkey是否跟之前的一致
			$ret = db_exists($db_host, $db_user, $db_passwd, $db_name, $db_prefix);
			if ('10001' == $ret || '10000' == $ret) {
				$ver = '0';
			} elseif ('0' != $ret) {
				/// 检查之前安装的版本号
				$ver = check_version($db_host, $db_user, $db_passwd, $db_name, $db_prefix);
				if ('1' == $ver) {
					/// 相同版本的前提，检查之前安装的语言类型
					$ver = check_lang($db_host, $db_user, $db_passwd, $db_name, $db_prefix);
				}
			} else {
				$ver = $ret;
			}
		}
		
		if ('1' == $ver) {
			/// 相同版本
			return '20000';
		} elseif ('0' == $ver) {
			/// 查询不到版本信息，就覆盖安装
			create_db($db_host, $db_user, $db_passwd, $db_name);

			//clear_tables($db_host, $db_user, $db_passwd, $db_name, $db_prefix);

			create_tables($db_host, $db_user, $db_passwd, $db_name, $db_prefix);

			init_site_data($db_host, $db_user, $db_passwd, $db_name, $db_prefix);
		} else {
			/// 先备份数据
			/*
			$link = db_resource($db_host, $db_user, $db_passwd, $db_name);
			$sql = 'SHOW tables';
			$result = mysql_query($sql, $link);
			$list = array();
			$db_prefix = $db_prefix;
			$tables = array();
			$sql_dump = '';
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				if (!preg_match("/$db_prefix.+/i", $row[0])) {
					continue;
				}
				$sql_dump .= backup_data_sql($link, $row[0]);
				$tables[] = $row[0];
			}
			/// 生成备份数据表的sql文件
			$file_name = 'install_'.$db_name.'_data_backup.sql';
			$fp = fopen(ROOT_PATH.'/var/data/'.$file_name, 'wb+');
			if ($fp == false) {
				die($_LANG['datadir_access']);
			}
			if (fwrite($fp, $sql_dump) === false) {
				die($_LANG['xweibo_uninstall_backup_error']);
			}
			fclose($fp);
			 */

			/// 不同版本
			$fun_name = 'action_db'.str_replace('.', '', XWEIBO_VERSION);
			include_once 'upgrade.class.php';
			$upgrade = new upgrade($db_host, $db_user, $db_passwd, $db_name);
			$fun_lists = get_class_methods($upgrade);
			if (in_array($fun_name, $fun_lists)) {
				call_user_func(array($upgrade, $fun_name), $db_prefix);
			}
			return '20001';
		}
	}
}

/**
 * 备份数据
 *
 *
 */
function backup_data($db_host, $db_user, $db_passwd, $db_name, $db_prefix) 
{
	/// 先备份数据
	$link = db_resource($db_host, $db_user, $db_passwd, $db_name);
	$sql = 'SHOW tables';
	$result = mysql_query($sql, $link);
	$list = array();
	$db_prefix = $db_prefix;
	$tables = array();
	$sql_dump = '';
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if (!preg_match("/$db_prefix.+/i", $row[0])) {
			continue;
		}
		$sql_dump .= backup_data_sql($link, $row[0]);
		$tables[] = $row[0];
	}
	/// 生成备份数据表的sql文件
	$file_name = date('Ymd').'_'.$db_name.'_data_backup.sql';
	$fp = fopen(ROOT_PATH.'/var/data/'.$file_name, 'wb+');
	if ($fp == false) {
		die($_LANG['datadir_access']);
	}
	if (fwrite($fp, $sql_dump) === false) {
		die($_LANG['xweibo_uninstall_backup_error']);
	}
	fclose($fp);
}

/**
 * 备份数据表sql语句
 *
 * @param unknown_type
 * @return unknown
 */
function backup_data_sql($link, $table)
{
	$sql_dump = '';
	$sql = 'SELECT * FROM '.$table;
	$result = mysql_query($sql, $link);
	$field_key = array();
	$field_value = array();
	$field_value_string = array();
	while($row = mysql_fetch_assoc($result)) {
		if (empty($row)) {
			continue;
		}
		foreach ($row as $key => $var) {
			$field_key[$key] = "`".$key."`";
			$field_value[$key] = "'".mysql_real_escape_string($var)."'";
		}
		$field_value_string[] = '('.implode(', ', $field_value).')';
	}

	if (!empty($field_value_string)) {
		$sql_dump .= "INSERT INTO $table (".implode(', ', $field_key).")VALUES".implode(',', $field_value_string)."\r\n";
	}
	return $sql_dump;
}

/**
 * 初始化网站信息
 *
 * @param unknown_type
 * @return unknown
 */
function init_site_data($db_host, $db_user, $db_passwd, $db_name, $db_prefix = 'pf_')
{
	global $_LANG;

	$link = db_resource($db_host, $db_user, $db_passwd, $db_name);
	$table = $db_prefix.'sys_config';
	$sql = "REPLACE INTO $table (`key`,`value`)VALUES('site_name','".mysql_real_escape_string(WB_USER_SITENAME)."'),('wb_version','".mysql_real_escape_string(XWEIBO_VERSION)."'),('app_key', '".mysql_real_escape_string(WB_AKEY)."'),('app_secret', '".mysql_real_escape_string(WB_SKEY)."'),('db_prefix', '".mysql_real_escape_string($db_prefix)."'),('wb_lang_type','" .(isset($_COOKIE['xwb_install_config_lang'])?$_COOKIE['xwb_install_config_lang']: 'zh_cn'). "')";
	if (mysql_query($sql, $link) == false) {
		/// 错误日志
		install_log('sql: '.$sql." \r\nerrno: ".mysql_errno($link)." \r\nerror: ".mysql_error($link));
		show_msg($_LANG['add_admin_errno']);
	}

	mysql_close($link);
}

/**
 * 获取临时ini文件变量
 *
 * @param unknown_type
 * @return unknown
 */
function get_ini_config()
{
	if (!file_exists(ROOT_PATH.'/var/data/temp.ini')) {
		return false;
	}
	$fp = fopen(ROOT_PATH.'/var/data/temp.ini', 'rb+');
	$content = fread($fp, filesize(ROOT_PATH.'/var/data/temp.ini'));

	$site_base_info = array();
	parse_str($content, $site_base_info);
	return $site_base_info;
}

/**
 * 创建临时ini文件
 *
 * @param unknown_type
 * @return unknown
 */
function set_ini_file($config)
{
	global $_LANG;
	extract($config, EXTR_SKIP);

	$app_config = get_ini_config();

	$site_name = isset($site_name) ? $site_name : $app_config['site_name'];
	$site_info = isset($site_info) ? $site_info : $app_config['site_info'];
	$app_key = isset($app_key) ? $app_key : $app_config['app_key'];
	$app_secret = isset($app_secret) ? $app_secret : $app_config['app_secret'];

	$db_host = isset($db_host) ? $db_host : null;
	$db_name = isset($db_name) ? $db_name : null;
	$db_passwd = isset($db_passwd) ? $db_passwd : null;
	$db_user = isset($db_user) ? $db_user : null;
	$db_prefix = isset($db_prefix) ? $db_prefix : null;
	$cache = isset($cache) ? $cache : null;
	$mc_user = isset($mc_user) ? $mc_user : null;
	$mc_host = isset($mc_host) ? $mc_host : null;

	$config_file = 'site_name='.urlencode($site_name).'&site_info='.urlencode($site_info).'&app_key='.urlencode($app_key).'&app_secret='.urlencode($app_secret).'&db_host='.urlencode($db_host).'&db_name='.urlencode($db_name).'&db_user='.urlencode($db_user).'&db_passwd='.urlencode($db_passwd).'&db_prefix='.urlencode($db_prefix).'&cache='.urlencode($cache).'&mc_host='.urlencode($mc_host);
	/// 写临时ini配置文件
	$fp = fopen(ROOT_PATH.'/var/data/temp.ini', 'wb+');
	if ($fp == false) {
		show_msg($_LANG['datadir_access']);
	} else {
		if (fwrite($fp, $config_file) === false) {
			show_msg($_LANG['tmp_config_error']);
		}
		fclose($fp);
	}
}

/**
 * 修改css文件
 *
 * @param unknown_type
 * @return unknown
 */
function modifly_css_file($path)
{
	$fp = fopen(ROOT_PATH.'/css/default/base.css', 'rb+');
	if ($fp == false) {
		show_msg($_LANG['create_config_error']);
	} else {
		$css = fread($fp, filesize(ROOT_PATH.'/css/default/base.css'));
		$css = preg_replace('/(\.zoom-move){.+}/', "\$1{ cursor:url($path/css/default/bgimg/big.cur), auto;}", $css);
		$css = preg_replace('/(\.narrow-move){.+}/', "\$1{ cursor:url($path/css/default/bgimg/small.cur), auto;}", $css);
		fclose($fp);

		$fp = fopen(ROOT_PATH.'/css/default/base.css', 'wb+');
		if (fwrite($fp, $css) === false) {
			show_msg($_LANG['write_css_error']);
		}
		fclose($fp);
	}
}

/**
 * 获取ip
 *
 * @return unknown
 */
function get_real_ip() {
	/// Gets the default ip sent by the user
	if (!empty($_SERVER['REMOTE_ADDR'])) {
		$direct_ip = $_SERVER['REMOTE_ADDR'];
	}
	/// Gets the proxy ip sent by the user
	$proxy_ip     = '';
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$proxy_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
		$proxy_ip = $_SERVER['HTTP_X_FORWARDED'];
	} else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
		$proxy_ip = $_SERVER['HTTP_FORWARDED_FOR'];
	} else if (!empty($_SERVER['HTTP_FORWARDED'])) {
		$proxy_ip = $_SERVER['HTTP_FORWARDED'];
	} else if (!empty($_SERVER['HTTP_VIA'])) {
		$proxy_ip = $_SERVER['HTTP_VIA'];
	} else if (!empty($_SERVER['HTTP_X_COMING_FROM'])) {
		$proxy_ip = $_SERVER['HTTP_X_COMING_FROM'];
	} else if (!empty($_SERVER['HTTP_COMING_FROM'])) {
		$proxy_ip = $_SERVER['HTTP_COMING_FROM'];
	}
	/// Returns the true IP if it has been found, else FALSE
	if (empty($proxy_ip)) {
		/// True IP without proxy
		return $direct_ip;
	} else {
		$is_ip = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $proxy_ip, $regs);
		if ($is_ip && (count($regs) > 0)) {
			/// True IP behind a proxy
			return $regs[0];
		} else {
			/// Can't define IP: there is a proxy but we don't have
			/// information about the true IP
			return $direct_ip;
		}
	}
}

/**
 * 错误提示信息
 *
 * @param unknown_type
 * @return unknown
 */
function show_msg($msg, $type = 1)
{
	global $_LANG;
	//include 'templates/error.php';
	include dirname(__FILE__). '/../templates/error.php';
	exit;
}

/**
 * 写错误日志
 *
 *
 */
function install_log($msg)
{
	global $_LANG;

	$file_log = ROOT_PATH.'/install/data/log.php';
	$msg = sprintf("[%s]:\t%s\r\n",date("Y-m-d H:i:s"),$msg);
	if (!file_exists($file_log)){
		$define_string = 'if(!defined("XWEIBO_ACCESS")) { exit("NOT ACCESS"); }';
		$msg = "<?php  ".$define_string." ?> \r\n\r\n".$msg;
	}
	$fp = fopen($file_log, 'ab');
	if ($fp === false) {
		show_msg($_LANG['create_log_error']);
	}
	flock($fp, LOCK_EX);
	if (fwrite($fp, $msg) === false) {
		show_msg($_LANG['write_log_error']);
	}
	flock($fp, LOCK_UN);
	@fclose($fp);
}

/**
 * 替换配置选的值
 *
 *
 */
function setDefineValue($s,$k,$v=''){
	if (is_array($k)){
		foreach($k as $kk=>$vv){
			$p = "#define\s*\(\s*'".preg_quote($kk)."'\s*,(\s*)'.*?'\s*\)\s*;#sm";
			$s = preg_replace($p, "define('".$kk."',\\1'".$vv."');",$s);
		}
		return $s;
	}else{
		$p = "#define\s*\(\s*'".preg_quote($k)."'\s*,(\s*)'.*?'\s*\)\s*;#sm";
		return preg_replace($p, "define('".$k."',\\1'".$v."');",$s);
	}
}

/**
 * 创建配置文件
 *
 * @param unknown_type
 * @return unknown
 */
function set_config_file()
{
	global $_LANG;
	
	$site_configs = get_ini_config();
	$app_key = $site_configs['app_key'];
	$app_secret = $site_configs['app_secret'];
	$site_name = $site_configs['site_name'];
	$site_info = $site_configs['site_info'];
	$db_host = $site_configs['db_host'];
	$db_user = $site_configs['db_user'];
	$db_name = $site_configs['db_name'];
	$db_passwd = $site_configs['db_passwd'];
	$db_prefix = $site_configs['db_prefix'];
	$db_charset = XWEIBO_DB_CHARSET;

	/// 生成安装目录
	$local_uri = '';
	if (isset($_SERVER['REQUEST_URI'])){
		$local_uri = $_SERVER['REQUEST_URI'];
	}
	if (empty($local_uri) && isset($_SERVER['PHP_SELF']) ){
		$local_uri = $_SERVER['PHP_SELF'];
	}
	if (empty($local_uri) && isset($_SERVER['SCRIPT_NAME']) ){
		$local_uri = $_SERVER['SCRIPT_NAME'];
	}
	if (empty($local_uri) && isset($_SERVER['ORIG_PATH_INFO']) ){
		$local_uri = $_SERVER['ORIG_PATH_INFO'];
	}
	if (empty($local_uri)){
		//todo　获取不了　可供计算URI的　路径　错误显示
	}

	$uri_array = explode('/', $local_uri);
	$paths = array();
	foreach ($uri_array as $var) {
		if ($var == 'install' || $var == 'uninstall' || strpos($var, '.php')) {
			break;
		}
		$paths[] = $var;
	}
	$path_string = implode('/', $paths);
	$path_string = empty($path_string) ? '/' : $path_string.'/';

	/// 是否加载curl模块
	if (xwb_function_exists('curl', array('init', 'exec'))) {
		$http_adapter = 'curl';
	} else {
		$http_adapter = 'fsockopen';
	}

	$upload_size = ini_get('upload_max_filesize');
	$u = substr($upload_size, -1, 1);
	$max_upload = $u == 'M' ? (int)$upload_size : ($u == 'K' ? (int)$upload_size / 1024 : (int)$upload_size / (1024 * 1024));
	$upload_max_filesize = min($max_upload, XWEIBO_MAX_UPLOAD_FILE_SIZE);
	$config_array = array('W_BASE_URL_PATH' => $path_string, 
		'DB_HOST' => $db_host, 
		'DB_NAME' => $db_name,
		'DB_USER' => $db_user,
		'DB_PASSWD' => $db_passwd,
		'DB_CHARSTE' => $db_charset,
		'DB_PREFIX' => $db_prefix,
		'WB_AKEY' => $app_key,
		'WB_SKEY' => $app_secret,
		'HTTP_ADAPTER' => $http_adapter,
		'MAX_UPLOAD_FILE_SIZE' => $upload_max_filesize,
		'APP_FLAG_VER' => date('mdHis'),
		'WB_USER_SITENAME' => $site_name,
		'WB_USER_SITEINFO' => $site_info);

	if ($site_configs['cache'] == 1) {
		$config_array = array_merge($config_array, array('CACHE_ADAPTER' => 'memcache', 'MC_HOST' => $site_configs['mc_host']));
	} else {
		$config_array = array_merge($config_array, array('CACHE_ADAPTER' => 'file'));
	}

	/// 写配置文件
	$fp = fopen(ROOT_PATH.'/user_config.php', 'r');
	if ($fp == false) {
		show_msg($_LANG['create_config_error']);
	} else {
		$config_content = fread($fp, filesize(ROOT_PATH.'/user_config.php'));
		$config_file = setDefineValue($config_content, $config_array);
		fclose($fp);
	}
	$fp = fopen(ROOT_PATH.'/user_config.php', 'w');
	if (fwrite($fp, $config_file) === false) {
		show_msg($_LANG['write_config_error']);
	}
	fclose($fp);

	/// 创建安装成功文件标示
	$fp = fopen(ROOT_PATH.'/var/data/install.lock', 'w+');
	if ($fp != false) {
		if (fwrite($fp, time()) === false) {
			show_msg($_LANG['create_install_lock_error']);
		}
		fclose($fp);
	} else {
		show_msg($_LANG['create_install_lock_error']);
	}

	/// 删除临时ini配置文件
	@unlink(ROOT_PATH.'/var/data/temp.ini');

	session_start();
	/// 清零cookie, session
	if (isset($_COOKIE) && !empty($_COOKIE)) {
		foreach ($_COOKIE as $key => $var) {
			setcookie($key , '' , time()-3600 , $path_string, '' , 0 );
			unset($_COOKIE[$key]);
		}	
	}
	if (isset($_SESSION) && !empty($_SESSION)) {
		foreach ($_SESSION as $key => $var) {
			unset($_SESSION[$key]);	
		}
		session_destroy();
	}
}

/**
 * 修改user_config配置文件
 *
 * @param array $value
 * 
 * @return 
 */
function set_userConfig($values) {
	/// 写配置文件
	$fp = fopen(ROOT_PATH.'/user_config.php', 'r');
	if ($fp == false) {
		show_msg($_LANG['create_config_error']);
	} else {
		$config_content = fread($fp, filesize(ROOT_PATH.'/user_config.php'));
		$config_file = setDefineValue($config_content, $values);
		fclose($fp);
	}
	$fp = fopen(ROOT_PATH.'/user_config.php', 'w');
	if (fwrite($fp, $config_file) === false) {
		show_msg($_LANG['write_config_error']);
	}
	fclose($fp);
}

/**
 * 修改配置环境
 *
 */
function set_config_env() {
	global $_LANG;
	
	$db_charset = XWEIBO_DB_CHARSET;

	/// 生成安装目录
	$local_uri = '';
	if (isset($_SERVER['REQUEST_URI'])){
		$local_uri = $_SERVER['REQUEST_URI'];
	}
	if (empty($local_uri) && isset($_SERVER['PHP_SELF']) ){
		$local_uri = $_SERVER['PHP_SELF'];
	}
	if (empty($local_uri) && isset($_SERVER['SCRIPT_NAME']) ){
		$local_uri = $_SERVER['SCRIPT_NAME'];
	}
	if (empty($local_uri) && isset($_SERVER['ORIG_PATH_INFO']) ){
		$local_uri = $_SERVER['ORIG_PATH_INFO'];
	}
	if (empty($local_uri)){
		//todo　获取不了　可供计算URI的　路径　错误显示
	}

	$uri_array = explode('/', $local_uri);
	$paths = array();
	foreach ($uri_array as $var) {
		if ($var == 'install' || $var == 'uninstall' || strpos($var, '.php')) {
			break;
		}
		$paths[] = $var;
	}
	$path_string = implode('/', $paths);
	$path_string = empty($path_string) ? '/' : $path_string.'/';

	/// 是否加载curl模块
	if (xwb_function_exists('fsockopen')) {
		$http_adapter = 'fsockopen';
	} elseif (xwb_function_exists('curl', array('init', 'exec'))) {
		$http_adapter = 'curl';
	}

	$upload_size = ini_get('upload_max_filesize');
	$u = substr($upload_size, -1, 1);
	$max_upload = $u == 'M' ? (int)$upload_size : ($u == 'K' ? (int)$upload_size / 1024 : (int)$upload_size / (1024 * 1024));
	$upload_max_filesize = min($max_upload, XWEIBO_MAX_UPLOAD_FILE_SIZE);
	$config_array = array('W_BASE_URL_PATH' => $path_string, 
		'DB_CHARSTE' => $db_charset,
		'HTTP_ADAPTER' => $http_adapter,
		'MAX_UPLOAD_FILE_SIZE' => $upload_max_filesize,
		'APP_FLAG_VER' => date('mdHis'));

	if (MC_HOST) {
		$config_array = array_merge($config_array, array('CACHE_ADAPTER' => 'memcache'));
	} else {
		$config_array = array_merge($config_array, array('CACHE_ADAPTER' => 'file'));
	}

	/// 写配置文件
	$fp = fopen(ROOT_PATH.'/user_config.php', 'r');
	if ($fp == false) {
		show_msg($_LANG['create_config_error']);
	} else {
		$config_content = fread($fp, filesize(ROOT_PATH.'/user_config.php'));
		$config_file = setDefineValue($config_content, $config_array);
		fclose($fp);
	}
	$fp = fopen(ROOT_PATH.'/user_config.php', 'w');
	if (fwrite($fp, $config_file) === false) {
		show_msg($_LANG['write_config_error']);
	}
	fclose($fp);

	/// 创建安装成功文件标示
	$fp = fopen(ROOT_PATH.'/var/data/install.lock', 'w+');
	if ($fp != false) {
		if (fwrite($fp, time()) === false) {
			show_msg($_LANG['create_install_lock_error']);
		}
		fclose($fp);
	} else {
		show_msg($_LANG['create_install_lock_error']);
	}


	@session_start();
	/// 清零cookie, session
	if (isset($_COOKIE) && !empty($_COOKIE)) {
		foreach ($_COOKIE as $key => $var) {
			setcookie($key , '' , time()-3600 , $path_string, '' , 0 );
			unset($_COOKIE[$key]);
		}	
	}
	if (isset($_SESSION) && !empty($_SESSION)) {
		foreach ($_SESSION as $key => $var) {
			unset($_SESSION[$key]);	
		}
		session_destroy();
	}
}

/**
 * 检查数据库连接
 *
 * @param string $db_host
 * @param string $db_user
 * @param string $db_passwd
 *
 * @return bool
 */
function check_db_connect($db_host, $db_user, $db_passwd) {
	global $_LANG, $xwb_isError;

	/// 注册错误处理方法
	register_shutdown_function('error', $_LANG['database_exists_error']);

	$link = @mysql_connect($db_host, $db_user, $db_passwd);

	$xwb_isError = 0;
	
	if (!$link) {
		/// 错误日志
		install_log("sql:  \r\nerrno: ".mysql_errno()." \r\nerror: ".mysql_error());
		show_msg($_LANG['database_connect_error']);
	}
	return true;
}

/**
 * 标识安装步骤，防止跳过
 */
function set_db_cookie($db_host = null, $db_user = null, $db_passwd = null) {
	$db_host = empty($db_host) ? DB_HOST : $db_host;
	$db_user = empty($db_user) ? DB_USER : $db_user;
	$db_passwd = empty($db_passwd) ? DB_PASSWD : $db_passwd;
	$value = md5($db_host.'#'.$db_user.'#'.$db_passwd);
	setCookie('check_db_admin', $value);
}

/**
 * 获取安装步骤标识`
 */
function get_db_cookie($db_host = null, $db_user = null, $db_passwd = null) {
	$db_host = empty($db_host) ? DB_HOST : $db_host;
	$db_user = empty($db_user) ? DB_USER : $db_user;
	$db_passwd = empty($db_passwd) ? DB_PASSWD : $db_passwd;
	$key = md5($db_host.'#'.$db_user.'#'.$db_passwd);
	$check_db_admin = isset($_COOKIE['check_db_admin']) ? $_COOKIE['check_db_admin'] : null;
	if ($check_db_admin == $key) {
		return true;
	}
	return false;
}

/**
 * 清空所有旧数据表:所有sys_config表里db_prefix指定前缀所有数据表
 */
function clear_tables($db_host, $db_user, $db_passwd, $db_name, $db_prefix) {
	
	$link = db_resource($db_host, $db_user, $db_passwd, $db_name);
	$sql = "show tables like '%sys_config'";
	$result = mysql_query($sql, $link);
	$list = array();
	$table_name = false;
	$tables = array();
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if (empty($row[0])) {
			return '10000';
		}
		$sql = 'SELECT value FROM '.$row[0].' WHERE `key` = "db_prefix"';
		$ret = mysql_query($sql, $link);
		$fields_rows = mysql_fetch_assoc($ret);
		if ($fields_rows) {
			$db_prefix_old = $fields_rows['value'];
			// 删除当前版本外的所有数据表
			if ($db_prefix_old == $db_prefix || trim($db_prefix_old) == '') {
				continue;
			}

			$sql = "show tables like '{$db_prefix_old}%'";
			$rs = mysql_query($sql, $link);
			while ($table = mysql_fetch_row($rs)) {
				$tables[] = $table['0'];
			}
		}
	}
	if (!empty($tables)) {
		$sql = 'DROP TABLE IF EXISTS ' . implode(',', $tables);
		mysql_query($sql, $link);
	}

	mysql_close($link);

}

/**
 *
 * 更新表结构和数据
 *
 */
function upgrade_tables($db_prefix, $link) {
	global $_LANG;
	$lang_type = getLangType();
	$data_sql = 'upgrade_'.$lang_type.'.'.XWEIBO_VERSION.'.sql';
	$fp = fopen(ROOT_PATH.'/install/data/'.$data_sql, 'r');
	$sql_items = fread($fp, filesize(ROOT_PATH.'/install/data/'.$data_sql));
	fclose($fp);

	/// 删除SQL行注释
	$sql_items = preg_replace('/^\s*(?:--|#).*/m', '', $sql_items);
	/// 删除SQL块注释
	$sql_items = preg_replace('/^\s*\/\*.*?\*\//ms', '', $sql_items);
	/// 代替表前缀
	$keywords = 'CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|'
			  . 'DROP\s+TABLE(?:\s+IF\s+EXISTS)?|'
			  . 'ALTER\s+TABLE|'
			  . 'UPDATE|'
			  . 'REPLACE\s+INTO|'
			  . 'DELETE\s+FROM|'
			  . 'INSERT\s+INTO|'
			  .	'LOCK\s+TABLES';
	$pattern = '/(' . $keywords . ')(\s*)`?' . XWEIBO_SCRPIT_DB_PREFIX . '(\w+)`?(\s*)/i';
	$replacement = '\1\2`' . $db_prefix . '\3`\4';
	$sql_items = preg_replace($pattern, $replacement, $sql_items);

	$pattern = '/(UPDATE.*?WHERE)(\s*)`?' . XWEIBO_SCRPIT_DB_PREFIX . '(\w+)`?(\s*\.)/i';
	$replacement = '\1\2`' . $db_prefix . '\3`\4';
	$sql_items = preg_replace($pattern, $replacement, $sql_items);

	$sql_items = str_replace("\r", '', $sql_items);
	$query_items = explode(";\n", $sql_items);

	$sign = true;
	
	foreach ($query_items as $var) {
		$var = trim($var);

		if (empty($var)) {
			continue;
		}

		$sign = mysql_query($var, $link);
		if (!$sign) {
			/// 错误日志
			install_log('sql: '.$var." \r\nerrno: ".mysql_errno($link)." \r\nerror: ".mysql_error($link));
		}
	}

	if (!$sign) {
		show_msg($_LANG['tables_create_error']);
	}
}

/**
 *
 * 返回系统管理员的id
 */
function getAdminId() {
	return SYSTEM_SINA_UID;
}

/**
 * 返回系统语言类型
 */
function getLangType() {
	$lang_type = isset($_COOKIE['xwb_install_config_lang']) ? $_COOKIE['xwb_install_config_lang'] : 'zh_cn';
	return $lang_type;
}

/**
 * 获取之前xweibo版本的版本号
 */
function getXweiboVer($db_host, $db_user, $db_passwd, $db_name, $db_prefix) {
	$link = db_resource($db_host, $db_user, $db_passwd, $db_name, true);
	if ($link == '-1') {
		return false;
	}

	$sysConfigTable = $db_prefix.'sys_config';

	$sql = 'SELECT value FROM '.$sysConfigTable.' WHERE `key` = "wb_version"';
	$ret = mysql_query($sql, $link);
	if ($ret) {
		$fields_rows = mysql_fetch_assoc($ret);
		if ($fields_rows) {
			$old_ver = $fields_rows['value'];
			return $old_ver;
		}
	}

	return false;

	mysql_close($link);
}

//检查是否有xweibo的表存在？
function checkXweiboTableExist($db_host, $db_user, $db_passwd, $db_name, $db_prefix){
	$link = db_resource($db_host, $db_user, $db_passwd, $db_name, true);
	if ($link == '-1') {
		return true;
	}

	$tablecheck = array('sys_config','users','admin');
	foreach($tablecheck as $table){
		$table = $db_prefix.$table;
		$sql = "SHOW TABLES LIKE '{$table}'";
		$result = mysql_query($sql, $link);
		if(mysql_num_rows($result) > 0){
			return true;
		}
	}
	return false;
	
}

/**
 * 添加新定义到 userconfig文件
 */
function modifyUserConfig($str) {
	global $_LANG;
	if (empty($str)) {
		return false;
	}
	$fp = fopen(ROOT_PATH.'/user_config.php', 'ab+');
	if ($fp == false) {
		show_msg($_LANG['create_config_error']);
	} else {
		if (fwrite($fp, $str) === false) {
			show_msg($_LANG['write_config_error']);
		}
		fclose($fp);
	}
}

/**
 * 检测之前安装xweibo的语言类型
 *
 * @param unknown_type
 * @return unknown
 */
function check_lang($db_host, $db_user, $db_passwd, $db_name, $db_prefix = null)
{
	$link = db_resource($db_host, $db_user, $db_passwd, $db_name);

	if ($db_prefix) {
		$table_name = $db_prefix.'sys_config';
	} else {
		$sql = "show tables like '%sys_config'";
		$result = mysql_query($sql, $link);
		$list = array();
		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			if (empty($row[0])) {
				return '0';
			}
			$sql = 'SELECT value FROM '.$row[0].' WHERE `key` = "wb_version"';
			$ret = mysql_query($sql, $link);
			$fields_rows = mysql_fetch_assoc($ret);
			if ($fields_rows) {
				$table_name = $row[0];
				break;
			}
			return '0';
		}
	}

	$sql = 'SELECT value FROM '.$table_name.' WHERE `key` = "wb_lang_type"';
	$ret = mysql_query($sql, $link);
	if ($ret) {
		$row = mysql_fetch_assoc($ret);
		if ($row['value'] == getLangType()) {
			return '1';
		}
		return '0';
	}
	return '0';

	mysql_close();
}  
?>