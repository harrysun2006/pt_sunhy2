<?php

/**
 * 重置密码
 *
 * @package Better.Email
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Email_Resetpwd
{

	public static function send($userInfo)
	{
		$hash = md5(uniqid(rand()));

		$link = BETTER_BASE_URL.'/resetpwd/form?h='.$userInfo['uid'].'_'.$hash;
					
		$s = array();
		$s['uid'] = $userInfo['uid'];
		$s['dateline'] = time();
		$s['hash'] = $hash;
		Better_DAO_Resetpwd::getInstance($userInfo['uid'])->insert($s);

		$mailer = new Better_Email($userInfo['uid']);
		$mailer->setSubject(Better_Registry::get('lang')->email->subject->resetpwd);
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/resetpwd.html');
		$mailer->set($userInfo);
		$mailer->set(array(
			'RESETPWD_LINK' => "<a href='".$link."' target='_blank'>".$link."</a>",
			'NAME'=> $userInfo['nickname']
			));
		$mailer->addReceiver($userInfo['email'], $userInfo['nickname']);
		
		return $mailer->send();
	}

}