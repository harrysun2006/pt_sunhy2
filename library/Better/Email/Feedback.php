<?php

/**
 * 反馈
 *
 * @package Better.Email
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Email_Feedback
{
	public static function send($data, $lan='zh-cn')
	{
		$receiver = $data['receiver'];
		
		$lang = Better_Language::loadIt($lan);
		$subject = $lang->email->subject->feedback;
		
		$mailer = new Better_Email();
		//$mailer->set($data);
		$mailer->setSubject($subject);
		$mailer->setTemplate(APPLICATION_PATH.'/configs/language/email/zh-cn/feedback.html');
		$mailer->addReceiver($receiver, $receiver);
		
		$params = array();
		foreach ($data as $k=>$v) {
			$params[strtoupper($k)] = $v;
		}
		
		$mailer->set($params);
		
		return $mailer->send2();
	}
}