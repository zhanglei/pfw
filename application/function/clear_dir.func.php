<?php
/**
 * 删除文件夹,包括子文件夹,如果有文件或文件夹删除不成功则返回false
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09 
 */

/**
 * 删除文件夹,包括子文件夹,如果有文件或文件夹删除不成功则返回false
 * @return bool
 */
function clear_dir($dir) {
	if (! is_dir ( $dir )) {
		return false;
	}
	$objects = scandir ( $dir );
	foreach ( $objects as $object ) {
		if ($object != "." && $object != "..") {
			$name = $dir . "/" . $object;
			if (filetype ( $name ) == "dir") {
				if (! clear_dir ( $name )) {
					return false;
				}
				$rs = @rmdir ( $name );
			} else {
				$rs = @unlink ( $name );
			}
			if (! $rs) {
				return false;
			}
		}
	}
	reset ( $objects );
	return true;
}
?>