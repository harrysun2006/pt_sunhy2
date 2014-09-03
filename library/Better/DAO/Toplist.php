<?php

/**
 * 排行榜DAO操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Toplist extends Better_DAO_Base
{
	private static $instance = array();
	
	public function __construct($identifier=0)
	{
		parent::__construct($identifier);
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Toplist($identifier);
		}
		
		return self::$instance[$identifier];
	}

	public static function followersTop5()
	{
		$data = array();
		$results = array();
	
		/*$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile', array(
				'uid', 'nickname', 'username', 'followers',
				));
			$select->where('followers>?', '0');
			$select->order('followers DESC');
			$select->limit(5);
			
			$rs = parent::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach ($rows as $row) {
				$data[$row['followers']][] = $row;
			}
		}
		
		if (count($data)>0) {
			krsort($data);
			foreach ($data as $v) {
				foreach ($v as $row) {
					$results[] = $row;
				}
			}
			
			$data = array_chunk($results, 5);
			$results = $data[0];
		}*/
		
		return $results;
	}
	
}