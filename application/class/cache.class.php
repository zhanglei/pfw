<?php
/**
 * 数据缓存
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
abstract class Cache {

	/**
	 * 构造函数
	 */
	public function __construct() {

	}
	
	/**
	 * @param string $key 
	 * @return bool
	 */
	public abstract function get($key);

	/**
	 * @param string $key
	 * @param string $value
	 */
	public abstract function set($key, $value);

}

/**
 * memcached缓存
 */
class MemcacheCache extends Cache {
	/**
	 * 构造函数
	 */
	public function __construct() {

	}

}

/**
 * xcache缓存
 */
class Xcache extends Cache {
	/**
	 * 构造函数
	 */
	public function __construct() {

	}

}

/**
 * 文件序列化缓存
 */
class SerializeCache extends Cache {

}

/**
 * 文件缓存
 */
class FileCache extends Cache {

}
?>