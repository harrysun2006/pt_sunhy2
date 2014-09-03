<?php

/**
 * 云海肴up!
 * 半小时内，3位好友关系的用户签到以下任一POI，同时获得此勋章
云海肴（后海店）http://k.ai/poi/6855779
云海肴（中关村店）http://k.ai/poi/19051741

此勋章有补发的，所以另外写了一个脚本定时运行

 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Upyunhaiyao extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);	
		$poi_list = array(6855779,19051741);
		$poi_str = implode(",", $poi_list);	
		if(in_array($poiId,$poi_list)){			
			$friends = Better_User_Friends::getInstance($uid)->getFriends();
			if(count($friends)>=2){
				$friendstr = implode(",",$friends);
				$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
				$checkintime = time()-1800;
				$total = 0;
				$sql = "select count(*) as total from better_user_place_log where poi_id=".$poiId."  and checkin_time>=".$checkintime." and uid in (".$friendstr.") and checkin_score>0";				
				foreach($sids as $sid) {
					$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);				
					$rdb = &$cs['r'];
					$wdb = &$cs['w'];		
					
					$rs = Better_DAO_Base::squery($sql, $rdb);			
					$data = $rs->fetch();
					$total = $total + $data['total'];			
				}			
				if($total>=2){
					$result = true;
				}
			}
		}
		return $result;
	}
}