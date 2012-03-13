<?php
/**
 * 序号生成器类
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
/**
 * 使用方法
 * SeqGenerator::init( time() );
 * for ( $i=0; $i < 10; $i++ ) {
 * 	   $seq = SeqGenerator::next();
 *     var_dump($seq);
 * }
 * 
 */
/*
 * 信号量(Semaphore)。
 * 这是一个包装类，用于解决不同平台下对“信号量”的不同实现方式。
 * 目前这个类只是象征性的，在 Windows 平台下实际是空跑（并没有真的实现互斥）。
 */
class SemWrapper {
	private $hasSemSupport;
	private $sem;
	const SEM_KEY = 1;

	public function __construct() {
		$this -> hasSemSupport = function_exists('sem_get');
		if ($this -> hasSemSupport) {
			$this -> sem = sem_get(self::SEM_KEY);
		}
	}

	public function acquire() {
		if ($this -> hasSemSupport) {
			return sem_acquire($this -> sem);
		}
		return true;
	}

	public function release() {
		if ($this -> hasSemSupport) {
			return sem_release($this -> sem);
		}
		return true;
	}

}

/*
 * 顺序号发生器。
 */
class SeqGenerator {
	const SHM_KEY = 1;

	/**
	 * 对顺序号发生器进行初始化。
	 * 仅在服务器启动后的第一次调用有效，此后再调用此方法没有实际作用。
	 * @param int $start 产生顺序号的起始值。
	 * @return boolean 返回 true 表示成功。
	 */
	static public function init($start = 1) {
		// 通过信号量实现互斥，避免对共享内存的访问冲突
		$sw = new SemWrapper;
		if (!$sw -> acquire()) {
			return false;
		}

		// 打开共享内存
		$shm_id = shmop_open(self::SHM_KEY, 'n', 0644, 4);
		if (empty($shm_id)) {
			// 因使用了 'n' 模式，如果无法打开共享内存，可以认为该共享内存已经创建，无需再次初始化
			$sw -> release();
			return true;
		}

		// 在共享内存中写入初始值
		$size = shmop_write($shm_id, pack('L', $start), 0);
		if ($size != 4) {
			shmop_close($shm_id);
			$sw -> release();
			return false;
		}

		// 关闭共享内存，释放信号量
		shmop_close($shm_id);
		$sw -> release();
		return true;
	}

	/**
	 * 产生下一个顺序号。
	 * @return int 产生的顺序号
	 */
	static public function next() {
		// 通过信号量实现互斥，避免对共享内存的访问冲突
		$sw = new SemWrapper;
		if (!$sw -> acquire()) {
			return 0;
		}

		// 打开共享内存
		$shm_id = shmop_open(self::SHM_KEY, 'w', 0, 0);
		if (empty($shm_id)) {
			$sw -> release();
			return 0;
		}

		// 从共享内存中读出顺序号
		$data = shmop_read($shm_id, 0, 4);
		if (empty($data)) {
			$sw -> release();
			return 0;
		}

		$arr = unpack('L', $data);
		$seq = $arr[1];

		// 把下一个顺序号写入共享内存
		$size = shmop_write($shm_id, pack('L', $seq + 1), 0);
		if ($size != 4) {
			$sw -> release();
			return 0;
		}

		// 关闭共享内存，释放信号量
		shmop_close($shm_id);
		$sw -> release();
		return $seq;
	}

}
?>