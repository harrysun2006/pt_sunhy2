<?php

/**
 * 转换微博
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Status extends Better_Api_Translator_Base
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
			
			$blogPoi = (isset($blog['poi_id']) && $blog['poi_id']) ? ($blog['blog_poi'] ? $blog['blog_poi'] : Better_Poi_Info::getInstance($blog['poi_id'])->getBasic()) : ($data['poi_id'] ? $data['poi'] : array());
			
			$result['create_at'] = parent::time((float)$data['dateline']);
			$result['id'] = $data['bid'];
			$result['category'] = $data['type']=='tips' ? 'tip' : $data['type'];
			
			$poi = isset($blogPoi['name']) ? $blogPoi : (array)$data['poi'];
			if ($data['type'] == 'normal' || $data['type'] == 'tips') {
				$message = $data['message'] ? $data['message'] :
					($data['attach'] ? Better_Language::load()->javascript->blog_with_photo_no_message :
					($data['upbid'] ? Better_Language::load()->javascript->global->blog->rt : ''));
			} else {
				$message = $data['message'];
			}
			$message = str_replace(array('＠'), array('@'), $message);
			
			
			if ($data['type']=='checkin' && $data['major']>0) {
				$result['text'] = str_replace('{POI}', $poi['name'], Better_Language::load()->global->checkin->got_major).($message ? ' : '.$message : '');
			} else if ($data['type']=='checkin' && $data['badge_id']) {
				$result['text'] = str_replace('{BADGE}', ' '.$data['badge_detail']['name'].' ', Better_Language::load()->global->checkin->got_badge).($message ? ' : '.$message : '');
			} else if ($data['type']=='checkin' && $message=='') {
				$result['text'] = str_replace('{CITY}', (trim($poi['city']) ? ' '.$poi['city'].' ' : ''), str_replace('{POI}', ' '.$poi['name'].' ', Better_Language::load()->javascript->noping->global->checkin->at));
			} else if($data['type']=='todo'){
					$result['category'] = 'normal';
					if($message){
						$result['text'] = str_replace('{MSG}', ' '.$message.' ', str_replace('{POI}', ' '.$blogPoi['name'].' ', Better_Language::load()->javascript->noping->global->todo->withmsg));
					}else{
						$result['text'] = str_replace('{POI}', ' '.$blogPoi['name'].' ', Better_Language::load()->javascript->noping->global->todo->nomsg);
					}
			}else {
				$result['text'] = preg_replace('/\\s/', ' ', $message);
			}
			
			if ($data['badge_id'] && !$data['attach']) {
				$badge = Better_Badge::getBadge($data['badge_id'])->getParams();
				$badgeUrl = str_replace('images/badges', 'images/badges/big', $badge['badge_picture']);
				
				$_uid = Better_Registry::get('user')->getUid();
				if ($_uid) {
					$clientCache = Better_User::getInstance($_uid)->cache()->get('client');
					$res = $clientCache['res'];
					if ($res) {
						list($w, $h) = explode('x', $res);
						if ($w < 320) {
							$badgeUrl = str_replace('images/badges', 'images/badges/96', $badge['badge_picture']);
						} 
					}				
				}
		
				$data['attach_url'] = $badgeUrl;
				$data['attach_thumb'] = $badgeUrl;
				$data['attach_tiny'] = $badge['badge_picture'];
				$result['badge_id'] = $data['badge_id'];
			} else {
				$result['badge_id'] = '';
			}
			
			$result['text'] = stripslashes(htmlspecialchars_decode($result['text']));
			$result['text_at'] = array();
			$ats = Better_Blog::apiParseBlogAt($message);
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

			$result['source'] = Better_Functions::source($data['source']);
			$result['favorited'] = in_array($data['bid'], (array)$user->favorites) ? 'true' : 'false';
			$result['photo_url'] = $data['attach_thumb'];
			$result['photo_thumb_url'] = $data['attach_tiny'];
			$result['photo_origin_url'] = $data['attach_url'];
			$result['up'] = (int)$data['up'];
			$result['down'] = (int)$data['down'];

			$result['user_concise'] = Better_Api_Translator::getInstance('user_concise')->translate(array(
				'data' => &$blogUserInfo,
				'userInfo' => &$userInfo,
				'withStatus' => false,
				));
			$result['poi_concise'] = Better_Api_Translator::getInstance('poi_concise')->translate(array(
				'data' => $blogPoi
				));
			
			if ($data['upbid']) {
				$upData = Better_Blog::getBlog($data['upbid']);
				$blog = &$upData['blog'];
				if($blog['priv']!='private' || $blog['uid']==$userInfo['uid']){
				$upPoiInfo = array();
				if ($blog['poi_id']) {
					$upPoiInfo = Better_Poi_Info::getInstance($blog['poi_id'])->getBasic();
				}
				
				$blogPoi = isset($blog['blog_poi']['name']) ? $blog['blog_poi'] : (array)$blog['poi'];
				$message = $blog['message'];
				if ($blog['dateline']>0) {
					if ($blog['type']=='checkin' && $blog['major']>0) {
						$message = str_replace('{POI}', $blogPoi['name'], Better_Language::load()->global->checkin->got_major).($message ? ' : '.$message : '');
					} else if ($blog['type']=='checkin' && $blog['badge_id']) {
						$message = str_replace('{BADGE}', ' '.$blog['badge_detail']['name'].' ', Better_Language::load()->global->checkin->got_badge).($message ? ' : '.$message : '');				
					} else if ($blog['type']=='checkin' && $message=='') {
						$message = str_replace('{CITY}', ' '.$blogPoi['city'].' ', str_replace('{POI}', ' '.$blogPoi['name'].' ', Better_Language::load()->javascript->noping->global->checkin->at));
					} else if($blog['type']=='todo'){
						$blog['type']='normal';
						if($message){
							$message = str_replace('{MSG}', ' '.$message.' ', str_replace('{POI}', ' '.$blogPoi['name'].' ', Better_Language::load()->javascript->noping->global->todo->withmsg));
						}else{
							$message = str_replace('{POI}', ' '.$blogPoi['name'].' ', Better_Language::load()->javascript->noping->global->todo->nomsg);
						}
					}else{
						$message = $blog['message'];
					}
					
					/*if ($blog['badge_id'] && !$blog['attach']) {
						$badge = Better_Badge::getBadge($blog['badge_id'])->getParams();
						$badgeUrl = str_replace('images/badges', 'images/badges/big', $badge['badge_picture']);
						$blog['attach_thumb'] = $blog['attach_tiny'] = $blog['attach_url'] = $badgeUrl;
					}*/
					
					if ($blog['badge_id'] && !$blog['attach']) {
						$badge = Better_Badge::getBadge($blog['badge_id'])->getParams();
						$badgeUrl = str_replace('images/badges', 'images/badges/big', $badge['badge_picture']);
						$blog['attach_url'] = $badgeUrl;
						$blog['attach_thumb'] = $badgeUrl;
						$blog['attach_tiny'] = $badge['badge_picture'];
					} else {
						$blog['badge_id'] = '';
					}
										
					$textAt = array();
					$ats = Better_Blog::apiParseBlogAt($message);
					if (count($ats)>0) {
						foreach ($ats as $atNickname=>$atUid) {
							$textAt[] = array(
								'at' => Better_Api_Translator::getInstance('status_at')->translate(array(
									'data' => array(
										'uid' => $atUid,
										'nickname' => $atNickname
										)
									))
								);
						}
					}					

					$result['in_reply_to_status'] = array(
						'id' => $data['upbid'],
						'category' => $blog['type'],
						'text' => stripslashes(htmlspecialchars_decode($message)),
						'text_at' => $textAt,
						'source' => Better_Functions::source($blog['source']),
						'create_at'=> parent::time((float)$blog['dateline']),
						'photo_url' => $blog['attach_thumb'],
						'photo_thumb_url' => $blog['attach_tiny'],
						'photo_origin_url' => $blog['attach_url'],
						'user_concise' => Better_Api_Translator::getInstance('user_concise')->translate(array(
							'data' => &$upData['user'],
							'userInfo' => &$userInfo,
							)),
						'poi_concise' => Better_Api_Translator::getInstance('poi_concise')->translate(array(
							'data' => &$blogPoi,
							'userInfo' => &$userInfo,
							)),
							'comments_count'=> $blog['comments']? $blog['comments']: 0
						);
				} else {
					$result['in_reply_to_status'] = array();
				}
				}
			}

			//comments
			$result['comments_count'] = $data['comments']? $data['comments']: 0;
			if($params['comments']){
				$comments = Better_Blog::getRepliesByBid($data['bid']);
				if(count($comments)>0){
					foreach($comments as $row){
						$result['comments'][] = array('comment'=>Better_Api_Translator::getInstance('comment')->translate(array(
								'data' => &$row
						)));
					}
				}else{
					$result['comments'] = array();
				}
			}
			
			$result['allow_rt'] = isset($data['allow_rt']) ? ($data['allow_rt']==1 ? 'true': 'false') : '';
			$result['is_top'] = isset($data['is_top']) ? ($data['is_top']==1 ? 'true': 'false') : '';
			$result['featured'] = isset($data['featured']) ? ($data['featured']==1 ? 'true': 'false') : '';
		}

		return $result;		
	}
}