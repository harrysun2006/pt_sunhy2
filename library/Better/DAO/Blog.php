<?php

/**
 * 微博客相关数据操作
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Blog extends Better_DAO_Base
{
	
	private static $instance = array();
	protected static $overAll = false;
	
	private $profileTbl = '';
	private $attachTbl = '';

 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'blog';
		$this->profileTbl = BETTER_DB_TBL_PREFIX.'profile';
		$this->attachTbl = BETTER_DB_TBL_PREFIX.'attachments';
		$this->priKey = 'bid';
		$this->orderKey = 'dateline';
		
		parent::__construct ($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Blog($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	/**
	 * 设置查询时是否要采用隐私策略
	 *
	 * @param bool $overAll
	 * @return null
	 */
	public static function setOverAll($overAll)
	{
		return self::$overAll = $overAll ? true : false;
	}
	
	/**
	 * 
	 * 取出Cache所需要的数据
	 * @param unknown_type $bid
	 */
	public function &preCacheFetch($bid)
	{
		$data = array();
		
		$sql = "SELECT b.bid, b.upbid, b.uid, b.dateline, b.message, b.ip, b.attach, b.source, b.checked, b.favorited, b.address, b.city, X(b.xy) AS x, Y(b.xy) AS y, b.range
					 ,b.`type`, b.poi_id, b.priv, b.badge_id, b.major, b.up, b.down
					 ,p.nickname, p.username, p.gender, p.self_intro, p.last_checkin_poi, p.avatar, p.priv_blog
					 ,p.address AS user_address, p.range AS user_range, p.city AS user_city, X(p.xy) AS user_xy, Y(p.xy) AS user_y, p.lbs_report
					 ,at.file_id, at.filename, at.dateline AS at_dateline, at.mimetype, at.filesize, at.ext
				FROM ".BETTER_DB_TBL_PREFIX."blog AS b
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."profile AS p
						ON p.uid=b.uid
					LEFT JOIN ".BETTER_DB_TBL_PREFIX."attachments AS at
						ON b.uid=at.uid AND at.file_id=b.attach	
				WHERE b.bid='".$bid."'
				LIMIT 1
			";		

		$rs = self::squery($sql, $this->rdb);
		$data = $rs->fetch();
		
		return $data;
	}
	
	/**
	 * 貌似采用PDO的Statement执行xy相关操作会报错，只能直接通过sql进行操作
	 *
	 */
	public function insert($data)
	{
		return $this->_insertXY($data) ? $data['bid'] : 0;
	}

	public function update($data, $val='', $cond='AND')
	{
		return $this->_updateXY($data, $val, $cond);
	}
	
	/**
	 * 删贴时重置upbid
	 * 
	 * @param $upbid
	 * @return null
	 */
	public static function resetUpbid($upbid)
	{
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();

		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog', array('bid', 'uid'));
			$select->where('upbid=?', $upbid);
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $row) {
				self::getInstance($row['uid'])->updateByCond(array(
					'upbid' => '0',
				), array(
					'bid' => $row['bid']
				));
			}
		}
	}

	/**
	 * 根据指定的用户id读取消息
	 *
	 * @param $uids
	 * @param $cacheKey
	 * @return array
	 */
	public function getBlogsByUids($uids, $sessUserInfo=array(), $cacheKey='')
	{

		if (is_array($uids) && count($uids)>0) {

			$cacheKey = '';	//	Temp disable cache
			if ($cacheKey!='') {
				$cacher = Better_Cache::remote();
				$cacheKey .= '_'.md5(implode(',', $uids)).self::$cacheIdentifier;
				$cacher->test($cacheKey) && $results = $cacher->get($cacheKey);
			}
			
			if (!$results) {
				$dua = Better_DAO_User_Assign::getInstance();
				$rows = $dua->fetchAll($dua->getAdapter()->quoteInto('uid IN (?)', $uids));
				$prefix = BETTER_DB_TBL_PREFIX;
		
				//	获得所有待查询的server
				$servers = array();
				foreach($rows as $row) {
					$servers[$row['sid']][] = $row['uid'];
				}
				unset($rows);
				
				$results = array();
	
				//	遍历每个server取出limit个消息
				foreach($servers as $_sid=>$_uids) {
					$cs = parent::assignDbConnection('user_server_'.$_sid);
					$rdb = $cs['r'];
					$select = $rdb->select();
					$select->from($prefix.'blog AS b', array(
						'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 'b.checked', 
						'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
						'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major'
						));
					$select->join($prefix.'profile AS p', 'p.uid=b.uid', array(
						'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.avatar', 'p.priv_blog', 'p.address AS user_address', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.range AS user_range', 'p.lbs_report'
						));
					$select->join($prefix.'profile_counters AS c', 'c.uid=b.uid', array(
						'c.followers', 'c.followings', 'c.favorites', 'c.places', 'c.posts AS posts', 
						));
					$select->joinleft($prefix.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
					
					$select->where('b.checked=?', 1);
					$select->where('b.type=?', 'normal');
					
					$select->where(' b.uid IN (?)', $_uids);
					$select->order('b.dateline DESC');
					$select->limit(BETTER_MAX_LIST_ITEMS);

					$rs = self::squery($select, $rdb);
					
					$rows = $rs->fetchAll();
					foreach($rows as $v) {
						$results[$v['dateline']] = $v;
					}
				}
	
				krsort($results);
				
				if ($cacheKey!='') {
					/**
					 * TODO: 缓存保持老是失败？查原因...
					 */
					$cacher->set($cacheKey, $results, 300) ;
				}
			}
			
			//	取出合并后的limit条数据
			$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);

			unset($results);

			return isset($data[0]) ? $data[0] : array();
		
		} else {
			return array();
		}
	}
	
	/**
	 * 检测有效的bids
	 * 典型用例：从qbs搜索所得的bids中可能有已经删除的bid，这种情况就会造成结果数目不正确
	 *
	 * @param array $bids
	 * @return array
	 */
	public function validBids($bids)
	{
		$newBids = array();
		$uids = array();
		
		foreach($bids as $bid) {
			list($uid, $cnt) = explode('.', $bid);
			$uids[] = $uid;
		}
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog', array('bid'));
			$select->where('bid IN (?)', $bids);
			
			$rs = $this->query($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $row) {
				if (!in_array($row['bid'], $newBids)) {
					$newBids[] = $row['bid'];
				}
			}
		}

		return $newBids;
		
	}
	
	/**
	 * 根据指定的消息id取数据
	 *
	 * @param array $bids
	 * @param string $cacheKey
	 * @return array
	 */
	public function getBlogsByBids($bids, $sessUserInfo=array(), $cacheKey='')
	{
		$cacheKey = $cacheKey=='' ? 'lastest' : $cacheKey;
		$results = array();
		$uids = array();
		
		foreach($bids as $bid) {
			list($uid, $cnt) = explode('.', $bid);
			$uids[] = $uid;
		}

		$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);

		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major',
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=b.uid', array(
				'c.followers', 'c.followings', 'c.favorites', 'c.posts AS posts', 'c.places'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'poi_poll AS pp', 'pp.blog_id=b.bid AND pp.uid=\''.Better_Registry::get('sess')->get('uid').'\' AND b.type=\'tips\'', array(
				'pp.id AS polled',
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
	
			$select->where('b.checked=?',1);
			$select->where('b.bid IN(?)', $bids);
			
			$select->order('b.dateline DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $row) {
				$results[$row['dateline'].$row['bid']] = $row;
			}
		}

		krsort($results);
		$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
		unset($results);
		
		return isset($data[0]) ? $data[0] : array();

	}
	

	/**
	 * 根据指定的消息id取图片
	 *
	 * @param array $bids
	 * @param string $cacheKey
	 * @return array
	 */
	public function getPhotosByBids($bids, $sessUserInfo=array(), $cacheKey='')
	{
		$cacheKey = $cacheKey=='' ? 'lastest' : $cacheKey;
		$results = array();
		$uids = array();
		
		foreach($bids as $bid) {
			list($uid, $cnt) = explode('.', $bid);
			$uids[] = $uid;
		}

		$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);

		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
				'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 
				'b.checked', 'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
				'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.last_checkin_poi',
				'p.avatar', 'p.priv_blog',  'p.address AS user_address', 'p.range AS user_range', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.lbs_report'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=b.uid', array(
				'c.followers', 'c.followings', 'c.favorites', 'c.posts AS posts', 'c.places'
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
				
			$select->where('b.attach!=""');
												
			$select->where('b.checked=?',1);
			$select->where('b.bid IN(?)', $bids);
			
			$select->order('b.dateline DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $row) {
				$results[$row['dateline']] = $row;
			}
		}

		krsort($results);
		$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
		unset($results);

		return isset($data[0]) ? $data[0] : array();

	}
	
	public function getIndex()
	{
		$cacheKey = 'index_data';
		
		$cacher = Better_Cache::remote();
		$cacher->test($cacheKey) && $results = $cacher->get($cacheKey);

		if (!$results) {
			$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
			$prefix = BETTER_DB_TBL_PREFIX;

			foreach($servers as $_sid) {
				$cs = parent::assignDbConnection('user_server_'.$_sid);
				$rdb = $cs['r'];

				$sql = 'SELECT `b`.`bid`, `b`.`upbid`, `b`.`uid`, `b`.`dateline`, `b`.`message`, `b`.`ip`, `b`.`attach`, `b`.`source`, `b`.`checked`, `b`.`favorited`, `b`.`address`, `b`.`city`, X(b.xy) AS `x`, Y(b.xy) AS `y`, `b`.`range`, `b`.`type`, `b`.`poi_id`, `b`.`priv`, `b`.`badge_id`, `b`.`major`, 
					`p`.`nickname`, `p`.`username`, `p`.`gender`, `p`.`self_intro`, `p`.`last_checkin_poi`, `p`.`avatar`, `p`.`priv_blog`, `p`.`address` AS `user_address`, `p`.`range` AS `user_range`, `p`.`city` AS `user_city`, X(p.xy) AS `user_x`, Y(p.xy) AS `user_y`, `p`.`lbs_report`, `p`.`karma`, `p`.`last_checkin_poi`, `c`.`followers`, `c`.`followings`, `c`.`favorites`, `c`.`posts`, `c`.`places` 
					FROM `better_blog` AS `b` FORCE INDEX(dateline)
					INNER JOIN `better_profile` AS `p` ON p.uid=b.uid
					INNER JOIN `better_profile_counters` AS `c` ON c.uid=b.uid
					LEFT JOIN `better_user_banned` AS `ub` ON ub.uid=b.uid AND ub.uid IS NULL 
					inner join (select uid, max(dateline) as md from better_blog group by uid) AS bb on bb.uid=b.uid WHERE (b.checked=1) AND (b.priv="public") AND (p.avatar!="") AND (b.badge_id!="0" OR b.major!="0") AND (b.type IN ("checkin")) AND (b.dateline=bb.md) 
					ORDER BY `b`.`dateline` DESC 
					LIMIT 200
				';				
				
				$rs = self::squery($sql, $rdb);
				$rows = $rs->fetchAll();
				foreach($rows as $v) {
					$results[$v['dateline'].$v['bid']] = $v;
				}
			}
			
			krsort($results);
			
			$cacher->set($cacheKey, $results, 3600*24);
		}

		$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
		unset($results);
	
		return isset($data[0]) ? $data[0] : array();		
	}
		
	/**
	 * 获取所有最新的消息
	 *
	 * @return array
	 */
	public function getLastest($excludeUids=array(), $userInfo=array(), $page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$cacheKey = 'lastest_'.self::$cacheIdentifier.'_'.md5(serialize($excludeUids)).'_'.md5(serialize($userInfo));
		
		/**
		$cacher = Better_Cache::remote();
		$cacher->test($cacheKey) && $results = $cacher->get($cacheKey);
		**/
		$result = false;

		if (!$results) {
			$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
			$prefix = BETTER_DB_TBL_PREFIX;

			foreach($servers as $_sid) {
				$cs = parent::assignDbConnection('user_server_'.$_sid);
				$rdb = $cs['r'];
				
				$sql = "SELECT `b`.`bid`, `b`.`upbid`, `b`.`uid`, `b`.`dateline`, `b`.`message`, `b`.`ip`, `b`.`attach`, `b`.`source`, `b`.`checked`, `b`.`favorited`, `b`.`address`, `b`.`city`, X(b.xy) AS `x`, Y(b.xy) AS `y`, `b`.`range`, `b`.`type`, `b`.`poi_id`, `b`.`priv`, `b`.`badge_id`, `b`.`major`, 
				`p`.`nickname`, `p`.`username`, `p`.`gender`, `p`.`self_intro`, `p`.`last_checkin_poi`, `p`.`avatar`, `p`.`priv_blog`, `p`.`address` AS `user_address`, `p`.`range` AS `user_range`, `p`.`city` AS `user_city`, X(p.xy) AS `user_x`, Y(p.xy) AS `user_y`, `p`.`lbs_report`, `p`.`karma`, `p`.`last_checkin_poi`,
				 `c`.`followers`, `c`.`followings`, `c`.`favorites`, `c`.`posts`, `c`.`places` FROM `better_blog` AS `b` FORCE INDEX(dateline)
INNER JOIN `better_profile` AS `p` ON p.uid=b.uid
INNER JOIN `better_profile_counters` AS `c` ON c.uid=b.uid
LEFT JOIN `better_user_banned` AS `ub` ON ub.uid=b.uid AND ub.uid IS NULL 
WHERE (b.checked=1) AND (b.priv='public')
ORDER BY `b`.`dateline` DESC LIMIT 100";

				$rs = self::squery($sql, $rdb);
				$rows = $rs->fetchAll();
				foreach($rows as $v) {
					$results[$v['dateline']] = $v;
				}
			}
			
			krsort($results);
			
			//$cacher->set($cacheKey, $results, 300);
		}

		$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
		unset($results);
		
		return isset($data[0]) ? $data[0] : array();
	}
	
	/**
	 * @TODO 待删除
	 *
	 * @param $val
	 * @param $fuids
	 * @param $limit
	 * @param $method
	 * @return unknown_type
	 */
	public function getAllWithPrivacy($val=array(), $fuids=array(), $limit=0, $method='limitPage')
	{
		if (is_array($val)) {
			$val['m`.`privacy`=0 OR `m`.`uid'] = $fuids;
		} else {
			$val = array(
				$this->priKey=>$val,
				'm`.`privacy`=0 OR `m`.`uid' => $fuids,
				);
		}
		
		return $this->getAll($val, $limit, $method);
	}

	public function getAll($val=array(), $limit=0, $method='limitPage')
	{
		$method = $method=='limit' ? 'limit' : 'limitPage';
		
		$select = $this->db->select();
		$select->from($this->tbl.' AS m');
		$select->joinleft($this->attachTbl.' AS a', 'a.file_id=m.attach', array(
																	'a.filename AS attach_filename',
																	'a.dateline AS attach_dateline',
																	'a.thumb AS attach_thumb',
																	'a.ext AS attach_ext',
																	));
		$select->joinleft($this->attachTbl.' AS b', 'b.file_id=m.avatar', array(
																	'b.filename AS avatar_filename',
																	'b.dateline AS avatar_dateline',
																	'b.thumb AS avatar_thumb',
																	'b.ext AS avatar_ext',
																	));
		$this->parseWhere($select, $val);
		if (is_array($val) && isset($val['order'])) {
			$this->parseOrder($select,$val['order']);
		} else {
			$select->order($this->priKey.' DESC');
		}
		self::$method($select, $limit);

		$result = $this->query($select);
		
		return $result->fetchAll();
	}
	
	public function getCount($cond=array())
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, 'COUNT('.$this->priKey.') AS cnt');
		foreach($cond as $k=>$v) {
			if (is_array($v)) {
				$select->where($k.' IN (?)', $v);
			} else {
				$select->where($k.'=?', $v);
			}
		}
		$result = $this->query($select);
		$row = $result->fetch();
		
		return $row['cnt'];
	}
		
	public function getLatest($limit=0,$cond=array())
	{
		return $this->getAll($cond,$limit);
	}
	
	public function getLastestCount($cond=array())
	{
		return $this->getCount($cond=array());
	}
	
	public function getAllByUids($uids=array(), $limit='0', $method='limitPage')
	{
		return $this->getAll(array(
				'm`.`uid' => $uids,
				), $limit, $method);
	}
	
	public function getCountByUids($uids=array())
	{
		return $this->getCount(array(
				'uid' => $uids,
				));
	}
		
	public function getUserBlog($uid, $st=0, $limit=15)
	{
		
	}
	
	public function getUserBlogAndFollowed($uid, $st=0, $limit=15)
	{
		
	}
	
	public function getUserTodoPois($uid,$st=0, $limit=15)
	{		
		$results = array();
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($servers as $_sid) {
			$cs = self::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];			
			$sql = "select poi_id,dateline from better_todo where uid=".$uid.' order by dateline desc limit '.$st.','.$limit;
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			if($rows){
				foreach($rows as $row) {
					$results[] = $row;
				}
			}
		}
		return $results;
	}
	
	public function isTodoPoi($uid,$poiId,$type='todo')
	{		
			$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
			$count=0;
			foreach($servers as $_sid) {
				$cs = self::assignDbConnection('user_server_'.$_sid);
				$rdb = $cs['r'];			
				$sql = "select count(*) as todo from better_blog where type= '{$type}' and uid={$uid} and poi_id={$poiId}";
				$rs = self::squery($sql, $rdb);
				$rows = $rs->fetch();
				$count += $rows['todo'];
			}
			return $count;
	}
	public function getBidByPoi($uid,$poiid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, 'bid');
		$select->where("uid=?",$uid);
		$select->where("poi_id=?",$poiid);
		$select->where("type='todo'");
		$result = self::squery($select, $this->rdb);
		$return=$result->fetch();
		return $return['bid'];
	}
	public function getBidByCond($uid,$poiid,$type)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, 'bid');
		$select->where("uid=?",$uid);
		$select->where("poi_id=?",$poiid);
		$select->where("type=?",$type);
		$select->where("type='todo'");
		$result = self::squery($select, $this->rdb);
		$return=$result->fetch();
		return $return['bid'];
	}

	public function getUserLastCheckin($uid,$poiid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$select->where("uid=?",$uid);
		$select->where("poi_id=?",$poiid);
		$select->where("type='checkin'");
		$select->order('dateline desc');
		$select->limit(1);
		$result = self::squery($select, $this->rdb);
		$return = $result->fetch();
		return $return;
	}
	public function getUserCheckinCount($uid,$poiid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, 'count(*) as checkin_count');
		$select->where("uid=?",$uid);
		$select->where("poi_id=?",$poiid);
		$select->where("type='checkin'");
		$result = self::squery($select, $this->rdb);
		$return = $result->fetch();
		unset($result);
				
		return $return['checkin_count']?$return['checkin_count']:0;
	}
	
}

?>