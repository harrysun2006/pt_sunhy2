<?php

/**
 * 客户端软件下载产品
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_DownloadProduct extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'download_product';
    	$this->priKey = 'pid';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
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
	
	public function getProductByPhoneId($pid, $limit=null)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS p', array(
			'p.desc', 'p.postdate', 'p.version', 'p.flag', 'p.filename'
			));
		$select->join(BETTER_DB_TBL_PREFIX.'download_phone AS ph', 'ph.oid=p.oid', array(
			'ph.name AS phone_name',
			));
		$select->join(BETTER_DB_TBL_PREFIX.'download_os AS o', 'o.oid=p.oid', array(
			'o.name AS os_name',
			));
		$select->where('ph.pid=?', $pid);
		$select->order('p.flag DESC');
		
		$limit!=null && $select->limit($limit);
		
		$rs = parent::squery($select, $this->rdb);
		return $rs->fetchAll();
	}	
	
	public function getPostdateByOs($oid){
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS p', array('p.postdate'));
		$select->where('p.oid=?', $oid);
		$select->limit(1);
		$rs = parent::squery($select, $this->rdb);
		$date = $rs->fetch();
		return $date;		
	}
	
	public function getPostInfoByOs($oid){
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS p', array('p.postdate','p.version'));
		$select->where('p.oid=?', $oid);
		$select->limit(1);
		$rs = parent::squery($select, $this->rdb);
		$date = $rs->fetch();
		return $date;		
	}
}
