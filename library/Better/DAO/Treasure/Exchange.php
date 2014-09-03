<?php

/**
 * 宝物兑换
 * 
 * @package Better.DAO.Treasure
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Treasure_Exchange extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'treasure_exchange';
		$this->priKey = 'id';
		$this->orderKey = 'dateline';
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
	
	/**
	 * 获取有哪些可以兑换的宝物
	 * 
	 * @return array
	 */
	public static function &getCanExchangeTreasures()
	{
		$result = array();
		
		$db = parent::registerDbConnection('common_server');
		$select = $db->select();
		$select->from(BETTER_DB_TBL_PREFIX.'treasure AS t', array(
			't.id', 't.name', 't.description', 't.image_url', 't.en_name', 't.en_description'
			));
		$select->join(BETTER_DB_TBL_PREFIX.'treasure_exchange AS te', 't.id=te.treasure_id', array(
			'te.total', 'te.remain', 'te.start_time', 'te.end_time',
			));
		$select->where('te.end_time>?', time());
		$select->order('te.end_time ASC');

		$rs = self::squery($select, $db);
		$tmp = $rs->fetchAll();	
		$result = array();
		foreach ($tmp as $row) {
			$row['name'] = Better_Language::loadDbKey('name', $row);
			$row['description'] = Better_Language::loadDbKey('description', $row);	

			$result[] = $row;
		}
		
		return $result;
	}	
	
	/**
	 * 判断宝物是否可兑换
	 * 
	 * @return bool
	 */
	public function canExchange($treasureId)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('treasure_id=?', $treasureId);
		$select->where('remain>?', '0');
		$select->where('start_time<?', time());
		$select->where('end_time>?', time());
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return isset($row['treasure_id']) ? true : false;
	}
	
	/**
	 * 减少可供兑换的宝物数
	 * 
	 * @return bool
	 */
	public function reduceRemain($id)
	{
		$sql = "UPDATE `".$this->tbl."` SET remain=remain-1 WHERE `treasure_id`='".intval($id)."'";
		return $this->wdb->query($sql);
	}

}