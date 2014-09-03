<?php

/**
 * 评论类型的 Status
 * 
 * @package Better.Api.Translator
 * @author yangl
 *
 */
class Better_Api_Translator_Status_Comment extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
		$data = &$params['data'];
		$userInfo = $data['user'];
		
		if (isset($data['comment_id'])) {
			$result['create_at'] = parent::time((float)$data['dateline']);
			$result['id'] = $data['bid'];
			$result['category'] = 'comment';
			
			$bid = $data['bid'];
			$blog = Better_User_Status::getInstance(Better_Registry::get('sess')->get('uid'))->byBid($bid);
			if($blog['type']=='checkin'){
				$type='签到';
				$blog['message'] = '在 '.$blog['poi']['city'].' '.$blog['poi']['name'].' 签到'.($blog['message']? '：'.$blog['message'] : '');
			}else if($blog['type']=='tips'){
				$type='贴士';
			}else{
				$type='吼吼';
			}
			if ($blog['message'] == '') {
				$blog['message'] = $blog['attach'] ? '上传一张新图片' : ($blog['upbid'] ? Better_Language::load()->javascript->global->blog->rt : '');
			}
			$old_msg = Better_Cutstring::mystrcut($blog['message'], 30);
			
			$text = $data['message']."\r\n".'评论了'.$type.':“'.$old_msg.'”';
			$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
			$result['text'] = htmlspecialchars_decode($text);
			$result['text_at'] = array();
			$ats = Better_Blog::apiParseBlogAt($result['text']);
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
			
			$result['user_concise'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => $userInfo,
				'userInfo' => &$userInfo
				));
			
			$result['favorited'] = '';
			$result['photo_url'] = '';
			$result['photo_thumb_url'] = '';
			$result['photo_origin_url'] = '';
			$result['up'] = '';
			$result['down'] = '';
			$result['poi_concise'] = '';
			list($blog_uid, $o) = explode('.', $data['bid']);
			$result['comments_count'] = Better_DAO_Blogreply::getInstance($blog_uid)->getRepliesCount($data['bid']);
			
			
		}
		
		return $result;
	}
}