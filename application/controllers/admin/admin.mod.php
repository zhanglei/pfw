<?php
/**
 * 后台控制器入口
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */
class AdminMod extends CoreMod {

	/**
	 * 构造函数
	 */
	public function __construct() {

	}

	/**
	 * 首页
	 */
	public function index() {
		Tpl::display('admin/index');
	}

	/**
	 * 用户登录
	 */
	public function login() {
		Tpl::display('admin/login');
	}

	public function logout() {

	}

}
?>