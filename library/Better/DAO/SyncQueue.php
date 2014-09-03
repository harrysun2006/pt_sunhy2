<?php

/**
 * 抄送第三方队列
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_SyncQueue extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'sync_queue';
    	$this->priKey = 'queue_id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_SyncQueue($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	/**
	 * 获取最新的一个队列内容
	 * 
	 * @return array
	 */
	public static function popupQueue($protocols)
	{
		$result = array();

		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		shuffle($sids);
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid, true);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'sync_queue AS s', array(
				's.*',
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'blog AS b', 'b.bid=s.bid', array(
				'b.message', 'b.type', 'b.poi_id AS b_poi_id', 'b.badge_id', 'b.major','b.upbid', 'X(b.xy) x', 'Y(b.xy) y', 'b.ip', 'b.address'
				));
			$select->where('s.sync_time=?', '0');
			$select->where('s.protocol IN (?)', $protocols);
			$select->order('s.queue_time ASC');
			$select->limit(1);

			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();

			if (isset($row['queue_id'])) {
				if ($row['b_poi_id']) {
					$row['poi_id'] = $row['b_poi_id'];
				}
				$result = &$row;
				break;
			}
		}

		return $row;
	}
		

	public static function addThirdId($uid, $bid, $protocol, $id)
	{
		$third = "$protocol|$id,";
		
		$data = Better_Blog::getBlog($bid);
		$blog = $data['blog'];		
		$third_info = $blog['third_info'] . $third;
		
		Better_DAO_Blog::getInstance($uid)->updateByCond(array(
				'third_info' => $third_info	
			), array(
				'bid' => $bid
			));
			
		return true;				
	}
	
	/**
	 * 消息中加上转发的原始内容
	 */
	public static function addUpMsg($upbid,$message, $syncUid=0)
	{
		if (!$upbid) {
			return $message;
		}
		
		$addMessage  = '';
		list($uid, $bid) = explode('.', $upbid);
		
		$bdua = Better_DAO_User_Assign::getInstance();	
		$rdb = $bdua->getRdbByUid($uid);
		$sql = $rdb->quoteInto('SELECT `type`,`poi_id`,`message` FROM better_blog WHERE bid=?', $upbid);
		$result = $rdb->query($sql);
					
		$row = $result->fetch();
		
		if ($syncUid) {
			$user = Better_User::getInstance($syncUid);
			$userInfo = $user->getUserInfo();
			$lang = Better_Language::loadIt($userInfo['language']);
		} else {
			$lang = Better_Language::loadIt('zh-cn');
		}
		
		$rtUser = Better_User::getInstance($uid);
		$rtUserInfo = $rtUser->getUserInfo();
			
		if ($row['message']) {
			
			if ($row['type']=='checkin' && $row['poi_id']) {
				$poiInfo = Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
				$addMessage .= ' '.$lang->global->at . ' ' .$poiInfo['city'] . ' ' . $poiInfo['name'] ;
			} 
			
			$message .= ' //@' . $rtUserInfo['nickname'] . $addMessage  . ' : ' . $row['message']; 
		} else if ($row['type']=='checkin' && $row['poi_id']) {
			$poiInfo = Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
			$addMessage .= ' ' . $lang->global->at . ' ' . $poiInfo['city'] . ' ' . $poiInfo['name'];
			
			$message .= ' //@' . $rtUserInfo['nickname'] . $addMessage; 			
		}

		return $message;
	}
	
	/**
	 * 得到token
	 */
	
	public static function getToken($uid, $protocol='douban.com')
	{
		$bdua = Better_DAO_User_Assign::getInstance();	
		$rdb = $bdua->getRdbByUid($uid);
		$sql = $rdb->quoteInto("SELECT * FROM better_3rdbinding WHERE protocol='$protocol' AND uid=?  ", $uid);
		$result = $rdb->query($sql);
					
		$row = $result->fetch();

		return $row;		
	}
	/*
	 * 根据第三方的EMAIL地址去检索用户绑定的同步信息
	 */
	public function getSyncbysiteuser($site,$email){
		$data = array();	
		$sids = Better_DAO_User_Assign::getInstance()->getServerids();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding AS a', '*');
			$select->where('protocol=?', $site);
			$select->where('username=?', $email);
		
			$rs = parent::squery($select, $rdb);
			$data = $rs->fetch();
			if (isset($data['uid']) && $data['uid']) {
				return $data;
			}			
		}
		
		return $data;
	}
	
	
	/**
	 * sql
	 */
	public function execSql($sql)
	{
		$wdb = $this->wdb;
		$rs = parent::squery($sql, $wdb);
	}
}























