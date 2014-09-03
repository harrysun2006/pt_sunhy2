<?php

/**
 * 加关注请求
 *
 * @package Better.Email
 * @author pysche
 *
 */
class Better_Email_FriendsRequest
{
	public static function send($userInfo, $requestUserInfo)
	{

		$homepage = BETTER_BASE_URL.'/'.$requestUserInfo['username'];

		$subject = $requestUserInfo['nickname'].Better_Registry::get('lang')->email->subject->friends_request;
		$mailer = new Better_Email();
		$mailer->setSubject($subject);
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/'.Better_Registry::get('language').'/friendsrequest.html');
		$mailer->addReceiver($userInfo['email'], $userInfo['nickname']);
		$mailer->set(array(
			'REQUEST_HOMEPAGE' => $homepage,
			'REQUEST_REALNAME' => $requestUserInfo['nickname'],
			'REALNAME' => $userInfo['nickname'],
			));
		return $mailer->send();
	}
}