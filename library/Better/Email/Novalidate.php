<?php

/**
 * 3天未验证发邮件
 *
 * @package Better.Email
 *
 */
class Better_Email_Novalidate
{
	public static function send()
	{	
		
		$lang_cn = Better_Language::loadIt('zh-cn');
		$lang_en = Better_Language::loadIt('en');
		
		$users = Better_DAO_Emailuser::getNovalidateEmails();
		foreach($users as $user){
			$uid = $user['uid'];
			$row = Better_DAO_BindEmail::getInstance()->get(array('uid'=>$uid));
			$link = '';
			if($row && $row['hash']){
				$hash = $row['hash'];
				$link = BETTER_BASE_URL.'/signup/enable?h='.$hash;
			}
			
			$lan = $user['language'];
			$lan = strtolower($lan);
			if($lan=='en'){
				$lang = $lang_en;
			}else{
				$lang = $lang_cn;
				$lan = 'zh-cn';
			}

		$subject = $lang->email->subject->novalidate;	
		$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/novalidate.html';
		
		$email = $user['email'];
		$name = $user['nickname'];
		
		$mailer = new Better_Email($uid);
		$mailer->setSubject($subject);
		$mailer->setTemplate($template);
		$mailer->addReceiver($email, $email);
		$mailer->setType('novalidate');
		$mailer->set(array(
			'NAME'=> $name,
			'ENABLE_LINK'=> $link
			));
			
		$mailer->send2();
		}
		
		return count($users);
	}
	
}