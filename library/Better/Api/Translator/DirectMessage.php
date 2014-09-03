<?php

/**
 * 
 * 私信数据转换
 * 
 * @package Better.Api.Translate
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_DirectMessage extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];
		$sessUserInfo = &$params['sessUserInfo'];
		
		$row = array(
			'id' => '',
			'sender' => '',
			'text' => '',
			'recipient_id' => '',
			'created_at' => '',
			'sender_screen_name' => '',
			'recipient_screen_name' => '',
			'sender' => array(
									'id' => '',
									'name' => '',
									'screen_name' => '',
									'gender' => '',
									'age' => '',
									'location' => '',
									'description' => '',
									'profile_image_url' => '',
									'url' => '',
									'place' => '',
									'last_place_at' => '',
									'protected' => '',
									'followers_count' => '',
									'friends_count' => '',
									'favourites_count' => '',
									'statuses_count' => '',
									'following' => '',
									'block' => '',
									),
									
			'recipient' => array(
									'id' => '',
									'name' => '',
									'screen_name' => '',
									'gender' => '',
									'age' => '',
									'location' => '',
									'description' => '',
									'profile_image_url' => '',
									'url' => '',
									'place' => '',
									'last_place_at' => '',
									'protected' => '',
									'followers_count' => '',
									'friends_count' => '',
									'favourites_count' => '',
									'statuses_count' => '',
									'following' => '',
									'block' => '',
									),
			);

		if (isset($data['uid']) && $data['uid'] && isset($userInfo['username'])) {
			$row['id'] = $data['msg_id'];
			$row['text'] = $data['content'];
			$row['created_at'] = parent::time((float)$data['dateline']);
			
			if (isset($data['from_uid'])) {
				$row['sender'] = $data['from_uid'];
				$row['recipient_id'] = $data['uid'];
				$row['sender_screen_name'] = $userInfo['username'];
				$row['recipient_screen_name'] = $sessUserInfo['username'];
				
				$row['sender'] = Better_Api_Translator::getInstance('user')->translate(array(
					'data' => &$userInfo,
					'userInfo' => &$sessUserInfo,
					'withStatus' => false
					));
				$row['recipient'] = Better_Api_Translator::getInstance('user')->translate(array(
					'data' => &$sessUserInfo,
					'userInfo' => &$sessUserInfo,
					'withStatus' => false
					));
			} else if (isset($data['to_uid'])) {
				$row['sender'] = $data['uid'];
				$row['recipient_id'] = $data['to_uid'];
				$row['sender_screen_name'] = $sessUserInfo['username'];
				$row['recipient_screen_name'] = $userInfo['username'];
				
				$row['sender'] = Better_Api_Translator::getInstance('user')->translate(array(
					'data' => &$sessUserInfo,
					'userInfo' => &$sessUserInfo,
					'withStatus' => false
					));
				$row['recipient'] = Better_Api_Translator::getInstance('user')->translate(array(
					'data' => &$userInfo,
					'userInfo' => &$sessUserInfo,
					'withStatus' => false
					));				
				
			}
			unset($row['sender']['status']);
		}
		
		return $row;	
	}
	
}