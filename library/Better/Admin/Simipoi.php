<?php
/**
 * admin页面 POI操作
 * @author  yangl
 */

class Better_Admin_Simipoi{
	
	/**
	 * 获得一些POI
	 */
	public static function getSimiPOIs($params=array()){
		$uid = Better_Registry::get('sess')->admin_uid;
		$return = array('rows'=>array(), 'count'=>0);
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$namekeyword = $params['namekeyword']? $params['namekeyword'] : '';
		$placekeyword = $params['placekeyword']? $params['placekeyword'] : '';
		
		$type = $params['type']=='simiday' ? $params['type']: 'similar';
		
		$reload = $params['reload'] ? 1 : 0;
		
		$result=Better_DAO_Admin_Simipoi::getInstance($type)->getAllSimiPOIs(array(
			'page' => $page,
			'reload' => $reload,
			'pageSize' => $pageSize,
			'namekeyword'=> $namekeyword,
			'placekeyword'=> $placekeyword
			)
		);
		
		foreach($result['rows'] as $row){
			$refid = $row['refid'];
			$return['rows'][$refid][] = $row;
		}
		$return['count'] = $result['count'];
		
		return $return;
	}
	
	/**
	 * 根据refid删除记录
	 */
	public static function deleteSimiByRefid($refid, $type='similar'){
		if($refid){
			$type = $type=='simiday' ? $type: 'similar';
			
			$cond = array('refid'=>$refid);
			Better_DAO_Admin_Simipoi::getInstance($type)->deleteByCond($cond);	
			return 1;	
		}else{
			return false;
		}
	}
	
	

	//合并POI
	public static function mergePOI($poi_id, $target_pid){
		
		$poi = Better_Poi_Info::getInstance($poi_id)->getBasic();
		$target_poi = Better_Poi_Info::getInstance($target_pid)->getBasic();
		
		Better_Poi_Info::destroyInstance($poi_id);
		Better_Poi_Info::destroyInstance($target_poi);
		
		if($poi['poi_id']  && $target_poi['poi_id'] ){
			file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', "\n".'time:'.date('Y-m-d H:i:s', time()+8*3600).'--' , FILE_APPEND);
			
			$flag = false;
			if($target_poi['closed']){
					file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', 'target_pid:'.$target_poi['poi_id'].' closed', FILE_APPEND);
					
					while($target_poi['ref_id'] && $target_poi['ref_id']!=$target_poi['poi_id']){
						
						file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->ref_id:'.$target_poi['ref_id'], FILE_APPEND);
						
						$target_poi = Better_Poi_Info::getInstance($target_poi['ref_id'])->getBasic();	
						
						Better_Poi_Info::destroyInstance($target_poi['ref_id']);
						
						file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->ref_closed:'.$target_poi['closed'], FILE_APPEND);
						
						if(!$target_poi['closed']){
							$flag = true;
							break;
						}
					}
					
					if(!$flag){
							file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->no target found', FILE_APPEND);
							return 2;
					}
					file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->found target_poi:'.$target_poi['poi_id'], FILE_APPEND);
					
			}
			
			if($poi['poi_id']==$target_poi['poi_id']){
				file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->poi id and target id is same:'.$poi['poi_id'], FILE_APPEND);
			}
			
			if(!$target_poi['closed'] && $poi['poi_id']!=$target_poi['poi_id']){
				if(!$poi['closed']){
					Better_DAO_Admin_Poi::getInstance()->update(array('closed'=>1), $poi['poi_id']);
					Better_DAO_Admin_Poimerge::getInstance()->doMerge($poi, $target_poi);
					
					Better_DAO_Admin_Poi::getInstance()->refPOI($poi['poi_id'], $target_poi['poi_id']);
					
					
					//log
					$disc = round(Better_Service_Lbs::getDistance($poi['lon'], $poi['lat'], $target_poi['lon'], $target_poi['lat'])/1000, 3);
					$sour_poi = $poi['creator']? '用户自建': ($poi['aibang_id']? '爱帮':'谷歌');
					$sour_tar_poi = $target_poi['creator']? '用户自建': ($poi['aibang_id']? '爱帮':'谷歌');
					$logmsg = <<<EOF
						合并POI:<br>
						<table>
							<tr>
								<td>
									id: {$poi['poi_id']}<br>
									等级：{$poi['level']}<br>
									名称：{$poi['name']}<br>
									地址：{$poi['address']}<br>
									距离：{$disc}公里<br>
									签到数：{$poi['checkins']}<br>
									来源：{$sour_poi}
								</td>
								<td>
									=>
								</td>
								<td>
									id: {$target_poi['poi_id']}<br>
									等级：{$target_poi['level']}<br>
									名称：{$target_poi['name']}<br>
									地址：{$target_poi['address']}<br>
									签到数：{$target_poi['checkins']}<br>
									来源：{$sour_tar_poi}
								</td>
							</tr>
						</table>
EOF;
					
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($poi_id, 'merge_poi', $logmsg);
					
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($poi['poi_id'], 2);
					Better_DAO_Poi_Fulltext::getInstance()->updateItem($target_poi['poi_id'], 1);
					
					file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->'.$poi['poi_id'].'==>'.$target_poi['poi_id'].' successfully.', FILE_APPEND);
				}else{
					file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->poi_id:'.$poi['poi_id'].' closed', FILE_APPEND);
				}
			}
			return 1;
		}else{
			return 3;
		}
		
	}
	
	
	
