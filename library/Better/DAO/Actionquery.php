<?php

/**
 * 后台操作数据库 得到相关数据
 *
 * @author fengjun <fengj@peptalk.cn>
 */

require_once(dirname(__FILE__).'/Base.php');
require_once(dirname(__FILE__).'/db_mysql.php');

class Better_DAO_ActionQuery extends Better_DAO_Base {

	private static $instance = null;
	
	function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'admin_tracelog';
		$this->priKey = 'bid';
		$this->orderKey = 'bid';
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_Adminblog();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public static function getActionquery($username,$by,$start,$end,$page=1,$pagesize=20)
	{
		$db = &$this->rdb;
		$username = trim($username);
		if (!$username) return false;
		
		$start = self::__getUnixTime($start);
		$end = self::__getUnixTime($end);
		$end = $end + 3600*24;
		
		$select = $db->select();
		$select->from(BETTER_DB_TBL_PREFIX.'admin_tracelog');
		$select->where('uid=?', $username);
		if ($by=='actor') {
			$select->where('adminuid=?', $username);
		}
		$select->where('postdate>?', $start);
		$select->where('postdate<?', $end);
		$select->limitPage($page, $pagesize);
		$select->order('id DESC');
		
		$rs = self::squery($select, $db);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
	
	public static function __getUnixTime($timestring)
	{
		if("".$timestring=="")
		    return time();
		
		$mad = explode(" ", $timestring);
		$date = explode("-", $mad[0]);
		$time = explode(":", $mad[1]);
		
		$year = intval($date[0]);
		$month = intval($date[1]);
		$day = intval($date[2]);
		
		$hour = intval($time[0]);
		$minute = intval($time[1]);
		$second = intval($time[2]);
		
		$ret = mktime($hour,$minute,$second,$month,$day,$year);
		
		return $ret;
	}
}