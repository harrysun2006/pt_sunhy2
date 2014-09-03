<?php

/**
 * 
 * 用户数据转换
 * 
 * @package Better.Api.Translate
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_User extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		if (isset($params['userInfo'])) {
			$userInfo = &$params['userInfo'];	
		} else {
			$userInfo = &$params['data'];
		}
		
		if (isset($params['withStatus']) && !$params['withStatus']) {
			$withStatus = false;
		} else {
			$withStatus = true;
		}
		
		$fromLogin = (isset($params['from_login']) && $params['from_login']==true) ? true : false;
	
		$result = array();
		
		if (isset($data['uid'])) {
			$user = Better_User::getInstance($userInfo['uid']);
			$showUser = Better_User::getInstance($data['uid']);
			
			$result['id'] = $data['uid'];
			$result['name'] = $data['username'];
			$result['screen_name'] = $data['nickname'];
			$result['gender'] = $data['gender'];
			$result['age'] = ($data['birthday'] && $data['birthday']!='0000-00-00') ? ceil(Better_Functions::date('Y')-intval(substr($data['birthday'],0,4))) : 0;
			$result['location'] = strip_tags(Better_User::filterLocation($data));
			$result['description'] = $data['description'] ? $data['description'] : ($data['self_intro'] ? $data['self_intro'] : '');
			$result['profile_image_url'] = $data['avatar_normal'];
			$result['rp'] =  (int)$data['rp'];
			$result['karma'] =  (int)$data['karma'];
			$result['birthday'] = $data['birthday'];
			
			$result['url'] = BETTER_BASE_URL.'/'.$data['username'];
			$result['protected'] = ($data['priv_blog']=='1' || $data['sys_priv_blog']=='1') ? 'true' : 'false';
			
			$result['friend'] = isset($userInfo['uid']) ? ( $data['uid'] == BETTER_SYS_UID || ($userInfo['uid']==$data['uid'] || in_array($data['uid'], $user->friends)) ? 'true' : 'false') : 'false';
			$result['following'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || in_array($data['uid'], $user->followings)) ? 'true' : 'false') : 'false';
			$result['block'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || !in_array($data['uid'], $user->blocks)) ? 'false' : 'true') : 'false';
			$result['relation'] = ($userInfo['uid']!=$data['uid'] && $result['friend']=='false' && $result['block']=='false') ? $user->getRelation($data['uid']) : '0';
			
			$result['sync_sns'] = $userInfo['sync_sns'];
			
			$lang = Better_Language::load('zh-cn');
			$lang = $lang->api->toArray();
			$_SNS = array(
							'sina.com' => $lang['sns']['sina_com']['name'],
							'qq.com' => $lang['sns']['qq_com']['name'],
							'msn.com' => $lang['sns']['msn_com']['name'],
							'renren.com' => $lang['sns']['renren_com']['name'],
							'kaixin001.com' => $lang['sns']['kaixin001_com']['name'],
							'douban.com' => $lang['sns']['douban_com']['name'],
							'fanfou.com' => $lang['sns']['fanfou_com']['name'],
							'facebook.com' => $lang['sns']['facebook_com']['name'],
							'twitter.com' => $lang['sns']['twitter_com']['name'],
							'4sq.com' => $lang['sns']['4sq_com']['name'],
							'sohu.com' => $lang['sns']['sohu_com']['name'],
							'163.com' => $lang['sns']['163_com']['name'],
							'139.com' => $lang['sns']['139_com']['name'],
							'follow5.com' => $lang['sns']['follow5_com']['name'],
							'zuosa.com' => $lang['sns']['zuosa_com']['name'],
						);
						
			$syncSites = (array)Better_User_Syncsites::getInstance($data['uid'])->getSites();
			$result['bind_sns'] = $_r = array();
			
			foreach ( $_SNS as $k => $v ) {
				if ( !array_key_exists($k, $syncSites) ) {
					continue;	
				}
				$_sns['sns'] = array('name' => $v, 'key' => $k);
				$_r[] = $_sns;	 
			}

			$result['bind_sns'] = $_r;
			
			if ($data['uid']!=BETTER_SYS_UID && $user->canViewDoing($data['uid'])) {
				$result['friends_count'] =  (int)$data['friends'];
				$result['favourites_count'] =  (int)$data['favorites'];
				$result['statuses_count'] =  (int)$data['now_posts'];
				$result['checkins_count'] =  (int)$data['checkins'];
				$result['crowns_count'] = (int)$data['majors'];
				$result['followers_count'] = (int)$data['followers'];
				$result['followings_count'] = (int)$data['followings'];
				$result['treasures_count'] = (int)$data['treasures'];
				$result['tips_count'] = (int)$data['now_tips'];
				$result['badges_count'] = (int)$data['badges'];
				$result['poi_favourites_count'] = (isset($data['poi_favorites']) && $data['poi_favorites']>0) ? (int)$data['poi_favorites']: 0;
			} else {
				$result['friends_count'] =  0;
				$result['favourites_count'] =  0;
				$result['statuses_count'] =  (int)$data['now_tips'];
				$result['checkins_count'] =  0;
				$result['crowns_count'] = 0;
				$result['followers_count'] = 0;
				$result['followings_count'] = 0;
				$result['treasures_count'] = 0;
				$result['tips_count'] = 0;
				$result['badges_count'] = 0;				
				$result['poi_favourites_count'] = 0;				
			}
			$result['live_province'] = $data['live_province'];
			$result['live_city'] = $data['live_city'];
			
			$result['ping'] = in_array($data['uid'], (array)$userInfo['pings']) ? 'true' : 'false';

			if ($data['lbs_report']) {
				$result['last_checkin']['create_at'] = parent::time($data['lbs_report']);
				$result['last_checkin']['poi_simple'] = Better_Api_Translator::getInstance('poi_simple')->translate(array(
					'data' => array(
						'poi_id' => (int)$data['last_checkin_poi'],
						'poi_name' => $data['poi']['name'],
						'lon' => $data['poi']['lon'],
						'lat' => $data['poi']['lat']
						),
					'userInfo' => &$userInfo,
					));
			} else {
				if ($fromLogin==true) {
					$tmp = Better_Service_Ip2ll::parse();

					$result['last_checkin'] = array(
						'create_at' => '',
						'poi_simple' => Better_Api_Translator::getInstance('poi_simple')->translate(array(
							'data' => array(
								'force' => true,
								'poi_id' => '',
								'poi_name' => '',
								'lon' => $tmp['lon'],
								'lat' => $tmp['lat'],
								),
							'userInfo' => &$userInfo,
							))
						);
				} else {
					$result['last_checkin'] = array();
				}
			}
			
			//好友是否在动态列表里显示其动态2011-3-29
			$result['home_show'] = isset($params['home_show'])? ($params['home_show'] ? 'true' : 'false') : '';
		}
		
		return $result;
	}
}