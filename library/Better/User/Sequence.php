<?php

/**
 * 用户Uid生成策略
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Sequence extends Better_User_Base
{
	protected static $instance = null;

	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}	

	
	/**
	 * 生成一个最终的新uid
	 * 
	 * @return integer
	 */
	public static function genUid()
	{
		$cnt = 0;
		$uid = false;

		while(($uid = self::_genUid()) == false && $cnt<100) {
			Better_DAO_User_Sequence::getInstance()->update();
			$cnt++;
		}

		if ($uid!=false) {
			Better_DAO_User_Sequence::getInstance()->update();
		}

		return $uid;
	}
	
	/**
	 * 尝试生成一个uid交给genUid校验
	 *
	 * @return misc
	 */
	private static function _genUid()
	{
		$uid = false;
		$seq = Better_DAO_User_Sequence::getInstance()->get();
		$uid = $seq^intval(Better_Config::getAppConfig()->user->id_const);
		$len = strlen($uid);
		
		$last_0 = $uid[$len-1];
		$last_1 = $uid[$len-2];
		$last_2 = $uid[$len-3];

		//	后三位连续的需要保留
		if (($last_0==($last_1+1) && $last_1==($last_2+1)) || ($last_0==($last_1)-1 && $last_1==($last_2-1))) {
			$uid = false;
		}
		
		//	前三位三位相同的需要保留
		if ($uid!=false && $len>=6 && substr($uid,0,3)==substr($uid,3,3)) {
			$uid = false;
		}
		
		//	前五位连续的需要保留
		if ($uid!=false && $len>=5) {
			$first = $uid[0];
			$second = $uid[1];
			$third = $uid[2];
			$fouth = $uid[3];
			$fifhty = $uid[4];
			
			$asc = ($fifhty==($fouth+1) && $fouth==($third+1) && $third==($second+1) && $second==($first+1)) ? true : false;
			$desc = ($first==($second+1) && $second==($third+1) && $third==($fouth+1) && $fouth==($fifhty+1)) ? true : false;
			
			if ($asc || $desc) {
				$uid = false;
			}
		}
		
		//	后三位三位相同的需要保留
		if ($uid!=false && $len>6 && substr($uid,-3)==substr($uid, -6, 3)) {
			$uid = false;
		}
		
		//	前两位两位两位相同的需要保留
		if ($uid!=false && $len>=6 && substr($uid,0,2)==substr($uid,2,2) && substr($uid,0,2)==substr($uid,4,2)) {
			$uid = false;
		}
		
		//	后两位两位两位相同的需要保留
		if ($uid!=false && $len>6 && substr($uid,-2)==substr($uid,-4,2) && substr($uid,-2)==substr($uid,-6,2)) {
			$uid = false;
		}

		return $uid;
	}
		
}