<?php

/**
 * 勋章名称
 蒲公英
 
获得条件
 绑定了腾讯微博
 
上线时间
 即时
 
下线时间
 无
 
描述语
 您成功绑定了腾讯微博。
 
同步语
 我获得了开开k.ai〖蒲公英〗勋章。愿我的足迹追随着蒲公英到这个世界的各个美好角落：）
 

 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Bedocom extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$now = time();
		$checktm = gmmktime(16, 0, 0, 9, 3, 2011);
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		$sql = "select jid from ".BETTER_DB_TBL_PREFIX."account_bedosync where uid=".$uid." limit 1";
		$rs = self::squery($sql, $rdb);
		$row = $rs->fetch();		
		if ($row['jid']>0) {
			if($now<=$checktm){
				$result = true;
			} else {				
				$bedouserinfo = Better_Service_BedoBinding::getInstance()->userinfo($row['jid']);
				$checkedtm = $now-$bedouserinfo['regdate']-8*3600-180*24*3600;
				if($checkedtm>0){
					$result = true;
				}
			}
		}
		
		return $result;
	}
}