<?php

/**
 * 用户阻止别人相关数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Blocking extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = null)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'blocking';
    	$this->priKey = 'uid';
    	$this->orderKey = 'dateline';
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Blocking($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function getBlockingsDetail($page, $pageSize=BETTER_PAGE_SIZE)
	{

		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile AS p', array(
				'p.uid', 'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma', 
				'p.last_checkin_poi', 'p.timezone'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
			$select->join(BETTER_DB_TBL_PREFIX.'blockedby AS blb', 'blb.uid=p.uid AND blb.blocked_by_uid='.$this->identifier, array('blb.dateline'));

			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
			$select->order('blb.dateline DESC');

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['dateline'].'.'.$v['uid']] = $v;
			}
		}

		krsort($results, SORT_NUMERIC);
		$data = array_chunk($results, $pageSize);
		$rows = isset($data[$page-1]) ? $data[$page-1] : array();

		return array(
						'pages' => count($data),
						'count' => count($rows),
						'total' => count($results),
						'rows' => $rows
						);		
	}	
		
}
