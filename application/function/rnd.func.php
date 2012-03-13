<?php
/**
 * 生成唯一值
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */

/**
 * 根据客户端地址和ip生成唯 一值
 *
 * @return string
 */
function rnd() {

	$s = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . time() . rand();

	return sha1($s);

}
?>