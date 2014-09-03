<?php

/**
 * 用户关系
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 * 2010-01-30 过期 yangl
 */

class Better_DAO_User_Relations extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    /*public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_relations';
    	
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
	}*/

	/**
	 * 
	 * 获取粉丝详情
	 * @param unknown_type $page
	 * @param unknown_type $pageSize
	 * @return array
	 */
	/*public function &getFollowersDetail($page, $pageSize=BETTER_PAGE_SIZE)
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
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog', 'p.priv_location',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma', 
				'p.last_checkin_poi', 'p.timezone'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
			$select->join(BETTER_DB_TBL_PREFIX.'user_relations AS r', 'r.uid=p.uid AND r.relation_uid='.$this->identifier.' AND relation=1' , array('r.dateline'));

			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
			$select->order('r.dateline DESC');
			$select->limit($page*$pageSize+1);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['dateline'].'_'.$v['uid']] = $v;
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
	}*/
	
	/**
	 * 
	 * 获取关注的人详情
	 * @param unknown_type $page
	 * @param unknown_type $pageSize
	 * @return array
	 */
	/*public function &getFollowingsDetail($page, $pageSize=BETTER_PAGE_SIZE)
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
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog', 'p.priv_location',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma', 
				'p.last_checkin_poi', 'p.timezone'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures',
				));	
			$select->join(BETTER_DB_TBL_PREFIX.'user_relations AS r', 'r.uid=p.uid AND r.relation=2 AND r.relation_uid='.$this->identifier, array('r.dateline'));

			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
			$select->order('r.dateline DESC');

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v['karma'].'.'.$v['uid']] = $v;
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
	}	*/
}