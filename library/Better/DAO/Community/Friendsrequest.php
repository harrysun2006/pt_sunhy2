<?php

class Better_DAO_Community_Friendsrequest extends Better_DAO_Community_Base{

	
	public static function getAllResults(){
		$return = $tmp = array();
		$sids = parent::getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->join(BETTER_DB_TBL_PREFIX.'account as a', array('a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p', 'a.uid=p.uid', array('p.nickname', 'p.username', 'p.language', 'p.avatar'));
			$select->join(BETTER_DB_TBL_PREFIX.'friends_request as f', 'a.uid=f.uid', array('f.uid', 'f.request_to_uid'));
			$select->where('f.dateline>=?', time()-self::$hours*3600);
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$tmp[$row['request_to_uid']][] = $row;
			}
		}
		
		$return = self::parseRow($tmp);
		return $return;
	}
	
	
	private static function parseRow($rows){
		$return = array();
		foreach ($rows as $uid=>$row){
			$user = Better_DAO_User::getInstance($uid)->get($uid);
			if($user['email4community'] && $user['state']!=Better_User_State::BANNED){
				$lang = Better_Language::loadIt($user['language']);
				$msg = $lang->email->notice->friends_request->title;
				$content = $lang->email->notice->friends_request->msg;
				$tmp ='';
				foreach($row as $val){
					$avatar = Better_User::getInstance($val['uid'])->getUserAvatar('thumb', $val);
					$tmp = str_replace('{AVATAR}', $avatar, $content);
					$tmp = str_replace('{NAME}', $val['nickname'], $tmp);
					$tmp = str_replace('{USERNAME}', $val['username'], $tmp);
					$msg .= $tmp;
				}
				$return[$uid] = $msg;
			}
		}
		return $return;
	}
	
	
}