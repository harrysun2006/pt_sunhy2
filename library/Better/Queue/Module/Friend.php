<?php

/**
 * 
 * “好友动态”队列处理 一个好友就保存一条
 * 
 * @package Better.Queue.Module
 * @author fengj <fengj@peptalk.cn>
 *
 */
class Better_Queue_Module_Friend extends Better_Queue_Module_Base
{
	protected static $instance = null;
	protected $offset = 300;
	protected $passUids = array(10000);
	
	protected function __construct()
	{
		parent::__construct();	
		$this->offset = Better_Config::getAppConfig()->queue->publictimeline->offset;
	}
	
	public function __destruct()
	{
		
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 写入数据到队列
	 * 
	 * @see Better_Queue_Module_Base::push()
	 */
	public function push(array $data)
	{

	}
	
	public function pop(array $params)
	{

	}
	
	/**
	 * 队列处理方法
	 * 
	 * @see Better_Queue_Module_Base::cal()
	 */
	public function cal(array $params)
	{
		$result = false;

		switch ($params['act_type']) {
			//	发新贴（吼吼/签到/贴士） done
			case 'post':
			case '1':
				$result = $this->calPost($params);
				break;
				
			//	删除 done
			case 'delete':
			case '2':
				$result = $this->calDelete($params);
				break;
				
			//	被踢 done
			case 'banned':
			case '5':
				$result = $this->calBanned($params);
				break;

			//	取消好友 done
			case 'cancel_friend':
			case '7':
				$result = $this->calUnfriend($params);
				break;
				
			//	从被踢转换为正常 这个以前就没有处理 是不可能发生的 done
			case 'unbanned': 
			case '8':
				//$result = $this->calUnbanned($params);
				$result = true;
				break;
			
			//加好友 done
			case 'friend':
			case '10':
				$result = $this->calFriend($params);
				break;
				
			//设置首页显示该好友的动态 done
			case '11':
				$result = $this->calFriend($params);
				break;
				
			//设置首页不显示该好友的动态  done
			case '12':
				$result = $this->calUnfriend($params);
				break;
				
			default:
				$result = true;
				break;
		}
		
		return $result;
	}
	
	public function complete($id, $handleResult=1)
	{
		//要是挂了 我们也没有办法处理了 是不是记个日志就行了?
	}
	
	
	/**
	 * 
	 * 发新内容
	 * @param array $params
	 */
	protected function calPost(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$bid = trim($params['bid']);
		
		if ($uid==BETTER_SYS_UID) {
			$result = true;
		} else {
			$data = Better_Cache_Module_Blog::load($bid);
			if (!$data ) {
				return false;
			} 
			
			$uid = $data['uid'];
			
			$dateline = $data['dateline'] ? $data['dateline'] : time();
			$priv = $data['priv']=='private' ? 'private' : 'public';

			switch ($priv) {
				case 'public':
					$friendsUids = Better_User_Friends::getInstance($uid)->getFriendsWithHomeShow();
					$uids = array();
					if (count($friendsUids)>0) {
						$uids = array_unique($friendsUids);
					}
					
					if (is_array($uids) && count($uids)>0) {
						foreach ($uids as $todoUid) {
							Better_DAO_User_Friendstatus::getInstance($todoUid)->replace(array(
								'uid' => $todoUid,
								'blog' => $data
								));
						}
						
						$result = true;
					}
					break;

				case 'private':
				default:
					$result = true;
					break;
			}
		} 
		
		return $result;
	}
	
	/**
	 * 
	 * 删除内容
	 * 
	 * @param array $params
	 */
	protected function calDelete(array $params)
	{
		$result = false;
		
		$bid = trim($params['bid']);
		$uid = $params['uid'];
		
		if ($uid==BETTER_SYS_UID) {
			$result = true;
		} else {
			$friendsUids = Better_User::getInstance($uid)->friends; //取全部好友
			$uids = array();
			if (count($friendsUids)>0) {
				$uids = array_unique($friendsUids);
			}
			if (is_array($uids) && count($uids)>0) {
				foreach ($uids as $todoUid) {
					Better_DAO_User_Friendstatus::getInstance($todoUid)->clean($todoUid, $params, 'bid');
				}
			}
	
			$result = true;
		}
		
		return $result;
	}

	/**
	 * 
	 * 成为好友
	 * @param array $params
	 */
	protected function calFriend(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		if ( $friendUid == BETTER_SYS_UID ) {
			return true;	
		}
		
		$blog = Better_DAO_User_Friendstatus::getInstance($friendUid)->getUserStatus($friendUid, 1, true);
		Better_DAO_User_Friendstatus::getInstance($uid)->replace(array(
			'uid' => $uid,
			'blog' => $blog[0],
			));		
		
		$result = true;
		return $result;		
	}

	
	/**
	 * 
	 * 解除好友关系
	 * 
	 * @param array $params
	 * $params['uid'] 是要被处理的uid  
	 * $params['friend_uid']  是要被删除的uid
	 */
	protected function calUnfriend(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		$result = Better_DAO_User_Friendstatus::getInstance($uid)->clean($uid, array('uid'=>$friendUid), 'uid');
		
		return $result;
	}	
	
	/**
	 * 
	 * 账号被封
	 * 
	 * @param array $params
	 */
	protected function calBanned(array $params)
	{
		$result = false;
		
		$uid = $params['uid'];
		$friendsUids = Better_User::getInstance($uid)->friends; //取全部好友
		
		foreach ($friendsUids as $friendUid) {
			$this->calUnfriend(array(
								'uid' => $uid,
								'friend_uid' => $friendUid
								));
		}
		
		$result = true;
		return $result;
	}
	
	

	
	
	/**
	 * 
	 * 账号解封
	 * 
	 * @param array $params
	 */
	protected function calUnbanned(array $params)
	{
		$result = true;		
		return $result;
	}
	
	/**
	 * 
	 * 隐私保护
	 * 
	 * @param array $params
	 */
	protected function calSetProtected(array $params)
	{
		$result = false;				
		return $result;
	}
	
	/**
	 * 
	 * 解除隐私保护
	 * @param array $params
	 */
	protected function calSetPublic(array $params)
	{
		$result = false;	
		return $result;
	}
	
	
	/**
	 * 设置显示好友动态
	 * @param $params
	 */
	protected function calFriendHomeShow(array $params)
	{
		$result = true;
		return $result;
	}
	
	
	/**
	 * 设置不显示好友的动态
	 * @param $params
	 */
	protected function calFriendUnHomeShow(array $params)
	{
		$result = true;
		return $result;
	}
	
	
	protected function log(array $params)
	{
		$type = $params['type'];
		$uid = $params['uid'];
		$content = $params['content'];
		
		Better_Log::getInstance()->logInfo($type.'|'.$uid.'|'.$content, 'new_pt_debug', true);
	}
}