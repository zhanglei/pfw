<?php
/**
 * 首页控制器入口
 * @author qingmu
 * @version
 * Created at:  2011-12-09
 */
class index_mod extends BaseMod{
	
	/**
	 * 构造函数
	 */
	public function __construct() {

	}
	
	/**
	 * 首页
	 */
	public function index() {
		Tpl::display('index');
	}
	
} 
?>