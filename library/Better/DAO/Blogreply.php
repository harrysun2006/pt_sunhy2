<?php

/**
 * 微博客回复相关数据操作
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Blogreply extends Better_DAO_Base
{
	
	private static $instance = array();
	
	private $profileTbl = '';
	private $attachTbl = '';

 	/**
   	*
    */
    public function __construct($identifier = 0) {
		$this->tbl = BETTER_DB_TBL_PREFIX.'blog_reply';
		$this->priKey = 'id';
		$this->orderKey = 'dateline';
		
		parent::__construct ($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Blogreply($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	
	/**
	 * 获取某个消息的所有回复
	 *
	 * @param string $bid
	 * @return array
	 */
	public function getRepliesByBid($bid, $page=1, $pageSize=20)
	{
		$rows = array();
		
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS b', array(
			'b.id', 'b.bid', 'b.uid', 'b.message', 'b.dateline'
			));
		$select->where('b.bid=?', $bid);
		if(Better_Registry::get('sess') && Better_Registry::get('sess')->get('uid')){
			$select->where('b.checked=1 OR b.uid=?', Better_Registry::get('sess')->get('uid'));
		}else{
			$select->where('b.checked=1');
		}
		$select->order('b.dateline DESC');
		if ($page*$pageSize>BETTER_MAX_LIST_ITEMS) {
			$page = intval(BETTER_MAX_LIST_ITEMS/$pageSize);
		}
		$select->limitPage($page, $pageSize);

		$rs = $this->query($select);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
	
	
	/**
	 * 某id评论数量
	 */
	public function getRepliesCount($bid){
		if(Better_Registry::get('sess') && Better_Registry::get('sess')->get('uid')){
			$sql = "select count(*) as count from ".$this->tbl." where bid='".$bid."' and (checked=1 OR uid=".Better_Registry::get('sess')->get('uid').")";
		}else{
			$sql = "select count(*) as count from ".$this->tbl." where bid='".$bid."' and checked=1";
		}
		
		$rs = $this->query($sql, $this->rdb);
		$data = $rs->fetch();
		
		return $data['count'];
	}
	
	
	/**
	 * 设为需要审核
	 */
	public static function setNeedCheck($id, $blog_uid, $needCheck=true)
	{
		Better_DAO_Blogreply::getInstance($blog_uid)->update(array(
			'checked' => $needCheck==true ? '0' : '1',
			), $id);
	}

	/**
	 *  获得一条评论
	 */
	public function get($id){
		$row = parent::get($id);
		$row['message'] = stripslashes($row['message']);
		
		return $row;
	}
	
}

?>