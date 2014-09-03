<?php

/**
 * 取宝物
 * 
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Treasure extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'treasure';
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
	
	protected function randomOne()
	{
		$ratio = (float)(rand(1,100000)/100000);
		
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('active=?', 1);
		$select->where('remains>?', 0);
		$select->order(new Zend_Db_Expr('rand()'));
		$select->limit(1);
				
		if ($ratio>0.0001) {
			$select->where('ratio>?', $ratio);
		} else if ($ratio<=0.00001) {
			$select->where('ratio<=?', $ratio);
		} else if ($ratio>0.00001 && $ratio<=0.0001) {
			$select->where('ratio>?', 0.00001);
			$select->where('ratio<=?', 0.0001);
		} else {
			$select->where('ratio>?', 0.5);
		}
		
		$rs = self::squery($select, $this->rdb);
		$tmp = $rs->fetch();
		
		$tmp['name'] = Better_Language::loadDbKey('name', $tmp);
		$tmp['description'] = Better_Language::loadDbKey('description', $tmp);
		
		$this->updateByCond(array(
			'grants' => $tmp['grants']+1,
			), array(
				'id' => $tmp['id'],
			));
		
		return $tmp;
	}
	
	/**
	 * 随机抛出几个宝物
	 * 
	 * @return array
	 */
	public function randomThrow($nums=2)
	{
		$rows = array();
		for($i=0;$i<$nums;$i++) {
			$rows[] = $this->randomOne();
		}
		
		return $rows;
	}
	
	public function get($val)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$this->parseWhere($select, $val);
		$select->limit(1);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		$row['name'] = Better_Language::loadDbKey('name', $row);
		$row['description'] = Better_Language::loadDbKey('description', $row);			

		return $row;
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
		$tmp = $result->fetchAll();
		$data = array();
		
		foreach ($tmp as $row) {
			$row['name'] = Better_Language::loadDbKey('name', $row);
			$row['description'] = Better_Language::loadDbKey('description', $row);	
						
			$data[] = $row;
		}

		return $data;
	}	
		
}