<?php

/**
 * 帐号数据
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Profile extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		$data = &$params['data'];
		$userInfo = &$params['userInfo'];

		if (isset($data['uid'])) {
			$result['email'] = $data['email'];
			$result['phone'] = $data['cell_no'];
			$result['username'] = $data['username'];
			$result['nickname'] = $data['nickname'];
			$result['realname'] = $data['realname'];
			$result['image_url'] = $data['avatar_normal'];
			$result['gender'] = $data['gender'];
			$result['province'] = $data['province'];
			$result['city'] = $data['city'];
			$result['birthday'] = $data['birthday'];
			$result['description'] = $data['description'] ? $data['description'] : ($data['self_intro'] ? $data['self_intro'] : '');
			$result['protected'] = ($data['priv_blog']=='1' || $data['sys_priv_blog']=='1') ? 'true' : 'false';
			$result['province'] = $data['live_province'];
			$result['city'] = $data['live_city'];
			
			/*switch ($data['priv_location']) {
				case '2':
					$result['place_visibility'] = 'none';
					break;
				case '1':
					$result['place_visibility'] = 'city';
					break;
				case '0':
					$result['place_visibility'] = 'exact';
					break;
			}*/		
			
			//imlist
			$result['imlist'] = array();
			if (is_array($data['ims']) && count($data['ims'])>0) {
				foreach ($data['ims'] as $im) {
					$result['imlist'][] = array(
						'im' => array(
							'name' => $im['name'],
							'account' => $im['account'],
							),
						);
				}
			}
			
			//snslist
			$result['snslist'] = array();
			if (is_array($data['sns']) && count($data['sns'])>0) {
				foreach ($data['sns'] as $sns) {
					$result['snslist'][] = array(
						'sns' => array(
							'name' => $sns['name'],
							'account' => $sns['account'],
							),
						);
				}
			}
			$result['pingall'] = $data['allow_ping'] ? 'true' : 'false';
			
			$result['allow_rt'] = $data['allow_rt'] ? 'true': 'false';
			$result['fr_msg'] = $data['friend_sent_msg'] ? 'true': 'false';
		}
		
		return $result;
	}
}