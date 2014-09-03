<?php

/**
 * 用户的好友动态
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Friendstatus extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_friendstatus';
    	$this->priKey = 'uid';
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
	}
	
	public function reconnection()
	{
		try {
			$this->wdb->getConnection();
			$this->rdb->getConnection();
		} catch (Exception $e) {
			$this->wdb->closeConnection();
			$this->rdb->closeConnection();
			
			$this->assignUserDbConnection(true);
		}
		
		return self::$instance[$this->identifier];
	}
	
	
	/**
	 * 取自己的好友动态
	 * @param $params
	 * @return unknown_type
	 */
	public function getMine($params)
	{
		$return = array();
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$withoutKai = isset($params['without_kai']) ? (bool)$params['without_kai'] : false;
		$row = $this->get($this->identifier);
		if (!$row['uid']) { //还没有初始化过  数据库查询
			$bids = $this->initData($this->identifier);
		} else {
			$bids = unserialize($row['bids']);
		}

		//自己的
		$me_status = $this->getUserStatus($this->identifier, 1, true, true);
		if ($me_status) {
			$key = $me_status[0]['dateline'] . '_' . $me_status[0]['uid'];
			$bids[$key] = $me_status[0]['bid'];
		}
		
		//开开的
		$kai_status = $this->getUserStatus(BETTER_SYS_UID, 1, true);
		$key = $kai_status[0]['dateline'] . '_' . $kai_status[0]['uid'];
		$bids[$key] = $kai_status[0]['bid'];	
			
		
		krsort($bids);
		$start = ($page - 1) * $pageSize;
		$return = array_slice($bids, $start, $pageSize);		
		
		return $return;
	}

	
	/**
	 * 初始数据
	 * @param $uid
	 * @return unknown_type
	 */
	public function initData($uid)
	{
		$friendsUids = Better_User_Friends::getInstance($uid)->getFriendsWithHomeShow();
		$rows = Better_DAO_User_Status::getInstance($uid)->getFriendStatus($friendsUids);				
		$data = array();
		foreach ($rows as $row) {
			$_bid = $row['bid'];
			$_uid = $row['uid'];
			$_dateline = $row['dateline'];
			
			$key = $_dateline . '_' . $_uid;
			$data[$key] = $_bid;
		}
		$this->_replace($this->tbl, $uid, $data);
		
		return $data;
	}
	
	
	/**
	 * 得到用户的最新状态
	 * @param $uid
	 * @param $count
	 * @param $isFriend
	 * @return unknown_type
	 */
	public function getUserStatus($uid, $count=1, $isFriend=false, $is_me=false)
	{
		$bids = array();
		
		$count = (int)$count;
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "SELECT `uid`,`bid`,`dateline` FROM `".BETTER_DB_TBL_PREFIX."blog` WHERE uid='".$uid."' ";
		
		if (!$is_me) {
			if ($isFriend) {
				$sql .= " AND priv!='private' ";
			} else {
				$sql .= " AND priv='public' ";
			}
		}
		
		$sql .= " ORDER BY `dateline` DESC LIMIT ".$count;
		
		$rs = self::query($sql, $rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
	
	public static function getSomebodyBidsByCountWithDateline($uid, $dateline, $count=300, $isFriend=false)
	{

	}
	
	/**
	 * 删除过期的数据
	 */
	public  function delPublic($data)
	{
		return array_slice($data, 0, 300);
	}
	
	
	/**
	 * 删除动态
	 * @param $array
	 * @param $uid
	 * @param $mode 匹配是对bid 还是uid
	 * @return unknown_type
	 */
	public function delStatus($array, $del_data, $mode)
	{
		$return = array();
		$uid = $del_data['uid'];
		$bid = $del_data['bid'];
		
		foreach ($array as $o_dateline_uid => $o_bid) {
			if ( $bid == $o_bid ) {
				continue;
			}
			
			if ($mode == 'uid' ) {
				list($o_uid, $o_blogid) = explode('.', $o_bid);
				if ($o_uid == $uid) {
					continue;	
				}
			}
						
			$return[$o_dateline_uid] = $o_bid;
		}

		if ( $mode == 'bid' ) { //取一条纪录补充进来
			$blogs = Better_DAO_User_Friendstatus::getInstance($uid)->getUserStatus($uid, 1, 1);
			$blog = $blogs[0];
			
			$return[$blog['dateline']. '_' . $blog['uid']] = $blog['bid'];
		}
		
		krsort($return);
		return $return;
	}
	
	
	/**
	 * 增加状态
	 * @param $blog
	 * @param $array
	 * @return unknown_type
	 */
	public function addStatus($blog, $array=array())
	{
		$return = array();
		
		$uid = $blog['uid'];
		$bid = $blog['bid'];
		$dateline = $blog['dateline'];
		
		$return[$dateline . '_' . $uid] = $bid;
		
		foreach ($array as $o_dateline_uid => $o_bid) {
			list($_uid, $_blog_id) = explode('.', $o_bid);
			if ($_uid == $uid ) {
				continue;
			}
			$return[$o_dateline_uid] = $o_bid;
		}

		krsort($return);
		return $return;
	}
	
	
	/**
	 * 执行Replace的SQL操作
	 * 
	 * @param $data
	 * @param $tbl
	 * @return unknown_type
	 */
	public function replace($data, $tbl='')
	{
		$tbl =='' && $tbl = $this->tbl;
		
		$uid = $data['uid'];
		$blog = $data['blog'];
		
		try {
			$this->reconnection();
			$_data = $this->get($uid);
			
			if (!$_data['uid']) { //这个用户还没有处理过  初始化一下就可以了
				$this->initData($uid);
				return true;
			}
 			
			if ($_data['bids']) { //已经有值了
				$text = $_data['bids'];
				$t_array = unserialize($text);
				$t_array = $this->addStatus($blog, $t_array);
			} else { //没有值
				$t_array = $this->addStatus($blog);
			}
			
			$t_array = $this->delPublic($t_array);
			$result = $this->_replace($tbl, $uid, $t_array);
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg('Data:['.serialize($data)."\n".'SQL_REPLACE_ERROR:['.$e->getMessage().']'.' Message:['.$e->getTraceAsString().']', 'db_error');
		}

		return $this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : $result;
	}	
	
	/**
	 * 删除的动作
	 * @param $uid
	 * @param $del_uid
	 * @return unknown_type
	 */
	public function clean($uid, $del_data, $mode='bid')
	{
		try {
			$this->reconnection();
			$_data = $this->get($uid);
			
			if (!$_data['uid']) { //这个用户还没有处理过  初始化一下就可以了
				$this->initData($uid);
				return true;
			}
			
			$t_array = array();
			if ($_data['bids']) {
				$text = $_data['bids'];
				$t_array = unserialize($text);
				$t_array = $this->delStatus($t_array, $del_data, $mode);
			}
			$result = $this->_replace($this->tbl, $uid, $t_array);
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg('Data:['.serialize($del_data)."\n".'SQL_REPLACE_ERROR:['.$e->getMessage().']'.' Message:['.$e->getTraceAsString().']', 'db_error');
		}		
		
		return $result;
	}		

	
	/**
	 * 数据更新
	 * @param $tbl
	 * @param $uid
	 * @param $t_array
	 * @return unknown_type
	 */
	public function _replace($tbl, $uid, $t_array)
	{
		$sqldata = array(
						'uid' => $uid,
						'bids' => serialize($t_array),
						);
						
		$keys = '`'.implode('`,`', array_keys($sqldata)).'`';
		$values = ':'.implode(',:', array_keys($sqldata));
		$sql = "REPLACE INTO `{$tbl}` ({$keys}) VALUES ({$values})";			
		$result = $this->query($sql, $this->wdb, $sqldata);	

		return $result;
	}

}