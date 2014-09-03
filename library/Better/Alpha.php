<?php

/**
 * 根据数字生成短地址
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Alpha
{
	/**
	 * 加密的密钥
	 * @var string
	 */
	private $_key = 'better_is_best';
	
	private static $_instances = array();
	
	/**
	 * 段地址的长度
	 * @var integer
	 */
	private $_hashLen = 6;
	
	private function __construct($key)
	{
		$this->_key = $key;
	}
	
	public static function getInstance($key='')
	{
		
		$key=='' && $key = 'better_is_best';
		
		if (!isset(self::$_instances[$key])) {
			self::$_instances[$key] = new self($key);
		}
		
		return self::$_instances[$key];
	}
	
	/**
	 * 设置生成的Hash长度
	 * 
	 * @param $len
	 * @return unknown_type
	 */
	public function setLen($len=6)
	{
		$this->_hashLen = $len;
	}
	
	/**
	 * 正向转换：数字到短地址
	 * 
	 * @param $str
	 * @return string
	 */
	public function C($str)
	{
		return self::_alpha($str, false, $this->_hashLen);
	}
	
	/**
	 * 逆向转换：短地址到数字
	 * 
	 * @param $str
	 * @return unknown_type
	 */
	public function R($str)
	{
		return self::_alpha($str, true, $this->_hashLen);
	}
	
	/**
	 * 加密算法
	 * 
	 * @param $in
	 * @param $to_num
	 * @param $pad_up
	 * @return unknown_type
	 */
	private static function _alpha($in, $to_num=false, $pad_up=false, $passKey=null)
	{
		$pad_up === false && $pad_up = self::$_hashLen;
	    $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	    if ($passKey !== null) {
	        for ($n = 0; $n<strlen($index); $n++) {
	            $i[] = substr( $index,$n ,1);
	        }
	 
	        $passhash = hash('sha256',$passKey);
	        $passhash = (strlen($passhash) < strlen($index))
	            ? hash('sha512',$passKey)
	            : $passhash;
	 
	        for ($n=0; $n < strlen($index); $n++) {
	            $p[] =  substr($passhash, $n ,1);
	        }
	        
	        array_multisort($p,  SORT_DESC, $i);
	        $index = implode($i);
  	  }
	 
	    $base  = strlen($index);
	 
	    if ($to_num) {
	        // Digital number  <<--  alphabet letter code
	        $in  = strrev($in);
	        $out = 0;
	        $len = strlen($in) - 1;
	        for ($t = 0; $t <= $len; $t++) {
	            $bcpow = bcpow($base, $len - $t);
	            $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
	        }
	 
	        if (is_numeric($pad_up)) {
	            $pad_up--;
	            if ($pad_up > 0) {
	                $out -= pow($base, $pad_up);
	            }
	        }
	    } else { 
	        // Digital number  -->>  alphabet letter code
	        if (is_numeric($pad_up)) {
	            $pad_up--;
	            if ($pad_up > 0) {
	                $in += pow($base, $pad_up);
	            }
	        }
	 
	        $out = "";
	        for ($t = floor(log10($in) / log10($base)); $t >= 0; $t--) {
	            $a   = floor($in / bcpow($base, $t));
	            $out = $out . substr($index, $a, 1);
	            $in  = $in - ($a * bcpow($base, $t));
	        }
	        $out = strrev($out); // reverse
	    }
	 
	    return $out;		
	}
	
}