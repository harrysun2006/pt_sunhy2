<?php

/**
〖爱乐透〗勋章 
上下线时间确定 6月1日10点至6月15日24点（banner同时上下线） 
获得条件 签到任意地点，吼吼#爱乐透#，并同步至至少一个社交网络 


5月6日即时
6月7日1:00am

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Letou extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 6, 1, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 15, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm){	
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);						
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {	
				$blog = &$params['blog'];						
				if ($blog['type']=='normal' || $blog['type']=='checkin') {
					$message = strtolower($blog['message']);
					$checked1 = '/爱乐透/';	
					
					if (preg_match($checked1, $message)) {					
						$result = true;			
					}
				}			
			}			
		}
		
		$result && self::__sentMsg($uid);
		return $result;
	}
	
	
	public static function __sentMsg($uid)
	{
		
		$appConfig = Better_Config::getAppConfig();
		$sys_user_id = $appConfig->user->sys_user_id;
		$content = <<<EOT
恭喜您获得爱乐透彩票网提供的7元彩金红包兑换券一张（2元直接赠送，5元充值无论金额大小再次送）。兑换券代码：CODE
开友可在6月1日-6月25日凭此兑换券在http://kk.iletou.com（手机登陆）进行兑换，兑换的2元直接赠送彩金可在爱乐透购买任意彩种彩票。彩金红包兑换券共计5000份，送完即止！
EOT;
		
		$data = Better_DAO_Letou::getInstance()->get(array('uid' => 0));
		$id = $data['id'];
		if (!$id) return false;	

		$content = str_replace('CODE', $id, $content);
		Better_User_Notification_DirectMessage::getInstance($sys_user_id)->send(array(
												'content' => $content,
												'receiver' => $uid
												));	
		$_data['uid'] = $uid;
		$_data['dateline'] = time();
		$a = Better_DAO_Letou::getInstance()->update($_data, $id);	

		return $a;
												
	}
}