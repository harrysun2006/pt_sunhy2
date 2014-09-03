<?php

/**
 * 转换用户简要信息
 * 
 * @package Better.Api.Translator.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_User_Concise extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		if (isset($params['userInfo'])) {
			$userInfo = &$params['userInfo'];	
		} else {
			$userInfo = &$params['data'];
		}

		$result = array();
			
		if (isset($data['uid'])) {
			$user = Better_User::getInstance($userInfo['uid']);
			
			$result['id'] = $data['uid'];
			$result['name'] = $data['username'];
			$result['screen_name'] = $data['nickname'];
			if ($data['adbname']) $result['screen_name'] .= '(' . $data['adbname'] . ')';
			$result['gender'] = $data['gender'];
			$result['age'] = ($data['birthday'] && $data['birthday']!='0000-00-00') ? ceil(Better_Functions::date('Y')-intval(substr($data['birthday'],0,4))) : 0;
			$result['location'] = strip_tags(Better_User::filterLocation($data));
			$result['description'] = $data['description'] ? $data['description'] : ($data['self_intro'] ? $data['self_intro'] : '');
			$result['profile_image_url'] = $data['avatar_normal'];
			$result['rp'] = (int)$data['rp'];
			$result['karma'] = (int)$data['karma'];
			$result['protected'] = ($data['priv_blog']=='1' || $data['sys_priv_blog']=='1') ? 'true' : 'false';
			$result['friend'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || in_array($data['uid'], $user->friends)) ? 'true' : 'false') : 'false';
			$result['following'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || in_array($data['uid'], $user->followings)) ? 'true' : 'false') : 'false';
			$result['block'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || !in_array($data['uid'], $user->blocks)) ? 'false' : 'true') : 'false';			
			$result['relation'] = ($userInfo['uid']!=$data['uid'] && $result['friend']=='false' && $result['block']=='false') ? $user->getRelation($data['uid']) : '0';
			$result['live_province'] = $data['live_province'];
			$result['live_city'] = $data['live_city'];
			if ($data['lbs_report']) {
				$result['last_checkin']['create_at'] = parent::time($data['lbs_report']);
				$result['last_checkin']['poi_simple'] = Better_Api_Translator::getInstance('poi_simple')->translate(array(
					'data' => array(
						'poi_id' => $data['last_checkin_poi'],
						'poi_name' => $data['poi']['name'],
						'lon' => $data['poi']['lon'],
						'lat' => $data['poi']['lat'],
						),
					'userInfo' => &$userInfo,
					));
			} else {
				$result['last_checkin'] = array();
			}
			
			//访客信息增加时间和次数2011-2-13
			if($data['checkin_time']){
				$result['visit_time'] = parent::time($data['checkin_time']);
			}
			if($data['checkin_count']){
				$result['visit_count'] = $data['checkin_count'];
			}
			
			$result['allow_rt'] = $data['allow_rt'] ? 'true': 'false';
			$result['fr_msg'] = (Better_Registry::get('sess') && Better_Registry::get('sess')->get('uid')!=BETTER_SYS_UID) ? ($data['friend_sent_msg'] ? 'true': 'false') : 'false';
			
			//好友是否在动态列表里显示其动态2011-3-29
			$result['home_show'] = isset($params['home_show'])? ($params['home_show'] ? 'true' : 'false') : '';
		}
		
		return $result;
	}
}