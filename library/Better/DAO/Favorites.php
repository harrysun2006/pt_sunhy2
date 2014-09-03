<?php

/**
 * 用户收藏相关数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Favorites extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'favorites';
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
	
	public function delete($uid, $bid)
	{
		return parent::deleteByCond(array(
					'bid' => $bid,
					'uid' => $uid,
					));
	}
	
	public function decreaseUsersFavorites($bid)
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		$prefix = BETTER_DB_TBL_PREFIX;

		foreach($servers as $_sid) {
			$uids = array();
			$cs = parent::assignDbConnection('user_server_'.$_sid);

			$select = $cs['r']->select();
			$select->from($this->tbl, array('uid'));
			$select->where('bid=?', $bid);
			
			$rs = self::squery($select, $cs['r']);

			$rows = $rs->fetchAll();
			foreach($rows as $row) {
				$uids[] = $row['uid'];
			}
			
			if (count($uids)>0) {
				$where = $cs['w']->quoteInto('`uid` IN (?)', $uids);
				self::squery('UPDATE `'.BETTER_DB_TBL_PREFIX.'profile` SET `favorites`=`favorites`-1 WHERE '.$where, $cs['w']);
			}
		}
		
		return $uids;
	}
	
	public function getFavorites($bids, array $type=array('normal'))
	{
		$results = array();
		$uids = array();
		
		foreach($bids as $bid) {
			list($uid, $cnt) = explode('.', $bid);
			$uids[] = $uid;
		}
		$uids = array_unique($uids);

		$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);

		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
						'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 'b.checked', 
						'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
						'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.nickname',  'p.username', 'p.gender', 'p.self_intro', 'p.avatar', 'p.priv_blog', 'p.address AS user_address', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.range AS user_range', 'p.lbs_report'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=b.uid', array(
				'c.followers', 'c.followings', 'c.favorites', 'c.places', 'c.posts AS posts', 
				));
					
			$select->where('b.checked=?',1);
			$select->where('b.type IN (?)', $type);
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
	
	public function getTipsFavorites($bids)
	{
		$results = array();
		$uids = array();
		
		foreach($bids as $bid) {
			list($uid, $cnt) = explode('.', $bid);
			$uids[] = $uid;
		}
		$uids = array_unique($uids);

		$sids = Better_DAO_User_Assign::getInstance()->getServerIdsByUids($uids);

		$keys = array_flip($bids);
		
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
						'b.bid', 'b.upbid', 'b.uid', 'b.dateline', 'b.message', 'b.ip', 'b.attach', 'b.source', 'b.checked', 
						'b.favorited', 'b.address', 'b.city', 'X(b.xy) AS x', 'Y(b.xy) AS y', 'b.range',
						'b.type', 'b.poi_id', 'b.priv', 'b.badge_id', 'b.major', 'b.up', 'b.down'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.nickname', 'p.username', 'p.gender', 'p.self_intro', 'p.avatar', 'p.priv_blog', 'p.address AS user_address', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.range AS user_range', 'p.lbs_report'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=b.uid', array(
				'c.followers', 'c.followings', 'c.favorites', 'c.places', 'c.posts AS posts', 
				));
					
			$select->where('b.checked=?',1);
			$select->where('b.type=?', 'tips');
			$select->where('b.bid IN(?)', $bids);
			$select->order('b.dateline DESC');
			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach($rows as $row) {
				$key = $keys[$row['bid']];
				$results[$key] = $row;
			}
		}

		krsort($results);
		$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
		unset($results);
		
		return isset($data[0]) ? $data[0] : array();		
	}	
		
}
