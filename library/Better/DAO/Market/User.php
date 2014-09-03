<?php

/**
 * 大声展活动
 * 
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Market_User extends Better_DAO_Base
{
	
	private static $instance = array();

 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'market_user';
		$this->priKey = 'bid';
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
	
	public static function getAllData($poiId)
	{
		$dua = Better_DAO_User_Assign::getInstance();
		$servers = $dua->getServerIds();

		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);	
			$select = $cs['r']->select();
			$select->from(BETTER_DB_TBL_PREFIX.'market_user');
			$select->where('poi_id=?', $poiId);
			$rs = self::squery($select, $cs['r']);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				if ($row['kai_checked'] || $row['partner_checked']) {
					$results[] = $row['uid'];
				}
			}
		}
		
		return $results;
	}
}