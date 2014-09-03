<?php

/**
 * 宝物流转历史
 *
 * @package Better.DAO.User.Treasure
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Treasure_Log extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_treasure_log';
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
	 * 取得宝物流转历史
	 * 
	 * @param $tid
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public static function getTreasureLogs($tid, $page=1, $count=BETTER_PAGE_SIZE)
	{
		$result = array(
			'count' => 0,
			'rows' => array(),
			);
			
		$limit = $page*$count+1;
		$rows = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$sql = "SELECT l.uid, l.poi_id, l.treasure_id, l.dateline, p.username, p.nickname
			FROM `".BETTER_DB_TBL_PREFIX."user_treasure_log` AS l
				INNER JOIN `".BETTER_DB_TBL_PREFIX."profile` AS p
					ON p.uid=l.uid
			WHERE l.treasure_id='".$tid."' AND l.category='pickup'
			ORDER BY l.dateline DESC
			LIMIT ".$limit."
			";
			
			$rs = self::squery($sql, $rdb);
			$tmp = $rs->fetchAll();
			foreach($tmp as $v) {
				if ($v['poi_id']) {
					$v['poi_info'] = Better_Poi_Info::getInstance($v['poi_id'])->getBasic();
				}
				
				$rows[$v['dateline']] = $v;
			}
		}
		if (count($rows)>0) {
			krsort($rows);
			$data = array_chunk($rows, $count);
			if (isset($data[$page-1])) {
				$result['rows'] = &$data[$page-1];
			}
		}

		return $result;
	}
	
	/**
	 * 
	 */
	public function getPlayedWith($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$result = array(
			'rows' => array(),
			'count' => 0,
			);
			
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(DISTINCT(co_uid)) AS total')
			));
		$select->where('uid=?', $this->identifier);
		$select->where('category=?', 'got');
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		$result['count'] = $row['total'];
		
		if ($row['total']>0) {
			$select = $this->rdb->select();
			$select->from($this->tbl, array(
				 new Zend_Db_Expr('DISTINCT(co_uid) AS co_uid'), new Zend_Db_Expr('MAX(dateline) AS dateline'),
				));
			$select->where('uid=?', $this->identifier);
			$select->where('category=?', 'got');
			$select->group('co_uid');
			$select->limitPage($page, $pageSize);
			$select->order('dateline DESC');
			
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			
			foreach ($rows as $row) {
				$result['rows'][$row['dateline']] = $row['co_uid'];
			}
		}
		
		return $result;
	}

}