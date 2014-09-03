<?php

/**
 * 群发Email
 *
 * @package Better.Email
 *
 */
class Better_Email_Common  
{
	public static function send($params)
	{	
		
		$lang_cn = Better_Language::loadIt('zh-cn');
		$lang_en = Better_Language::loadIt('en');
		
		$act_name = $params['act_name'];
		$source = $params['source'];
		$poi_id = $params['poiid'];
		$type = $params['type'];
		$content = nl2br($params['content']);
		
		if($type=='7' && $source){
			$users = Better_DAO_Emailuser::getMobileEmails($source);
		}else if($type=='8' && $poi_id){
			$poi = Better_Poi_Info::getInstance($poi_id)->getBasic();
			if($poi['poi_id']){
				$content = str_replace('{POI}', $poi['name'], $content);
				$content = str_replace('{POI_ID}', $poi['poi_id'], $content);
			}
			$users = Better_DAO_Emailuser::getPOIEmails($poi_id);
		}else if($type=='5' || $type=='6'){
			$users = Better_DAO_Emailuser::getAllEmails(2);
		}else{
			$users = Better_DAO_Emailuser::getAllEmails();
		}
		
		foreach($users as $user){
			$lan = $user['language'];
			$lan = strtolower($lan);
			if($lan=='en'){
				$lang = $lang_en;
			}else{
				$lang = $lang_cn;
				$lan = 'zh-cn';
			} 
		
		switch($type)
		{
			case '1':
				$subject = $lang->email->subject->new_pre;
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/foretell_new_feature.html';
				break;
			case '2':
				$subject = $lang->email->subject->new_now;
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/new_feature_online.html';
				break;
			case '3':
				$subject = $lang->email->subject->new_activity;
				$subject = str_replace('{NAME}', $act_name, $subject);
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/new_activity.html';
				break;
			case '4':
				$subject = $lang->email->subject->maintenance;
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/sys_maintance.html';
				break;
			case '5':
				$subject = $lang->email->subject->treasure_pre;
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/foretell_treasure.html';
				break;
			case '6':
				$subject = $lang->email->subject->treasure;
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/treasure.html';
				break;
			case '7':
				$subject = $lang->email->subject->new_version;
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/new_version.html';
				break;
			case '8':
				$subject = $lang->email->subject->poi_major;
				$template = APPLICATION_PATH.'/configs/language/email/'.$lan.'/poi_major.html';
				break;
			default:
				break;
		}
		
		
		$uid = $user['uid'];
		$email = $user['email'];
		$name = $user['nickname'];
		
		$mailer = new Better_Email($uid);
		$mailer->setSubject($subject);
		$mailer->setTemplate($template);
		$mailer->addReceiver($email, $email);
		$mailer->set(array(
			'CONTENT' => $content,
			'NAME'=> $name
			));
			
		$mailer->send2();
		}
		
		return count($users);
		
	}
	
}