<?php

/**
 * 
 * 微博操作基类
 * 
 * @package Better.DAO.Blog
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Blog_Base extends Better_DAO_Base
{

	protected $profileTbl = '';
	protected $attachTbl = '';
	
	protected $type = '';
	
	protected static $types = array(
		'normal', 'checkin', 'tips'
		);

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
		
		parent::__construct($identifier);
		$this->assignUserDbConnection();
	}

	protected static function &search(array $params)
	{
		$servers = array();
		$results = array();
		
		$uids = (array)$params['uid'];
		$page = (int)$params['page'];
		$count = (int)$params['count'];
		$keyword = trim($params['keyword']);
		$pois = $params['poi'] ? (array)$params['poi'] : array();
		$types = (array)$params['type'];
		$order = isset($params['order']) ? $params['order'] : 'b.dateline DESC';
		
		$page<=0 && $page=1;
		$count<=0 && $count=20;

		$us = Better_DAO_User_Assign::getInstance();
		if (count($uids)>0) {
			$servers = $us->getServerIdsByUids($uids);
		} else {
			$servers = $us->getServerIds();
		}
		
		$uid = Better_Registry::get('sess')->getUid();

		foreach($servers as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog AS b', array(
				'b.*'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=b.uid', array(
				'p.nickname', 'p.username', 'p.gender', 'p.self_intro', 'p.avatar', 'p.priv_blog', 'p.address AS user_address', 'p.city as user_city', 'X(p.xy) AS user_x', 'Y(p.xy) AS user_y', 'p.range AS user_range', 'p.lbs_report'
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile_counters AS c', 'c.uid=b.uid', array(
				'c.followers', 'c.followings', 'c.favorites', 'c.places', 'c.posts AS posts', 
				));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'user_banned AS ub', 'ub.uid=b.uid AND ub.uid IS NULL', array());
			$select->where('b.checked=1 OR (b.checked=0 AND b.uid=?)', $uid);
			
			$keyword!='' && $select->where('b.message LIKE ?', '%'.$keyword.'%');
			count($uids)>0 && $select->where('b.uid IN (?)', $uids);
			count($types)>0  && $select->where('b.type IN (?)', $types);
			count($pois)>0 && $select->where('b.poi_id IN (?)', $pois);

			if (is_array($order)) {
				foreach ($order as $o) {
					$select->order($o);
				}
			} else {
				$select->order($order);
			}

			$select->limit(BETTER_MAX_LIST_ITEMS);

			$rs = self::squery($select, $rdb);
					
			$rows = $rs->fetchAll();
			foreach($rows as $row) {
				$bid = $row['bid'];
				if (!in_array($bid, $results)) {

					$key = 1000000+$row['poll_result'];
					$results[$key.'.'.$row['dateline'].'.'.$row['bid']] = $row;
				}
			}
		}
		krsort($results);

		//	取出合并后的limit条数据
		$data = array_chunk($results, BETTER_MAX_LIST_ITEMS);
		unset($results);
		
		$rows = isset($data[0]) ? $data[0] : array();
		
		$pages = array_chunk($rows, $count);
		$result = isset($pages[$page-1]) ? $pages[$page-1] : array();
		
		$results['pages'] = count($pages);
		$results['rows'] = &$result;
		$results['count'] = count($rows);

		return $results;		
	}
}