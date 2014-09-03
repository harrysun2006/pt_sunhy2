<?php

class Better_Cache_Clear
{
	public static function blogBid($bid)
	{
		$cacheKey = md5('kai_blog_bid_'.$bid);
		$cacher = Better_Cache::remote();
		
		$cacher->set($cacheKey, null);
	}
	
	public static function changeBlogType($poiId, $type)
	{
		$typeWhere = $type ? " AND `type`='".$type."'" : "";
		$sql = "SELECT bid
		FROM `".BETTER_DB_TBL_PREFIX."blog`
		WHERE poi_id='".((int)$poiId)."' ".$typeWhere."
		ORDER BY dateline DESC
		";
		
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($servers as $sid) {
			$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$rs = Better_DAO_Base::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				self::blogBid($row['bid']);
			}			
		}
	}
	
	public static function ranking()
	{
		$cacher = Better_Cache::remote();
		
		$cs = array(20, 30);
		foreach ($cs as $count) {
			for ($page=1;$page<10;$page++) {
				$cacher->set('karma_ranking_global_'.$page.'_'.$count);
			}	
		}
	}
	
	public static function kai()
	{
		$cacher = Better_Cache::remote();
		
		$cacher->set('kai_pt');
		$cacher->set('kai_pt_min');
		$cacher->set('kai_pt_max');
	}
	
	public static function badges()
	{
		$cacher = Better_Cache::remote();
		
		$bs = Better_Badge::getAllBadges();
		foreach ($bs as $bid=>$badge) {
			$cacher->set('badge_users_'.$bid, null);
		}
		
		$cacher->set('kai_badges', null);
	}
	
	public static function treasures()
	{
		Better_Cache::remote()->set('kai_treasures', null);
	}
	
	public static function userAvatar($uid)
	{
		Better_Cache::remote()->set('kai_user_avatar_'.$uid, null);
	}
	
	public static function allAvatar()
	{
		$rows = Better_DAO_User_Assign::getInstance()->getAll();
		foreach ($rows as $row) {
			$uid = $row['uid'];
			self::userAvatar($uid);
		}
	}
	
	public static function userInfo($uid)
	{
		$key = md5(APPLICATION_ENV.'kai_user_cache_'.$uid);
		Better_Cache::remote()->set($key, null);
		Better_Cache::remote()->set('user_badges_'.$uid, null);
		self::userAvatar($uid);
	}
	
	public static function allUserInfo()
	{
		$rows = Better_DAO_User_Assign::getInstance()->getAll();
		foreach ($rows as $row) {
			$uid = $row['uid'];
			self::userInfo($uid);
		}		
	}
	
	public static function blog($uid)
	{
		$cacher = Better_Cache::remote();
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "SELECT bid FROM `".BETTER_DB_TBL_PREFIX."blog` WHERE uid='".$uid."'";
		$rows = Better_DAO_Base::squery($sql, $rdb);
		foreach ($rows as $row) {
			$bid = $row['bid'];
			$key = md5('kai_blog_bid_'.$bid);
			$cacher->set($key, null);
		}
	}
	
	public static function allBlog()
	{
		$cacher = Better_Cache::remote();
		$rows = Better_DAO_User_Assign::getInstance()->getAll();
		foreach ($rows as $row) {
			$uid = $row['uid'];
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$sql = "SELECT bid FROM `".BETTER_DB_TBL_PREFIX."blog` WHERE uid='".$uid."'";
			$rows2 = Better_DAO_Base::squery($sql, $rdb);
			foreach ($rows2 as $row2) {
				$bid = $row2['bid'];
				$key = md5('kai_blog_bid_'.$bid);
				$cacher->set($key, null);
			}
		}				
	}
}