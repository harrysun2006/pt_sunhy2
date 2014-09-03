<?php

/**
 * 用户阻止相关操作
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Block extends Better_User_Base
{
	protected static $instance = array();

	public $blocked = array();
	public $blockedby = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * 获取被哪些人阻止了
	 * 
	 * @return array
	 */
	public function getBlockedBy($force=false)
	{
		if ($force===true || count($this->blockedby)==0) {
			$this->blockedby = $this->user->cache()->get('blockedby');
			if (!Better_Config::getAppConfig()->relation_use_cache || !$this->blockedby) {
				$this->blockedby = array();
			}
			
			if (count($this->blockedby)==0) {
				$rows = Better_DAO_Blockedby::getInstance($this->uid)->getAll(array(
								'uid' => $this->uid,
								));
				foreach ($rows as $row) {
					if (!in_array($row['blocked_by_uid'], $this->blockedby)) {
						$this->blockedby[] = (string)$row['blocked_by_uid'];
					}
				}
			
				$this->blockedby = array_unique($this->blockedby);
				$this->user->cache()->set('blockedby', $this->blockedby);
			}
		}
		
		return $this->blockedby;		
	}
	
	/**
	 * 获取阻止了哪些人
	 * 
	 * @return array
	 */
	public function getBlocks($force=false)
	{
		if ($force===true || count($this->blocked)==0) {
			$this->blocked = $this->user->cache()->get('blocks');
			if (!Better_Config::getAppConfig()->relation_use_cache || !$this->blocked) {
				$this->blocked = array();	
			}
			
			if (count($this->blocked)==0) {
				$blocks = Better_DAO_Blocking::getInstance($this->uid)->getAll(array(
									'uid' => $this->uid,
									));
				
				foreach ($blocks as $row) {
					if (!in_array($row['blocking_uid'], $this->blocked)) {
						$this->blocked[] = (string)$row['blocking_uid'];
					}
				}
				
				$this->blocked = array_unique($this->blocked);
				$this->user->cache()->set('blocks', $this->blocked);
			}
		}
		
		return $this->blocked;
	}	
	
	/**
	 * 获取阻止用户的详细资料
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function &getBlocksWithDetail($page, $pageSize=BETTER_PAGE_SIZE)
	{
		$tmp = array(
						'rows' => array(),
						'pages' => 0,
						);
		$rows = array();
		$sessUid = Better_Registry::get('sess')->get('uid');
		$this->getUserInfo();
		
		if ($sessUid==$this->uid || ($sessUid!=$this->uid && ($this->userInfo['priv']=='public' || ($this->userInfo['priv']=='protected' && $this->user->isFriend($sessUid))))) {
			$this->getBlocks();

			if (count($this->blocked)>0) {
				$tmp = Better_DAO_Blocking::getInstance($this->uid)->getBlockingsDetail($page, $pageSize);
	
				foreach ($tmp['rows'] as $k=>$row) {
					$tmp['rows'][$k] = $this->user->parseUser($row);
				}
			}
		}

		return $tmp;		
	}
	
	/**
	 * 阻止一个用户
	 * 
	 * @param $uid
	 * @return integer
	 */
	public function add($uid, $from='', $msgid='')
	{
		$result = 0;
		
		if ($uid==BETTER_SYS_UID) {
			$result = -1;
		} else {
		
			if ($uid>0 && $uid!=$this->uid) {
				$blockingUser = Better_User::getInstance($uid);
				$blockingUserInfo = $blockingUser->getUser();
				
				if (!in_array($uid, $this->blocked)) {
					$from = $blockingUser->friends()->hasRequest($this->uid) ? 'friend_request' : $from;
					Better_DAO_Blocking::getInstance($this->uid)->insert(array(
						'uid' => $this->uid,
						'blocking_uid' => $uid,
						'dateline' => time(),
						));
						
					Better_DAO_Blockedby::getInstance($uid)->replace(array(
						'uid' => $uid,
						'blocked_by_uid' => $this->uid,
						'dateline' => time(),
						));

					$this->user->updateUser(array(
						'last_active' => time(),
						));

					$result = 1;
					
					Better_Hook::factory(array(
						'Karma','DirectMessage','Badge', 'User', 'Clean', 'Cache'
						))->invoke('BlockedSomebody', array(
						'uid' => $this->uid,
						'blocked_uid' => $uid,
						'from' => $from,
						'msgid' => $msgid
						));
				}
			}
		}
		
		return $result;		
	}
	
	/**
	 * 取消一个阻止
	 * 
	 * @param $uid
	 * @return integer
	 */
	public function delete($uid)
	{
		$result = 0;
		
		if ($uid>0) {
			$blockingUser = Better_User::getInstance($uid);
			$blockingUserInfo = $blockingUser->getUser();
			
			if (in_array($uid, $this->user->blocks)) {
				Better_DAO_Blocking::getInstance($this->uid)->deleteByCond(array(
					'uid' => $this->uid,
					'blocking_uid' => $uid,
					));
				
				Better_DAO_Blockedby::getInstance($uid)->deleteByCond(array(
					'uid' => $uid,
					'blocked_by_uid' => $this->uid,
					));

				Better_Hook::factory(array(
					'User', 'Karma', 'Cache'
				))->invoke('UnBlockSomebody', array(
					'uid' => $this->uid,
					'unblocked_uid' => $uid	
				));

				$result = 1;
			}
		}
		
		return $result;
	}
	
}