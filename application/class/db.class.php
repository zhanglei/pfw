<?php

/** 
 * 文件上传类
 * 
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09 
 */
class Db {
	
	private $prefix = '';//表前缀
	
	private $table;//表名
	
	private $config = array ();//默认配置
	
	private $debug = false;//是否为调试
	
	private $autoFree = false;//是否自动释放
	
	private $ignore_insert = false;//是否忽略insert
	
	private $last_query_id = false;
	
	private $last_sql;
	
	private $querys = array ();
	
	/**
	 * contructor
	 * @return void
	 */
	public function __construct() {
	
	}
	
	/**
	 * @param $params array
	 * @return void
	 */
	public function init($config) {
		$this->config = $config;
		$this->prefix = $config ['tbpre'];
	}
	
	/**
	 * get prefix of table
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}
	
	/**
	 * set table name
	 * @param $table_name string tablename
	 */
	public function setTable($table) {
		$this->table = $table;
	}
	
	/**
	 * get table name
	 * @param $table_name string tablename
	 * @param $tb_prefix sting prefix of table
	 * @return string
	 */
	public function getTable($table_name = '', $tb_prefix = '') {
		if (empty ( $tb_prefix )) {
			$tb_prefix = $this->getPrefix ();
		}
		$table_name = trim ( $table_name );
		$table_name = $table_name == '' ? $this->table : $table_name;
		return $tb_prefix . $table_name;
	}
	
	/**
	 * 设置调试模式(注意：调试模式将打印出sql帐号信息)
	 * @param $flag bool
	 * @return void
	 */
	public function setDebug($flag = true) {
		$this->debug = ( boolean ) $flag;
	}
	
	/**
	 * 设置是否忽略insert语句
	 * @param $ignore bool
	 * @return void
	 */
	public function setIgnoreInsert($ignore = true) {
		$this->ignore_insert = ( boolean ) $ignore;
	}
	
	/**
	 * push sql
	 * @param $sql string
	 * @return string
	 */
	public function pushSql($sql) {
		array_push ( $this->querys, $sql );
		return $this->last_sql = $sql;
	}
	
	/**
	 * set auto free query result
	 * @param $v bool
	 * @return void
	 */
	public function setAutoFree($v) {
		$this->autoFree = ( bool ) $v;
	}
	
	/**
	 * return last query SQL
	 * @return string last query SQL
	 */
	public function getLastQuery() {
		return $this->last_sql;
	}
	
	/**
	 * return query history SQL
	 * @return array
	 */
	public function getHistory() {
		return $this->querys;
	}
	
	/**
	 * 调用框架日志接口写日志
	 *@param $msg string log message
	 *@param $type 错误类型
	 */
	public function log($msg, $extra = array()) {
		LOGSTR ( 'db', $msg, LOG_LEVEL_ERROR, $extra );
	}
	
	public function infoLog($msg, $extra = array(), $startTime = false) {
		LOGSTR ( 'db', $msg, LOG_LEVEL_INFO, $extra, $startTime );
	}
	
	public function waringLog($startTime, $msg, $extra = array()) {
		$used = microtime ( TRUE ) - $startTime;
		$longExe = $startTime && LOG_DB_WARNING_TIME && $used > LOG_DB_WARNING_TIME;
		if ($longExe) {
			LOGSTR ( 'db', $msg . " Used=$used", LOG_LEVEL_WARNING, $extra );
		}
	}

}

?>