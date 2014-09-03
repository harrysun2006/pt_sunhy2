<?php

/**
 * 邀请Email联系人
 *
 * @package Better.Email
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Email_Invite
{
	public static function send($toInvite, $data, $content='')
	{
		$hash = md5(uniqid(rand()));
		$uid = $data['uid'];
		$email = $data['email'];
		$nickname = $data['nickname'];
		$signupLink = BETTER_BASE_URL.'/signup?ref='.$uid;
		$subject = Better_Registry::get('lang')->email->noping->subject->invite;
		$content = strlen($content) ? '<br />'.$content.'<br />' : '';
		
		$mailer = new Better_Email();
		$mailer->set($data);
		$mailer->setSubject($subject);
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/invite.html');
		$mailer->addReceiver(strtolower($toInvite), $toInvite);
		$mailer->set(array(
			'EMAIL_TO_INVITE' => $toInvite,
			'SIGNUP_LINK' => $signupLink,
			'CONTENT' => $content,
			));
		return $mailer->send();
	}
	//增加发送用户姓名
	public static function sendbyname($toInvite, $data, $content='')
	{
		$hash = md5(uniqid(rand()));
		$uid = $data['uid'];
		$email = $data['email'];
		$nickname = $data['nickname'];
		$signupLink = BETTER_BASE_URL.'/signup?ref='.$uid;
		$subject = Better_Registry::get('lang')->email->subject->invite;
		$content = strlen($content) ? '<br />'.$content.'<br />' : '';
		if(is_array($toInvite)){
			$toInvite_mail = $toInvite['mail'];
			if(strlen($toInvite['name'])>0){
				$toInvite_name = $toInvite['name'];
			} else {
				$toInvite_name = $toInvite_mail;
			}			
		} else {
			$toInvite_mail = $toInvite_name = $toInvite;
		}
		$mailer = new Better_Email();
		$mailer->set($data);
		$mailer->setSubject($subject);
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/invite.html');
		$mailer->addReceiver(strtolower($toInvite_mail), $toInvite_name);
		$mailer->set(array(
			'EMAIL_TO_INVITE' => $toInvite_name,
			'SIGNUP_LINK' => $signupLink,
			'CONTENT' => $content,
			));
		return $mailer->send();
	}
}