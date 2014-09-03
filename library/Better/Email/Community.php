<?php

/**
 * 社区动态
 *
 * @package Better.Email
 *
 */
class Better_Email_Community
{
	public static function send()
	{	
		
		$lang_cn = Better_Language::loadIt('zh-cn');
		$lang_en = Better_Language::loadIt('en');
		
		$rows = Better_DAO_Emailuser::getCommunityEmails(array('friendsjoin', 'friendsrequest', 'receivemessage', 'getbadge', 'lostmajor'));
		foreach($rows as $uid=>$row){
			$user = Better_DAO_User::getInstance($uid)->get($uid);
			if($user['uid']){
				$lan = $user['language'];
				$lan = strtolower($lan);
				if($lan=='en'){
					$lang = $lang_en;
					$lan = 'en';
				}else{
					$lang = $lang_cn;
					$lan = 'zh-cn';
				}
			}

		$subject = $lang->email->subject->secretary;	
		$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/community.html';
		$content = '<ul>';
		foreach($row as $val){
			$content .= $val.'<br>';
		}
		$content .= '</ul>';
		
		$email = $user['email'];
		$name = $user['nickname'];
		
		$mailer = new Better_Email($uid);
		$mailer->setSubject($subject);
		$mailer->setTemplate($template);
		$mailer->addReceiver($email, $email);
		$mailer->set(array(
			'NAME'=> $name,
			'CONTENT'=> $content
			));
			
		$mailer->send2();
		}
		
		return count($rows);
		
	}
	
}