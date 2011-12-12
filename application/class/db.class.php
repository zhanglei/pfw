<?php

/**
 * 数据库接口
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
if (!class_exists('ADOConnection')) {
	require P_THIRDPARTY_ADODB . '/adodb.inc.php';
}
class Db {

	private $prefix = '';
	//表前缀

	private $table;
	//表名

	private $config = array();
	//默认配置

	private $debug = false;
	//是否为调试

	private $autoFree = false;
	//是否自动释放

	private $ignore_insert = false;
	//是否忽略insert

	private $last_query_id = false;

	private $last_sql;

	private $querys = array();

	/**
	 * contructor
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * 得到读数据库连接(使用主从数据库)时，如果不是主从架构，则返回唯一的连接(主从设置可以站点根目录中的config.php中设置)
	 * @param $mode 'read'|'write' 使用读服务器还是写服务器
	 * @param $index int 指定要使用的读服务器配置项,如果不设置则随机选择配置项中指定的服务器，以达到读服务器均衡效果
	 * @param $reconnect boolean 指定是否强制重新连接,true为强制重连
	 * @return resource 返回数据库连接
	 */
	public function getConnect($mode = 'write', $index = null, $reconnect = false) {
		$log_func_start_time = microtime(TRUE);
		static $connect = null;
		static $count_reconnect = 0;
		// 重连次数
		static $error_connect = 0;
		// 连接错误的服务器数
		$mode = in_array($mode, array('write', 'read')) ? strtolower($mode) : 'write';

		// 如果第一次连接
		if (!isset($connect[$mode]) || $reconnect !== false) {
			$count_reconnect = 0;
			// 重新计算重连次数
			// 如果设置时使用读服务器，但没有相关配置项，则尝试使用写服务器
			if ($mode == 'read' && isset($this -> params['slaves']) && !empty($this -> params['slaves'])) {
				// 配置的服务器数量
				$count = count($this -> params['slaves']);
				if ($index === null || !is_int($index) || $index < 0 || $index >= $count) {
					$index = rand(0, $count - 1);
				}
				$p = $this -> config['slaves'][$index];
			} else {
				$p = $this -> config;
			}

			// 检查配置项完整
			if (!(isset($p['host']) && isset($p['user']) && isset($p['pwd']))) {
				$this -> log('[getConnect]Database config error', $p);
				$this -> debug && print('Database config error');
				exit();

				//return $this->getWriteConnect();
			}
			if (!isset($p['port'])) {
				$p['port'] = 3306;
			}

			$this -> infoLog('[getConnect]Create Database (' . $mode . ')connection:Host:' . $p['host'] . ':' . $p['port'] . ' , User:' . $p['user']);
			$this -> debug && print('Create Database (' . $mode . ')connection:Host:' . $p['host'] . ':' . $p['port'] . ' , User:' . $p['user'] . ' , ' . $p['pwd'] . "<br />");
			$connect[$mode] = @mysql_connect($p['host'] . ':' . $p['port'], $p['user'], $p['pwd']);

			// 如果连接失败，则尝试连接下一台读服务器
			if (!$connect[$mode]) {
				$this -> log('[getConnect]数据库连接失败.Error:' . mysql_error(), $p);
				F('error', mysql_error());
				if ($mode == 'read') {
					++$error_connect;
					//如查所有读服务器都连接失败,尝试连接写服务器
					if ($error_connect >= $count) {
						$this -> infoLog('[getConnect]Try to connect next (write)server');
						$this -> debug && print('Try to connect next (write)server<br />' . "\n");
						return $this -> getConnect();
					}

					if (++$index > $count) {
						$index = 0;
					}

					$this -> infoLog('[getConnect]Try to connect next (read)server');
					$this -> debug && print('Try to connect next (read)server<br />' . "\n");
					return $this -> getConnect('read', $index);
				} else {
					$this -> log('[getConnect]Connect Mysql server error', $p);
					exit('Connect Mysql server error.<br />' . "\n");
				}
			}

			// 默认使用UTF8编码
			$p['charset'] = isset($p['charset']) ? $p['charset'] : 'UTF8';
			$this -> _setCharset($p['charset'], $connect[$mode]);
			//mysql_query('SET NAMES ' . $p['charset'], $connect[$mode]);

			// 默认使用和写数据库相同的库名
			if (!isset($p['db'])) {
				$p['db'] = $this -> params['db'];
			}

			$this -> infoLog('[getConnect]Using db:' . $p['db']);
			$this -> debug && print('Using db:' . $p['db'] . "<br />\n");
			mysql_select_db($p['db'], $connect[$mode]);
		}

		// 如果出现长时间没mysql动作而引起超时，则尝试重连，重连次数为3
		if (!mysql_ping($connect[$mode])) {
			if ($count_reconnect < 3) {
				$count_reconnect++;
				mysql_close($connect[$mode]);

				$this -> infoLog('[getConnect]Try reconnect');
				$this -> debug && print('Try reconnect<br />' . "\n");
				return $this -> getConnect('read', $index, true);
			} else {
				$this -> log('[getConnect]Reconnect MySQL read server error');
				$this -> debug && print("Reconnect MySQL read server error <br />\n");
				return false;
			}
		}
		//$this->last_query_connect = $connect;
		$this -> waringLog($log_func_start_time, '[getConnect]数据库链接');
		return $connect[$mode];
	}

	/**
	 * @param $config array
	 * @return void
	 */
	public function init($config = array()) {
		$this -> config = $config;
		$this -> prefix = $config['tbpre'];
	}

	/**
	 * get prefix of table
	 * @return string
	 */
	public function getPrefix() {
		return $this -> prefix;
	}

	/**
	 * set table name
	 * @param $table_name string tablename
	 */
	public function setTable($table) {
		$this -> table = $table;
	}

	/**
	 * get table name
	 * @param $table_name string tablename
	 * @param $tb_prefix sting prefix of table
	 * @return string
	 */
	public function getTable($table_name = '', $tb_prefix = '') {
		if (empty($tb_prefix)) {
			$tb_prefix = $this -> getPrefix();
		}
		$table_name = trim($table_name);
		$table_name = $table_name == '' ? $this -> table : $table_name;
		return $tb_prefix . $table_name;
	}

	/**
	 * 设置调试模式(注意：调试模式将打印出sql帐号信息)
	 * @param $flag bool
	 * @return void
	 */
	public function setDebug($flag = true) {
		$this -> debug = ( boolean )$flag;
	}

	/**
	 * 设置是否忽略insert语句
	 * @param $ignore bool
	 * @return void
	 */
	public function setIgnoreInsert($ignore = true) {
		$this -> ignore_insert = ( boolean )$ignore;
	}

	/**
	 * push sql
	 * @param $sql string
	 * @return string
	 */
	public function pushSql($sql) {
		array_push($this -> querys, $sql);
		return $this -> last_sql = $sql;
	}

	/**
	 * set auto free query result
	 * @param $v bool
	 * @return void
	 */
	public function setAutoFree($v) {
		$this -> autoFree = ( bool )$v;
	}

	/**
	 * return last query SQL
	 * @return string last query SQL
	 */
	public function getLastQuery() {
		return $this -> last_sql;
	}

	/**
	 * return query history SQL
	 * @return array
	 */
	public function getHistory() {
		return $this -> querys;
	}

	/**
	 * 调用框架日志接口写日志
	 *@param $msg string log message
	 *@param $type 错误类型
	 */
	public function log($msg, $extra = array()) {
		LOGSTR('db', $msg, LOG_LEVEL_ERROR, $extra);
	}

	public function infoLog($msg, $extra = array(), $startTime = false) {
		LOGSTR('db', $msg, LOG_LEVEL_INFO, $extra, $startTime);
	}

	public function waringLog($startTime, $msg, $extra = array()) {
		$used = microtime(TRUE) - $startTime;
		$longExe = $startTime && LOG_DB_WARNING_TIME && $used > LOG_DB_WARNING_TIME;
		if ($longExe) {
			LOGSTR('db', $msg . " Used=$used", LOG_LEVEL_WARNING, $extra);
		}
	}

	/**
	 * return escape value
	 * @param $str string
	 * @return string
	 */
	public function escape($str) {

	}

	/**
	 * return last insert id
	 * @return string|int|false
	 */
	public function getInsertId() {

	}

	/**
	 * get info by row id
	 * @param $id int query row id
	 * @param $table string table name
	 * @param $id_name string field name(primary key)
	 * @return array
	 */
	public function get($id, $table = '', $id_name = 'id') {

	}

	/**
	 * delete info
	 * @param $id int|array row id
	 * @param $table string table name
	 * @param $id_name string field name(primary key)
	 * @return int|false
	 */
	public function delete($id, $table = '', $id_name = 'id') {

	}

	/**
	 * insert or update table
	 * @param $data key/value array data
	 * @param $id int update id
	 * @param $table string table name
	 * @param $id_name string field name(primary key)
	 * @return int|false lastinsert id or update id
	 */
	public function save($data, $id = '', $table = '', $id_name = 'id') {

	}

	/**
	 * execute sql
	 * @param $sql string SQL
	 * @return void
	 */
	public function execute($sql) {

	}

	/**
	 *
	 * @return int affected rows
	 */
	public function getAffectedRows() {

	}

	/**
	 * execute SQL and return result
	 * @param $sql string SQL
	 * @return array
	 */
	public function query($sql) {

	}

	/**
	 * return the first field of a row
	 * @param $sql string SQL
	 * @return array
	 */
	public function getOne($sql, $index = 0) {

	}

	/**
	 * retun a row
	 * @param $sql string SQL
	 * @return array
	 */
	public function getRow($sql) {

	}

	/**
	 * return an error
	 * @return string
	 */
	public function getError() {

	}

	/**
	 * free query result
	 * @param int query handle
	 * @return void
	 */
	public function free($query_id = "") {

	}

	/**
	 * close connection
	 * @return void
	 */
	public function close() {

	}

}
?>