<?php

/**
 * 用户Major变化记录
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_MajorLog extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_major_log';
    	$this->priKey = 'id';
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
	
	public function getMyMajorLog($page=1, $count=BETTER_PAGE_SIZE)
	{
		$return = array(
			'rows' => array(),
			'count' => 0,
			'pages' => 0,
			);
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $this->identifier);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		$total = $row['total'];
		if ($total>0) {
			$return['count'] = $total;
			$return['pages'] = Better_Functions::calPages($total, $count); 
			
			$tmp = $this->getAll(array(
				'uid' => $this->identifier,
				'order' => 'dateline DESC',
				), $page.','.$count, 'limitPage');
				
			$rows = array();
			foreach ($tmp as $row) {
				$poiInfo = Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
				$rows[] = array_merge(array(
					'major_time' => $row['dateline'],
					), $poiInfo);
			}
			
			$return['rows'] = &$rows;
		}
		
		return $return;
	}

}