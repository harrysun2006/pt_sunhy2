<?php

/**
 * 用户相关数据操作
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_User extends Better_DAO_Base
{

	protected static $usersPerpage = 10;
	protected static $usersOrder = 'last_active';
	
	protected $serverId = 0;

	/**
	 * 用户个人资料表
	 *
	 * @var string
	 */
	protected $profileTbl = '';
	
	/**
	 * 用户帐号表
	 *
	 * @var string
	 */
	protected $accountTbl = '';
	
	/**
	 * 用户关注表
	 *
	 * @var string
	 */
	protected $followingTbl = '';
	
	/**
	 * 用户消息表
	 *
	 * @var string
	 */
	protected $blogTbl = '';
	
	/**
	 * 计数表
	 * 
	 * @var string
	 */
	protected $counterTbl = '';
	
	/**
	 * 附件表
	 *
	 * @var unknown_type
	 */
	protected $attachTbl = '';
	
	protected $settingTbl = '';

	private static $instance = array();
	
	protected static $inited = false;
	
	protected static $AccountTbl = '';
	protected static $ProfileTbl = '';
	protected static $AttachTbl = '';
	protected static $FollowingTbl = '';
	protected static $BlogTbl = '';
	protected static $SettingTbl = '';
	protected static $CounterTbl = '';
	
	protected static $unMaps = array();
	protected static $cache = array();
	
	public function __construct($identifier=0)
	{
		if (self::$inited===false) {
			self::$AccountTbl = BETTER_DB_TBL_PREFIX.'account';
			self::$ProfileTbl = BETTER_DB_TBL_PREFIX.'profile';
			self::$SettingTbl = BETTER_DB_TBL_PREFIX.'settings';
			self::$CounterTbl = BETTER_DB_TBL_PREFIX.'profile_counters';
			self::$inited = true;
		}
		
		$this->tbl = self::$AccountTbl;
		$this->priKey = 'uid';
		
		$this->accountTbl = &$this->tbl;
		$this->profileTbl = self::$ProfileTbl;
		$this->counterTbl = self::$CounterTbl;
		$this->settingTbl = self::$SettingTbl;

		$this->orderKey = 'last_active';

		parent::__construct($identifier);
		
		$this->assignUserDbConnection(true);
	}

  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}

	public static function setPerpage($perpage)
	{
		self::$usersPerpage = $perpage;
	}

	public static function setOrder($key)
	{
		self::$usersOrder = $key;
	}

	public function get($val)
	{
		$key = is_array($val) ? key($val) : '`a`.'.$this->priKey;
		
		return $this->getByKey($val, $key);
	}

	/**
	 * 根据用户id读取用户资料
	 *
	 * @param $val
	 * @param $key
	 * @return unknown_type
	 */
	public function getByUid($uid)
	{
		$arr = $this->getByKey($uid);
		return $arr;
	}
	
	/**
	 * 删除用户
	 * @see library/Better/DAO/Better_DAO_Base#delete($val, $key)
	 */
	public function delete($uid)
	{
		$this->wdb->delete($this->tbl, $this->wdb->quoteInto('uid=?', $uid));
		$this->wdb->delete($this->profileTbl, $this->wdb->quoteInto('uid=?', $uid));
	}

	/**
	 * 取一组用户的资料及其第一条消息
	 *
	 * @param array $uids
	 * @return array
	 */
	public function getUsersWithFirstBlog($uids)
	{
		$dao = Better_DAO_User_Assign::getInstance();

		$rows = $dao->fetchAll($dao->getAdapter()->quoteInto('uid IN (?)', $uids));
		$servers = array();
		foreach($rows as $row) {
			$servers[$row['sid']][] = $row['uid'];
		}
		unset($rows);
		
		$results = array();
		foreach($servers as $_sid=>$_uids) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'profile AS p');
			$select->joinleft(BETTER_DB_TBL_PREFIX.'blog AS b', 'p.uid=b.uid AND p.last_bid=b.bid', array(
							'b.bid', 'b.dateline', 'b.message', 'b.xy', 'b.attach', 'b.source', 'b.checked',
							));
			$select->where('p.uid IN (?)', $_uids);
			//$select->where('p.state!=?', Better_User_State::BANNED);
			
			$select->order('p.last_active DESC');

			$rs = $this->query($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['last_active']] = $v;
			}
		}

		return $results;
	}
	
	/**
	 * 根据某个字段获得用户的uid
	 * 
	 * @param $key
	 * @param $val
	 * @return unknown_type
	 */
	public static function getUidByKey($key, $val)
	{
		$uid = 0;
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();			
			$select->from(BETTER_DB_TBL_PREFIX.'account AS a', array(
				'a.uid',
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=a.uid', array());
			$select->where($key.'=?', $val);
			$select->limit(1);
			
			$rs = parent::squery($select, $rdb);
			$data = $rs->fetch();
			
			if (isset($data['uid']) && $data['uid']) {
				return $data['uid'];
			}
		}
		
		return $uid;
	}
	
	/**
	 * 根据昵称取用户名
	 * 
	 * @param unknown_type $nickname
	 * @return string
	 */
	public static function getUsernameByNickname($nickname)
	{
		if (!isset(self::$unMaps[$nickname])) {
			$cacher = Better_Cache::remote();
			$cacheKey = 'kai_unmap_'.md5($nickname);
			self::$unMaps[$nickname] = $cacher->get($cacheKey);
			
			if (!self::$unMaps[$nickname]) {
				$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
				
				foreach($sids as $sid) {
					$cs = parent::assignDbConnection('user_server_'.$sid);
					$rdb = &$cs['r'];
					$select = $rdb->select();
					
					$sql = "SELECT `username` FROM `".BETTER_DB_TBL_PREFIX."profile`WHERE `nickname`='".addslashes($nickname)."' LIMIT 1";
					
					$rs = parent::squery($sql, $rdb);
					$data = $rs->fetch();
					
					if (isset($data['username']) && $data['username']) {
						self::$unMaps[$nickname] = $data['username'];
						$cacher->set($cacheKey, self::$unMaps[$nickname]);
						break;
					}
				}		
			}	
		}
		
		return self::$unMaps[$nickname];
	}
	
	/**
	 * 根据某字段读取用户资料
	 * 
	 * @param $val
	 * @param $key
	 * @return array
	 */
	public static function searchUser($val, $key='')
	{
		$key=='' && $key = 'a.uid';
		$data = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerids();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'account AS a', '*');
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=a.uid', array(
				'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range', 'p.state', 'p.karma',
				'p.last_checkin_poi', 'p.timezone'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=a.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.now_posts', 'c.posts', 
				'c.received_msgs', 'c.sent_msgs', 'c.new_msgs', 'c.files', 'c.friends', 'c.majors', 'c.badges', 'c.treasures',
				'c.places', 'c.invites', 'c.checkins',
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));				
			
			$select->where($key.'=?', $val);
			//$select->where('p.state!=?', Better_User_State::BANNED);
			$rs = parent::squery($select, $rdb);
			$data = $rs->fetch();

			if (isset($data['uid']) && $data['uid'] && $data['username']) {
				return $data;
			}			
		}
		
		return $data;
	}
		
	/**
	 * 根据某字段读取用户
	 *
	 * @param $val
	 * @param $key
	 * @return array
	 */
	public function getByKey($val, $key='', $needstate=true)
	{
		$key = $key=='' ? 'a.'.$this->priKey : $key;
		$data = array();

		if ($key=='a.'.$this->priKey || $key==$this->priKey) {
			$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids((array)$val);	
		} else {
			$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		}

		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->from($this->tbl.' AS a', '*');
			$select->join($this->profileTbl.' AS p', 'p.uid=a.uid', array(
				'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.range', 'p.receive_msn_notify', 'p.state', 'p.karma',
				'p.last_checkin_poi', 'p.allow_ping', 'p.timezone', 'p.ref_uid', 'p.sys_priv_blog', 'p.email4person', 'p.email4community', 'p.email4product', 
				'p.last_rt_mine', 'p.last_my_followers','p.rp', 'p.allow_rt', 'p.friend_sent_msg', 'p.sync_badge','p.recommend'
				));
			$select->join($this->counterTbl.' AS c', 'c.uid=a.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.now_posts', 'c.posts', 'c.received_msgs',  'c.badges', 'c.treasures',
				'c.sent_msgs', 'c.new_msgs', 'c.files', 'c.friends', 'c.majors', 'c.checkins', 'c.invites',
				'c.places','c.now_tips', 'c.poi_favorites'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
			
			$select->where($key.'=?', $val);
			if($needstate){
				//$select->where('p.state!=?', Better_User_State::BANNED);
			}

			$rs = self::squery($select, $rdb);
			$data = $rs->fetch();
			if (isset($data['uid']) && $data['uid'] && $data['username']) {
				$data['followers'] = 0;
				$data['followings'] = 0;
				
				$sql = "SELECT count(*) cnt from better_3rdbinding where uid='{$data['uid']}'";
				$rs = self::squery($sql, $rdb);
				$bind_data = $rs->fetch();
				$data['sync_sns'] = $bind_data['cnt'];
	
				return $data;
			}
		}

		return $data;
		
	}

	
	/**
	 * 在数据库中插入相应数据，生成一个新用户
	 *
	 * @see library/Better/DAO/Better_DAO_Base#insert($data)
	 */
	public function insert($data)
	{
		$uid = $data['uid'];

		if ($uid!=false) {
			$s = array();
			$s['uid'] = $uid;
			$s['email'] = $data['email'];
			$s['cell_no'] = $data['cell_no'];
			$s['password'] = md5($data['password'].$data['salt']);
			$s['salt'] = $data['salt'];
			$s['regtime'] = time();
			$s['regip'] = $data['regip'];
			$s['lastlogin'] = time();
			$s['lastloginip'] = $data['regip'];
			$s['enabled'] = $data['enabled']=='1' ? '1' : '0';
			$s['partner'] = $data['partner'] ? $data['partner'] : '';
			
			$this->wdb->insert(self::$AccountTbl, $s);

			$s = array();
			$s['uid'] = $uid;
			$s['nickname'] = $data['nickname'];
			$s['username'] = $data['username'];
			$s['language'] = $data['language'] ? $data['language'] : 'zh-cn';
			$s['visited'] = 0;
			$s['priv_profile'] = '0';
			$s['priv_blog'] = '0';
			$s['priv_location'] = '0';
			$s['priv_place'] = '0';
			$s['x'] = $data['x'];
			$s['y'] = $data['y'];
			$s['ref_uid'] = isset($data['ref_uid']) ? $data['ref_uid'] : 0;
			$s['receive_msn_notify'] = 1;
			$s['receive_gtalk_notify'] = 1;
			$s['last_update'] = $data['last_update'];

			$s['karma'] = Better_Config::getAppConfig()->karma->on_register;
			if (isset($data['birthday'])) {
				$s['birthday'] = $data['birthday'];
			}
			if (isset($data['gender'])) {
				$s['gender'] = $data['gender'];
			}
			
			$s['state'] = $data['state'];
			
			parent::_insertXY($s, self::$ProfileTbl);
			
			//	插入缓存表
			$S = array();
			$S['uid'] = $uid;
			$S['3rdbindings'] = serialize(array());
			$S['followings'] = serialize(array());
			$S['followers'] = serialize(array());
			$S['friends'] = serialize(array());
			$S['badges'] = serialize(array());
			$S['treasures'] = serialize(array());
			$S['blocking'] = serialize(array());
			$S['blockedby'] = serialize(array());
			$S['avatar'] = serialize(array());
			$this->wdb->insert(BETTER_DB_TBL_PREFIX.'user_cache', $S);
			
			$s = array();
			$s['uid'] = $uid;
			$s['posts'] = 0;
			$s['now_posts'] = 0;
			$this->wdb->insert(self::$CounterTbl, $s);
			
			//	插入apn设置表
			$s = array();
			$s['uid'] = $uid;
			$s['game'] = 1;
			$s['direct_message'] = 1;
			$s['request'] = 1;
			$s['friends_shout'] = 1;
			$s['friends_checkin'] = 1;
			$this->wdb->insert(BETTER_DB_TBL_PREFIX.'user_apn_settings', $s);
			
			/*
			$s = array();
			$s['uid'] = $uid;
			$s['priv_blog'] = 0;
			$s['priv_place'] = 0;
			$s['priv_location'] = 0;
			$s['priv_profile'] = 0;
			$s['receive_msn_notify'] = 1;
			$s['receive_gtalk_notify'] = 1;
			$this->wdb->insert($this->settingTbl, $s);*/
			
			Better_DAO_User_Assign::getInstance()->insert(array(
				'uid' => $uid ,
				'username' => $data['username'],
				'sid' => $this->serverId,
				));
		}

		return $uid;
	}
	
	/**
	 * 执行更新用户资料的数据库操作
	 *
	 * @see library/Better/DAO/Better_DAO_Base#update($data, $val, $cond)
	 */
	public function update($data)
	{
		$uid = $data['uid'] ? $data['uid'] : $this->identifier;
		$flag = false;

		if ($uid>0) {
			try {
				//	account 表
				$s = array();
				isset($data['email']) && $s['email'] = $data['email'];
				isset($data['cell_no']) && $s['cell_no'] = $data['cell_no'];
	
				if (isset($data['password'])) {
					$salt = Better_Functions::genSalt();
					$s['salt'] = $salt;
					$s['password'] = md5($data['password'].$salt);
				}
				isset($data['enabled']) && $s['enabled'] = $data['enabled']=='1' ? '1' : '0';
				isset($data['lastlogin']) && $s['lastlogin'] = $data['lastlogin'];
				isset($data['lastlogin_partner']) && $s['lastlogin_partner'] = $data['lastlogin_partner'];
				count($s)>0 && parent::update($s, $uid);
				
				//	profile 表
				$s = array();
				isset($data['username']) && $s['username'] = $data['username'];
				isset($data['nickname']) && $s['nickname'] = $data['nickname'];
				isset($data['gender']) && $s['gender'] = $data['gender'];
				isset($data['birthday']) && $s['birthday'] = $data['birthday'];
				isset($data['self_intro']) && $s['self_intro'] = $data['self_intro'];
				isset($data['language']) && $s['language'] = $data['language'];
				isset($data['tags']) && $s['tags'] = $data['tags'];
				isset($data['avatar']) && $s['avatar'] = $data['avatar'];
				isset($data['visits']) && $s['visits'] = $data['visits'];
				isset($data['visted']) && $s['visited'] = $data['visited'];
				isset($data['last_bid']) && $s['last_bid'] = $data['last_bid'];
				isset($data['last_active']) && $s['last_active'] = $data['last_active'];
				isset($data['status']) && $s['status'] = $data['status'];
				isset($data['address']) && $s['address'] = $data['address'];
				isset($data['xy']) && $s['xy'] = $data['xy'];
				isset($data['x']) && $s['x'] = $data['x'];
				isset($data['y']) && $s['y'] = $data['y'];
				isset($data['lbs_report']) && $s['lbs_report'] = $data['lbs_report'];
				isset($data['province']) && $s['province'] = $data['province'];
				isset($data['city']) && $s['city'] = $data['city'];
				isset($data['msn']) && $s['msn'] = $data['msn'];
				isset($data['gtalk']) && $s['gtalk'] = $data['gtalk'];
				isset($data['range']) && $s['range'] = floatval($data['range']);
				isset($data['priv_profile']) && $s['priv_profile'] = $data['priv_profile'];
				isset($data['priv_location']) && $s['priv_location'] = $data['priv_location'];
				isset($data['priv_blog']) && $s['priv_blog'] = $data['priv_blog'];
				isset($data['priv_place']) && $s['priv_place'] = $data['priv_place'];
				isset($data['receive_msn_notify']) && $s['receive_msn_notify'] = $data['receive_msn_notify'];
				isset($data['receive_gtalk_notify']) && $s['receive_gtalk_notify'] = $data['receive_gtalk_notify'];
				isset($data['language']) && $s['language'] = $data['language'];
				isset($data['karma']) && $s['karma'] = $data['karma']>=Better_Karma::BASE ? Better_Karma::BASE-0.1 : (float)$data['karma'];
				isset($data['last_checkin_poi']) && $s['last_checkin_poi'] = (int)$data['last_checkin_poi'];
				isset($data['poi']) && $s['last_checkin_poi'] = (int)$data['poi'];
				isset($data['poi_id']) && $s['last_checkin_poi'] = (int)$data['poi_id'];
				isset($data['allow_ping']) && $s['allow_ping'] = $data['allow_ping'] ? 1 : 0;
				isset($data['last_checkin_from']) && $s['last_checkin_from'] = $data['last_checkin_from'];
				isset($data['timezone']) && $s['timezone'] = (int)$data['timezone'];
			
				isset($data['state']) && $s['state'] = $data['state'];
				
				isset($data['last_update']) && $s['last_update'] = $data['last_update'];
				isset($data['sys_priv_blog']) && $s['sys_priv_blog']= $data['sys_priv_blog'];
				
				isset($data['email4person']) && $s['email4person'] = $data['email4person'];
				isset($data['email4community']) && $s['email4community'] = $data['email4community'];
				isset($data['email4product']) && $s['email4product'] = $data['email4product'];
				
				isset($data['last_rt_mine']) && $s['last_rt_mine'] = $data['last_rt_mine'];
				isset($data['last_my_followers']) && $s['last_my_followers'] = $data['last_my_followers'];
				
				(isset($data['live_province']) && $data['live_province']) && $s['live_province'] = $data['live_province'];
				(isset($data['live_city']) && $data['live_city']) && $s['live_city'] = $data['live_city'];
				isset($data['rp']) && $s['rp'] = $data['rp'];
				
				isset($data['allow_rt']) && $s['allow_rt'] = $data['allow_rt'];
				isset($data['friend_sent_msg']) && $s['friend_sent_msg'] = $data['friend_sent_msg'];
				isset($data['sync_badge']) && $s['sync_badge'] = $data['sync_badge'];
				isset($data['recommend']) && $s['recommend'] = $data['recommend'];
				
				count($s)>0 && $this->_updateXY($s, $uid, 'AND', $this->profileTbl);

				// Counter表
				// 修改吼吼贴士计数更新方式
				$s = array();
				isset($data['followings']) && $s['followings'] = (int)$data['followings'];
				isset($data['followers']) && $s['followers'] = (int)$data['followers'];
				isset($data['posts']) && $s['posts'] = (int)$data['posts'];
				isset($data['favorites']) && $s['favorites'] = (int)$data['favorites'];
				isset($data['places']) && $s['places'] = (int)$data['places'];
				isset($data['received_msgs']) && $s['received_msgs'] = intval($data['received_msgs']);
				isset($data['sent_msgs']) && $s['sent_msgs']  = intval($data['sent_msgs']);
				isset($data['new_msgs']) && $s['new_msgs'] = intval($data['new_msgs']);
				isset($data['files']) && $s['files'] = intval($data['files']);
				// isset($data['now_posts']) && $s['now_posts'] = intval($data['now_posts']);
				isset($data['now_posts']) && $s['now_posts'] = intval($data['now_posts']) > 0 ? new Zend_Db_Expr('now_posts+1') : new Zend_Db_Expr('now_posts-1');
				isset($data['treasures']) && $s['treasures'] = intval($data['treasures']);
				// isset($data['checkins']) && $s['checkins'] = intval($data['checkins']);
				isset($data['checkins']) && $s['checkins'] = intval($data['checkins']) > 0 ? new Zend_Db_Expr('checkins+1') : new Zend_Db_Expr('checkins-1');
				isset($data['friends']) && $s['friends'] = intval($data['friends']);
				isset($data['badges']) && $s['badges'] = intval($data['badges']);
				isset($data['majors']) && $s['majors'] = intval($data['majors']);
				isset($data['invites']) && $s['invites'] = intval($data['invites']);
				// isset($data['now_tips']) && $s['now_tips'] = intval($data['now_tips']);
				isset($data['now_tips']) && $s['now_tips'] = intval($data['now_tips']) > 0 ? new Zend_Db_Expr('now_tips+1') : new Zend_Db_Expr('now_tips-1');
				count($s)>0 && parent::update($s, $uid, 'AND', BETTER_DB_TBL_PREFIX.'profile_counters');
			}catch (Exception $e) {
				Better_Log::getInstance()->logAlert('Update user failed: ['.serialize($s).']', 'user');
			}
		}
		
		$flag = true;
		
		return $flag;
	}
	
	/**
	 * 获取关注的uids
	 *
	 * @param $uid
	 * @return array
	 */
	public function getFollowing($uid=0)
	{
		$uid = $this->identifier ? $this->identifier : ($uid>0 ? $uid : 0);
		$fuids = array();
		
		/*if ($uid>0) {
			$tbl = Better_DAO_Following::getInstance($uid);
			$data = $tbl->getAll(array(
								'uid' => $uid,
								'order' => 'dateline DESC',
								), null);

			foreach($data as $row) {
				$fuids[] = $row['following_uid'];
			}
		}*/
		
		return $fuids;
	}
	
	/**
	 * 获取被关注的uids
	 *
	 * @param $uid
	 * @return array
	 */
	public function getFollowers($uid=0)
	{
		$uid = $this->identifier ? $this->identifier : ($uid>0 ? $uid : 0);
		$fuids = array();
		
		/*if ($uid>0) {
			$tbl = Better_DAO_Follower::getInstance($uid);
			$data = $tbl->getAll(array(
								'uid' => $uid,
								'order' => 'dateline DESC',
								), null);

			foreach($data as $row) {
				$fuids[] = $row['follower_uid'];
			}
		}*/
		
		return $fuids;
	}

	/**
	 *	根据指定的uids获取用户信息
	 *
	 * @param $uids
	 * @return array
	 */
	public function getUsersByUids($uids, $page=1, $pageSize=BETTER_PAGE_SIZE, $cacheKey='', $order='', $hasBadge=0, $columns=array())
	{
		$pageSize==0 && $pageSize = self::$usersPerpage;
		$dua = Better_DAO_User_Assign::getInstance();
		$rows = $dua->fetchAll($dua->getAdapter()->quoteInto('uid IN (?)', $uids));

		$servers = array();
		foreach($rows as $row) {
			$servers[$row['sid']][] = $row['uid'];
		}
		unset($rows);

		$results = array();
		foreach($servers as $_sid=>$_uids) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();
			$select->from($this->profileTbl.' AS p', array(
				'p.uid', 'p.username', 'p.nickname',  'p.gender', 'p.birthday', 'p.self_intro', 'p.language',
				'p.avatar', 'p.live_province', 'p.live_city',
				'p.visits', 'p.visited', 'p.priv_profile', 'p.priv_blog',
				'p.last_active', 'p.last_bid', 'p.status', 'p.address', 'p.lbs_report', 'p.city', 'p.places', 'p.msn',
				'X(p.xy) AS x', 'Y(p.xy) AS y', 'p.receive_msn_notify', 'p.state', 'p.karma' , 
				'p.last_checkin_poi', 'p.timezone', 'p.rp', 'p.allow_rt', 'p.friend_sent_msg', 'p.sync_badge'
				));
			$select->join($this->counterTbl.' AS c', 'c.uid=p.uid', array(
				'c.followings', 'c.followers', 'c.favorites', 'c.posts AS posts', 'c.received_msgs', 'c.friends', 'c.majors',
				'c.places', 'c.checkins', 'c.invites', 'c.badges', 'c.treasures', 'c.poi_favorites'
				));	
			if ($hasBadge) {
				$select->join(BETTER_DB_TBL_PREFIX.'user_badges AS ub', 'ub.uid=p.uid AND ub.bid='.$hasBadge, array());
			}

			if (defined('BETTER_SEARCH_EMAIL')) $columns[] = 'email';
			if (defined('BETTER_SEARCH_CELL')) $columns[] = 'cell_no';
			if (count($columns) > 0) {
				$arr = array();
				foreach ($columns as $column) $arr[] = 'a.' . $column;
				$select->join(BETTER_DB_TBL_PREFIX.'account AS a', 'a.uid=p.uid', $arr);
			}
				
			$select->joinleft(BETTER_DB_TBL_PREFIX.'attachments AS at', 'at.uid=p.uid AND p.avatar=at.file_id', array(
				'at.file_id', 'at.filename', 'at.dateline AS at_dateline', 'at.mimetype', 'at.filesize', 'at.ext'
				));
								
			$select->where('p.uid IN(?)', $_uids);

			$order=='' && $order==self::$usersOrder;
			switch ($order) {
				case 'p.karma DESC':
				case 'karma':
					$order = 'karma';
					$select->order('p.karma DESC');
					break;
				case 'c.badges DESC':
					$order = 'badges';
					$select->order('c.badges DESC');
					break;
				case 'c.majors DESC':
					$order ='majors';
					$select->order('c.majors DESC');
					break;
				case 'c.followers DESC':
					$order = 'followers';
					$select->order('c.followers DESC');
					break;
				default:
					$order = 'friends';
					$select->order('c.friends DESC');
					break;
			}			
			$select->order('p.last_active DESC');
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $v) {
				$results[$v[$order].'_'.$v['uid']] = $v;
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
	
	/**
	 * 用户排序
	 * 
	 */
	public static function sort(array $params=array())
	{
		$results = array(
			'rows' => array(),
			'count' => 0,
			);
		$uids = isset($params['uids']) ? (array)$params['uids'] : array();
		$option = $params['option'];
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$count = isset($params['count']) ? (int)$params['count'] : BETTER_PAGE_SIZE;
		
		if (count($uids)>0) {
			$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);
		} else {
			$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		}
		
		foreach($servers as $_sid=>$_uids) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();

		}
	}

}
