<?php

/**
获得条件：签到4家优悦优品店铺中的任意一家，并同步任意一个社交网站。POI ID如下
优悦优品江西南昌百盛店：http://k.ai/poi?id=19083560,19083557,19083556,19083562

优悦优品深圳万象岁宝店：http://k.ai/poi?id=19083557

优悦优品深圳龙珠岁宝店：http://k.ai/poi?id=19083556

优悦优品深圳京基百纳店：http://k.ai/poi?id=19083562

 
时间：7月21日10:00上线，8月21日24:00下线

 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Youyuecom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 7, 21, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 21, 2011);
		$poilist = array(19083560,19083557,19083556,19083562);		
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
			if ($row['total']>=1) {				
				$result = true;			
			}			
			
		}	
		if($result)	{
			try{
				self::__sentMsg($uid);
			} catch (Exception $e){
				Better_Log::getInstance()->logInfo($uid,'notgotyouyeah');
			}
		}	
		
		return $result;
	}
	
	public static function __sentMsg($uid)
	{
		
		$appConfig = Better_Config::getAppConfig();
		$sys_user_id = $appConfig->user->sys_user_id;
		$content = <<<EOT
		恭喜您获得了优悦优品商城电子代金券30元！您的代金券编码是： CODE 使用详情请见开开官方博客：http://blog.k.ai/?p=1852 	
EOT;
		$partner = 'youyeahcom';
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