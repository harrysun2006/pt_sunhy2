<?php

/**
 * 绑定手机
 *
 * @package Better.Email
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Email_Bindcell
{
	public static function send($data)
	{
		$email = $data['email'];
		$nickname = $data['nickname'];
		$uid = (int)$data['uid'];
		
		$subject = Better_Registry::get('lang')->email->subject->bind_cell;
		
		$mailer = new Better_Email($uid);
		$mailer->setSubject($subject);
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/bindcell.html');
		$mailer->addReceiver($email, $email);
		
		$mailer->set(array('NAME'=>$nickname));
		
		return $mailer->send();
	}
}