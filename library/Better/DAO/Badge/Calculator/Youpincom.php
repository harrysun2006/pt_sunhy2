<?php

/**
上线：9月1日     10:00；下线：9月15日    24:00 
获得条件 签到任意地点，签到时/吼吼/贴士发布内容中含有"最爱有品网"这几个字，   并至少同步到一个SNS 

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Youpincom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		
		$cc = new Better_User_Diybadge($params);
		$condition = "CC::had_text(array('/最爱有品网/')) && CC::had_syncs(1) && CC::blog_type(array('tips','checkin','normal'))";		
		$todo = str_replace('CC::','$cc->',$condition);			
		try{
			$result =  eval("return ".$todo.";");	
		} catch(Exception $e){
			Better_Log::getInstance()->logInfo($todo."--\n".$e,'diybadgeerror');
		}
		
		if($result)	{
			try{
				self::__sentMsg($uid);
			} catch (Exception $e){
				Better_Log::getInstance()->logInfo($uid,'notgotyoupin');
			}
		}	
		
		return $result;
	}
	
	public static function __sentMsg($uid)
	{
		
		$appConfig = Better_Config::getAppConfig();
		$sys_user_id = $appConfig->user->sys_user_id;
		$content = <<<EOT
		恭喜您获得有品网Yobrand【满99元减30元优惠券】一张！优惠券的激活码为：CODE
使用方式：单笔订单实际支付满99元使用一张，仅限完善资料用户在线支付，每张只能使用一次
优惠券有效期：即日起至2011年9月30日。
详见http://blog.k.ai/?p=2654			
EOT;
		$partner = 'youpincom';
		$coupon = array(
			'uid' => 0,
			'partner' => $partner
		);
		$data = Better_DAO_Marketcoupon::getInstance()->get($coupon);
		$id = $data['id'];
		$no = $data['no'];
		if (!$id) return false;
		$content = str_replace('CODE', $no, $content);
		Better_User_Notification_DirectMessage::getInstance($sys_user_id)->send(array(
												'content' => $content,
												'receiver' => $uid
												));	
		$_data['uid'] = $uid;
		$_data['gtime'] = time();
		$a = Better_DAO_Marketcoupon::getInstance()->update($_data, $id);	

		return $a;
												
	}
	
	
}