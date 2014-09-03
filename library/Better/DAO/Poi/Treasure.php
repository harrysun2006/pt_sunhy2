<?php

/**
 * POI的宝物
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Poi_Treasure extends Better_DAO_Base
{

	public static function logs($poiId, $page=1, $count=BETTER_PAGE_SIZE)
	{
		$result = array(
			'count' => 0,
			'rows' => array(),
			);
			
		$rows = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		$tids = array();
		
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'user_treasure_log AS l', array(
				'l.uid', 'l.poi_id', 'l.treasure_id', 'l.dateline',
				));
			$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=l.uid', array(
				'p.username', 'p.nickname',
				));
			$select->where('l.poi_id=?', $poiId);
			$select->where('category=?', 'pickup');
			$select->order('l.dateline DESC');

			$rs = self::squery($select, $rdb);
			$tmp = $rs->fetchAll();
			foreach($tmp as $v) {
				if (!in_array($v['treasure_id'], $tids)) {
					if ($v['poi_id']) {
						$v['poi_info'] = Better_Poi_Info::getInstance($v['poi_id'])->getBasic();
					}
					$v['treasure_detail'] = Better_Treasure::getInstance($v['treasure_id'])->getInfo();
					
					$rows[$v['dateline']] = $v;
					
					$tids[] = $v['treasure_id'];
				}
			}
		}

		if (count($rows)>0) {
			krsort($rows);
			$result['count'] = count($rows);
			
			$data = array_chunk($rows, $count);
			if (isset($data[$page-1])) {
				$result['rows'] = &$data[$page-1];
			}
		}

		return $result;		
	}

}