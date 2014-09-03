<?php

class Better_DAO_Community_Getbadge extends Better_DAO_Community_Base{

	
	public static function getAllResults(){
		$return = $tmp = array();
		$sids = parent::getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->join(BETTER_DB_TBL_PREFIX.'account as a', array('a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p', 'a.uid=p.uid', array('p.nickname', 'p.username', 'p.language'));
			$select->join(BETTER_DB_TBL_PREFIX.'user_badges as b', 'a.uid=b.uid', array('b.uid', 'b.bid'));
			$select->where('b.get_time>=?', time()-self::$hours*3600);
			$select->where('p.email4community=?', '1');
			$select->where('p.state!=?', Better_User_State::BANNED);
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$tmp[$row['uid']][] = $row;
			}
		}
		
		$return = self::parseRow($tmp);
		return $return;
	}
	
	
	private static function parseRow($rows){
		$return = array();
		foreach ($rows as $uid=>$row){
			$lang = Better_Language::loadIt($row[0]['language']);
			$msg = '';
			$cont = $lang->email->notice->badge->msg;
			$tmp ='';
			foreach($row as $k=>$val){
				if($k==0){
					$content = '<li>'.$cont.'</li>';
				}else{
					$content = $cont;
				}
				$bid = $val['bid'];
				$badge = Better_DAO_Badge::getInstance()->get(array('id' => $bid));
				if($row[0]['language']=='en'){
					$tmp = str_replace('{ACTION}', $badge['en_got_tips'], $content);
					$tmp = str_replace('{BADGE}', $badge['en_badge_name'], $tmp);
				}else{
					$tmp = str_replace('{ACTION}', $badge['got_tips'], $content);
					$tmp = str_replace('{BADGE}', $badge['badge_name'], $tmp);
				}
				$msg .= $tmp.'<br />';
			}
			$msg .= str_replace('{USERNAME}', $row[0]['username'], $lang->email->notice->badge->title);
			
			$return[$uid] = $msg;
		}
		return $return;
	}
	
	
}