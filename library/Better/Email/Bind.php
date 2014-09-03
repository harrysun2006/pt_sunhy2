<?php

/**
 * 绑定Email地址
 *
 * @package Better.Email
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Email_Bind
{
	
	public static function hasQueue($email)
	{
		$data = Better_DAO_BindEmail::getInstance()->get(array(
			'email' => $email,
			));
			
		if (!$data['uid']) {
			$user = Better_User::getInstance($email, 'email');
			$data = $user->getUserInfo();
		}
			
		return isset($data['uid']) ? $data['uid'] : 0;
	}

	public static function send($data)
	{
		$hash = md5(uniqid(rand()));
		$uid = $data['uid'];
		$email = $data['email'];
		$nickname = $data['nickname'];
		
		$link = BETTER_BASE_URL.'/signup/enable?h='.$hash;
		
		Better_DAO_BindEmail::getInstance()->deleteByCond(array(
			'uid' => $uid
		));

		Better_DAO_BindEmail::getInstance()->insert(array(
			'uid' => $uid,
			'email' => $email,
			'hash' => $hash,
			'dateline' => time(),
			));

		$mailer = new Better_Email($uid);
		$mailer->set($data);
		$mailer->setSubject(Better_Registry::get('lang')->email->subject->bind);
		
		if ($data['userChange']) {
			$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/modifyemail.html');
		} else {
			$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/bindemail.html');
		}
		$mailer->addReceiver($email, $nickname);
		$mailer->set(array(
			'ENABLE_LINK' => $link,
			'RESEND_LINK' => BETTER_BASE_URL.'/signup/resend',
			'NICKNAME' => $nickname
			));
		return $mailer->send();
	}

}