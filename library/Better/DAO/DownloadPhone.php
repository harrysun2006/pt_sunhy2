<?php

/**
 * 客户端软件下载手机型号
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */
class Better_DAO_DownloadPhone extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'download_phone';
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
	public function getPhoneByBidWap($params)	
	{	
		$select = "select * from ".BETTER_DB_TBL_PREFIX.'download_phone' ." where bid = ".$params['bid']." order by name desc limit ".($params['page']-1)*$params['page_size'].",".$params['page_size'];		
		$rs = self::squery($select, $this->rdb);
				
		$rows = $rs->fetchAll();

		return $rows;
		
	}
	
	public function getPhoneByBidCountWap($params)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, new Zend_Db_Expr(
			'COUNT(*) AS total'
			));	
		$params['bid'] && $select->where('bid=?', $params['bid']);			
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();		
		return $row;
	}
	public function getPhoneByOid($params)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');	
		$select->where('oid=?', $params['oid']);
		$select->order('BINARY(`name`) DESC');
		$select->limit($params['limit']);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();			
		return $row;
		
	}
	public function getPhoneByName($param)
	{
		$param = mysql_escape_string($param);
		$select = "select p.*,o.`desc` odesc,o.`name` oname,o.`version`,b.`name` bname,b.`img` bimg,o.`postdate` from ".BETTER_DB_TBL_PREFIX."download_phone p join ".BETTER_DB_TBL_PREFIX."download_product o on o.oid = p.oid  join ".BETTER_DB_TBL_PREFIX."download_brand b on b.bid = p.bid where lower(p.`name`) = '{$param}'";
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetchAll();
		return $row;
	}
	
}
