<?php

/** 
 * 文件上传类
 * 
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09 
 */
class Upload {
	
	private $config = array ('upload_path' => '', 'allowed_types' => 'jpg|gif|jpeg|png', 'field' => 'upload_file', 'max_size' => 1048576 ); //默认配置
	

	private $path;
	
	private $file_name;
	
	private $jpeg_quality = 90;
	
	/**
	 * 构造函数
	 * @param array $config
	 */
	public function __construct($config = null) {
		if ($config) {
			foreach ( $config as $key => $var ) {
				$this->config [$key] = $var;
			}
		}
	}
	
	/**
	 * 上传文件
	 * @param string $field 上传文件的表单域名
	 * @return string
	 */
	public function upload($field = null) {
		if ($field) {
			$this->config ['field'] = $field;
		}
		
		$fieldName = $this->config ['field'];
		
		//post的数据大于php.ini的post_max_size设置
		$post_max_size = ini_get ( 'post_max_size' );
		$unit = substr ( $post_max_size, - 1 );
		$unit = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
		if (V ( 's:CONTENT_LENGTH' ) > $unit * ( int ) $post_max_size) {
			return $this->setError ( '2' );
		}
		
		if (empty ( $_FILES [$fieldName] ['name'] )) {
			return $this->setError ( '1' );
		}
		
		if (is_uploaded_file ( $_FILES [$fieldName] ['tmp_name'] )) {
			//判断文件大小
			if ($_FILES [$fieldName] ['size'] > $this->config ['max_size']) {
				return $this->setError ( '2' );
			}
			
			//获取文件的后缀名
			$suffix = $this->getSuffix ( $_FILES [$fieldName] ['name'] );
			//判断上传的文件类型是否被允许
			$allowed_types = explode ( '|', $this->config ['allowed_types'] );
			if (! in_array ( $suffix, $allowed_types )) {
				return $this->setError ( '3' );
			}
			
			$file_name = $this->getStorageFileName ( $_FILES [$fieldName] ['name'], $suffix );
			if (move_uploaded_file ( $_FILES [$fieldName] ['tmp_name'], $file_name )) {
				$result = GetImageSize ( $this->path . $this->file_name );
				if (! $result || ! in_array ( $result [2], array (1, 2, 3 ) )) {
					return $this->setError ( '3' );
				}
				return $this->file_name;
			}
			
			return $this->setError ( '4' );
		}
		
		return $this->setError ( '5' );
	}
	
	/**
	 * 生成缩略图
	 *
	 * @param string $srcfile
	 * @param string $tofile
	 * @param int $toWidth
	 * @param int $toHeight
	 * @param bool $streth
	 * @return void
	 */
	public function makeThumb($srcfile, $tofile, $toWidth = 100, $toHeight = 100, $stretch = false) {
		//获取图片信息
		$imgInfo = GetImageSize ( $srcfile );
		switch ($imgInfo [2]) {
			case 1 :
				$im = @ImageCreateFromGIF ( $srcfile );
				break;
			case 2 :
				$im = @ImageCreateFromJpeg ( $srcfile );
				break;
			case 3 :
				$im = @ImageCreateFromPNG ( $srcfile );
				break;
		}
		
		$srcWidth = $imgInfo [0];
		$srcHeight = $imgInfo [1];
		
		//原图及新图比例
		$newRatio = $toWidth / $toHeight;
		$oldRatio = $srcWidth / $srcHeight;
		
		if (! $stretch) { //不拉伸,保持原图比例
			if ($srcHeight <= $toHeight && $srcWidth <= $toWidth) { //原图宽高都比目标图小, 直接用原图
				

				return @copy ( $srcfile, $tofile );
			
			} else {
				if ($toWidth / $srcWidth <= $toHeight / $srcHeight) {
					$ratio = $toWidth / $srcWidth;
				} else {
					$ratio = $toHeight / $srcHeight;
				}
				
				$toWidth = $srcWidth * $ratio;
				$toHeight = $srcHeight * $ratio;
			}
		
		} else {
			//图片处理
			if ($oldRatio >= $newRatio) { //高度优先
				$toWidth = $toWidth;
				$toHeight = $toWidth / $oldRatio;
			} else { //宽度优先
				$toWidth = $toHeight * $oldRatio;
				$toHeight = $toHeight;
			}
		}
		
		if (empty ( $toWidth ) || empty ( $toHeight )) {
			$toWidth = $toHeight = 100;
		}
		
		if (function_exists ( 'imagecreatetruecolor' )) {
			$ni = @ImageCreateTrueColor ( $toWidth, $toHeight );
			@ImageCopyResampled ( $ni, $im, 0, 0, 0, 0, $toWidth, $toHeight, $srcWidth, $srcHeight );
		} else {
			$ni = @ImageCreate ( $toWidth, $toHeight );
			@ImageCopyResized ( $ni, $im, 0, 0, 0, 0, $toWidth, $toHeight, $srcWidth, $srcHeight );
		}
		//生成jpg的缩略图
		@ImageJpeg ( $ni, $tofile, $this->jpeg_quality );
		@ImageDestroy ( $ni );
		@ImageDestroy ( $im );
	}
	
	/**
	 * 获取文件的后缀名
	 *
	 * @param string $name 文件名
	 * @return string
	 */
	public function getSuffix($name) {
		$file_array = explode ( '.', $name );
		$suffix = end ( $file_array );
		return strtolower ( $suffix );
	}
	
	/**
	 * 获取存储文件的绝对路径
	 *
	 * @param string $name 文件名
	 * @return string
	 */
	function getStorageFileName($name, $suffix) {
		$upload_path = $this->config ['upload_path'];
		if ('/' != $upload_path {0}) {
			$path = P_VAR_UPLOAD . '/' . $upload_path;
		} else {
			$path = P_VAR_UPLOAD . $upload_path;
		}
		
		if ('/' != substr ( $upload_path, - 1, 1 )) {
			$path .= '/';
		}
		IO::mkdir ( $path );
		
		$this->path = $path;
		
		//实例化存储
		$storage = APP::N ( 'clientUser' );
		$id = $storage->getInfo ( 'sina_uid' );
		$rid = empty ( $id ) ? rand () : $id;
		$tosrc = date ( 'YmdHis', APP_LOCAL_TIMESTAMP ) . '_' . $rid . '.' . $suffix;
		$this->file_name = $tosrc;
		
		$file_name = $path . $tosrc;
		
		return $file_name;
	}
	
	/**
	 * 获取保存文件的路径
	 *
	 * @return string
	 */
	function getPath() {
		return $this->path;
	}
	
	/**
	 * 获取保存文件的新文件名
	 *
	 * @return string
	 */
	function getFileName() {
		return $this->file_name;
	}
	
	/**
	 * 错误代码提示
	 *
	 * @param string $code
	 * @return string
	 */
	function setError($code) {
		switch ($code) {
			case '1' :
				$result = "-1\r\nempty";
				break;
			case '2' :
				$result = "-1\r\nmax_size";
				break;
			case '3' :
				$result = "-1\r\nallowed_types";
				break;
			case '4' :
				$result = "-1\r\nupload";
				break;
			case '5' :
				$result = "-1\r\nis_uploaded_file";
				break;
			default :
				$result = "-1";
		}
		return $result;
	}
	
	/**
	 * 删除文件
	 *
	 * @param string $src
	 * @return bool
	 */
	function delFile($src) {
		if (file_exists ( $src )) {
			@unlink ( $src );
		}
	}
}

?>