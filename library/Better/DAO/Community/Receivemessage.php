<?php

class Better_DAO_Community_Receivemessage extends Better_DAO_Community_Base{

	
	public static function getAllResults(){
		$return = $tmp = array();
		$sids = parent::getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->join(BETTER_DB_TBL_PREFIX.'account as a', array('a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p', 'a.uid=p.uid', array('p.nickname', 'p.username', 'p.language'));
			$select->join(BETTER_DB_TBL_PREFIX.'dmessage_receive as d', 'a.uid=d.uid', array('d.uid'));
			$select->where('d.dateline>=?', time()-self::$hours*3600);
			$select->where('p.email4community=?', '1');
			$select->where('d.readed=?', 0);
			$select->where('d.type=?', 'direct_message');
			$select->where('p.state!=?', Better_User_State::BANNED);
			$select->group('d.uid');
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$tmp[$row['uid']] = $row;
			}
		}
		
		$return = self::parseRow($tmp);
		return $return;
	}
	
	
	private static function parseRow($rows){
		$return = array();
		foreach ($rows as $uid=>$row){
			$lang = Better_Language::loadIt($row['language']);
			$msg = $lang->email->notice->dmessage;
			
			$return[$uid] = $msg;
		}
		return $return;
	}
	
}