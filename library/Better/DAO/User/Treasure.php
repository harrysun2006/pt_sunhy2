<?php

/**
 * 用户的宝物
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Treasure extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_treasures';
    	$this->priKey = 'uid';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	/**
	 * 获取我有哪些可以兑换的宝物
	 * 
	 * @return array
	 */
	public static function &getCanExchangeTreasures(array $myTreasureIds)
	{
		$result = array();
		
		if (count($myTreasureIds)>0) {
			$db = parent::registerDbConnection('common_server');
			$select = $db->select();
			$select->from(BETTER_DB_TBL_PREFIX.'treasure AS t', array(
				't.id', 't.name', 't.description', 't.image_url',
				));
			$select->join(BETTER_DB_TBL_PREFIX.'treasure_exchange AS te', 't.id=te.treasure_id', array(
				'te.total', 'te.remain', 'te.start_time', 'te.end_time',
				));
			$select->where('te.start_time<?', time());
			$select->where('te.end_time>?', time());
			$select->where('t.id IN (?)', $myTreasureIds);
			$select->where('te.remain>?', 0);
			$select->order('te.end_time ASC');
	
			$rs = self::squery($select, $db);
			$result = $rs->fetchAll();	
		}
		
		return $result;
	}
	
	/**
	 * 获取我的宝物兑换记录
	 * 
	 * @return array
	 */
	public static function &getMyExchangeHistory($uid, $page=1, $count=BETTER_PAGE_SIZE)
	{
		$result = array(
			'count' => 0,
			'rows' => array(),
			);
		
		$db = parent::registerDbConnection('common_server');
		$select = $db->select();
		$select->from(BETTER_DB_TBL_PREFIX.'treasure_exchange_request', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$rs = self::squery($select, $db);
		$row = $rs->fetch();
		$total = $row['total'];
		
		if ($total>0) {
			$result['count'] = $total;
			
			$select = $db->select();
			$select->from(BETTER_DB_TBL_PREFIX.'treasure_exchange_request AS ter', array(
				'ter.dateline', 'ter.status', 'ter.note'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'treasure_exchange AS te', 'te.treasure_id=ter.treasure_id', array(
				'te.total', 'te.remain', 'te.start_time', 'te.end_time',
				));
			$select->join(BETTER_DB_TBL_PREFIX.'treasure AS t', 't.id=ter.treasure_id', array(
				't.id', 't.name', 't.description', 't.image_url',
				));
			$select->where('ter.uid=?', $uid);
			$select->order('ter.dateline DESC');
			$select->limitPage($page, $count);

			$rs = self::squery($select, $db);
			$result['rows'] = $rs->fetchAll();
		}
		
		return $result;
	}
	
	/**
	 * 获取我曾经跟哪些人玩过游戏
	 * 
	 * @return array
	 */
	public function &getMyGameLogs($uid, $page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		
	}

}