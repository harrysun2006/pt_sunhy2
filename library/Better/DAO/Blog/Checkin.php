<?php

/**
 * 
 * 微博表的Checkin
 * 
 * @package Better.DAO.Blog
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Blog_Checkin extends Better_DAO_Blog_Base
{
	
    public function __construct($identifier = 0)
    {
		parent::__construct ($identifier);
	}
	
	public function uid($uid)
	{
		return self::search(array(
			'uid' => $uid,
			));
	}
	
	public static function &search(array $params)
	{
		$params['type'] = 'checkin';
		
		return parent::search($params);
	}
	
	public function getlotspoiCheckin($params)
	{
		$poi_id = $params['poi_id'];
		$count = $params['page_size'];
	    $page = $params['page'];
		$return = array(
			'total' => 0,
			'rows' => array()
			);		
		$limit = $page*$count+1;		
		$results = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];			
			$sql = "SELECT b.uid, b.source, b.dateline AS checkin_time, a.email,
				p.username, p.nickname, p.gender, p.birthday, p.self_intro, p.language, p.tags, p.avatar, p.live_province, p.live_city, p.visits, p.visited, p.priv_profile, 
					p.priv_blog, p.priv_location, p.last_active, p.last_bid, p.status, p.address, p.lbs_report, p.city, p.msn, p.gtalk, X(p.xy) AS x, Y(p.xy) AS y, p.range, p.state, p.karma,
					p.last_checkin_poi, p.timezone, p.email4person, p.email4community, p.email4product, p.rp
				
			FROM `".BETTER_DB_TBL_PREFIX."blog` AS b
				INNER JOIN `".BETTER_DB_TBL_PREFIX."account` AS a
					ON a.uid=b.uid
				INNER JOIN `".BETTER_DB_TBL_PREFIX."profile` AS p
					ON p.uid=b.uid AND p.priv_blog='public'
				INNER JOIN `".BETTER_DB_TBL_PREFIX."profile_counters` AS c
					ON c.uid=b.uid				
			WHERE b.type='checkin' AND b.priv='public' AND b.poi_id in (".$poi_id.")";			
			$sql .= "			
			ORDER BY b.dateline DESC
			LIMIT ".$limit."
			";
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['checkin_time']] = $v;
			}
		}
		krsort($results);
		//	取出合并后的limit条数据
		

		$data = array_chunk($results, $count);

		unset($results);

		return isset($data[0]) ? $data[0] : array();		
	}
	
}