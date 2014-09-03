<?php

/**
 * 后台操作数据库 得到相关数据
 *
 * @author fengjun <fengj@peptalk.cn>
 */

require_once(dirname(__FILE__).'/Base.php');
require_once(dirname(__FILE__).'/db_mysql.php');

class Better_DAO_Adminblog extends Better_DAO_Base {

	private static $instance = null;
	
	function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'admin_newblog';
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
	
	public static function getNewblog($page=1, $pagesize=20)
	{
		$newdb = new DB();
		$sql = "SELECT * FROM better_admin_newblog ORDER BY id DESC ";
		$limit = $newdb->limit($page,$pagesize);
		
		$sql .= $limit;
		
		$rows = $newdb->get_matrix($sql);
		return $rows;
	}
	
	public static function deleteBlog($bid)
	{
		$newdb = new DB();
		$bid = $newdb->escape_string($bid);
		$sql = "DELETE FROM better_admin_newblog WHERE bid='$bid'";
		$newdb->update($sql);
	}

	public static function getfilterblog($page=1, $pagesize=20)
	{
		$newdb = new DB();
		$sql = "SELECT * FROM better_admin_filterwordlog ORDER BY id DESC ";
		$limit = $newdb->limit($page,$pagesize);
		
		$sql .= $limit;
		
		$rows = $newdb->get_matrix($sql);
		return $rows;
	}
	
	
}