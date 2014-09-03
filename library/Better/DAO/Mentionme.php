<?php

/**
 * 提到我的
 *
 * @package Better.DAO
 * @author yangl
 *
 */

class Better_DAO_Mentionme extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'mention_me';
    	$this->priKey = 'mid';
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
	
	
	/**
	 * 根据uid取提到我的
	 */
	public function getMentionsByUid($params){
		$return = array(
			'count'=>0,
			'rows'=> array()
		);
		
		$uid = $params['uid'] ? $params['uid']: '';
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$page<=0 && $page = 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : 1;
		$pageSize<=0 && $pageSize = BETTER_PAGE_SIZE;
		
		if ($page*$pageSize>BETTER_MAX_LIST_ITEMS) {
			$page = intval(BETTER_MAX_LIST_ITEMS/$pageSize);
		}
		
		$sql = "SELECT bid, comment_id, uid, type, dateline FROM ".$this->tbl." WHERE uid=".$uid." 
				ORDER BY dateline DESC LIMIT " .($page-1)*$pageSize.", ".$pageSize."
		";
		
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		$return['rows'] = $rows;
		
		$sql = "select count(*) as count from ".$this->tbl." where uid=".$uid;
		$rs = self::squery($sql, $rdb);
		$count = $rs->fetch();
		$return['count'] = $count['count'];
		
		return $return;
	}
	
	
	/**
	 * 根据bid删除
	 */
	public function deleteByBid($bid){
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$this->wdb = &$cs['w'];
			
			$this->deleteByCond(array('bid'=>$bid));		
		}
		
		return true;
	}
	
	
	/**
	 * 根据reply id删除
	 */
	public function deleteByRid($id, $bid){
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$this->wdb = &$cs['w'];
			
			$this->deleteByCond(array('bid'=>$bid, 'comment_id'=>$id));		
		}
		
		return true;
	}
	
	
	/**
	 * 未读的提到我的数目
	 */
	public function newMentionmeCount($last_rt_mine=0){
		$sql = "SELECT COUNT(*) as count FROM ".$this->tbl." WHERE uid='".$this->identifier."' AND dateline>".$last_rt_mine;
		
		$rs = self::squery($sql, $this->rdb);
		$row = $rs->fetch();
		
		return $row['count'];
	}
	
}