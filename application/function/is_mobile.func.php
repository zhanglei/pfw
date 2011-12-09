<?php
/**
 * 判断是否为手持设别的函数
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09 
 */

/**
 * 判断是否为手持设别的函数
 * @return bool
 */
function is_mobile() {
	
	$devices = array ("operaMobi" => "Opera Mobi", "android" => "android", "blackberry" => "blackberry", "iphone" => "(iphone|ipod)", "opera" => "opera mini", "palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)", "windows" => "windows ce; (iemobile|ppc|smartphone)", "generic" => "(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)" );
	
	if (isset ( $_SERVER ['HTTP_X_WAP_PROFILE'] ) || isset ( $_SERVER ['HTTP_PROFILE'] )) {
		return TRUE;
	} elseif (isset ( $_SERVER ['HTTP_ACCEPT'] ) && (strpos ( $_SERVER ['HTTP_ACCEPT'], 'text/vnd.wap.wml' ) > 0 || strpos ( $_SERVER ['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml' ) > 0)) {
		return TRUE;
	} else {
		
		if (isset ( $_SERVER ['HTTP_USER_AGENT'] )) {
			foreach ( $devices as $device => $regexp ) {
				
				if (preg_match ( "/" . $regexp . "/i", $_SERVER ['HTTP_USER_AGENT'] )) {
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}
?>