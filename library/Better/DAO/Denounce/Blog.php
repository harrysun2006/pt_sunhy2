<?php

/**
 * 举报吼吼 处理类
 * 
 * @package Better.DAO.Denounce
 * @author yanglei
 *
 */
class Better_DAO_Denounce_Blog extends Better_DAO_Base
{
	
	private static $instance= null;
	
	function __construct(){
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'denounce_blog';
		$this->priKey = 'id';
	}
	
	public static function getInstance()
	{
		if(self::$instance==null){
			self::$instance=new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
	public function denounced($uid, $bid, $reason='others')
	{
		$result = false;
		$uid = (int)$uid;
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $uid);
		$select->where('denounce_bid=?', $bid);
		$select->where('dateline>?', time()-3600*24);
		$select->where('denounce_reason=?', $reason);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		$result = $row['total'] ? true : false;
		
		return $result;
	}	
	
}

?>