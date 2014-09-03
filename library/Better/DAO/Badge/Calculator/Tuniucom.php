<?php

/**
勋章名 途牛旅游 
描述 2011年7月6日至7月31日，签到热门旅游景点、及途牛各城市分公司，并同步至2个社交网络，即可自动获得途牛旅游网虚拟勋章，同时获得抽奖资格，奖品为价值960元丽江酒店体验一晚，名额两位！同时会收到50元途牛网旅游代金券! 
同步语 我获得了开开〖途牛旅游〗勋章！同时还送50元代金券，参与丽江酒店免费住宿抽奖！ 
上下线时间确定 上线：7月6日上午10点；下线：7月31号24点（banner和勋章同时上下线） 
获得条件 签到附件中POI中的任意一个，并同步至2个社交网站 

 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Tuniucom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 7, 6, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 31, 2011);
		$poilist = array(19083715,19083716,873285,7163277,10438875,19083720,19082383,19083721,19083722,19083723,19083724,19083725,19083726,19083727,19083728,19083729,19083731,19083734,19083735,19083736,58349,63261,78042,78611,78859,79526,122811,125333,126634,126952,127356,127630,137972,140279,148182,154889,175173,177416,278069,303109,352231,360012,369668,421809,436492,473893,594608,594619,840397,1029029,1263847,1277196,1355486,1418145,4349651,4436246,4439292,4454289,4579494,4743545,4743901,5178021,7523144,9671383,9680680,9896058,10106454,11169944,11402201,13655324,14499994,15036162,15098848,15974637,15974687,15980302,17197991,17464227,17773503,17939705,18302336,18828170,18870103,19016224,19049615,19054280,19055148,19058981,19065523,19068587,19069166,19074374,19074390,19074395,19074401,19074404,19074406,19074408,19074410,19074414,19074417,19074420,19074422,19074429,19074430,19074590,19074598,19074646,19074774,19074778,19074783,19074791,19074794);
		$poilistcom = array(19083715,19083716,873285,7163277,10438875,19083720,19082383,19083721,19083722,19083723,19083724,19083725,19083726,19083727,19083728,19083729,19083731,19083734,19083735,19083736);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			$checked = false;
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);		
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=2) {				
				$checked = true;			
			}
			
			$blog = &$params['blog'];						
			if ($checked && ($blog['type']=='normal' || $blog['type']=='checkin')) {
				$message = strtolower($blog['message']);
				$checked1 = '/途牛推荐景点/';					
				if (preg_match($checked1, $message)) {		
					{			
						$result = true;			
					}
				}
			}
			if($checked && in_array($poiId,$poilistcom)){
				$result = true;	
			}
		}	
		if($result)	{
			try{
				self::__sentMsg($uid);
			} catch (Exception $e){
				Better_Log::getInstance()->logInfo($uid,'notgottuniu');
			}
		}	
		
		return $result;
	}
	
	public static function __sentMsg($uid)
	{
		
		$appConfig = Better_Config::getAppConfig();
		$sys_user_id = $appConfig->user->sys_user_id;
		$content = <<<EOT
恭喜你获得了途牛旅行网50元抵用券CODE！使用详情请见开开官博http://blog.k.ai/?p=1721			
EOT;
		$partner = 'tuniucom';
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