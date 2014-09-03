<?php

/**
 * 举报 处理类
 * @author yanglei
 *
 */
class Better_DAO_Denounce extends Better_DAO_Base{
	
	private static $instance= null;
	
	function __construct(){
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'denounce';
		$this->priKey = 'id';
	}
	
	public static function getInstance(){
		if(self::$instance==null){
			self::$instance=new Better_DAO_Denounce();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
	public function denounced($uid, $denouceUid, $reason='others')
	{
		$result = false;
		$uid = (int)$uid;
		$denounceUid = (int)$denounceUid;
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $uid);
		$select->where('denounce_uid', $denounceUid);
		$select->where('dateline>?', time()-3600*24);
		$select->where('denounce_reason=?', $reason);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		$result = $row['total'] ? true : false;
		
		return $result;
	}
	
}

?>