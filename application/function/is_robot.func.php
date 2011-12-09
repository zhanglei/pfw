<?php
/**
 * 检查是否是引擎爬虫和机器人访问网站
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09 
 */

/**
 * 检查是否是引擎爬虫和机器人访问网站
 * @return bool
 */
function is_robot() {
	static $is_robot = null;
	
	if (null == $is_robot) {
		$is_robot = false;
		$robotlist = 'bot|spider|crawl|nutch|lycos|robozilla|slurp|search|seek|archive';
		if (isset ( $_SERVER ['HTTP_USER_AGENT'] ) && preg_match ( "/{$robotlist}/i", $_SERVER ['HTTP_USER_AGENT'] )) {
			$is_robot = true;
		}
	}
	
	return $is_robot;

}
?>