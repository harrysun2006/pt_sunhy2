<?php

class Better_Admin_Administrators
{
	protected $uid = '0';
	protected $userInfo = array();
	protected static $instance = array();
	protected static $dao = null;
	
	private function __construct($uid)
	{
		$this->uid = $uid;
	}
	
	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		return self::$instance[$uid];
	}
	
	public static function login($username, $password)
	{
		$user = null;
		
		self::getDAO();
		$row = self::$dao->get(array(
			'username' => $username,
			'password' => $password,
			));

		if (!empty($row['uid'])) {
			$user = self::getInstance($row['uid']);
			Better_Registry::get('sess')->admin_uid = $row['uid'];
		}
		
		return $user;
	}
	
	public function logout()
	{
		Better_Registry::get('sess')->admin_uid = null;
	}
	
	protected static function getDAO()
	{
		if (self::$dao==null) {
			self::$dao = Better_DAO_Admin_Administrators::getInstance();
		}
		
		return self::$dao;
	}
	
	public function getInfo()
	{
		if ($this->uid) {
			$this->userInfo = self::getDAO()->get(array(
				'uid' => $this->uid
				));
		}
		
		return $this->userInfo;
	}
	
	/**
	 * 获得所有管理员
	 */
	public function getAdministrators($params){
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
			
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
			
		$rows=self::getDAO()->getAll();
		$data = array_chunk($rows, $pageSize);
		
		$return['count']=count($rows);
		$return['rows']=&$data[$page-1];
		unset($data);
		
		return $return;
	}
	
	/**
	 * 删除一条
	 */
	public function delAdministrators($vals){
		$result = false;
		
		self::getDAO();
		foreach ($vals as $val){
			self::$dao->delete($val);
		}
		
		$result = true;
		return $result;
	}
	
 	public function addLog($content, $type)
 	{
 		Better_DAO_Admin_Log::getInstance()->insert(array(
 			'admin_uid'=> $this->uid,
 			'dateline' => BETTER_NOW,
 			'content' => $content,
 			'act_type' => $type
 			));
 	}
 	
 	public function addUserLog($userInfo, $type, $content='')
 	{
 		$uid = $userInfo['uid'];
 		$username = $userInfo['username'];
 		
 		Better_DAO_Admin_Userlog::getInstance()->insert(array(
 			'uid' => $uid,
 			'admin_uid' => $this->uid,
 			'dateline' => BETTER_NOW,
 			'act_type' => $type,
 			'content' => $content,
 			'username' => $username,
 			));
 	}	
 	
 	public function addPoiLog($poi_id, $type, $content='')
 	{
 		Better_DAO_Admin_Poilog::getInstance()->insert(array(
 			'poi_id' => $poi_id,
 			'admin_uid' => $this->uid,
 			'dateline' => BETTER_NOW,
 			'act_type' => $type,
 			'content' => $content,
 			));
 	}	
}