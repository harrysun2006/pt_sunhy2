<?php

/**
 * 输出过滤器
 * 由于并不是每个从数据库中取出的字段在js前端都需要，并且这些与前端逻辑无关的字段可能暴露用户隐私及系统漏洞
 * 所以在使用ajax将数据库结果集发送给前端时，有必要去掉某些数据库字段数据
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Output
{
	/**
	 * 
	 * 过滤一个私信集合
	 * @param unknown_type $rows
	 */
	public static function &filterMessages(&$rows)
	{
		$output = array();

		foreach ($rows as $row) {
			$output[] = self::filterMessage($row);
		}
		
		return $output;		
	}
	
	/**
	 * 
	 * 过滤一条私信
	 * @param unknown_type $r
	 */
	public static function &filterMessage(&$r)
	{
		unset($r['email'], $r['cell_no'], $r['password'], $r['salt'], $r['regtime'], $r['lastlogin'], $r['regip'], $r['lastloginip'], $r['enabled'], $r['status'], $r['partner'], $r['lastlogin_partner'], $r['lastloginemail'], $r['lastvalidteemail'], $r['birthday'], $r['language']);
		unset($r['live_province'], $r['live_city'], $r['visits'], $r['visited'], $r['lastactive'], $r['last_bid'], $r['priv_profile'], $r['priv_blog'], $r['msn'], $r['gtalk']);
		unset($r['receive_msn_notify'], $r['state'], $r['karma'], $r['last_checkin_poi'], $r['allow_ping'], $r['timezone'], $r['ref_uid'], $r['sys_priv_blog'], $r['email4person']);
		unset($r['email4community'], $r['last_rt_mine'], $r['last_my_followers'], $r['followings'], $r['followers'], $r['favorites'], $r['now_posts'], $r['posts'], $r['received_msgs']);
		unset($r['badges'], $r['treasures'], $r['sent_msgs'], $r['new_msgs'], $r['files'], $r['friends'], $r['majors'], $r['checkins'], $r['invites'], $r['places'], $r['at_dateline']);
		unset($r['mimetype'], $r['filesize'], $r['ext'], $r['karma_dot'], $r['karma_main'], $r['priv'], $r['sid'], $r['delived'], $r['im_delived'], $r['email4product'], $r['self_intro']);
		unset($r['x'], $r['y']);
		
		is_array($r['poi']) && $r['poi'] = self::filterPoiRow($r['poi']);
		$r['userInfo'] = self::filterUser($r['userInfo']);
		
		if ($r['content'] && Better_Config::getAppConfig()->at->enabled) {
			$r['content'] = Better_Blog::parseBlogAt($r['content']);
		}		
		
		return $r;
	}
	
	/**
	 * 
	 * 过滤poi集合
	 * @param unknown_type $rows
	 */
	public static function &filterPoiRows(&$rows)
	{
		$output = array();

		foreach ($rows as $row) {
			$output[] = self::filterPoiRow($row);
		}
		
		return $output;
	}
	
	/**
	 * 
	 * 过滤poi数据
	 * @param unknown_type $row
	 */
	public static function &filterPoiRow(&$row)
	{
		$row['major_detail'] = self::filterPoiMajor($row['major_detail']);
		
		unset($row['major_change_time']);
		unset($row['creator']);
		unset($row['create_time']);
		unset($row['certified']);
		unset($row['intro']);
		unset($row['tags']);
		unset($row['ownerid']);
		unset($row['category_id']);
		unset($row['logo']);
		unset($row['category_name']);
		unset($row['cid']);
		unset($row['level']);
		unset($row['level_adjust']);
		unset($row['sms']);
		unset($row['sms_content']);
		unset($row['nphone']);
		unset($row['action']);
				
		return $row;
	}
	
	/**
	 * 
	 * 过滤poi掌门信息
	 * @param unknown_type $row
	 */
	public static function &filterPoiMajor(&$row)
	{
		$row = self::filterUser($row);
		
		unset($row['status']);
		unset($row['poi']);
		
		return $row;
	}
	
	/**
	 * 过滤一个poi结果集
	 * 
	 * @param array $rows
	 * @return array
	 */
	public static function &filterPois(&$rows, $unsetLL=false)
	{
		$output = array();
		
		foreach ($rows as $k=>$row) {
			$output[$k] = self::filterPoi($row, $unsetLL);
		}
		
		return $output;
	}
	
	/**
	 * 过滤一个poi详情
	 * 
	 * @param array $row
	 * @return array
	 */
	public static function filterPoi($row, $unsetLL=false)
	{
	
		$row['major_detail'] = self::filterUser($row['major_detail']);
		
		unset($row['major_change_time']);
		unset($row['creator']);
		unset($row['create_time']);
		unset($row['certified']);
		unset($row['intro']);
		unset($row['tags']);
		unset($row['cid']);
		unset($row['level']);
		unset($row['level_adjust']);
		unset($row['sms']);
		unset($row['sms_content']);
		unset($row['nphone']);
		unset($row['action']);
		if ($unsetLL) {
			unset($row['lon']);
			unset($row['lat']);
			unset($row['x']);
			unset($row['y']);
			unset($row['dist']);			
		}

		
		if ($row['users']>$row['visitors']) {
			$row['visitors'] = $row['users'];
		} else {
			$row['users'] = $row['visitors'];
		}
		
		$row['logo_url'] = (isset($row['logo_url']) && $row['logo_url']) ? $row['logo_url'] : Better_Poi_Category::getCategoryImage($row);
		
		return $row;
	}
	
	/**
	 * 过滤一个Blog结果集
	 * 
	 * @param array $rows
	 * @return array
	 */
	public static function &filterBlogs(&$rows)
	{
		$output = array();
		
		if (is_array($rows)) {
			foreach ($rows as $k=>$row) {
				$output[$k] = self::filterBlog($row);
			}
		}
		
		return $output;
	}
	
	/**
	 * 过滤一个Blog数组
	 * 
	 * @param array $row
	 * @return array
	 */
	public static function filterBlog($row)
	{
		unset($row['feuid']);
		unset($row['ip']);
		unset($row['x']);
		unset($row['y']);
		unset($row['range']);
		unset($row['gender']);
		unset($row['self_intro']);
		unset($row['user_x']);
		unset($row['user_y']);
		unset($row['at_dateline']);
		unset($row['filename']);
		unset($row['mimetype']);
		unset($row['ext']);
		unset($row['lon']);
		unset($row['lat']);
		unset($row['user_lon']);
		unset($row['user_lat']);
		unset($row['checked']);
		unset($row['synced']);
		unset($row['avatar_huge']);
		unset($row['user_xy']);

		unset($row['user_address']);
		unset($row['user_city']);
		unset($row['lbs_report']);
		unset($row['user_range']);
		
		unset($row['filesize']);
		unset($row['at_dateline']);
		unset($row['ext']);
		unset($row['mimetype']);
		unset($row['filename']);
		
		$row = self::filterBlogPoi($row);

		if ($row['message'] && Better_Config::getAppConfig()->at->enabled) {
			$row['message'] = Better_Blog::parseBlogAt($row['message']);
		}

		return $row;
	}
	
	/**
	 * 过滤一个用户结果集
	 * 
	 * @param array $rows
	 * @return array
	 */
	public static function &filterUsers(&$rows)
	{
		$output = array();
		
		foreach ($rows as $k=>$row) {
			$output[$k] = self::filterUser($row);
		}
		
		return $output;
	}
	
	/**
	 * 过滤一个用户详情
	 * 
	 * @param array $row
	 * @return array
	 */
	public static function filterUser($row)
	{
		unset($row['birthday']);
		unset($row['self_intro']);
		unset($row['live_province']);
		unset($row['live_city']);
		unset($row['visits']);
		unset($row['karma_main']);
		unset($row['karma_dot']);
		unset($row['x']);
		unset($row['y']);
		unset($row['lon']);
		unset($row['lat']);
		unset($row['visited']);
		unset($row['priv_profile']);
		unset($row['priv_blog']);
		unset($row['last_active']);
		unset($row['last_bid']);
		unset($row['places']);
		unset($row['msn']);
		unset($row['file_id']);
		unset($row['filename']);
		unset($row['mimetype']);
		unset($row['ext']);
		unset($row['ref_uid']);
		unset($row['password']);
		unset($row['salt']);
		unset($row['lastloginip']);
		unset($row['regip']);
		unset($row['cell_no']);
		unset($row['email']);
		unset($row['partner']);
		unset($row['lastlogin_partner']);
		unset($row['enabled']);
		unset($row['sys_priv_blog']);
		unset($row['language']);
		unset($row['city']);
		unset($row['filesize']);
		unset($row['lastlogin']);
		unset($row['regtime']);
		unset($row['lastlogin_email']);
		unset($row['lastvalidteemail']);
		unset($row['range']);
		unset($row['lastloginemail']);
		unset($row['allow_ping']);
		unset($row['last_rt_mine']);
		unset($row['last_my_followers']);
		unset($row['avatar_huge']);
		
		if (isset($row['status']) && is_array($row['status'])) {
			unset($row['status']['x']);
			unset($row['status']['y']);
			unset($row['status']['lon']);
			unset($row['status']['lat']);
			unset($row['status']['major']);
			unset($row['stauts']['ip']);
			unset($row['status']['source']);
			unset($row['status']['type']);
			unset($row['status']['priv']);
			unset($row['status']['city']);
			unset($row['status']['range']);
			unset($row['status']['ip']);
			unset($row['status']['poi_id']);
			unset($row['status']['attach']);
			unset($row['status']['address']);
			unset($row['status']['dateline']);
			unset($row['status']['synced']);
			
			if ($row['status']['message'] && Better_Config::getAppConfig()->at->enabled) {
				$row['status']['message'] = Better_Blog::parseBlogAt($row['status']['message']);
			}			
		}
		
		unset($row['state']);
		unset($row['receive_msn_notify']);
		unset($row['karma']);
		unset($row['timezone']);
		unset($row['followings']);
		unset($row['followers']);
		unset($row['favorites']);
		unset($row['posts']);
		unset($row['received_msgs']);
		unset($row['friends']);
		unset($row['majors']);
		unset($row['checkins']);
		unset($row['invites']);
		unset($row['badges']);
		unset($row['treasures']);
		unset($row['at_dateline']);
		unset($row['lon']);
		unset($row['lat']);
		unset($row['user_lon']);
		unset($row['user_lat']);
		unset($row['email4person']);
		unset($row['email4product']);
		unset($row['email4community']);
		unset($row['now_posts']);
		unset($row['sent_msgs']);
		unset($row['new_msgs']);
		unset($row['priv']);

		$row = self::filterUserPoi($row);

		return $row;
	}
	
	public static function filterUserPoi($row)
	{
		if (isset($row['poi'])) {
			unset($row['poi']['x']);
			unset($row['poi']['y']);
			unset($row['poi']['lon']);
			unset($row['poi']['lat']);
			unset($row['poi']['phone']);
			unset($row['poi']['creator']);
			unset($row['poi']['major']);
			unset($row['poi']['create_time']);
			unset($row['poi']['tips']);
			unset($row['poi']['checkins']);
			unset($row['poi']['users']);
			unset($row['poi']['phone']);
			unset($row['poi']['intro']);
			unset($row['poi']['visitors']);
			unset($row['poi']['notification']);
			unset($row['poi']['nid']);
			unset($row['poi']['title']);
			unset($row['poi']['dateline']);
			unset($row['poi']['label']);
			unset($row['poi']['ref_id']);
			unset($row['poi']['certified']);
			unset($row['poi']['ownerid']);
			unset($row['poi']['major_change_time']);
			unset($row['poi']['content']);
			unset($row['poi']['posts']);
			unset($row['poi']['favorites']);
			unset($row['poi']['image_url']);
			unset($row['poi']['logo_url']);
			unset($row['poi']['category_name']);
			unset($row['poi']['category_image']);
			unset($row['poi']['en_category_name']);
		}
		
		if (BETTER_HASH_POI_ID) {
			if ($row['last_checkin_poi']) {
				$row['last_checkin_poi'] = Better_Poi_Info::hashId($row['last_checkin_poi']);
			}
			
			if ($row['poi']['poi_id']) {
				$row['poi']['poi_id'] = Better_Poi_Info::hashId($row['poi']['poi_id']);				
			}
		}				
				
		return $row;
	}
	
	public static function filterBlogPoi($row)
	{
		if (isset($row['poi'])) {
			unset($row['poi']['x']);
			unset($row['poi']['y']);
			unset($row['poi']['lon']);
			unset($row['poi']['lat']);
			unset($row['poi']['create_time']);
			unset($row['poi']['creator']);
			unset($row['poi']['posts']);
			unset($row['poi']['tips']);
			unset($row['poi']['checkins']);
			unset($row['poi']['favorites']);
			unset($row['poi']['users']);
			unset($row['poi']['phone']);
			unset($row['poi']['visitors']);
			unset($row['poi']['notification']);
			unset($row['poi']['nid']);
			unset($row['poi']['title']);
			unset($row['poi']['dateline']);
			unset($row['poi']['ref_id']);
			unset($row['poi']['label']);
			unset($row['poi']['certified']);		
			unset($row['poi']['major']);
			unset($row['poi']['intro']);
			unset($row['poi']['major_change_time']);
			unset($row['poi']['content']);
			unset($row['poi']['image_url']);
			unset($row['poi']['logo_url']);
		}

		if (BETTER_HASH_POI_ID) {
			if ($row['poi']['poi_id']) {
				$row['poi']['poi_id'] = Better_Poi_Info::hashId($row['poi']['poi_id']);
			}
			
			if ($row['poi_id']) {
				$row['poi_id'] = Better_Poi_Info::hashId($row['poi_id']);
			}
			
			if ($row['last_checkin_poi']) {
				$row['last_checkin_poi'] = Better_Poi_Info::hashId($row['last_checkin_poi']);
			}
		}
				
		return $row;
	}
}