<?php

/**
 * 最新访客
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Visit extends Better_User_Base
{
	protected static $instance = array();
	protected $visitors = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * 将数据库中的访客信息解析成数组
	 * 
	 * @param $data
	 * @return array
	 */
	public static function parse($data)
	{
		$arr = unserialize($data);

		return $arr;
	}
	
	/**
	 * 将访客数组转换成数据库文字
	 * 
	 * @param $data
	 * @return string
	 */
	public static function deParse(&$data)
	{
		$str = serialize($data);
		
		return $str;
	}
	
	/**
	 * 增加访客记录
	 * 
	 * @param $uid
	 * @return unknown_type
	 */
	public function add($uid)
	{
		$this->getUserInfo();
		$this->getVisitors();
		
		if (($uid!=$this->uid && (!isset($this->visitors[$uid]) || (isset($this->visitors[$uid]) && time()-$this->visitors[$uid]>3600*24))) 
			||
			($uid!=$this->uid && isset($this->visitors[$uid]) && (time()-$this->visitors[$uid]>3600*24))
			) {
			$this->visitors[$uid] = time();
			arsort($this->visitors);
			$tmp = array_chunk($this->visitors, 18, true);
			$this->user->updateUser(array(
				'visits' => $tmp[0]	,
				));
		}
	}
	
	/**
	 * 
	 * 网页右边栏
	 */
	public function rightbar()
	{
		$rows = array();
		$this->getUserInfo();
		
		if (count($this->visitors)==0) {
			$this->visitors = self::parse($this->userInfo['visits']);
		}

		$this->visitors = (array)$this->visitors;
		$tmp0 = array_chunk($this->visitors, 14, true);
		$this->visitors = (array)$tmp0[0];

		if (is_array($this->visitors) && count($this->visitors)>0) {
			$tmp = array();
			$vuids = array_keys($this->visitors);
			if ($vuids) {
				$vs = Better_DAO_User_Visitors::getInstance($this->uid)->rightbar($vuids);
				$_vs  = @array_flip($this->visitors);
				krsort($_vs);
				foreach ($_vs as $uid) {
					if ($vs[$uid]) {
						$row = $vs[$uid];
						$row['avatar_normal'] = $row['avatar_url'] = $this->user->getUserAvatar('normal', $row);
						$row['avatar_small'] = $this->user->getUserAvatar('thumb', $row);
						$row['avatar_tiny'] = $this->user->getUserAvatar('tiny', $row);						
						$rows[] = $row;
					}
				}
			}
		}

		return $rows;
	}
	
	/**
	 * 获取所有访客
	 * 
	 * @return array
	 */
	public function &getVisitors()
	{
		return $this->rightbar();
	}
	
}