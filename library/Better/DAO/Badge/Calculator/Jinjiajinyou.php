<?php

/**
签到中附言“静佳精油”并同步到2个SNS；或吼吼“静佳精油”+2SNS
6月22日即时
7月7日24:00am


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Jinjiajinyou extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 6, 21, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 7, 2011);
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
			if ($row['total']>=2) {	
				$blog = &$params['blog'];						
				if ($blog['type']=='normal' || $blog['type']=='checkin') {
					$message = strtolower($blog['message']);
					$checked1 = '/静佳精油/';	
					
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
感谢您参与开开x静佳Jplus活动，请凭本精油直减券至乐蜂网在购买静佳Jplus精油产品时使用（代金券号码：25336550；密码：9ab72e1f98）请在注册成为乐蜂网（http://www.lafaso.com/）会员后使用。		
EOT;
		
		
		Better_User_Notification_DirectMessage::getInstance($sys_user_id)->send(array(
												'content' => $content,
												'receiver' => $uid
												));	
		
		
												
	}
}