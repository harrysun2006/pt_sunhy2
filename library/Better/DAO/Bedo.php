<?php
/**
 * 贝多同步数据
 *
 * @package Better.DAO.Bedo
 * @author zhoul <zhoul@peptalk.cn>
 */

class Better_DAO_Bedo extends Better_DAO_Base
{
	private static $instance = null;
	private static $cacheKey = 'uid_to_sid';
	private static $serverIds = array(
		'1', '2'
		);
		
	private static $FULLBLOGPATH = 'http://k.ai/bedoblog/?itemid=';
	
	public static $NONE = 0;
	private static $uid = '';

	public function __construct($identifier=null)
	{
		$this->tbl = 'pep_user';
		$this->priKey = 'jid';
		$this->orderKey = &$this->priKey;
		
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	
	public function userinfo($username)
	{
		
		$db = parent::registerDbConnection('bedo_server');
		self::$instance->_setAdapter($db);
		self::$instance->setDb($db);
		
		$select = self::$instance->select();
		$select->setIntegrityCheck(false);
		$select->from('pep_user AS u');
		$select->joinleft('pep_user_kai AS k', 'k.jid = u.jid', array(
				'k.uid'
				));
		$select->where('u.jid=?', $username);
//		echo $select->__toString();
		$rs = self::squery($select, self::$instance);
		$data = $rs->fetch();
		return $data;
	}
	
	public function getSorce($jid)
	{
		
		$db = parent::registerDbConnection('pepmc_server');
		$wdb = &$db;
		$sql = "select * from userscore where jid='{$jid}@pep'";
		$rs = $wdb->query($sql);
		$data = $rs->fetch();
		return $data['score'];
	}
	
	public function setSyncInfo($uid, $jid, $syncTime)
	{
		
		$db = parent::registerDbConnection('bedo_server');
		$wdb = &$db;
		$sql = "insert into pep_user_kai (`uid`, `jid`, `syncTime`) values ('{$uid}', '{$jid}', '{$syncTime}')";
		$wdb->query($sql);
		
		$dbId = Better_DAO_User_Assign::getInstance()->getServerIdByUid($uid);
		$kaiDb = parent::assignDbConnection('user_server_'.$dbId);
		$kWdb = $kaiDb['w'];
		$sql = "insert into better_account_bedosync (uid, jid, blog, photo, miniBlog, status, syncTime) ";
		$sql .= "values ('{$uid}', '{$jid}', -1,-1,-1,0, '{$syncTime}')";
		$kWdb->query($sql);
		return true;
	}
	
	public function setSyncContent($uid, $status)
	{
		$dbId = Better_DAO_User_Assign::getInstance()->getServerIdByUid($uid);
		$kaiDb = parent::assignDbConnection('user_server_'.$dbId);
		$kWdb = $kaiDb['w'];
		$sql = "update better_account_bedosync set `status` = {$status} where uid = {$uid}";
		$kWdb->query($sql);
		return;
	}
	
	public function getBedoUid($uid)
	{
		$db = parent::registerDbConnection('bedo_server');
		$rdb = &$db;
		$sql = "select u.uid bedoUid,u.jid jid from pep_user_kai k join pw_user u on u.jid = k.jid where k.uid = '{$uid}'";
		$rs = $rdb->query($sql);
		$data = $rs->fetch();
		return $data;
	}
	
	public function getSyncDataInfo($uid)
	{
		$info = array();
		$data = $this->getBedoUid($uid);
		if (!$data) {
			return;
		}
		
		$syncInfo = $this->getSyncInfo($uid);
		$info['status'] = $syncInfo['status'];
		
		$db = parent::registerDbConnection('bedo_server');
		$rdb = &$db;
		
		$sql = "select count(*) cnt from pw_items where uid = {$data['bedoUid']} and type = 'blog' and itemid > {$syncInfo['blog']}";
		$blogQ = $rdb->query($sql);
		$blogR = $blogQ->fetch();
		$info['blog'] = $blogR['cnt'];
		
		$sql = "select count(aid) cnt from pw_items i join pw_upload u on u.itemid = i.itemid where i.uid = {$data['bedoUid']} and i.type = 'photo' and u.aid > {$syncInfo['photo']}";
		$photoQ = $rdb->query($sql);
		$photoR = $photoQ->fetch();
		$info['photo'] = $photoR['cnt'];
		
		$sql = "select count(*) cnt from pep_miniblog where jid = '{$data['jid']}' and mbid > {$syncInfo['miniBlog']}";
		$rs = $rdb->query($sql);
		$data2 = $rs->fetch();
		$info['miniBlog'] = $data2['cnt'];
		$info['jid'] = $data['jid'];
		return $info;
	}
	
	public function getSyncInfo($uid)
	{
		$dbId = Better_DAO_User_Assign::getInstance()->getServerIdByUid($uid);
		$kaiDb = parent::assignDbConnection('user_server_'.$dbId);
		$kWdb = $kaiDb['w'];
		$sql = "select * from better_account_bedosync where uid = '{$uid}' ";
		$query = $kWdb->query($sql);
		return $query->fetch();
	}
	/**
	 * 根据bedo号查询用户的开开UID
	 * @param unknown_type $uid
	 */
	public function getUidByJid($jid)
	{
		$data = array();
		foreach (self::$serverIds as $sid){
			if(!empty($data)){
				break;
			}
			$kaiDb = parent::assignDbConnection('user_server_'.$sid);
			$kWdb = $kaiDb['w'];
			$sql = "select uid from better_account_bedosync where jid = '{$jid}' ";
			$query = $kWdb->query($sql);
			$data = $query->fetch();			
		}		
		return $data['uid'];
		
	}
	
	
	public function getSyncUsers()
	{
		$return = array();
		foreach (self::$serverIds as $sid)
		{
			$kaiDb = parent::assignDbConnection('user_server_'.$sid);
			$rdb = $kaiDb['r'];
			$sql = "select *, {$sid} as sid from better_account_bedosync where status > " . self::$NONE;
			$rs = $rdb->query($sql);
			$data = $rs->fetchAll();
			if ($data) {
				$return = array_merge($return, $data);
			}
		}
		return $return;
	}
	
	public function import($user)
	{
		$userData = $this->getBedoUid($user['uid']);
		if (!$userData) {
			return;
		}
		
		$db = parent::registerDbConnection('bedo_server');
		$rdb = &$db;
		
		$this->rdb = $rdb;
		$this->tbl = 'pep_user_kai';
		$this->priKey = 'uid';
		
		$lang = Better_Language::load();
		if (Better_Registry::get('sess') == null) {
			Better_Registry::set('sess', $this);
		}
		
		Better_Registry::get('sess')->set('uid', $user['uid']);
		
		if (in_array($user['status'], array(1, 3, 5, 7))) {
			$sql = "select *,i.itemid blogid from pw_items i join pw_blog b on b.itemid = i.itemid left join pw_upload u on u.itemid = i.itemid where i.uid = {$userData['bedoUid']} and i.itemid > {$user['blog']} and i.type = 'blog' group by i.itemid order by i.itemid";
			$blogQ = $rdb->query($sql);
			$blogRs = $blogQ->fetchAll();
			if ($blogRs) {
				$llArr = array();
				$sql = "select i.itemid ,x(g.ll) x,y(g.ll) y from pw_items i join pep_geotagged g on g.refid = i.itemid where g.jid = {$userData['jid']} and i.itemid > {$user['blog']} and i.type = 'blog'";
				$llQ = $rdb->query($sql);
				$llR = $llQ->fetchAll();
				if ($llR) {
					foreach ($llR as $val) {
						$llArr[$val['itemid']] = $val;
					}
				}
				
				foreach ($blogRs as $val) {
					if ($val['attachurl']) {
						$attachId = Better_Attachment_Save::getInstance('photo')->uploadImgLink(Better_Config::getAppConfig()->bedo->attachment_url . $val['attachurl'], '', '', $user['uid']);
					} else {
						$attachId = 0;
					}
					$message = Better_Service_BedoBinding::formatContent($val['content']);
					if (mb_strlen($message, 'UTF-8') > 80) {
						$message = mb_substr($message, 0, 80, 'UTF-8') . '...... ' . $lang->user->bedoFull . ' ' . Better_Config::getAppConfig()->base_url.'/bedoblog/?itemid=' . $val['blogid'];
					} else {
						$message .= ' ' . $lang->user->bedoFull . ' ' . Better_Config::getAppConfig()->base_url.'/bedoblog/?itemid=' . $val['blogid'];
					}
					$priv = !$val['ifhide'] ? 'public' : 'private';
					$data = array('message'=>$message,'dateline'=>$val['postdate'], 'badge_id'=>0, 'need_sync'=>0,'synced'=>0, 'attach'=>"{$attachId}",'priv'=>$priv,'no_publictimeline'=>true, 'no_queue'=>true);
					if ($llArr['blogid']['x'] && $llArr['blogid']['y']) {
						$data['x'] = $llArr['blogid']['x'];
						$data['y'] = $llArr['blogid']['y'];
					}
					$rId = Better_Blog::post($user['uid'], $data, 0);
					if ($rId > 0) {
						$this->updateSyncData(array('uid'=>$user['uid'], 'sid'=>$user['sid'], 'blog'=>$val['blogid']));
					} else {
						if ($val['attachurl']) {
							$data['photo'] = Better_Config::getAppConfig()->bedo->attachment_url . $val['attachurl'];
						}
						$data['uid'] = $user['uid'];
						Better_Log::getInstance()->log($rId . "\tbedoSync\tblog\t" . serialize($data), 'bedoSyncErr', false);
					}
				}
			}
		}
		
		if (in_array($user['status'], array(2, 3, 6, 7))) {
			$sql = "select *,i.itemid iId from pw_items i join pw_upload u on u.itemid = i.itemid where i.uid = {$userData['bedoUid']} and u.aid > {$user['photo']} and i.type = 'photo' order by u.aid";
			$photoQ = $rdb->query($sql);
			$photoRs = $photoQ->fetchAll();
			if ($photoRs) {
				$llArr = array();
				$sql = "select x(g.ll) x,y(g.ll),g.refid y from pw_items i join pep_geotagged g on g.refid = i.itemid where g.jid = '{$userData['jid']}' and i.type = 'photo'";
				$llQ = $rdb->query($sql);
				$llR = $llQ->fetchAll();
				if ($llR) {
					foreach ($llR as $val)
					{
						$llArr[$val['refid']] = $val;
					}
				}
				
				foreach ($photoRs as $val) {
					$attachId = Better_Attachment_Save::getInstance('photo')->uploadImgLink(Better_Config::getAppConfig()->bedo->attachment_url . $val['attachurl'], '', '', $user['uid']);
					$message = mb_strlen(trim($val['descrip']), 'UTF-8') > 0 ? trim($val['descrip']) : $lang->javascript->blog_with_photo_no_message;
					$priv = !$val['ifhide'] ? 'public' : 'private';
					$data = array('message'=>$message,'dateline'=>$val['uploadtime'], 'badge_id'=>0, 'need_sync'=>0,'synced'=>0, 'attach'=>"{$attachId}",'priv'=>$priv,'no_publictimeline'=>true, 'no_queue'=>true);
					if ($llArr['iId']['x'] && $llArr['iId']['y']) {
						$data['x'] = $llArr['iId']['x'];
						$data['y'] = $llArr['iId']['y'];
					}
					if ($attachId && $attachId != 1008) {
						$data['attach'] = $attachId;
						$rId = Better_Blog::post($user['uid'], $data, 0);
						if ($rId > 0) {
							$this->updateSyncData(array('uid'=>$user['uid'], 'sid'=>$user['sid'], 'photo'=>$val['aid']));
						} else {
							$data['uid'] = $user['uid'];
							$data['photo'] = Better_Config::getAppConfig()->bedo->attachment_url . $val['attachurl'];
							Better_Log::getInstance()->log($rId . "\tbedoSync\tphoto\t" . serialize($data), 'bedoSyncErr', false);
						}
					} else {
						$data['uid'] = $user['uid'];
						$data['photo'] = Better_Config::getAppConfig()->bedo->attachment_url . $val['attachurl'];
						Better_Log::getInstance()->log($rId . "\tbedoSync\tphoto\t" . serialize($data), 'bedoSyncErr', false);
						continue;
					}
				}
			}
		}
		
		if (in_array($user['status'], array(4,5,6,7))) {
			$sql = "select * from pep_miniblog where jid = '{$userData['jid']}' and mbid > {$user['miniBlog']}";
			$miniBlogQ = $rdb->query($sql);
			$miniBlogRs = $miniBlogQ->fetchAll();
			if ($miniBlogRs) {
				foreach ($miniBlogRs as $val) {
					$message = $val['content'];
					if (mb_strlen($message) > 140) {
						continue;
					}
					$priv = !$val['ifhide'] ? 'public' : 'private';
					$data = array('message'=>$message,'dateline'=>$val['postdate'], 'badge_id'=>0, 'need_sync'=>0,'synced'=>0, 'attach'=>"0", 'priv'=>$priv, 'no_publictimeline'=>true, 'no_queue'=>true);
					$rId = Better_Blog::post($user['uid'], $data, 0);
					if ($rId > 0) {
						$this->updateSyncData(array('uid'=>$user['uid'], 'sid'=>$user['sid'], 'miniBlog'=>$val['mbid']));
					} else {
						$data['uid'] = $user['uid'];
						Better_Log::getInstance()->log($rId . "\tbedoSync\tminiBlog\t" . serialize($data), 'bedoSyncErr', false);
					}
				}
			}
		}
		$this->updateSyncData(array('uid'=>$user['uid'], 'sid'=>$user['sid'], 'status'=> self::$NONE));
		Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
			'content' => $lang->global->bedoimport_finish_notice,
			'receiver' => $user['uid']
			));
	}
	
	public function getBlogInfo($itemid)
	{
		
		$db = parent::registerDbConnection('bedo_server');
		$rdb = &$db;
		
		$sql = "select *,i.itemid blogid, i.uid bedoUid from pw_items i join pw_blog b on b.itemid = i.itemid left join pw_itemtype t on t.typeid = i.dirid where i.itemid = {$itemid} and i.type = 'blog'";
		$query = $rdb->query($sql);
		$rs = $query->fetch();
		
		if (!$rs) {
			return false;
		}
		
		$sql = "select k.uid  kaiUid from pw_user u join pep_user_kai k on k.jid = u.jid where u.uid = {$rs['bedoUid']}";
		$query = $rdb->query($sql);
		$bedo = $query->fetch();
		$rs['kaiUid'] = $bedo['kaiUid'];
		return $rs;
	}
	
	public function getBlogPhoto($itemId)
	{
		$db = parent::registerDbConnection('bedo_server');
		$rdb = &$db;
		
		$sql = "select * from pw_upload where itemid = {$itemId} and type = 'img'";
		$query = $rdb->query($sql);
		$rs = $query->fetchAll();
		return $rs;
	}
	
	public function getKaiFriends($jid)
	{
		$db = parent::registerDbConnection('bedo_server');
		$rdb = &$db;
		$sql = "select c.fjid,k.uid,c.fnickname from pep_contacts c join pep_user_kai k on k.jid = c.fjid where c.jid = '{$jid}' and ifcheck = 1";
		$query = $rdb->query($sql);
		return $query->fetchAll();
	}
	
	public function getBlogComment($itemid, $page, $limit)
	{
		$db = parent::registerDbConnection('bedo_server');
		$rdb = &$db;
		$index = $page < 2 ? 0 : ($page - 1) * $limit;
		$sql = "select * from pw_comment c join pw_user u on u.uid = c.authorid where itemid = '{$itemid}' order by id desc limit {$index}, {$limit}";
		$query = $rdb->query($sql);
		return $query->fetchAll();
	}
	
	private function updateSyncData($params)
	{
		if (!$params['uid']) {
			return ;
		}
		$kaiDb = parent::assignDbConnection('user_server_'.$params['sid']);
		$wdb = $kaiDb['w'];
		$sql = "update better_account_bedosync set ";
		if (isset($params['blog'])) {
			$sql .= "blog = '{$params['blog']}',";
		}
		if (isset($params['photo'])) {
			$sql .= "photo = '{$params['photo']}',";
		}
		if (isset($params['miniBlog'])) {
			$sql .= "miniBlog = '{$params['miniBlog']}',";
		}
		if (isset($params['status'])) {
			$sql .= "status = '{$params['status']}',";
		}
		$sql = substr($sql, 0, strlen($sql) - 1) . " where uid = {$params['uid']}";
		$wdb->query($sql);
	}
	
	public function addThirdBind($uid, $qq = false)
	{
		$db = parent::registerDbConnection('bedo_server');
		$wdb = &$db;
		$sql = "update pep_user_kai set thirdsync = 1 where uid = '{$uid}' and thirdsync = 0";
		$wdb->query($sql);
		if ($qq) {
			$sql = "update pep_user_kai set qqsync = 1 where uid = '{$uid}' and qqsync = 0";
			$wdb->query($sql);
		}
		return;
	}
	
	public function delThirdBind($uid, $qq = false, $all = false)
	{
		$db = parent::registerDbConnection('bedo_server');
		$wdb = &$db;
		if ($all) {
			$sql = "update pep_user_kai set thirdsync = 0 where uid = '{$uid}' and thirdsync = 1";
			$wdb->query($sql);
		}
		if ($qq) {
			$sql = "update pep_user_kai set qqsync = 0 where uid = '{$uid}' and qqsync = 1";
			$wdb->query($sql);
		}
		return;
	}
}