<?php

/**
 * API使用日志
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_ApiLog extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct()
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'api_log';
    	$this->priKey = 'id';
    	$this->orderKey = 'id';
    	
		parent::__construct (0);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_ApiLog();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function getCount($val=null)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, 'COUNT(id) AS num');
		$this->parseWhere($select, $val);
		$select->limit(1);

		$result = $this->query($select);
		$r = $result->fetch();
		
		return $r['num'];
	}
	
	public function ipCount($ip, $start, $end)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, ' COUNT(id) AS num');
		$select->where('ip=?', $ip);
		$select->where('dateline>?', $start);
		$select->where('dateline<=?', $end);
		$select->limit(1);
		$r = $this->query($select)->fetch();
		
		return $r['num'];
	}
		
	public function userCount($uid, $start, $end)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, ' COUNT(id) AS num');
		$select->where('uid=?', $uid);
		$select->where('dateline>?', $start);
		$select->where('dateline<=?', $end);
		$select->limit(1);
		$r = $this->query($select)->fetch();
		
		return $r['num'];
	}
	
	public function statusCount($uid, $start, $end)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, ' COUNT(id) AS num');
		$select->where('uid=?', $uid);
		$select->where('request=?', '/statuses/update.%');
		$select->where('dateline>?', $start);
		$select->where('dateline<=?', $end);
		$select->limit(1);
		$r = $this->query($select)->fetch();
		
		return $r['num'];
	}
		
}
