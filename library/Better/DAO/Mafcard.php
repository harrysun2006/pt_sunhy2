<?php

/**
 * 取宝物
 * 
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Mafcard extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'maf_card';
		$this->priKey = 'id';
		$this->orderKey = &$this->priKey;
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
	
	public function getAll($val=null, $limit='0', $method='limit')
	{
		$method = $method=='limit' ? 'limit' : 'limitPage';

		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$this->parseWhere($select, $val);
		if (is_array($val) && isset($val['order'])) {
			$this->parseOrder($select,$val['order']);
		} else {
			$select->order($this->orderKey.' DESC');
		}
		$limit!=null && self::$method($select, $limit);

		$result = self::squery($select, $this->rdb);
		$data = $result->fetchAll();
		return $data;
	}	
	
	public function getMine($uid, $limit='0', $method='limit')
	{
		$method = $method=='limit' ? 'limit' : 'limitPage';

		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$select->where('uid=?', $uid);		

		$result = self::squery($select, $this->rdb);
		$data = $result->fetchAll();
		return $data;
	}
		
}