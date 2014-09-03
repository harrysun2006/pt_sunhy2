<?php

class Better_DAO_Community_Lostmajor extends Better_DAO_Community_Base{

	
	public static function getAllResults(){
		$return = $tmp = array();
		$sids = parent::getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->join(BETTER_DB_TBL_PREFIX.'account as a', array('a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p', 'a.uid=p.uid', array('p.nickname', 'p.username', 'p.language'));
			$select->join(BETTER_DB_TBL_PREFIX.'user_major_log as m', 'a.uid=m.uid', array('m.uid', 'm.poi_id', 'm.dateline'));
			$select->where('m.dateline>=?', time()-self::$hours*3600);
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$tmp[$row['poi_id']][$row['dateline']] = $row;
			}
		}

		$return = self::parseRow($tmp);
		return $return;
	}
	
	
	private static function parseRow($rows){
		$return = $tmp2 = array();
		foreach ($rows as $pid=>$row){
			$tmp =array();
			$poi = Better_Poi_Info::getInstance($pid)->getBasic();
			if($poi['poi_id'] && !$poi['closed']){
				$count = count($row);
				ksort($row);
				foreach($row as $v){
					$tmp[] = $v;
				}
				if($count>1){
					for($i=0; $i<$count; $i++){
						if($i==0){
							$obj = self::getOldMajor($pid, $tmp[0]['dateline']);
							if($obj){
								$uid= $obj['uid'];
								$rob_uid = $tmp[0]['uid'];	
							}else{
								$uid = $rob_uid = 0;
							}
						}else{
							$uid = $tmp[$i-1]['uid'];
							$rob_uid = $tmp[$i]['uid'];
						}
						
						if($uid && $rob_uid){
							$user = Better_DAO_User::getInstance($uid)->get($uid);
							$rob_user = Better_DAO_User::getInstance($rob_uid)->get($rob_uid);
							if($user['email4community'] && $user['state']!=Better_User_State::BANNED){
								$lang = Better_Language::loadIt($user['language']);
								$msg = $lang->email->notice->major;
								$msg = str_replace('{POI}', $poi['name'], $msg);
								$msg = str_replace('{POI_ID}', $pid, $msg);
								$msg = str_replace('{NAME}', $rob_user['nickname'], $msg);
								$tmp2[$uid][] = $msg;
							}
						}
						
					}
				}else if($count==1){
					$obj = self::getOldMajor($pid, $tmp[0]['dateline']);
					if($obj){
						$uid= $obj['uid'];
						$rob_uid = $tmp[0]['uid'];
						$user = Better_DAO_User::getInstance($uid)->get($uid);
						$rob_user = Better_DAO_User::getInstance($rob_uid)->get($rob_uid);
						if($user['email4community']){
							$lang = Better_Language::loadIt($user['language']);
							$msg = $lang->email->notice->major;
							$msg = str_replace('{POI}', $poi['name'], $msg);
							$msg = str_replace('{POI_ID}', $pid, $msg);
							$msg = str_replace('{NAME}', $rob_user['nickname'], $msg);
							$tmp2[$uid][] = $msg;
						}
					}
					
				}
			}
		}
		
		foreach($tmp2 as $uid=>$arr){
			$content = '';
			foreach($arr as $k=>$val){
				if($k==0){
					$val = '<li>'.$val.'</li>';
				}
				$content .= $val.'<br />';
			}
			$return[$uid] = $content;
		}
		
		return $return;
	}
	
	
	private static function getOldMajor($poi_id, $dateline){
		$return = $tmp = array();
		$sids = parent::getServerIds();
		foreach($sids as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->join(BETTER_DB_TBL_PREFIX.'account as a', array('a.email'));
			$select->join(BETTER_DB_TBL_PREFIX.'profile as p', 'a.uid=p.uid', array('p.nickname', 'p.username', 'p.language'));
			$select->join(BETTER_DB_TBL_PREFIX.'user_major_log as m', 'a.uid=m.uid', array('m.uid', 'm.poi_id', 'm.dateline'));
			$select->where('m.dateline<?', intval($dateline));
			$select->where('m.poi_id=?', $poi_id);
			$select->order('m.dateline desc');
			$select->limit(1);
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $row){
				$tmp[$row['dateline']] = $row;
			}
		}
		
		if($tmp && count($tmp)>0){
			krsort($tmp);
			$tmp = array_chunk($tmp, 1);
			$return = $tmp[0][0];
		}
		
		return $return;
	}
	
	
}