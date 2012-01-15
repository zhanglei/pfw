<?php
/**
 * 框架兼容函数
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */

//JSON LIB
if (!function_exists('json_decode')) {
	function json_decode($s, $ass = false) {
		$assoc = ($ass) ? 16 : 32;
		$gloJSON = APP::N('servicesJSON', $assoc);

		return $gloJSON -> decode($s);
	}

}

if (!function_exists('json_encode')) {
	function json_encode($s) {
		$gloJSON = APP::O('servicesJSON');

		return $gloJSON -> encode($s);
	}

}

if (!function_exists('hash_hmac')) {
	function hash_hmac($algo, $data, $key, $raw = false) {
		if (empty($algo)) {
			return false;
		}
		switch ($algo) {
			case 'md5' :
				return mhash(MHASH_MD5, $data, $key);
				break;
			case 'sha1' :
				return mhash(MHASH_SHA1, $data, $key);
				break;
		}
	}

}

if (!function_exists('array_combine')) {
	function array_combine($keys, $values) {
		if (!is_array($keys) || !is_array($values) || empty($keys) || empty($values) || count($keys) != count($values)) {
			trigger_error("array_combine() expects parameters 1 and 2 to be non-empty arrays with an equal number of elements", E_USER_WARNING);
			return false;
		}
		$keys = array_values($keys);
		$values = array_values($values);
		$result = array();
		foreach ($keys as $index => $key) {
			$result[$key] = $values[$index];
		}
		return $result;
	}

}

if (!function_exists('file_get_contents')) {
	function file_get_contents($filename) {
		$isUrl = count(explode("://", $filename)) > 1;
		if ($isUrl) {
			$http = APP::ADP('http');
			$http -> setUrl($filename);
			return $http -> request();
		} else {
			return IO::read($filename);
		}
	}

}

if (!function_exists('http_build_query')) {
	function http_build_query($data, $prefix = '', $sep = '', $key = '') {
		$ret = array();
		foreach ((array)$data as $k => $v) {
			if (is_int($k) && $prefix != null) {
				$k = urlencode($prefix . $k);
			}
			if ((!empty($key)) || ($key === 0))
				$k = $key . '[' . urlencode($k) . ']';
			if (is_array($v) || is_object($v)) {
				array_push($ret, http_build_query($v, '', $sep, $k));
			} else {
				array_push($ret, $k . '=' . urlencode($v));
			}
		}
		//if (empty($sep)) $sep = ini_get('arg_separator.output');
		$sep = '&';
		return implode($sep, $ret);
	}

}

if (!function_exists('mb_strwidth')) {
	function mb_strwidth($str, $encoding) {
		$str = F('xwb_iconv', $str, $encoding, 'gbk');
		return strlen($str);
	}

}

if (!function_exists('mb_substr')) {
	function mb_substr($str, $start = 0, $length = 0, $encode = 'utf-8') {
		$encode_len = strtolower($encode) == 'utf-8' ? 3 : 2;

		for ($byteStart = $i = 0; $i < $start; ++$i) {
			$byteStart += ord($str{$byteStart}) < 128 ? 1 : $encode_len;
			if ($str{$byteStart} == '')
				return '';
		}

		for ($i = 0, $byteLen = $byteStart; $i < $length; ++$i)
			$byteLen += ord($str{$byteLen}) < 128 ? 1 : $encode_len;

		return substr($str, $byteStart, $byteLen - $byteStart);
	}

}

if (!function_exists('mb_strlen')) {
	function mb_strlen($text, $encode = 'utf-8') {
		if (strtolower($encode) == 'utf-8') {
			return preg_match_all('%(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})%xs', $text, $out);
		} else {
			return strlen($text);
		}
	}

}

///二位数组根据key进行排序
function array_sort($array, $on, $order = SORT_ASC) {
	$new_array = array();
	$sortable_array = array();

	if (count($array) > 0) {
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $k2 => $v2) {
					if ($k2 == $on) {
						$sortable_array[$k] = $v2;
					}
				}
			} else {
				$sortable_array[$k] = $v;
			}
		}

		switch ($order) {
			case SORT_ASC :
				asort($sortable_array);
				break;
			case SORT_DESC :
				arsort($sortable_array);
				break;
		}

		foreach ($sortable_array as $k => $v) {
			$new_array[$k] = $array[$k];
		}
	}

	return $new_array;
}
?>