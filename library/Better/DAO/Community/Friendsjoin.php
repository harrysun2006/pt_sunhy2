<?php

class Better_DAO_Community_Friendsjoin extends Better_DAO_Community_Base{

	
	public static function getAllResults(){
		$return = $tmp = array();
		$sids = parent::getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->join(BETTER_DB_TBL_PREFIX.'account as a', array('a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p', 'a.uid=p.uid', array('p.nickname', 'p.username', 'p.language', 'p.ref_uid'));
			$select->where('a.regtime>=?', time()-self::$hours*3600);
			$select->where('p.ref_uid>?', 0);
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$tmp[$row['ref_uid']][] = $row;
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
				$msg = $lang->email->notice->friends_join->title;
				$content = $lang->email->notice->friends_join->msg;
				$tmp ='';
				foreach($row as $val){
					$tmp = str_replace('{NAME}', $val['nickname'], $content);
					$tmp = str_replace('{USERNAME}', $val['username'], $tmp);
					$msg .= $tmp;
				}
				$return[$uid] = $msg;
			}
		}
		return $return;
	}
	
}