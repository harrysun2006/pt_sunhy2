<?php

/**
 * 转换微博
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Comment extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		
		$result = array();
		
		$data = &$params['data'];

		if (is_array($data) && isset($data['bid'])) {
			
			$result['create_at'] = parent::time((float)$data['dateline']);
			$result['id'] = $data['id'];
			$result['bid'] = $data['bid'];
			
			$result['text'] = preg_replace('/\\s/', ' ', $data['message']);
			$result['text'] = stripslashes(htmlspecialchars_decode($result['text']));
			
			$result['text_at'] = array();
			$ats = Better_Blog::apiParseBlogAt($data['message']);
			if (count($ats)>0) {
				foreach ($ats as $atNickname=>$atUid) {
					$result['text_at'][] = array(
						'at' => Better_Api_Translator::getInstance('status_at')->translate(array(
							'data' => array(
								'uid' => $atUid,
								'nickname' => $atNickname
								)
							))
						);
				}
			}

			$userInfo = $data['user'];
			$result['user_concise'] = array(
				'id'=>$userInfo['uid'],
				'screen_name'=>$userInfo['nickname'],
				'name'=>$userInfo['username'],
				'profile_image_url'=>$userInfo['avatar_normal']
			);
			
			
			//permit delete
			$sessUid = Better_Registry::get('sess') ? Better_Registry::get('sess')->get('uid'): '';
			$result['permit_del'] = ($sessUid==$data['uid'] || $sessUid==$data['blog_uid']) ? 'true': 'false';
				
		}

		return $result;		
	}
}