<?php

/**
 * 绑定苹果手机操作
 * 
 * @package Better.DAO.Phone
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Phone_Apple extends Better_DAO_Base
{
	
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'bind_iphone';
		$this->priKey = 'uid';
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
	
	public function getTokens(array $uids, $type='')
	{
		$data = array();
		
		if (count($uids)>0) {
			$tmp = $this->getAll(array(
				'uid' => $uids,
				));
			if (count($tmp)>0) {
				foreach ($tmp as $row) {
					$data[$row['uid']] = $row;
				}
				
				$pingSettings = array();
				$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);
				foreach ($sids as $sid) {
					$cs = parent::assignDbConnection('user_server_'.$sid);
					$rdb = $cs['r'];
					$select = $rdb->select();
					$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
						'p.uid', 'p.allow_ping'
						));
					$select->join(BETTER_DB_TBL_PREFIX.'user_apn_settings AS s', 's.uid=p.uid', array(
						's.request', 's.friends_shout', 's.friends_checkin', 's.game', 's.direct_message'
						));
					$select->where('p.uid IN (?)', $uids);
					
					$rs = parent::squery($select, $rdb);
					$rows = $rs->fetchAll();
					
					if ($type) {
						foreach ($rows as $row) {
							if ($row['allow_ping']=='0' || $row[$type]=='0') {
								unset($data[$row['uid']]);
							}
						}						
					} else {
						foreach ($rows as $row) {
							if ($row['allow_ping']=='0') {
								unset($data[$row['uid']]);
							}
						}
					}
				}
			}
		}
			
		return $data;
	}
}