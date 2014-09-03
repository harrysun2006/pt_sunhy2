<?php

class Better_Admin_Alltext
{

	public static function getAlltexts(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
			
		if ($params['from']) {
			$from = $params['from'];
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-BETTER_8HOURS;
			$params['from'] = $from;
		}
		
		if ($params['to']) {
			$to = $params['to'];
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-BETTER_8HOURS;
			$params['to'] = $to;
		}
		
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$uid = $params['uid']? $params['uid']: '';
		$cacheKey = md5('admin_all_text_'.$from.'_'.$to.'_'.$uid);
		$reload = $params['reload']? $params['reload']: 0 ;
		$page = $params['page'] ? (int) $params['page'] : 1;
		
	if($reload || !Better_DAO_Admin_Base::getDbCacher()->test($cacheKey)){
		$params['reload'] = 1;
		/*$params['type']='normal';
		
		$shouts = Better_DAO_Admin_Blog::getAllBlogs($params);
		
		$params['type']='tips';
		$tips = Better_DAO_Admin_Blog::getAllBlogs($params);
		
		unset($params['type']);*/
		//$users = Better_DAO_Admin_User::getAllUsers($params);
		
		$blogs = Better_DAO_Admin_Blog::getAllBlogs($params);
		
		$dmessages = Better_DAO_Admin_DmessageReceived::getAllReceived($params);
		
		$results = array();
		$tmp = array();
		
		foreach ($blogs as $val){
			switch($val['type'])
			{
				case 'normal':
					$ctype='吼吼';
					break;
				case 'tips':
					$ctype='贴士';
					break;
				case 'checkin':
					$ctype='签到';
					break;
				default:
					$ctype='';
					break;
			}
			
			$tmp['id'] = $val['bid'].'@blog';
			$tmp['dateline'] = $val['dateline'];
			$tmp['type'] = $ctype;
			$tmp['content'] = ($val['message']? $val['message']:($val['type']=='checkin'? '签到':'上传一张新图片')).'<br>来源: '.$val['source'].' ('.$val['priv'].')';
			$tmp['uinfo'] = $val['uid'].'/'.$val['nickname'];
			$tmp['uid'] = $val['uid'];
			$tmp['username'] = $val['username'];
			$tmp['priv'] = $val['priv'];
			$tmp['etype'] = $val['type'];
			
			$results[] = $tmp;
		}
		unset($blogs);
		
		/*foreach ($shouts as $val){
			$tmp['id'] = $val['bid'].'@blog';
			$tmp['dateline'] = $val['dateline'];
			$tmp['type'] = '吼吼';
			$tmp['content'] = ($val['message']? $val['message']:'上传一张新图片').'<br>来源: '.$val['source'].' ('.$val['priv'].')';
			$tmp['uinfo'] = $val['uid'].'/'.$val['nickname'];
			$tmp['uid'] = $val['uid'];
			$tmp['username'] = $val['username'];
			$tmp['priv'] = $val['priv'];
			$tmp['etype'] = $val['type'];
			
			$results[] = $tmp;
		}
		unset($shouts);
		
		foreach ($tips as $val){
			$tmp['id'] = $val['bid'].'@blog';
			$tmp['dateline'] = $val['dateline'];
			$tmp['type'] = '贴士';
			$tmp['content'] = ($val['message']? $val['message']:'上传一张新图片').'<br>来源: '.$val['source'].' ('.$val['priv'].')';
			$tmp['uinfo'] = $val['uid'].'/'.$val['nickname'];
			$tmp['uid'] = $val['uid'];
			$tmp['username'] = $val['username'];
			$tmp['priv'] = $val['priv'];
			$tmp['etype'] = $val['type'];
			
			$results[] = $tmp;
		}
		unset($tips);*/
		
		/*foreach ($users as $val){
			$tmp['id'] = $val['uid'];
			$tmp['dateline'] = $val['last_update'];
			$tmp['type'] = '用户信息';
			$tmp['content'] = $val['username'].'/'.$val['nickname'].'/'.$val['self_intro'];
			$tmp['uinfo'] = $val['uid'].'/'.$val['username'];
			
			$results[] = $tmp;
		}
		unset($users);*/
		
		foreach ($dmessages as $val){
			$tmp['id'] = $val['uid'].'.'.$val['msg_id'].'@dmessage';
			$tmp['fid'] = $val['from_uid'].'.'.$val['msg_id'];
			$tmp['dateline'] = $val['dateline'];
			$tmp['type'] = '私信';
			$tmp['content'] = $val['content'];
			$tmp['uinfo'] = $val['from_uid'];
			$tmp['uid'] = $val['from_uid'];
			$tmp['etype'] = 'dmessage';
			
			$results[] = $tmp;
		}
		unset($dmessages);
		
		foreach ($results as $key => $value) {
				$time[$key] = $value['dateline'];
				$type[$key] = $value['type'];
			}

		array_multisort($time, SORT_DESC, $type, $results); 
		unset($time);
		unset($type);
		
		Better_DAO_Admin_Base::getDbCacher()->set($cacheKey, $results, 300);
		
		}else{
			$results = Better_DAO_Admin_Base::getDbCacher()->get($cacheKey);
		}
		
		$data = array_chunk($results, $pageSize);
		
		$return['count'] = count($results);
		$return['rows'] = $data[$page-1];
		unset($results);
		
		return $return;
	}
}