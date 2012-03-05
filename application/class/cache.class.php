<?php
/**
 * 数据缓存
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
interface Cache {

	/**
	 * @param string $key
	 * @return bool
	 */
	public function get($key);

	/**
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function set($key, $value);

}

class CacheException extends Exception {

}

/**
 * memcached缓存
 */
class MemcacheCache implements Cache {

	/**
	 * contructor
	 * void
	 */
	public function __construct() {

	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function get($key) {

	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function set($key, $value) {

	}

}

/**
 * xcache缓存
 */
class Xcache implements Cache {
	/**
	 * contructor
	 * void
	 */
	public function __construct() {

	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function get($key) {

	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function set($key, $value) {

	}

}

/**
 * 文件序列化缓存
 */
class SerializeCache implements Cache {
	/**
	 * contructor
	 * void
	 */
	public function __construct() {

	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function get($key) {

	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function set($key, $value) {

	}

}

/**
 * 文件缓存
 */
class FileCache implements Cache {
	/**
	 * contructor
	 * void
	 */
	public function __construct() {

	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function get($key) {

	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function set($key, $value) {

	}

}
?>