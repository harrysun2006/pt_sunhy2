<?php

/**
 * 邀请序列表
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_InviteSequence extends Better_DAO_Base
{
	
	private static $instance = null;

	public function __construct($identifier=null)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'invite_sequence';
		$this->priKey = 'seq';
		$this->orderKey = &$this->priKey;
		
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_AttachSequence();
			$db = parent::registerDbConnection('assign_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
	/**
	 * 读取附件swq
	 * @see library/Better/DAO/Better_DAO_Base#get($val)
	 */
	public function get()
	{
		$seq = 0;
		
		try {
			$r = parent::get(null);
			$seq = $r['seq'];
			
			$this->_increaseSeq();
		} catch(Exception $e) {
			Better_Log::getInstance()->logInfo("Attach seq failed : [".$e->getMessage()."]", 'invite_seq');
		}
		
		return $seq;
	}
	
	private function _increaseSeq()
	{
		$sql = "UPDATE {$this->tbl} SET seq=seq+".rand(100,999);
		return $this->query($sql);
	}
	
}