<?php
/**
 * 文件IO操作
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
class file_io {
	var $err = "";
	var $logType = 'io';

	function file_io() {

	}

	function adp_init($config = array()) {

	}

	function write($file, $data, $append = false) {
		$log_func_start_time = microtime(TRUE);

		if (!file_exists($file)) {
			if (!$this -> mkdir(dirname($file))) {
				LOGSTR($this -> logType, "[write]Can not create the folder, File=$file", LOG_LEVEL_ERROR);
				return false;
			}
		}
		$len = false;
		$mode = $append ? 'ab' : 'wb';
		$fp = @fopen($file, $mode);
		if (!$fp) {
			LOGSTR($this -> logType, '[write]fopen file error,file:' . $file, LOG_LEVEL_ERROR);
			exit("Can not open file $file !");
		}
		flock($fp, LOCK_EX);
		$len = @fwrite($fp, $data);
		flock($fp, LOCK_UN);
		@fclose($fp);

		LogMgr::warningLog($log_func_start_time, $this -> logType, "[write]file=$file&result=$len", LOG_LEVEL_WARNING);
		LOGSTR($this -> logType, "[write]file=$file&result=$len", LOG_LEVEL_INFO, array(), $log_func_start_time);
		return $len;
	}

	function read($file) {
		$log_func_start_time = microtime(TRUE);

		if (!file_exists($file)) {
			LOGSTR($this -> logType, "[read]File not exists, File=$file", LOG_LEVEL_ERROR);
			return false;
		}
		if (!is_readable($file)) {
			LOGSTR($this -> logType, '[read]file can not be read,file:' . $file, LOG_LEVEL_ERROR);
			return false;
		}

		$result = '';
		if (function_exists('file_get_contents')) {
			$result = file_get_contents($file);
		} else {
			$result = (($contents = file($file))) ? implode('', $contents) : false;
		}

		LogMgr::warningLog($log_func_start_time, $this -> logType, "[read]file=$file", LOG_LEVEL_WARNING);
		LOGSTR($this -> logType, "[read]Input:file=$file", LOG_LEVEL_INFO, array(), $log_func_start_time);
		return $result;
	}

	/// get files and dirs not use recursion
	function ls($dir, $r = false, $info = false) {
		LOGSTR($this -> logType, "[ls]Input: dir=$dir&recursion=$r&info=$info", LOG_LEVEL_INFO);
		$log_func_start_time = microtime(TRUE);

		if (empty($dir))
			$dir = '.';
		if (!file_exists($dir) || !is_dir($dir)) {
			return false;
		}
		$fs = array();
		$ds = array($dir);
		while (count($ds) > 0) {
			foreach ($ds as $i => $d) {
				unset($ds[$i]);
				$handle = opendir($d);
				while (false !== ($item = readdir($handle))) {
					if ($item == '.' || $item == '..')
						continue;
					$fp = ($d == '.' || $d == '.\\' || $d == './') ? $item : $d . DIRECTORY_SEPARATOR . $item;
					$t = is_file($fp) ? 'f' : (is_dir($fp) ? 'd' : 'o');
					if (is_dir($fp) && $r) { $ds[] = $fp;
					}
					$fs[] = ($info ? array($t, $fp, $this -> info($fp)) : array($t, $fp));
				}
			}
		}
		LogMgr::warningLog($log_func_start_time, $this -> logType, "[ls]dir=$dir&recursion=$r&info=$info", LOG_LEVEL_WARNING);
		LOGSTR($this -> logType, "[ls]Output", LOG_LEVEL_INFO, $fs, $log_func_start_time);
		return $fs;
	}

	function mkdir($path) {
		LOGSTR($this -> logType, "[mkdir]Input: path=$path", LOG_LEVEL_INFO);
		$log_func_start_time = microtime(TRUE);

		$rst = true;
		if (!file_exists($path)) {
			$this -> mkdir(dirname($path));
			$rst = @mkdir($path, 0777);
		}

		LogMgr::warningLog($log_func_start_time, $this -> logType, "[mkdir]Input: path=$path", LOG_LEVEL_WARNING);
		LOGSTR($this -> logType, "[mkdir]Output", LOG_LEVEL_INFO, array('rst' => $rst), $log_func_start_time);
		return $rst;
	}

	function rm($path) {
		LOGSTR($this -> logType, "[rm]Input: path=$path", LOG_LEVEL_INFO);
		$log_func_start_time = microtime(TRUE);

		$path = rtrim($path, '/\\ ');
		if (!is_dir($path)) {
			return @unlink($path);
		}
		if (!$handle = opendir($path)) {
			LOGSTR($this -> logType, '[rm]opendir error,dir:' . $path, LOG_LEVEL_ERROR);
			return false;
		}

		while (false !== ($file = readdir($handle))) {
			if ($file == "." || $file == "..")
				continue;
			$file = $path . DIRECTORY_SEPARATOR . $file;
			if (is_dir($file)) {
				$this -> rm($file);
			} else {
				if (!@unlink($file)) {
					LOGSTR($this -> logType, '[rm]delete file error when delete dir,file:' . $file, LOG_LEVEL_ERROR);
					return false;
				}
			}
		}

		closedir($handle);
		if (!rmdir($path)) {
			LOGSTR($this -> logType, '[rm]delete dir error,dir:' . $path, LOG_LEVEL_ERROR);
			return false;
		}

		LogMgr::warningLog($log_func_start_time, $this -> logType, "[rm]Input: path=$path", LOG_LEVEL_WARNING);
		LOGSTR($this -> logType, "[rm]Rm success", LOG_LEVEL_INFO, array(), $log_func_start_time);
		return true;
	}

	function info($path = ".", $key = false) {
		$path = realpath($path);
		if (!$path)
			false;
		$result = array("name" => substr($path, strrpos($path, DIRECTORY_SEPARATOR) + 1), "location" => $path, "type" => is_file($path) ? 1 : (is_dir($path) ? 0 : -1), "size" => filesize($path), "access" => fileatime($path), "modify" => filemtime($path), "change" => filectime($path), "read" => is_readable($path), "write" => is_writable($path));
		clearstatcache();

		LOGSTR($this -> logType, "[info]Input:path=$path&key=$key", LOG_LEVEL_INFO, array('Output' => $key ? $result[$key] : $result));
		return $key ? $result[$key] : $result;
	}

}
?>
