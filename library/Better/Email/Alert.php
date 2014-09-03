<?php

class Better_Email_Alert
{

	public static function send($data)
	{
		$uid = $data['uid'];
		$treasureId = $data['treasure_id'];
		$treasureName = $data['treasure_name'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$mailer = new Better_Email($uid);
		$mailer->set($data);
		$mailer->setSubject('Somebody Got Low ratio treasure !!!');
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/zh-cn/alert.phtml');
		foreach (explode('|', Better_Config::getAppConfig()->alert_receiver) as $email) {
			$mailer->addReceiver($email, $email);
		}
		$mailer->set(array(
			'UID' => $uid,
			'NICKNAME' => $userInfo['nickname'],
			'USERNAME' => $userInfo['username'],
			'DATE' => date('Y-m-d H:i:s'),
			'TREASURE_ID' => $treasureId,
			'TREASURE' => $treasureName
			));
		return $mailer->send();
	}

}