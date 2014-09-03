<?php

/**
 * 用户POI收藏
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_PoiFavorites extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'poi_favorites';
    	$this->priKey = 'uid';
    	$this->orderKey = 'dateline';
    	
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
	 * 获取所有收藏的poi的id
	 * 
	 * @return array
	 */
	public function getFavorites()
	{
		$rows = $ids = array();
		
		$rows = $this->getAll(array(
			'uid' => $this->identifier
			));
		foreach ($rows as $row) {
			$ids[] = $row['poi_id'];
		}
		
		return $ids;
	}
	
	public function getFavoritesWithDetail($page=1, $count=BETTER_PAGE_SIZE)
	{
		$rows = array();
		$total = 0;

		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $this->identifier);

		$rs = self::squery($select, $this->rdb);
		$r = $rs->fetch();
		$total = (int)$r['total'];

		if ($total>0) {
			$select = $this->rdb->select();
			$select->from($this->tbl, array(
				'poi_id',
				));
			$select->where('uid=?', $this->identifier);
			$select->order('dateline DESC');
			$select->limitPage($page, $count);
			$rs = self::squery($select, $this->rdb);
			$tmp = $rs->fetchAll();

			foreach ($tmp as $row) {
				$rows[] = Better_Poi_Info::getInstance($row['poi_id'])->get();
			}
		}
		
		return array(
			'count' => $total,
			'rows' => $rows,
			);
	}

}