<?php
/**
 * 系统配置文件
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */
//Base Site URL
$config['base_url'] = '';

//
$config['charset'] = 'UTF-8';

//数据库配置
$database = array(
	'db_type' => 'mysql', 
	'masters' => array(
		array(
		'db_host' => '127.0.0.1',
		'db_user' => 'root',
		'db_password' => '123456',
		'db_port' => '3306',
		'db_name' => 'pfw',
		'db_table_prefix' => 'pf_'
		)
	),
	'slaves' => array(
		array(
		'db_host' => '127.0.0.1',
		'db_user' => 'root',
		'db_password' => '123456',
		'db_port' => '3306',
		'db_name' => 'pfw',
		'db_table_prefix' => 'pf_'
		)
	));

//smarty模块配置
$smarty = array('smarty_dir' => '', 'smarty_template_dir' => '', 'smarty_compile_dir' => '');
?>
