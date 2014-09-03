<?php

/**
 * 每10天未登录发邮件
 *
 * @package Better.Email
 *
 */
class Better_Email_Nologin
{
	public static function send()
	{	
		
		$lang_cn = Better_Language::loadIt('zh-cn');
		$lang_en = Better_Language::loadIt('en');
		
		$users = Better_DAO_Emailuser::getNologinEamils();
		foreach($users as $user){
			$lan = $user['language'];			$lan = strtolower($lan);
			if($lan=='en'){				$lang = $lang_en;			}else{				$lan = 'zh-cn';
				$lang = $lang_cn;			}

		$subject = $lang->email->subject->nologin;	
		$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/nologin.html';
		
		$uid = $user['uid'];
		$results = Better_User_Status::getInstance($uid)->webFollowings(array(
			'page' => 1,
			'page_size' => 2
			));
		$results = $results['rows'];
		
		if($results && count($results)>2){
			$tmp = array_chunk($results, 2);
			$result = $tmp[0];
		}else{
			$result = $results;
		}
		
		$friends = '最新好友动态：<br />';
		if($result && count($result)>0){
			foreach($result as $val){
				if($val['poi_id']){
					$val['poi'] = & Better_Poi_Info::getInstance($val['poi_id'])->getBasic();
				}else if($val['last_checkin_poi']){
					$val['poi'] = & Better_Poi_Info::getInstance($val['last_checkin_poi'])->getBasic();
				}else{
					$val['poi']='';
				}
				$friends .=$val['nickname'].'@'.$val['poi']['name'].': '.($val['message']? $val['message'] : ($val['type']=='checkin'? '签到': ($val['attach'] ? '上传新图片': ''))).'<br />';
			}
		}
		
		$email = $user['email'];
		$name = $user['nickname'];
		
		$mailer = new Better_Email($uid);
		$mailer->setSubject($subject);
		$mailer->setTemplate($template);
		$mailer->addReceiver($email, $email);
		$mailer->setType('nologin');
		$mailer->set(array(
			'NAME'=> $name,
			'FRIENDS'=> $friends
			));
			
		$mailer->send2();
		}
		
		return count($users);
		
	}
	
}