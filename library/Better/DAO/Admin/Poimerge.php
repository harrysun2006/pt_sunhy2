<?php

/**
 * POI合并
 * 
 * @package Better.Admin
 * @author yangl
 * 
 */

class Better_DAO_Admin_Poimerge extends Better_DAO_Admin_Base
{
	private static $instance = null;
	
	public static function getInstance(){
		//if (self::$instance==null) {
			self::$instance = new self();
		//}
		
		parent::getServerIds();
		
		return self::$instance;
	}
	
	//更新各个表
	public function doMerge($poi, $target_poi){
		$poi_id = $poi['poi_id'];
		$target_id = $target_poi['poi_id'];
		
		if($poi['level']>0 && $poi['level']<$target_poi['level']){
			Better_DAO_Admin_Poi::getInstance()->update(array('level'=>$poi['level']), $target_id);
		}
		
		($poi['level_adjust'] && !$target_poi['level_adjust']) && $levelAdjust = $poi['level_adjust'];
		(!$poi['level_adjust'] && $target_poi['level_adjust']) && $levelAdjust = $target_poi['level_adjust'];
		($poi['level_adjust'] && $target_poi['level_adjust']) && $levelAdjust = $poi['level_adjust']<$target_poi['level_adjust']? $poi['level_adjust']:$target_poi['level_adjust'];

		if($levelAdjust!=$target_poi['level_adjust']){
			Better_DAO_Admin_Poi::getInstance()->update(array('level_adjust'=>$levelAdjust), $target_id);
		}
		
		//清blog缓存
		Better_Cache_Clear::changeBlogType($poi_id, '');
		foreach(self::$serverIds as $sid){
			$cs = parent::assignDbConnection('user_server_'.$sid, true);
			$wdb = $cs['w'];
			$rdb = $cs['r'];
			$this->wdb = $wdb;
			$this->rdb = $rdb;
			$data = array('poi_id'=>$target_id);
			//blog
			parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'blog');
			//poi_poll
			parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'poi_poll');
			//badge
			parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'user_badges');
			//place_log
			parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'user_place_log');
			//treasures
			parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'user_treasures');
			//treasure_log
			parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'user_treasure_log');
			
			//user
			parent::_updateXY(array('last_checkin_poi'=>$target_id), array('last_checkin_poi'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'profile');
			
			//major
			$major = 0;
			if($poi['major']){
				$count1 = Better_DAO_User_PlaceLog::getInstance($poi['major'])->getMyValidCheckinCount($poi_id);
			}
			if($target_poi['major']){
				$count2 = Better_DAO_User_PlaceLog::getInstance($target_poi['major'])->getMyValidCheckinCount($target_id);
			}
			
			if($poi['major'] && !$target_poi['major']){
				$major = $poi['major'];
				Better_DAO_Admin_Poi::getInstance()->update(array('major'=>$major, 'major_change_time'=>$poi['major_change_time']), $target_poi['poi_id']);
			
				parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'user_major_log');
				
			}else if(!$poi['major'] && $target_poi['major']){
				$major = $target_poi['major'];	
			}else if($poi['major'] && $target_poi['major']){
				
				if($count1>$count2){
					$major = $poi['major'];
					Better_DAO_Admin_Poi::getInstance()->update(array('major'=>$major, 'major_change_time'=>$poi['major_change_time']), $target_poi['poi_id']);
					$this->tbl = BETTER_DB_TBL_PREFIX.'user_major_log';
					$this->deleteByCond(array('poi_id'=>$target_id));
					parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'user_major_log');
					
					$target_major = Better_User::getInstance($target_poi['major']);
					$target_major_info = Better_DAO_User::getInstance($target_poi['major'])->get($target_poi['major']);
					$target_major->updateUser(array(
						'majors' => $target_major_info['majors']-1,
					));
				}else{
					$this->tbl = BETTER_DB_TBL_PREFIX.'user_major_log';
					$this->deleteByCond(array('poi_id'=>$poi_id));
					
					$poi_major = Better_User::getInstance($poi['major']);
					$major_info = Better_DAO_User::getInstance($poi['major'])->get($poi['major']);
					$poi_major->updateUser(array(
						'majors' => $major_info['majors']-1,
					));
				}
				
			}
			
			
			//poi_favorites
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'poi_favorites as p', array('p.uid'));
			$select->where('p.poi_id IN ('.$poi_id.' , '.$target_id.')');
			$select->group('p.uid');
			$select->having('count(*)=?', 2);
			$rs = parent::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			$this->tbl = BETTER_DB_TBL_PREFIX.'poi_favorites';
			foreach($rows as $row){
				$uid = $row['uid'];
				$this->deleteByCond(array('uid'=>$uid, 'poi_id'=>$poi_id));				
			}
			
			parent::_updateXY($data, array('poi_id'=>$poi_id), 'AND', BETTER_DB_TBL_PREFIX.'poi_favorites');
			
		}
		
		//poi_notification
		Better_DAO_Poi_Notification::getInstance()->_updateXY(array('poi_id'=>$target_id), array('poi_id'=>$poi_id));
		
		
		//POI计数
		$data=array();
		$data['checkins']= intval($poi['checkins'])+intval($target_poi['checkins']);
		//$data['favorites']= intval($poi['favorites'])+intval($target_poi['favorites']);
		//$data['users']= intval($poi['users'])+intval($target_poi['users']);
		//$data['visitors']= intval($poi['visitors'])+intval($target_poi['visitors']);
		$data['posts']= intval($poi['posts'])+intval($target_poi['posts']);
		$data['tips']= intval($poi['tips'])+intval($target_poi['tips']);
		
		$result = Better_Poi_Checkin::getInstance($target_id)->users(1, 99999);
		$data['users'] = $result['total'];
		
		Better_DAO_Admin_Poi::getInstance()->update($data, $target_id);
		
	
	}
	
}