<?php
/**
 * 文件日志操作
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */

class file_log {
	var $logLevel = array('error' => 1, 'warning' => 2, 'info' => 3);
	static $logList;

	function file_log() {
		// To do
	}

	function adp_init($config = array()) {
		// To do
	}

	function log($type, $str, $level = 'info', $extra_params = array()) {
		//时间        项目名称    项目版本号    APP_KEY    错误类型    错误描述 sprintf()
		if ($this -> _needLog($level)) {
			// Extra Params Info
			$extraStr = (is_array($extra_params) && !empty($extra_params)) ? serialize($extra_params) : '';
			$isApiErr = isset($extra_params['code']) && 200 != $extra_params['code'];
			$isDbErr = isset($extra_params['errno']) && $extra_params['errno'];
			if (!($isApiErr || $isDbErr)) {
				$extraStr = substr($extraStr, 0, 400);
			}

			$log = WB_SOFT_NAME . "\t" . WB_VERSION . "\t" . WB_AKEY . "\t[" . $type . "]\t[$level]\t" . $str . "\t" . $extraStr;
			$log_file = $this -> _getLogFile($type, $level);
			$msg = sprintf("[%s]:\t%s\r\n", date("Y-m-d H:i:s"), $log);

			self::$logList[$log_file][] = $msg;
		}
	}

	/**
	 * Level filter
	 * @param $level, 当前日志等级
	 */
	function _needLog($level) {
		// Sae不写日志
		if (strtolower(XWB_SERVER_ENV_TYPE) != 'common' || (LOG_LEVEL != 4 && $level == 'info' && !V('g:_loginfo', false))) {
			return false;
		}

		$curLevel = isset($this -> logLevel[$level]) ? $this -> logLevel[$level] : 0;

		// Level filter
		return (LOG_LEVEL && (LOG_LEVEL >= $curLevel));
	}

	/**
	 * 写日志文件
	 */
	function run() {
		if (!is_array(self::$logList)) {
			return true;
		}

		// 删除过期api日志
		$this -> _clearApiLog();

		// 写文件
		foreach (self::$logList as $log_file => $aLog) {
			$msg = implode("", $aLog);
			if (isset($_SERVER['REQUEST_URI'])) {
				$msg = "\r\n===================={$_SERVER['REQUEST_URI']} Start====================\r\n" . $msg . "===================={$_SERVER['REQUEST_URI']} End====================\r\n";
			}

			if (!file_exists($log_file)) {
				$msg = "<?php  " . IS_IN_APPLICATION_CODE . " ?> \r\n\r\n" . $msg;
			}

			IO::write($log_file, $msg, true);
		}
	}

	/**
	 * 获取log文件名
	 * @param $type
	 * @param $level
	 */
	function _getLogFile($type, $level) {
		if ('api' == strtolower($type)) {
			if (LOG_LEVEL_INFO == $level) {
				return P_VAR . "/log" . date("/Y_m/Ymd") . '.' . LOG_LEVEL_INFO . ".api.php";
			} else {
				return P_VAR . "/log" . date("/Y_W") . '.' . LOG_LEVEL_ERROR . ".api.php";
			}
		}

		// info信息log文件
		if (LOG_LEVEL_INFO == $level) {
			return P_VAR . "/log" . date("/Y_m/Ymd") . '.' . LOG_LEVEL_INFO . ".php";
		}

		// error、waring放在log文件
		return P_VAR . "/log" . date("/Y_m") . '.' . LOG_LEVEL_ERROR . ".php";
	}

	/**
	 * 定时清除api错误日志
	 * @param $type
	 */
	function _clearApiLog() {
		// 每周一早上10点清除api错误日志
		$needClear = (date('w', APP_LOCAL_TIMESTAMP) == 1) && (date('H', APP_LOCAL_TIMESTAMP) == '10');

		if ($needClear) {
			$fileList = IO::ls(P_VAR . "/log", TRUE);
			if (is_array($fileList)) {
				// 当前api错误日志
				$curFile = str_replace('\\', '/', $this -> _getLogFile('api', 'error'));
				$infoFile = str_replace('\\', '/', $this -> _getLogFile('api', LOG_LEVEL_INFO));
				foreach ($fileList as $aFile) {
					if (!isset($aFile[0]) || !isset($aFile[1]) || 'f' != $aFile[0]) {
						continue;
					}

					// 删除当前api日志外的 api错误日志文件
					$aFile[1] = str_replace('\\', '/', $aFile[1]);
					if (substr($aFile[1], -7) == 'api.php' && $curFile != $aFile[1] && $infoFile != $aFile[1]) {
						unlink($aFile[1]);
					}
				}
			}
		}

	}

}
?>