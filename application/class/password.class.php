<?php
//error_reporting(E_ALL);
/**
 * Password Generator
 *
 * Simple password generator to help you generate
 * secure passwords.
 *
 * Changelog v1.0:
 * - first version ;)
 *
 * Bugs:
 * - If you set passwords longer than is range
 * of characters while having enabled unique_chars,
 * than it will do nothing good.
 *
 * Issues:
 * - way to get unique characters in password
 * can be improved. It's not the best solution
 * I think and sometimes cause slower load of
 * script.
 *
 * Usage:
 * - See example.php
 *
 * Notes:
 * - I'm now about to find a nice way to ensure
 * that password contains at least one character
 * from each option enabled.
 * - Also need to find a way how to keep some
 * security of code in unique_chars vs limited
 * count of chars range.
 * - Using special chars is disabled for default
 * because some systems, apps and websites don't
 * allow using them and also ppl in different
 * countries may not know how to make some of
 * them...
 *
 * Copyright 2007-2009, Daniel Tlach
 *
 * Licensed under GNU GPL

 * changed by Michael Schramm
 *
 * @copyright	Copyright 2007-2009, Daniel Tlach
 * @link		http://www.danaketh.com
 * @version		1.0
 * @license		http://www.gnu.org/licenses/gpl.txt
 */
class password{
	private $length			= 8;
	private $unique_chars	= true;
	private $use			= array(
				'lower_case'		=> true,
				'upper_case'		=> true,
				'digits'			=> true,
				'special'			=> true
			);
	private $work_range		= array();
	private $range			= array();
	
	public function __construct($length=8, $unique_chars=true){
		$this->length		= $length;
		$this->unique_chars	= $unique_chars;
		$this->init();
	}
	
	private function init(){
		//lower-case [a-z] chars
		$this->range['lc'] = range('a', 'z');
		//upper-case [A-Z] chars
		$this->range['uc'] = range('A', 'Z');
		//digits [0-9]
		$this->range['d'] = range('0', '9');
		//special chars
		//use more if you wish - I used only these because
		//a lost of ppl don't know how to make ^ or ~ and
		//also quotes can be tricky
		$this->range['s'] = array('*', '_', '-', '?', '!', '+', '#', '@', ';', ':');
		
		$this->prepareWorkRange();
	}
	
	private function prepareWorkRange(){
		$this->work_range = array(); // this will be range of chars we'll be working with
		// lower-case [a-z] chars
		if ($this->use['lower_case'] === true) {
			$this->work_range = array_merge($this->work_range,$this->range['lc']);
		}
		
		// upper-case [A-Z] chars
		if ($this->use['upper_case'] === true) {
			$this->work_range = array_merge($this->work_range,$this->range['uc']);
		}
		
		// digits [0-9]
		if ($this->use['digits'] === true) {
			$this->work_range = array_merge($this->work_range,$this->range['d']);
		}
		
		// special chars
		if ($this->use['special'] === true) {
			$this->work_range = array_merge($this->work_range,$this->range['s']);
		}
		
		// quit if we don't have any chars to generate password from
		if (empty($this->work_range)) {
        	throw new Exception('no working chars selected for passwd!');
		}
	}
	
	public function setConfig($lower_case=true,$upper_case=true,$digits=true,$special=true){
		if($lower_case)
			$this->use['lower_case']	= $lower_case;
		if($upper_case)
			$this->use['upper_case']	= $upper_case;
		if($digits)
			$this->use['digits']		= $digits;
		if($special)
			$this->use['special']		= $special;
		
		$this->prepareWorkRange();
	}
	
	
	public function generatePassword(){
		
		//echo '<pre>'; print_r($work_range); echo '</pre>';
		$range = count($this->work_range)-1; // count character arrays
		
		// Generate "$this->date['count']" passwords
		$c = 0; // password chars counter
		$pass = NULL; // empty password variable
		
		// Generate password
		while($c < $this->length){
			$pass .= $this->getChar( $this->work_range, $range, $pass);
			$c++;
		}
		
		return $pass;
	}
	
	public function generateMultiblePasswords($count=5){
		$passwords = array();
		
		for($i=0; $i<$count; $i++){
			$passwords[] = $this->generatePassword();
		}
		
		return $passwords;
	}
	
	/**
	 * Characted generator
	 *
	 * @author        Daniel Tlach <mail@danaketh.com>
	 * @access         private
	 * @return         string
	 */
	private function getChar( $charr, $range, $pass ){
		$index = mt_rand(0, $range);
		$char = $charr[$index];
		$check_char = $char;
		
		if(in_array($char, $this->range['s'])){
			$check_char = '\\'.$check_char;
		}

		if($this->unique_chars === true && strpos($pass, $check_char) !== FALSE && $this->length < $range){
			//unique fail
			return $this->getChar($charr, $range, $pass);
		}else{
			return $char;
		}
	}
}
/**
$pwd = new password(10,true);
var_dump($pwd->generatePassword());
*/
?>