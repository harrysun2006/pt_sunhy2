<?php

/**
 * POI掌门数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Poi_Visit_Major extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array(
			'user_concise' => array(),
			'message' => '',
			);

		$data = &$params['data'];
		$userInfo = &$params['userInfo'];
		$poiId = &$params['poi_id'];
		$uid = $userInfo['uid'];
		
		if ($poiId && isset($data['uid'])) {
			$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
			
			$result['user_concise'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$data,
				'userInfo' => &$userInfo
				));

			$majorChanged = (bool)Better_Hook::$hookResults['UserCheckin']['major_changed'];
			
			if ($majorChanged) {
				$message = Better_Language::get()->api->after_checkin->major_info_changed_to_you;
			} else {
				if ($poiInfo['major']==$uid) {
					$message = Better_Language::get()->api->after_checkin->major_info_is_you;
				} else {
					$row = Better_DAO_User_PlaceLog::getInstance($data['uid'])->getMyCheckinCount($poiId);
					$total = (int)$row['total'];
										
					$message = Better_Language::get()->api->after_checkin->major_info;
					$message = str_replace('{NICKNAME}', $data['nickname'], $message);
					$message = str_replace('{TOTAL}', $total, $message);					
				}
			}

			$result['message'] = $message;
		} else {
			$message = Better_Language::get()->api->after_checkin->no_major;
			$result['message'] = $message;
		}
			
		return $result;
	}
}