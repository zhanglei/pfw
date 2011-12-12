<?php
/**
 * 分页处理类
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
class Pagination {

	private $result;

	private $pageSize;

	private $page;

	private $numRows;

	private $row;

	private $showPageNumbers;

	private $resultPage;

	/**
	 * 构造函数
	 */
	public function __construct() {

	}

	public function getNumPages() {
		if (is_resource($this -> result)) {
			return ceil(($this -> numRows) / (float)($this -> pageSize));
		} else {
			return false;
		}
	}

	public function getPageNum() {
		return $this -> page;
	}

	public function getPageNav() {
		$nav = "";
		if (!($this -> page <= 1)) {
			$nextpage = $this -> getPageNum();
			$nextpage--;
			$nav .= "<a href=\"?resultPage=$nextpage\"><font color=\"blue\" size=\"2pt\"><b>Prev</b></font></a>\n";
		} else {
			$nav .= "<font size=\"2pt\"><b>Prev</b></font>\n";
		}

		if ($this -> getNumPages() >= 1) {
			for ($i = 1; $i <= $this -> getNumPages(); $i++) {
				if ($i == $this -> getPageNum()) {
					$nav .= "<font size=\"2pt\"><b>&nbsp;$i<b></font>&nbsp;\n";
				} else {
					$nav .= "<a href=\"?resultPage=$i\"><font color=\"blue\" size=\"2pt\"><b>&nbsp;$i</b></font></a>&nbsp;\n";
				}
			}
		}
		if (!($this -> page >= $this -> getNumPages())) {
			$nextpage = $this -> getPageNum();
			$nextpage++;
			$nav .= "<a href=\"?resultPage=$nextpage\"><font color=\"blue\" size=\"2pt\"><b>Next</b></font></a>\n";
		} else {
			$nav .= "<font size=\"2pt\"><b>Next</b></font>\n";
		}
		return $nav;
	}

}
?>