<?php

/**
 * IMEI日志记录
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Imei_Logs extends Better_DAO_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct()
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'imei_logs';
    	$this->priKey = 'id';
    	$this->orderKey = 'dateline';
    	
		parent::__construct (0);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function getUidRegParams($uid)
	{
		$row = $this->get(array(
			'uid' => $uid,
			'action' => '0',
			));
		return (isset($row['imei']) && $row['imei']) ? $row : array();
	}
	
	public function getPartnerRegCount($partner)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('action=?', '0');
		$select->where('partner=?', $partner);
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
}
