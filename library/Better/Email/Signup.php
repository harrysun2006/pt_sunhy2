<?php

/**
 * 注册欢迎信
 *
 * @package Better.Email
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Email_Signup
{

	public static function send($s)
	{
		$uid = $s['uid'];
		$email = $s['email'];
		$hash = md5(uniqid(rand()));
		$link = BETTER_BASE_URL.'/signup/enable?h='.$hash;
		$resendLink = BETTER_BASE_URL.'/signup/resend';
		
		Better_DAO_BindEmail::getInstance($uid)->insert(array(
			'uid' => $uid,
			'email' => $email,
			'hash' => $hash,
			'dateline' => time(),
			));

		$language = Better_Registry::get('language');
		if ($s['language']) {
			$language = $s['language'];
		}
		
		$mailer = new Better_Email($uid);
		$mailer->set($s);
		$mailer->setSubject(Better_Registry::get('lang')->email->subject->signup);
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.$language.'/signup.html');
		$mailer->addReceiver($s['email'], $s['nickname']);
		$mailer->set(array(
			'ENABLE_LINK' => $link,
			'RESEND_LINK' => $resendLink,
			'NICKNAME' => $s['nickname'],
			));
			
		return $mailer->send();
	}

}