	//合并一堆POI进一个
	public static function mergeMutiPOI($pids, $target_pid){
		$target_poi = Better_Poi_Info::getInstance($target_pid)->getBasic();
		Better_Poi_Info::destroyInstance($target_pid);
		
		if($target_poi['poi_id']){
			file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', "\n".'time:'.date('Y-m-d H:i:s', time()+8*3600).'--' , FILE_APPEND);
			if(is_array($pids) && $pids){
				$flag = false;
				if($target_poi['closed']){
						file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', 'target_pid:'.$target_poi['poi_id'].' closed', FILE_APPEND);
					while($target_poi['ref_id']  && $target_poi['ref_id']!=$target_poi['poi_id']){
						file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->ref_id:'.$target_poi['ref_id'], FILE_APPEND);
						
						$target_poi = Better_Poi_Info::getInstance($target_poi['ref_id'])->getBasic();
						
						Better_Poi_Info::destroyInstance($target_poi['ref_id']);
						
						file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->ref_closed:'.$target_poi['closed'], FILE_APPEND);
						if(!$target_poi['closed']){
							$flag = true;
							break;
						}
					}
					
					if(!$flag){
							file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->no target found', FILE_APPEND);
							return 2;
					}
					file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->found target_poi:'.$target_poi['poi_id'], FILE_APPEND);
				}
				
				foreach($pids as $pid){
					$poi = Better_Poi_Info::getInstance($pid)->getBasic();
					if($poi['poi_id']==$target_poi['poi_id']){
						file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->poi id and target id is same:'.$poi['poi_id'], FILE_APPEND);
					}
					if($poi['poi_id'] && !$target_poi['closed'] && $poi['poi_id']!=$target_poi['poi_id']){
						if(!$poi['closed'] ){
							Better_DAO_Admin_Poi::getInstance()->update(array('closed'=>1), $poi['poi_id']);
							Better_DAO_Admin_Poimerge::getInstance()->doMerge($poi, $target_poi);
							
							Better_DAO_Admin_Poi::getInstance()->refPOI($poi['poi_id'], $target_poi['poi_id']);
							
			if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
					//log
					$disc = round(Better_Service_Lbs::getDistance($poi['lon'], $poi['lat'], $target_poi['lon'], $target_poi['lat'])/1000, 3);
					$sour_poi = $poi['creator']? '用户自建': ($poi['aibang_id']? '爱帮':'谷歌');
					$sour_tar_poi = $target_poi['creator']? '用户自建': ($poi['aibang_id']? '爱帮':'谷歌');
					$logmsg = <<<EOF
						合并POI:<br>
						<table>
							<tr>
								<td>
									id: {$poi['poi_id']}<br>
									等级：{$poi['level']}<br>
									名称：{$poi['name']}<br>
									地址：{$poi['address']}<br>
									距离：{$disc}公里<br>
									签到数：{$poi['checkins']}<br>
									来源：{$sour_poi}
								</td>
								<td>
									=>
								</td>
								<td>
									id: {$target_poi['poi_id']}<br>
									等级：{$target_poi['level']}<br>
									名称：{$target_poi['name']}<br>
									地址：{$target_poi['address']}<br>
									签到数：{$target_poi['checkins']}<br>
									来源：{$sour_tar_poi}
								</td>
							</tr>
						</table>
EOF;

					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addPoiLog($pid, 'merge_poi', $logmsg);
				}
							Better_DAO_Poi_Fulltext::getInstance()->updateItem($poi['poi_id'], 2);
							
							file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->'.$poi['poi_id'].'==>'.$target_poi['poi_id'].' successfully.', FILE_APPEND);
						}else{
							file_put_contents(APPLICATION_PATH.'/../logs/merge_poi_log.log', '-->poi_id:'.$poi['poi_id'].' closed', FILE_APPEND);
						}
					}
					Better_Poi_Info::destroyInstance($pid);
				}
				Better_DAO_Poi_Fulltext::getInstance()->updateItem($target_poi['poi_id'], 1);
				
				return 1;
			}
		}else{
			return 3;
		}
		
	}
	
	
}


?>