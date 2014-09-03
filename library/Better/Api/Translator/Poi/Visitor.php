<?php

/**
 * poi访客
 * 
 * @package Better.Api.Translator.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Visitor extends Better_Api_Translator_Base
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
			$result['gender'] = $data['gender'];
			$result['age'] = ($data['birthday'] && $data['birthday']!='0000-00-00') ? ceil(Better_Functions::date('Y')-intval(substr($data['birthday'],0,4))) : 0;
			$result['location'] = strip_tags(Better_User::filterLocation($data));
			$result['description'] = $data['description'];
			$result['profile_image_url'] = $data['avatar_normal'];
			$result['rp'] = (int)$data['karma'];
			$result['protected'] = ($data['priv_blog']=='1' || $data['sys_priv_blog']=='1') ? 'true' : 'false';
			$result['friend'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || in_array($data['uid'], $user->friends)) ? 'true' : 'false') : 'false';
			$result['following'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || in_array($data['uid'], $user->followings)) ? 'true' : 'false') : 'false';
			$result['block'] = isset($userInfo['uid']) ? (($userInfo['uid']==$data['uid'] || !in_array($data['uid'], $user->blocks)) ? 'false' : 'true') : 'false';			

			if ($data['checkin_time']) {
				$result['last_checkin']['create_at'] = parent::time($data['checkin_time']);
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
		}
		
		return $result;
	}
}