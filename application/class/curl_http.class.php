<?php
/**
 * 基于curl的http Client请求类
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
class curl_http
{
	var $_curlInit;
	var $_serverUrl;
	var $_param = array();
	var $_config = array(
						'url',
						'method',
						'timeout',
						'header'
	);
	//默认设置
	var $_option = array(CURLOPT_RETURNTRANSFER => true,
							CURLOPT_HEADER => false,
							CURLOPT_TIMEOUT => 10,
							CURLOPT_USERAGENT => XWB_HTTP_USER_AGENT);
	//保存返回服务器的内容
	var $_server_content;
	//设置http的处理方法
	var $_method = null;

	var $_codeInfo;
	var $_request_params;
	var $_error;
	var $base_string;
	var $key_string;


	function adp_init($init_config = null)
	{
		if (isset($init_config['url'])) {
			$this->_serverUrl = $init_config['url'];
		}

		$this->_curlInit = curl_init();

		return $this;
	}


	/**
	 * 设置访问的url
	 *
	 * @param string $url
	 * @return object
	 */
	function setUrl($url)
	{
		$this->_serverUrl = $url;
		$this->_option[CURLOPT_URL] = $this->_serverUrl;
		return $this;
	}


	/**
	 * 设置请求的方式 'get'|'post'|'put'|'file'
	 *
	 * @param string $method
	 * @return object
	 */
	function setMethod($method = 'GET')
	{
		$method = empty($method) ? 'get' : $method;
		$method = strtolower($method);
		$this->_method = $method;
		switch ($method) {
			case 'post':
			case 'file':
				$this->_option[CURLOPT_POST] = true;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'POST';
				break;
			case 'get':
				$this->_option[CURLOPT_POST] = false;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'GET';
				break;
			case 'put':
				$this->_option[CURLOPT_POST] = false;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'PUT';
				break;
			case 'delete':
				$this->_option[CURLOPT_POST] = false;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				break;
				default:
					$this->_option[CURLOPT_POST] = false;
					$this->_option[CURLOPT_CUSTOMREQUEST] = 'GET';
		}
		return $this;
	}

	function _set($key, $value)
	{
		$this->$key = $value;
	}

	function _get($key)
	{
		if (empty($key)) {
			return false;
		}
		return $this->$key;
	}

	/**
	 * 使用rawurlencode编码的参数
	 *
	 * @param unknown_type $array
	 * @return unknown
	 */
	function http_build_query_rawurl($array)
	{
		if (!empty($array)) {
			if (is_array($array)) {
				$params = array();
				foreach ($array as $key => $value) {
					$params[] = $key .'='.rawurlencode($value);
				}
				$params_string = implode('&', $params);
			} else {
				$params_string = $array;
			}
			return $params_string;
		}
		return false;
	}


	/**
	 * 设置请求方式是get的参数
	 *
	 * @param array|string $data
	 * @param bool $raw 是否用rawurlencode编码
	 * @return object
	 */
	function _setParameterGet($data, $raw = false)
	{
		if (!empty($data)) {
			if (is_array($data)) {
				if ($raw == true) {
					$params = $this->http_build_query_rawurl($data);
				} else {
					$params = http_build_query($data);
				}
			} else {
				$params = $data;
			}
			if (strpos($this->_serverUrl, '?')) {
				$this->_serverUrl = $this->_serverUrl.'&'.$params;
			} else {
				$this->_serverUrl = $this->_serverUrl.'?'.$params;
			}

			$this->_option[CURLOPT_URL] = $this->_serverUrl;
			$this->_request_params = $params;
		}
		return $this;
	}


	/**
	 * 设置请求方式是post的参数
	 *
	 * @param array|string $data
	 * @param bool $isFile 是否上传文件
	 * @return object
	 */
	function _setParameterPost($data, $isFile = false)
	{
		if (!empty($data)) {
			if ($isFile){
				if (!is_array($data)) {
					$params['fileName'] = '@'.$data;
				} else {
					foreach ($data as $key => $value) {
						if ($key == 'fileName') {
							$params[$key] = '@'.$value;
						}
					}
				}
			} else {
				if (is_array($data)) {
					$temp = array();
					foreach ($data as $key => $value) {
						if (substr($key, -2) == '[]' && is_array($value)) {
							foreach ($value as $part) {
								$temp[] = $key . '=' . urlencode($part);
							}
						} else {
							$temp[] = $key .'='. urlencode($value);
						}
					}
					$params = implode('&', $temp);
				} else {
					$params = $data;
				}
			}

			$this->_option[CURLOPT_POSTFIELDS] = $params;
			$this->_request_params = $params;
		}
		return $this;
	}


	/**
	 * 设置get|post的数据
	 *
	 * @param array|string $data
	 * @return object
	 */
	function setData($data)
	{
		$this->_param = $data;
		return $this;
	}


	/**
	 * 设置发送头内容格式
	 *
	 * @param string $data
	 * @param string $content_type
	 */
	function setRawData($data = null, $content_type)
	{
		$this->_param = $data;
		switch ($content_type) {
			case 'xml':
				$this->_option[CURLOPT_HTTPHEADER] = array("Content-Type: text/xml");
				break;
			case 'bin':
				$this->_option[CURLOPT_HTTPHEADER] = array("Content-Type: application/octet-stream");
				break;
			case 'js':
				$this->_option[CURLOPT_HTTPHEADER] = array("Content-Type: application/x-javascript");
				break;
			case 'jpg':
			case 'jpeg':
				$this->_option[CURLOPT_HTTPHEADER] = array("Content-Type: image/jpeg");
				break;
			case 'gif':
				$this->_option[CURLOPT_HTTPHEADER] = array("Content-Type: image/gif");
				break;
			default:
				$this->_option[CURLOPT_HTTPHEADER] = array($content_type);
		}
		return $this;
	}
	
	function setHeader($k, $v){
		$h = isset($this->_option[CURLOPT_HTTPHEADER]) ? $this->_option[CURLOPT_HTTPHEADER] : array();
		if (is_array($h)){
			$h[] = $k.": ".$v;
		}else{
			$h = array($k.": ".$v);
		}
		$this->_option[CURLOPT_HTTPHEADER] = $h;
	}

	/**
	 * 设置curl的配置参数值
	 *
	 * @param array $config
	 */
	function setConfig($config)
	{

		//自定义设置,覆盖默认设置
		if (is_array($config)) {
			foreach ($config as $key => $opt) {
				/*
				if (!in_array($key, $this->_config)) {
					$this->_option[$key] = $opt;
					continue;
				}
				 */
				switch ($key) {
					case 'url':
						$this->_option[CURLOPT_URL] = $opt;
						break;
					case 'method':
						$this->setMethod($opt);
						break;
					case 'timeout':
						$this->_option[CURLOPT_TIMEOUT] = $opt;
						break;
					case 'header':
						$this->_option[CURLOPT_HEADER] = $opt;
						break;
					case 'http_header':
						$this->_option[CURLOPT_HTTPHEADER] = $opt;
						break;
				}
			}
		}

		return $this;
	}


	/**
	 * 设置curl选项,代替curl_setopt_array
	 *
	 * @param curl handle $ch
	 * @param array $data
	 */
	 function _setCurlOption($ch, $options)
	{
		foreach ($options as $key => $value) {
			curl_setopt($ch, $key, $value);
		}
	}


	/**
	 * 发送请求,获取的内容
	 *
	 * @param string $method
	 * @return array
	 */
	function request($method = null, $https = false)
	{
		list($usec, $sec) = explode(" ", microtime());
		$start_ex_time = (float)$usec + (float)$sec;

		//支持https
		if ($https) {
			$this->_option[CURLOPT_SSL_VERIFYPEER] = false;
		}
		$method = empty($method) ? "get" : $method;
		if (strtolower($method) == 'post' || strtolower($method) == 'put') {
			$this->setMethod($method);
			$this->_setParameterPost($this->_param);
		} elseif (strtolower($method) == 'file') {
			$this->setMethod('file');
			$this->_setParameterPost($this->_param, true);
		} elseif (strtolower($method) == 'reg') {
			$this->setMethod();
			$this->_setParameterGet($this->_param, true);
		} elseif (strtolower($method) == 'delete') {
			$this->setMethod('delete');
			$this->_setParameterGet($this->_param);
		} else {
			$this->setMethod();
			$this->_setParameterGet($this->_param);
		}

//		$this->_curlInit = curl_init();
		if (!function_exists('curl_setopt_array')) {
			$this->_setCurlOption($this->_curlInit, $this->_option);
		} else {
			curl_setopt_array($this->_curlInit, $this->_option);
		}

		//返回结果
		$this->_server_content = curl_exec($this->_curlInit);

		//获取curl请求的信息
		$this->_codeInfo = curl_getinfo($this->_curlInit);

		$this->_error = curl_error($this->_curlInit);
		

//		$this->closeHttp();
		//再重试访问一次
		if ($this->getState() == 0) {
//			$this->_curlInit = curl_init();
			if (!function_exists('curl_setopt_array')) {
				$this->_setCurlOption($this->_curlInit, $this->_option);
			} else {
				curl_setopt_array($this->_curlInit, $this->_option);
			}

			//返回结果
			$this->_server_content = curl_exec($this->_curlInit);

			//获取curl请求的信息
			$this->_codeInfo = curl_getinfo($this->_curlInit);

//			$this->closeHttp();
		}

		//记录调试或错误日志
//		if ((V('g:log')==1) || ($this->getState()!=200) ) {
//			list($usec, $sec) = explode(" ", microtime());
//			$end_ex_time = (float)$usec + (float)$sec;
//
//			$db = APP::ADP('db');
//
//			$db->setTable(T_LOG_HTTP);
//			$db->setAutoFree(true);
//			$data_log = array();
//			$data_log['url'] = $this->_serverUrl;
//			$data_log['base_string'] = $this->base_string;
//			$data_log['key_string'] = $this->key_string;
//			$data_log['http_code'] = $this->_codeInfo['http_code'];
//			//post, 记录post数据
//			if (strtolower($this->_method) == 'post') {
//				$data_log['post_data'] = $this->_request_params;
//			}
//			$data_log['ret'] = $this->_server_content;
//			$data_log['request_time'] = $this->_codeInfo['total_time'];
//			$data_log['total_time'] = $end_ex_time - $start_ex_time;
//			$data_log['s_ip'] = F('get_client_ip');
//			$data_log['log_time'] = date('Y-m-d H:i:s',APP_LOCAL_TIMESTAMP);
//			$db->save($data_log);
//		}

		//将设置的方法置空
		if ($this->_method) {
			$this->_method = null;
		}

		//清除数据
		if ($this->_param) {
			unset($this->_param);
			$this->_param = array();
		}

		//重置curl的配置选项
		unset($this->_option);
		$this->_option = array();
		$this->_option = array(CURLOPT_RETURNTRANSFER => true,
							CURLOPT_HEADER => false,
							CURLOPT_TIMEOUT => 10);

		return $this->_server_content;
	}


	/**
	 * 获取返回的http状态
	 *
	 * @return int
	 */
	function getState()
	{
		return $this->_codeInfo['http_code'];
	}


	/**
	 * 获取调用的url
	 *
	 * @return string
	 */
	function getUrl()
	{
		return $this->_codeInfo['url'];
	}


	/**
	 * 获取请求的参数
	 *
	 * @return string
	 */
	function getRequestParams()
	{
		return $this->_request_params;
	}


	/**
	 * 获取发送curl请求的返回有关curl的信息
	 *
	 * @return array
	 */
	function getHttpInfo()
	{
		return $this->_codeInfo;
	}

	/**
	 * 获取错误代码
	 */
	function getError() {
		return $this->_error;
	}


	/**
	 * 关闭curl
	 *
	 */
	function closeHttp()
	{
		curl_close($this->_curlInit);
	}


	/**
	 * 析函数,关闭curl
	 *
	 */
	function __destruct()
	{
		if ($this->_curlInit) {
			$this->closeHttp();
		}
	}
}
?>
