<?php

/**
 * 用户相关逻辑处理
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_User
{

	/**
	 * 用户uid
	 *
	 * @var integer
	 */
	private $_uid = 0;
	
	/**
	 * 用户详细资料
	 *
	 * @var array
	 */
	private $_userInfo = array();
	
	/**
	 * 用户状态
	 * 
	 * @var string
	 */
	private $_status = '';
	
	/**
	 * 用户关注的人（uids）
	 *
	 * @var array
	 */
	private $_followings = array();
	
	/**
	 * 用户的粉丝（uids）
	 *
	 * @var array
	 */
	private $_followers = array();
	
	/**
	 * 用户黑名单中的人（uids）
	 *
	 * @var array
	 */
	private $_blocks = array();
	
	private static $instance = array();
	private $_newInfo = array();
	
	/**
	 * Dao实例
	 *
	 * @var Better_DAO
	 */
	protected $dao = null;
	
	const CELL_PAT = '/^(130|131|132|133|134|135|136|137|138|139|150|151|152|153|154|155|156|157|158|159|183|184|185|186|187|188|189)([0-9]{8})/';
	
	/**
	 * 已经分析过的用户
	 * 由于一个页面中同一用户资料可能会被调用多次，所以将分析过后的结果缓存在该静态变量中，避免不必要的重复分析
	 *
	 * @var array
	 */
	public static $parsedUsers = array();
	public static $parsedAvatars = array();
	
	private $_toUpdateData = array();
	
	public $follow = null;
	public $block = null;
	public $blog = null;
	
	protected $_pro = array();
	protected $_relation = array();
	
	public $timezone = 8;

	private function __construct($uid)
	{
		if (is_numeric($uid)) {
			$this->_uid = $uid;
		} else if (is_array($uid)) {
			$this->_userInfo = $uid;
			$this->_uid = $uid['uid'];
		} else if (Better_Functions::checkEmail($uid)) {
			//
		} else if (preg_match('/^([a-zA-Z0-9\-_]+)$/is', $uid)) {
			//
		} else {
			$this->_uid = 0;
		}

	}
	
	/**
	 * 析构器
	 * 有些低优先级的用户资料更新
	 * 
	 * @return unknown_type
	 */
	function __destruct()
	{
		if (count($this->_toUpdateData)>0) {
			$this->updateUser($this->_toUpdateData, true);
		}
	}

	/**
	 * 获取用户实例
	 *
	 * @param misc $uid
	 * @return Better_User
	 */
	public static function getInstance($uid=0, $key='uid')
	{
		$_uid = 0;
		$userInfo = array();

		switch($key) {
			case 'username':
				$userInfo = Better_DAO_User::searchUser($uid, 'p.username');
				break;
			case 'nickname':
				$userInfo = Better_DAO_User::searchUser($uid, 'p.nickname');
				break;
			case 'cell':
			case 'cell_no':
				$userInfo = Better_DAO_User::searchUser($uid, 'a.cell_no');
				break;
			case 'msn':
				$userInfo = Better_DAO_User::searchUser($uid, 'p.msn');
				break;
			case 'email':
				$userInfo = Better_DAO_User::searchUser($uid, 'a.email');
				break;
			case 'uid':
			default:
				if (is_numeric($uid)) {
					$_uid = $uid;
				} else if (is_array($uid)) {
					$_uid = $uid['uid'];
				}
				break;
		}		
		
		isset($userInfo['uid']) && $_uid = $userInfo['uid'];

		if (!isset(self::$instance[$_uid])) {
			self::$instance[$_uid] = new self($_uid);
			if (isset($userInfo['uid']) && $userInfo['avatar_url']) {
				self::$instance[$_uid]->_userInfo = $userInfo;
			}
		}

		return self::$instance[$_uid];
	}
	
	/**
	 * 
	 * 销毁某个实例
	 * 由于后端运行脚本长时间驻留内存，而某个时间段内，用户的数据已经发生了变化，这时候采用单例模式就无法获得正确的用户数据，
	 * 所以有必要在某些时候销毁一些用户实例，以便程序自动从数据库中重新读取数据
	 * 
	 * @param unknown_type $uid
	 */
	public static function destroyInstance($uid)
	{
		if (isset(self::$instance[$uid])) {
			unset(self::$instance[$uid]);
			unset(self::$parsedUsers[$uid]);
			unset(self::$parsedAvatars[$uid]);
		}	
	}
	
	/**
	 * 
	 * 销毁所有用户实例
	 * 
	 * @return null
	 */
	public static function destroyAllInstances()
	{
		foreach (self::$instance as $uid=>$user) {
			unset(self::$instance[$uid]);
		}
		
		self::$instance = array();
	}
	
	/**
	 * 
	 * 魔术方法__get
	 * 为了减少不必要的数据库查询，如果使用该方法来读取、设置某些用户属性，这些属性的数据只有在被使用时才会触发数据库查询操作
	 * 
	 * @param unknown_type $key
	 */
	public function __get($key)
	{
		$return = '';
		switch ($key) {
			case 'friends':
				if (!isset($this->_pro['friends'])) {
					$this->_pro['friends'] = $this->friends()->getFriends(true);
				}
				
				$return = &$this->_pro['friends'];
				break;
			case 'followings':
				if (!isset($this->_pro['followings'])) {
					$this->_pro['followings'] = $this->follow()->getFollowings();
				}
				
				$return = &$this->_pro['followings'];
				break;
			case 'followers':
				if (!isset($this->_pro['followers'])) {
					$this->_pro['followers'] = $this->follow()->getFollowers();
				}
				
				$return = &$this->_pro['followers'];
				break;
			case 'blocks';
				if (!isset($this->_pro['blocks'])) {
					$this->_pro['blocks'] = $this->block()->getBlocks();
				}
				
				$return = &$this->_pro['blocks'];
				break;
			case 'blockedby':
				if (!isset($this->_pro['blockedby'])) {
					$this->_pro['blockedby'] = $this->block()->getBlockedBy();
				}
				
				$return = &$this->_pro['blockedby'];
				break;
			case 'favorites':
				if (!isset($this->_pro['favorites'])) {
					$this->_pro['favorites'] = $this->favorites()->getAllBids();
				}
				
				$return = &$this->_pro['favorites'];
				break;
			case 'visitors':
				if (!isset($this->_pro['visitors'])) {
					$this->_pro['visitors'] = $this->visit()->getVisitors();
				}
				$return = &$this->_pro['visitors'];
				break;
			case 'pendding':
				if (!isset($this->_pro['pendding'])) {
					$this->_pro['pendding'] = $this->friends()->getPenddingRequests();
				}
				$return = &$this->_pro['pendding'];
				break;
			default:
				$return = isset($this->_userInfo[$key]) ? $this->_userInfo[$key] : '';
				break;
		}
		
		return $return;
	}
	
	public function __set($key, $val)
	{
		switch ($key) {
			case 'followings':
			case 'followers':
			case 'blocks':
			case 'blockedby':
			case 'favorites':
			case 'visitors':
			case 'friends':
			case 'pendding':
				$this->_pro[$key] = $val;
				break;
			default:
				$this->_userInfo[$key] = $val;
				break;
		}
	}
	
	public function __call($method, $params)
	{
		$className = 'Better_User_'.ucfirst($method);
		
		if (class_exists($className)) {
			return call_user_func($className.'::getInstance', $this->_uid);
		} else {
			return null;
		}
	}
	
	public function push($key, $val)
	{
		$this->__get($key);
		if (!in_array($val, $this->_pro[$key])) {
			$this->_pro[$key][] = $val;
			if (is_array($this->_userInfo[$key])) {
				$this->_userInfo[$key][] = $val;
			}
		}
	}
	
	public function clean($key, $val)
	{
		$this->__get($key);
		$this->_pro[$key] = array_diff($this->_pro[$key], (array)$val);
		if (is_array($this->_userInfo[$key])) {
			$this->_userInfo[$key] = array_diff($this->_userInfo[$key], (array)$val);
		}
	}
	
	/**
	 * 获取用户id
	 * 
	 * @return integer
	 */
	public function getUid()
	{
		return $this->_uid;
	}
	
	/**
	 * 根据某个用户字段读取用户的uid
	 * 
	 * @param $key
	 * @param $val
	 * @return integer
	 */
	public static function search($key, $val)
	{
		return Better_DAO_User::getUidByKey($key, $val);
	}
	
	/**
	 * 获取当前用户详细资料
	 *
	 * @param $inLogin 由于parseUser方法里面会根据session中的uid来进行一些逻辑运算，这可能导致用户在登录取资料时
	 * 取不到正确、完整的自我资料
	 * @return array
	 */
	public function &getUser($key='uid',$val='', $inLogin=false)
	{
		if (!isset($this->_userInfo['uid']) || !$this->_userInfo['uid'] || !isset($this->_userInfo['username'])) {
			switch($key) {
				case 'email':
					$data = Better_DAO_User::getInstance()->getByKey($val, 'email');
					$data['uid'] ? $this->_userInfo = $this->parseUser($data, $inLogin) : $this->_userInfo = array('uid'=>0);
					break;
				case 'username':
					$data = Better_DAO_User::getInstance()->getByKey($val, 'username');
					$data['uid'] ? $this->_userInfo = $this->parseUser($data, $inLogin) : $this->_userInfo = array('uid'=>0);
					break;
				case 'uid':
				default:
					if ($this->_uid>0) {
						$data = Better_DAO_User::getInstance($this->_uid)->get($this->_uid);

						if ($data['uid']) {
							$this->_userInfo = $this->parseUser($data, $inLogin);
						} else {
							$this->_userInfo = array('uid'=>0);
						}
					}
					break;
			}
		}

		$this->_userInfo['email'] = strtolower($this->_userInfo['email']);
		return $this->_userInfo;
	}

	
	/**
	 * 分析用户资料
	 * 将数据库中的数据解析成可供直接调用的数据
	 *
	 */
	public function parseUser($data=array(), $inLogin=false, $force=false, $visitor_force=false)
	{
		if ($force===true) {
			$data = Better_DAO_User::getInstance($this->_uid)->get($this->_uid);
		} else if (count($data)==0) {
			$data = &$this->_userInfo;
		} 

		$uid = $data['uid'];
		$sessUid = Better_Registry::get('sess') ? Better_Registry::get('sess')->getUid() : 0;

		if ($visitor_force==true || $force===true || !isset(self::$parsedUsers[$uid]) || !self::$parsedUsers[$uid]['avatar_normal']) {
			

			if ($uid==$this->_uid) {
				$this->timezone = $data['timezone']=='' ? 8 : $data['timezone'];
				$this->language = $data['language'];
			}
			$data['language']='zh-cn';
			$data['username']=='' && $data['username'] = 'kai'.$data['uid'];
			list($data['karma_main'], $data['karma_dot']) = explode('.', Better_Rp::format($data['rp'])); 
		
			//	头像
			$data['avatar_normal'] = $data['avatar_url'] = $this->getUserAvatar('normal', $data);
			$data['avatar_small'] = $this->getUserAvatar('thumb', $data);
			$data['avatar_tiny'] = $this->getUserAvatar('tiny', $data);
			$data['avatar_huge'] = $this->getUserAvatar('huge', $data);

			// 最后的状态信息
			$data['status'] = self::filterStatus($data);

			// 位置
			$data['location_tips'] = self::filterLocation($data);

			if ($data['last_checkin_poi']) {
				$data['poi'] = Better_Poi_Info::getInstance($data['last_checkin_poi'])->getBasic();
				if($data['poi'] && $data['poi']['closed']){
					$data['city'] = '';
				}
			} 			

			//	经纬度信息
			if ($inLogin==true || ($sessUid && $sessUid==$data['uid'])) {
				list($data['lon'], $data['lat']) = Better_Functions::XY2LL($data['x'], $data['y']);
				list($data['user_lon'], $data['user_lat']) = Better_Functions::XY2LL($data['user_x'], $data['user_y']);
			} else if (($sessUid>0 && $sessUid!=$data['uid']) || !$sessUid) {
				list($data['lon'], $data['lat']) = Better_Functions::XY2LL($data['x'], $data['y']);
				list($data['user_lon'], $data['user_lat']) = Better_Functions::XY2LL($data['user_x'], $data['user_y']);
			} else {
				list($data['lon'], $data['lat']) = Better_Functions::XY2LL($data['x'], $data['y']);
			}
			
			//	隐私
			/*if ($data['priv_blog']=='1' || $data['sys_priv_blog']=='1') {
				$data['priv'] = 'protected';
			} else {
				$data['priv'] = 'public';
			}*/
			//2010-01-31 yangl 改为全部锁上
			$data['priv_blog'] = $data['sys_priv_blog'] = 1;
			$data['priv'] = 'protected';
			$data['followers'] = 0;
			$data['followings'] = 0;

			self::$parsedUsers[$uid] = &$data;
		}

		return self::$parsedUsers[$uid];
	}
	
	/**
	 * 解析用户全部信息，包括掌门详情、勋章详情等
	 * 
	 * @param $data
	 * @return array
	 */
	public function parseUserFull()
	{
		$sessUid = Better_Registry::get('sess') ? Better_Registry::get('sess')->getUid() : 0;
		
		//if ($sessUid==$this->_uid || ($sessUid!=$this->_uid && ($this->_userInfo['priv']=='public' || ($this->_userInfo['priv']=='protected' && $this->isFriend($sessUid))))) {
			if (!isset(self::$parsedUsers[$this->_uid]['majors_detail'])) {
				$majors = $this->major()->getAll(1, 100);
				
				self::$parsedUsers[$this->_uid]['majors_detail'] = (array)$majors['rows'];
				self::$parsedUsers[$this->_uid]['badges_detail'] = (array)$this->badge()->getMyBadges();
				self::$parsedUsers[$this->_uid]['treasures_detail'] = (array)$this->treasure()->getMyTreasures();
				self::$parsedUsers[$this->_uid]['pings'] = (array)$this->ping()->pingOns();
	
				$ims = $sns = array();
				if (self::$parsedUsers[$this->_uid]['msn']) {
					$ims[] = array(
						'name' => 'msn',
						'account' => self::$parsedUsers[$this->_uid]['msn'],
						);
				}
				if (self::$parsedUsers[$this->_uid]['gtalk']) {
					$ims[] = array(
						'name' => 'gtalk',
						'account' => self::$parsedUsers[$this->_uid]['gtalk'],
						);
				}			
				
				$rows = $this->syncsites()->getSites();
				foreach ($rows as $protocol=>$row) {
					$sns[] = array(
						'name' => $protocol,
						'account' => $row['username'],
						);
				}
				
				self::$parsedUsers[$this->_uid]['ims'] = $ims;
				self::$parsedUsers[$this->_uid]['sns'] = $sns;
			}		
		//}		

		
		return self::$parsedUsers[$this->_uid];
	}	
	
	/**
	 * 根据用户名取用户资料
	 *
	 * @param $username
	 * @return array
	 */
	public function getUserByUsername($username)
	{
		$data = Better_DAO_User::getInstance()->getByKey($username, 'username');

		if ($data['uid']) {
			$this->_uid = $data['uid'];
			$this->_userInfo = $this->parseUser($data);
		} else {
			$this->_userInfo = array('uid'=>0);
		}
		
		return $this->_userInfo;
	}
	
	/**
	 * 根据昵称取用户资料
	 * 
	 * @param $nickname
	 * @return array
	 */
	public function getUserByNickname($nickname)
	{
		$data = Better_DAO_User::getInstance()->getByKey($nickname, 'nickname');
		
		if ($data['uid']) {
			$this->_uid = $data['uid'];
			$this->_userInfo = $this->parseUser($data);
		} else {
			$this->_userInfo = array('uid' => 0);
		}
		
		return $this->_userInfo;
	}
	
	/**
	 * 根据手机号码取用户资料
	 *
	 * @param string $cell
	 * @return array
	 */
	public function getUserByCell($cell)
	{
		$data = Better_DAO_User::getInstance()->getByKey($cell, 'cell_no');

		if ($data['uid'] && $data['username']) {
			$this->_uid = $data['uid'];
			$this->_userInfo = $this->parseUser($data);
		} else {
			$this->_userInfo = array('uid'=>0);
		}
		
		return $this->_userInfo;
	}

	/**
	 * 根据MSN号码取用户资料
	 *
	 * @param string $cell
	 * @return array
	 */
	public function getUserByMsn($msn)
	{
		$data = Better_DAO_User::getInstance()->getByKey($msn, 'msn');

		if ($data['uid']) {
			$this->_uid = $data['uid'];
			$this->_userInfo = $this->parseUser($data);
		} else {
			$this->_userInfo = array('uid'=>0);
		}
		
		return $this->_userInfo;
	}
	
	/**
	 * 根据Email取用户资料
	 *
	 * @param string $email
	 * @return Better_User
	 */
	public static function getUserByEmail($email)
	{
		$dao = new Better_DAO_User();
		$data = $dao->getByKey($email, 'email');
		$user = null;

		if ($data['uid']) {
			$user = self::getInstance($data);
		}

		return $user;
	}

	/**
	 * 返回用户资料的数组
	 *
	 * @return array
	 */
	public function &getUserInfo()
	{
		if (isset($this->_userInfo['uid']) && $this->_userInfo['uid'] && $this->_userInfo['nickname'] && $this->_userInfo['username'] && $this->_userInfo['avatar_normal']) {
			return $this->_userInfo;
		} else {
			return $this->getUser();
		}
	}
	
	/**
	 * 取用户头像地址
	 *
	 */
	public function getUserAvatar($size='normal', $data=array())
	{
		static $defaultAvatarUrl = '';

		if ($defaultAvatarUrl=='') {
			$defaultAvatarUrl = Better_Config::getAttachConfig()->global->avatar->default_url;
		}
	
		$avatar = '';
		if (count($data)==0) {
			$data = &$this->_userInfo;
		}

		$cacher = Better_Cache::remote();
		if ($data['uid']>0) {
			$uid = $data['uid'];
			$avatar = '';
			
			self::$parsedAvatars[$uid] = $cacher->get('kai_user_avatar_'.$uid);
			if (isset(self::$parsedAvatars[$uid][$size]) && self::$parsedAvatars[$uid][$size]) {	
				$avatar = self::$parsedAvatars[$uid][$size];
			} else if ($data['avatar'] || $data['file_id']) {
				$tmp = ($data['avatar'] ? $data['avatar'] : $data['file_id']) ? Better_User_Avatar::getInstance($uid)->parse($data) : Better_User_Avatar::getInstance($uid)->parse();

				self::$parsedAvatars[$uid]['tiny'] = $tmp['tiny'];
				self::$parsedAvatars[$uid]['normal'] = $tmp['url'];
				self::$parsedAvatars[$uid]['thumb'] = $tmp['thumb'];
				self::$parsedAvatars[$uid]['huge'] = $tmp['huge'];

				$cacher->set('kai_user_avatar_'.$uid, self::$parsedAvatars[$uid]);

				switch($size) {
					case 'thumb':
					case 'small':
						$avatar = $tmp['thumb'];
						break;
					case 'tiny':
						$avatar = $tmp['tiny'];
						break;
					case 'huge':
						$avatar = $tmp['huge'];
						break;
					case 'normal':
					default:
						$avatar = $tmp['url'];
						break;
				}
			} else {
				self::$parsedAvatars[$uid]['thumb'] = self::$parsedAvatars[$uid]['huge'] = self::$parsedAvatars[$uid]['tiny'] = self::$parsedAvatars[$uid]['normal'] = $avatar = $defaultAvatarUrl;
				$cacher->set('kai_user_avatar_'.$uid, self::$parsedAvatars[$uid]);
			}

		}

		return $avatar;
	}


	/**
	 * 检测用户名（个性域名）
	 *
	 * @param $username
	 * @return integer
	 */
	public static function validUsername($username)
	{
		$code = 0;
		$username = trim($username);
		
		if ($username=='') {
			$code = 1;
		} else if (Zend_Validate::is($username, 'Digits')) {
			$code = 2;
		} else if (!Zend_Validate::is($username, 'Alnum')) {
			$code = 3;
		} else if (strlen($username)>20) {
			$code = 4;
		} else if (Better_User_Exists::getInstance()->username($username, Better_User_Exists::PROFILE)) {
			$code = 5;
		} else if (preg_match('/^user([0-9]+)$/is', $username)) {
			$code = 7;
		} else {
			$excluded_controllers = explode('|', Better_Config::getAppConfig()->routes->exclude_controllers);
			if (in_array($username, $excluded_controllers)) {
				$code = 6;
			}
		}
		
		return $code;
	}

	/**
	 * 更新用户资料
	 *
	 * @param $data
	 * @return bool
	 */
	public function updateUser($data, $now=true, $admin_id='')
	{
		$result = 1;
		$newEmail = '';
		
		if ($now===false) {
			$this->_toUpdateData = array_merge($this->_toUpdateData, $data);
			$this->_userInfo = array_merge($this->_userInfo, $data);
			self::$parsedUsers[$this->_uid] = array_merge(self::$parsedUsers[$this->_uid], $data);
		} else {
			if (count($this->_toUpdateData)) {
				$data = array_merge($this->_toUpdateData, $data);
			}
			
			$rows = array();
			$rows['uid'] = $this->_uid;
			$this->getUser();
			
			$hooks = array();
			
			if (isset($data['username']) && $data['username']!=$this->_userInfo['username']) {
				$rows['username'] = $data['username'];
				$hooks[] = 'Filter';
			}
			
			if (isset($data['nickname']) && $data['nickname']!=$this->_userInfo['nickname']) {
				$rows['nickname'] = $data['nickname'];
				$hooks[] = 'Filter';
			}
			
			if (isset($data['self_intro']) && $data['self_intro']!=$this->_userInfo['self_intro']) {
				$rows['self_intro'] = $data['self_intro'];
				$hooks[] = 'Filter';
			}

			if (isset($data['live_province']) && $data['live_province']!=$this->_userInfo['live_province']) {
				$rows['live_province'] = $data['live_province'];
				
			}
			
			if (isset($data['live_city']) && $data['live_city']!=$this->_userInfo['live_city']) {
				$rows['live_city'] = $data['live_city'];
				
			}
			
			if (isset($data['avatar']) && $data['avatar']!=$this->_userInfo['avatar']) {
				$rows['avatar'] = $data['avatar'];
			}
	
			if (isset($data['priv_blog']) && $data['priv_blog']!=$this->_userInfo['priv_blog']) {
				$rows['priv_blog'] = $data['priv_blog'];
			}
			
			if (isset($data['priv_place']) && $data['priv_place']!=$this->_userInfo['priv_place']) {
				$rows['priv_place'] = $data['priv_place'];
			}
			if (isset($data['sys_priv_blog']) && $data['sys_priv_blog']!=$this->_userInfo['sys_priv_blog']) {
				$rows['sys_priv_blog'] = $data['sys_priv_blog'];
			}
	
			if (isset($data['visits']) && is_array($data['visits'])) {
				$rows['visits'] = Better_User_Visit::deParse($data['visits']);
			}

			if (isset($data['email']) && $data['email']!=$this->_userInfo['email']) {	
				if ($data['emailBind'] == 1) {
					$rows['email'] = $data['email'];
					unset($data['emailBind']);
					$hooks[] = 'Email';
				} else {
					$todayCnt = 0;
					$cacher = Better_Cache::remote();
					$cacheKey = md5('kai_setting_email_cnt_'.$this->_uid);	
					$todayCnt = intval($cacher->get($cacheKey));
					if ($todayCnt < 3) {
						$newEmail = $data['email'];
//						$rows['enabled'] = '0';
						$rows['email'] = $this->_userInfo['email'];
//						$rows['state'] = Better_User_State::UPDATE_VALIDATING;
						$todayCnt++;
						$lasttime = strtotime(date('Y-m-d')) + 86400 - time();
						$cacher->set($cacheKey, $todayCnt, $lasttime);
						$hooks[] = 'Email';
					} else {
						unset($data['email']);
					}
				}
			}
			
			if (!empty($data['lon']) && !empty($data['lat'])) {
				$rows['lon'] = $data['lon'];
				$rows['lat'] = $data['lat'];
				
				$geo = new Better_Service_Geoname();
				$info = $geo->getGeoName($data['lon'], $data['lat']);
				$city = '';
	
				if ($info!=false && is_array($info)) {
					$city = $data['city']=='' ? $info['name'] : $data['city'];
					$address = $info['r1'].$info['r2'].'附近';
					if ($data['address']=='') {
						$rows['address'] = $address;
					}
					
					$rows['city'] = $city;
				} else {
					$rows['city'] = $city = '未知城市';
					$rows['address'] = $address = '未知位置';
				}
				$rows['lbs_report'] = time();
	
				list($rows['x'], $rows['y']) = Better_Functions::LL2XY($data['lon'], $data['lat']);
				unset($data['lon']);
				unset($data['lat']);
			}
			
			//2011-4-14 karma为负不禁言
			/*if (isset($data['karma']) && $data['karma']<0 && $this->_userInfo['karma']>=0) {
				$data['state'] = 'mute';
				
				if(!$admin_id){
					Better_DAO_Admin_Banaccountlog::getInstance()->insert(
					array('admin_uid'=>'auto',
						'uid'=> $this->_uid,
						'old_state'=>$this->_userInfo['state'],
						'now_state'=>'mute',
						'dateline'=>time(),
						'act_type'=>'mute_account',
						'reason'=>''
					)
					);
				}
			} else if (isset($data['karma']) && $data['karma']>=0 && $this->_userInfo['karma']<0) {
				$now_state = Better_DAO_Admin_Banaccountlog::getInstance()->getOldState($this->_uid, Better_User_State::MUTE);
				$now_state = $now_state? $now_state: 'enabled';
				$data['state'] = $now_state;
				
				if(!$admin_id){
					Better_DAO_Admin_Banaccountlog::getInstance()->insert(
					array('admin_uid'=>'auto',
						'uid'=>$this->_uid,
						'old_state'=>'mute',
						'now_state'=>$now_state,
						'dateline'=>time(),
						'act_type'=>'unmute_account',
						'reason'=>''
					)
					);
				}
			}*/
			
			if (isset($data['language'])) {
				$allowed = array('en', 'zh-cn');
				if (in_array($data['language'], $allowed)) {
					$rows['language'] = $data['language'];
				}
			}
			
			(isset($data['state']) && Better_User_State::isValidState($data['state'])) && $s['state'] = $data['state'];
	
			(isset($rows['username']) || isset($rows['nickname']) || isset($rows['self_intro']) || isset($rows['avatar'])) && $rows['last_update']=time();//增加最后一次更新时间
			
			$rows = array_merge($data, $rows);

			if ($this->_userInfo['gender']=='male' || $this->_userInfo['gender']=='female') {
				unset($rows['gender']);
			}
			
			//2011-2-17	隐私
			if (isset($data['allow_rt']) && $data['allow_rt']!=$this->_userInfo['allow_rt']) {
				$rows['allow_rt'] = $data['allow_rt'];
				//清缓存
				$cacher = Better_Cache::remote();
			    $bids = Better_DAO_User_Status::getInstance($this->_uid)->getBidsByuid($this->_uid);
			    
			    foreach($bids as $bid){
			    	$cacheKey = md5('kai_blog_bid_'.$bid['bid']);
			    	$cacher->set($cacheKey, null);
			    }
			}
			if (isset($data['friend_sent_msg']) && $data['friend_sent_msg']!=$this->_userInfo['friend_sent_msg']) {
				$rows['friend_sent_msg'] = $data['friend_sent_msg'];
			}
			if (isset($data['sync_badge']) && $data['sync_badge']!=$this->_userInfo['sync_badge']) {
				$rows['sync_badge'] = $data['sync_badge'];
			}
			if(isset($data['recommend']) && $data['recommend']!=$this->_userInfo['recommend']){
				$rows['recommend'] = $data['recommend'];
			}
			
			$result = Better_DAO_User::getInstance($this->_uid)->update($rows);
			
			if ($result) {
				$this->_toUpdateData = array();

				$hooks[] = 'Cache';
				$hooks[] = 'Market';
				$hooks[] = 'Queue';

				Better_Hook::factory(array_unique($hooks))->invoke('UserChanged', array(
					'oldUserInfo' => $this->_userInfo,
					'newUserInfo' => $rows,
					'new_email' => $newEmail
					));

				$this->_userInfo = array_merge($this->_userInfo, $rows);
				self::$parsedUsers[$this->_uid] = array_merge((array)self::$parsedUsers[$this->_uid], $rows);
			}
			
		}
		
		return $result;
	}
	
	/**
	 * 删除用户
	 * 
	 * @return bool
	 */
	public function deleteUser()
	{
		$deleted = false;
		$first = false;
		
		if ($this->_uid) {
			try {
				Better_Hook::factory(array(
					'Blog', 'BlogReply', 'DirectMessage', 'Email', 'Syncsites', 'User'
				))->invoke('UserDeleted', array(
					'uid' => $this->_uid
					));			
				
				$first = true;
			} catch (Better_Exception $e) {
				
			}
			
			if ($first) {
				Better_DAO_User::getInstance($this->_uid)->delete($this->_uid);
				$deleted = true;
			}
		}
			
		return $deleted;
	}
	
	/**
	 * 根据权限过滤用户状态
	 * 
	 * @param $data
	 * @return string
	 */
	public static function filterStatus($data=array())
	{

		if (count($data)==0) {
			$data = Better_Registry::get('user')->getUser();
		}

		$uid = Better_Registry::get('sess') ? Better_Registry::get('sess')->getUid() : 0;
		//$followings = $uid ? Better_User_Follow::getInstance($uid)->getFollowings() : array();
		$friends = $uid ? Better_User_Friends::getInstance($uid)->getFriends() : array();
		
		if (($uid && $uid==$data['uid']) || $data['priv_blog']=='0' || ($data['priv_blog']=='1' && in_array($data['uid'], $friends)) ) {
			$status = is_array($data['status']) ? $data['status'] : unserialize($data['status']);
			if ($status['message']) {
				$status['message'] = stripslashes($status['message']);
			}
		} else {
			$status = array(
				'bid' => '',
				'message' => '',
				'dateline' => '',
				);
		}
		
		if (!is_array($status) || (is_array($status) && count($status)==0)) {
			$status = array(
				'bid' => '',
				'message' => '',
				'dateline' => '',
			);
		}

		return $status;
	}
	
	/**
	 * 根据权限过滤用户位置
	 * 
	 * @param $data
	 * @return string
	 */
	public static function filterLocation($data=array())
	{
		$location = '';
		if (count($data)==0) {
			$data = Better_Registry::get('user')->getUser();
		}
		
		$lang = Better_Registry::get('lang');
		$sessUid = Better_Registry::get('sess') ? Better_Registry::get('sess')->getUid() : 0;
		
		if ($data['uid']) {
			$location = ($data['user_city'] ? $data['user_city'] : $data['city']).' '.($data['user_address'] ? $data['user_address'] : $data['address']);
		} else {
			$location = '';
		}
		
		$location = trim($location);

		return $location;
	}
	
	/**
	 * 判断跟某人是否好友
	 * 
	 * @return bool
	 */
	public function isFriend($uid)
	{
		return in_array($uid, $this->friends) ? true : false;
	}
	
	/**
	 * 判断是否关注了某人
	 * 
	 * @return bool
	 */
	public function isFollowing($uid)
	{
		$followings = $this->follow()->getFollowings();
		
		return in_array($uid, $followings) ? true : false;
	}
	
	/**
	 * 判断某人是不是我的粉丝
	 * 
	 * @return bool
	 */
	public function isFollower($uid)
	{
		$followers = $this->follow()->getFollowers();
		
		return in_array($uid, $followers) ? true : false;
	}
	
	
	public function isLoginActive()
	{
		return Better_User_State::isValidLoginState($this->_userInfo['state']);
	}

	public function isActive()
	{
		return Better_User_State::isActiveState($this->_userInfo['state'], $this->_userInfo['cell_no']);
	}
	
	public function isPublic()
	{
		//return (bool)(!$this->_userInfo['priv_blog'] && !$this->_userInfo['sys_priv_blog']);
		return true; 
	}
	
	/**
	 * 是否阻止了某人
	 * 
	 * @return bool
	 */
	public function isBlocking($uid)
	{
		$blockings = $this->block()->getBlocks();
		
		return in_array($uid, $blockings) ? true : false;
	}
	
	/**
	 * 是否被某人阻止了
	 * 
	 * @return bool
	 */
	public function isBlockedBy($uid)
	{
		$blockedby = $this->block()->getBlockedBy();
		
		return in_array($uid, $blockedby) ? true : false;
	}
	
	/**
	 * 	是否被禁言了
	 * 
	 * @return bool
	 */
	public function isMuted()
	{
		$result = false;
		
		if ($this->_userInfo['state']==Better_User_State::MUTE) {
			$result = true;	
		}
		
		return $result;
	}
	
	public function needValidate()
	{
		$result = false;
		
		if ($this->_userInfo['state']==Better_User_State::SIGNUP_VALIDATING || $this->_userInfo['state']==Better_User_State::UPDATE_VALIDATING) {
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * 用户是否被封号了
	 * 
	 * @return bool
	 */
	public function isBanned()
	{
		$result = false;
		
		if ($this->_userInfo['state']==Better_User_State::BANNED) {
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * 获取用户语言
	 * 
	 * @return Object
	 */
	public function getUserLang()
	{
		$language = $this->getUserLanguage();
		
		return Better_Language::loadIt($language);
	}
	
	/**
	 * 获取用户语言标识
	 * 
	 * @return string
	 */
	public function getUserLanguage()
	{
		$language = '';
		if (defined('IN_API') || defined('IN_CRON')) {
			$language = $this->cache()->get('api_lang');
		}
		
		if (!$language) {
			$this->getUserInfo();
			$language = $this->_userInfo['language'];
		}	

		return $language;
	}
	
	/**
	 * 
	 * 是否可以看某人的动态
	 * @param unknown_type $uid
	 */
	public function canViewDoing($uid)
	{
		/*$flag = false;
		if ($uid==$this->_uid || $uid==BETTER_SYS_UID) {
			$flag = true;
		} else if ($uid) {
			$user = Better_User::getInstance($uid);
			$user->getUserInfo();
			$public = $user->isPublic();
			if ($public || (!$public && $this->_uid && $this->isFriend($uid))) {
				$flag = true;
			}
		}		
		
		return $flag;*/
		return true;
	}
	
	/**
	 * 检查互相之间好友请求的关系
	 */
	public function getRelation($uid)
	{
		if (!isset($this->_relation[$uid])) {
			$from = $this->friends()->hasRequestToMe($uid);
			$to = in_array($uid, $this->pendding);
			if ($to) {
				$relation = 1;
			} else if ($from) {
				$relation = 2;
			} else {
				$relation = 0;
			}
			
			$this->_relation[$uid] = $relation;
		}
		return $this->_relation[$uid];
	}
}