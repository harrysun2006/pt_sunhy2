<?php 

class Better_DAO_Emailuser extends Better_DAO_Base
{

	private static $instance = array();
  	private static $login_days = 10;
  	private static $validate_days = 3;
  	private static $mobile_days = 30;
  	private static $range = 1000;
  	private static $_maxCnt = 2000;
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'account';
    	$this->priKey = 'uid';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
		$this->assignUserDbConnection(true);
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Emailuser($identifier);
		}
		
		return self::$instance[$identifier];
	}

	
	public function reconnection()
	{
		try {
			$this->rdb->getConnection();
			$this->wdb->getConnection();
		} catch (Exception $e) {
			$this->rdb->closeConnection();
			$this->wdb->closeConnection();
			
			$this->assignUserDbConnection(true);
		}
		
		return self::$instance[$this->identifier];
	}
	
		
	/**
	 * 所有用户email
	 */
	public static function getAllEmails($type=1){
		$return = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'account as a', array('a.uid', 'a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p','a.uid=p.uid', array('p.language', 'p.nickname'));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=a.uid AND ub.uid IS NULL', array());
			if($type==1){
				$select->where('p.email4product=?', '1');
			}else if($type==2){
				$select->where('p.email4person=?', '1');
			}
			$select->where('p.state!=?', 'signup_validating');		
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$return[] = $row;
			}
		}
		return $return;
	}
	
	
	/**
	 *	10天不登陆的用户
	 */
	public static function getNologinEamils(){
		$return = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'account as a', array('a.uid', 'a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p','a.uid=p.uid', array('p.language', 'p.nickname'));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=a.uid AND ub.uid IS NULL', array());
			
			$select->where('p.email4person=?', '1');
			$select->where('p.state!=?', 'signup_validating');
			$select->where('a.lastlogin<=?', time()-(self::$login_days*24*3600));	
			$select->where('a.lastloginemail<=?', time()-(self::$login_days*24*3600));
			$select->limit(self::$_maxCnt);
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$return[] = $row;
			}
		}
		return $return;
	}
	
	
	/**
	 * 3天不验证的用户
	 */
	public static function getNovalidateEmails(){
		$return = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'account as a', array('a.uid', 'a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p','a.uid=p.uid', array('p.language', 'p.nickname'));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=a.uid AND ub.uid IS NULL', array());
			
			$select->where('p.state=?', 'signup_validating');
			$select->where('a.regtime<=?', time()-(self::$validate_days*24*3600));	
			$select->where('a.lastvalidteemail=?', 0);
			$select->limit(self::$_maxCnt);
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$return[] = $row;
			}
		}
		return $return;
	}
	
	
	/**
	 * 近期使用过某一平台的用户
	 */
	public static function getMobileEmails($source){
		$return = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog as b', array('b.uid'));
			$select->join(BETTER_DB_TBL_PREFIX.'account as a', 'b.uid=a.uid', array('a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p', 'a.uid=p.uid', array('p.nickname', 'p.language'));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			
			$select->where('b.source=?', $source);
			$select->where('p.email4product=?', '1');
			$select->where('b.dateline>=?', time()-(self::$mobile_days*24*3600));
			$select->group('b.uid');
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$return[] = $row;
			}
		}
		return $return;
	}
	
	
	
	/**
	 * POI优惠信息，POI周围1km签到过的人 
	 */
	public static function getPOIEmails($poi_id){
		$poi = Better_Poi_Info::getInstance($poi_id)->getBasic();
		$return = array();
		if($poi['poi_id']){
			list($lon, $lat) = Better_Functions::XY2LL($poi['x'], $poi['y']);
			
			$result = Better_Search::factory(array(
			'what' => 'poi',
			'lon' => $lon,
			'lat' => $lat,
			'range' => self::$range
			))->search();
			
			if($result['rows']){
				$pois = $result['rows'];
				if(count($pois)>0){
					foreach($pois as $row){
						$pid = $row['poi_id'];
						$users = Better_Poi_Checkin::getInstance($pid)->users(1, BETTER_MAX_LIST_ITEMS);
						foreach($users['rows'] as $user){
							if($user['email4product'] && $user['state']!=Better_User_State::BANNED){
								$return[$user['uid']] = $user;
							}
						}
					}
				}
			}
		}
		return $return;
	}

	
	/**
	 * 社区动态
	 */
	public static function getCommunityEmails($params){
		$result = array();
		if(is_array($params)){
			foreach($params as $val){
				$className = 'Better_DAO_Community_'.ucfirst($val);
				if (class_exists($className)) {
					 $tmp = call_user_func(array($className, 'getAllResults'));
				} else {
					$tmp =array();
				}
				
				foreach($tmp as $key=>$val){
					$result[$key][] = $val;
				}
			}
		}
		return $result;
	}

}