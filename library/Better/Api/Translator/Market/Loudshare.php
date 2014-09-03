<?php

/**
 * 转换大声展微博
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Market_Loudshare extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
		
		$data = &$params['data'];
		$blog = &$params['blog'];

		if (isset($params['userInfo'])) {
			$userInfo = &$params['userInfo'];	
		} else {
			$userInfo = array();
		}

		if (is_array($data) && isset($data['bid'])) {
			
			$user = Better_User::getInstance($userInfo['uid']);
			$blogUser = Better_User::getInstance($data['uid']);
			$blogUserInfo = $blogUser->getUserInfo();
			
			$result['dateline'] = $data['dateline'];
			$result['username'] = $data['username'];
			$result['nickname'] = $data['nickname'];
			$result['avatar_url'] = $blogUserInfo['avatar'] ? $blogUserInfo['avatar_normal'] : 'http://k.ai/images/market/sharism-face.png';
			
			$blogPoi = (isset($blog['poi_id']) && $blog['poi_id']) ? ($blog['blog_poi'] ? $blog['blog_poi'] : Better_Poi_Info::getInstance($blog['poi_id'])->getBasic()) : ($data['poi_id'] ? $data['poi'] : array());

			$result['category'] = $data['type']=='tips' ? 'tip' : $data['type'];
			
			$poi = isset($blogPoi['name']) ? $blogPoi : (array)$data['poi'];
			$message = $data['message'];
			
			if ($data['type']=='checkin' && $data['major']>0) {
				$result['text'] = str_replace('{POI}', $poi['name'], Better_Language::load()->global->checkin->got_major).($message ? ' : '.$message : '');
			} else if ($data['type']=='checkin' && $data['badge_id']) {
				$result['text'] = str_replace('{BADGE}', ' '.$data['badge_detail']['name'].' ', Better_Language::load()->global->checkin->got_badge).($message ? ' : '.$message : '');
			} else {
				$result['text'] = $message;
			}
			
			$result['text'] = stripslashes(htmlspecialchars_decode($result['text']));

			$result['source'] = Better_Functions::source($data['source']);
			$result['photo_url'] = $data['attach_url'];
			$result['photo_thumb_url'] = $data['attach_thumb'];
			
			$result['poi'] = array(
				'name' => $blogPoi['name'],
				'lon' => $blogPoi['lon'],
				'lat' => $blogPoi['lat'],
				'id' => $blogPoi['poi_id']
				);
		}

		return $result;		
	}
}