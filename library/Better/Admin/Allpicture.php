<?php
/**
 * 图片管理
 */
 
class Better_Admin_Allpicture
{
	public static function clearImageLog($ids)
	{
		$image_log = Better_DAO_Newimg::getInstance();
		$image_log->remove($ids);
		Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('审核图片', 'pass_image');
	}
	
	public static function getImageLog(array $params)
	{
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$page = $params['page'] ? (int)$params['page'] : 1;
		
		$image_log = Better_DAO_Newimg::getInstance();
		$con =array(	'order' => 'changetime desc');
		/**
		 * 是否是通过bedo号精确查找
		 */
		if(isset($params['bedo_no']) && $params['bedo_no']!=""){
			//精确查找
			$uid = Better_DAO_Bedo::getInstance()->getUidByJid($params['bedo_no']);
			$con['uid']=$uid;
		}		
		$images = $image_log->getAll($con,"{$page}, {$pageSize}", 'limitPage');
		$rows = array();
		foreach ($images as $val) {
			$tmp['rowid'] = $val['id'];
			$tmp['dateline'] = $val['changetime'];
			$tmp['username'] = $val['username'];
			$tmp['uinfo'] = $val['uid'].'/'.$val['username'];
			$tmp['uid'] = $val['uid'];
			if ($val['type'] == 'attach') {
				$tmp['id'] = $val['id'].'@'.$val['refid'].'@blog';
				$tmp['picture'] = self::_getAttachUrl($val['imgurl']);
			} else {
				$tmp['id'] = $val['id'].'@'.$val['uid'].'@user';
				$tmp['picture'] = $val['imgurl'];
			}
			$rows[] = $tmp;
		}
		unset($tmp);
		$result['rows'] = $rows;
		$result['count'] = $image_log->getCount($con);
		return $result;
	}
	
	public static function getAllpictures(array $params)
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
		$cacheKey = md5('admin_all_picture_'.$from.'_'.$to);
		$reload = $params['reload']? $params['reload']: 0 ;
		$page = $params['page'] ? (int)$params['page'] : 1;
		
		
		if ($reload || !Better_DAO_Admin_Base::getDbCacher()->test($cacheKey)) {
			$params['reload'] = 1;
			$params['photo'] = 1;
			$photos = Better_DAO_Admin_Blog::getAllBlogs($params);
			
			$params['avatar'] = 1;
			$avatars = Better_DAO_Admin_User::getAllUsers($params);
			
			$results = array();
			$tmp = array();
			foreach ($photos as $val) {
				$val = self::_parseBlogRow($val);
				$tmp['id'] = $val['bid'].'@blog';
				$tmp['dateline'] = $val['dateline'];
				$tmp['picture'] = $val['attach_url'];
				$tmp['username'] = $val['username'];
				$tmp['uinfo'] = $val['uid'].'/'.$val['username'];
				$tmp['uid'] = $val['uid'];
				$results[] = $tmp;
			}
			unset($photos);
			
			foreach ($avatars as $val) {
				$val = self::_parseBlogRow($val);
				$tmp['id'] = $val['uid'].'@user';
				$tmp['dateline'] = $val['last_update'];
				$tmp['picture'] = $val['avatar_url'];
				$tmp['username'] = $val['username'];
				$tmp['uinfo'] = $val['uid'].'/'.$val['username'];
				$tmp['uid'] = $val['uid'];
				$results[] = $tmp;
			}
			unset($avatars);
			
			
			foreach ($results as $key => $value) {
					$time[$key] = $value['dateline'];
					$uinfo[$key] = $value['uinfo'];
			}
	
			array_multisort($time, SORT_DESC, $uinfo, $results); 
			unset($time);
			unset($uinfo);
			
			Better_DAO_Admin_Base::getDbCacher()->set($cacheKey, $results, 300);
		} else {
			$results = Better_DAO_Admin_Base::getDbCacher()->get($cacheKey);
		}
		
		$data = array_chunk($results, $pageSize);
		
		$return['count'] = count($results);
		$return['rows'] = $data[$page-1];
		unset($results);
		
		return $return;
	}
	
	private static function _getAttachUrl($fid)
	{
		$at = Better_Attachment::getInstance($fid);
		$attach = $at->parseAttachment();
		return $attach['url'];
	}
	
	private static function _parseBlogRow(&$row)
	{

		if ($row['attach']) {
			$at = Better_Attachment::getInstance($row['attach']);
			$attach = $at->parseAttachment();

			$row['attach_tiny'] = $attach['tiny'];
			$row['attach_thumb'] = $attach['thumb'];
			$row['attach_url'] = $attach['url'];
			
			$row['attach_size'] = Better_Attachment::formatSize($attach['filesize']);
			$row['filename'] = $attach['filename'];
		} else {
			$row['filename'] = $row['attach_size'] = $row['attach_url'] = $row['attach_tiny'] = $row['attach_thumb'] = '';
		}

		if ($row['avatar']) {
			$row['avatar_thumb'] = Better_Registry::get('user')->getUserAvatar('thumb', $row);
			$row['avatar_url'] = Better_Registry::get('user')->getUserAvatar('normal', $row);
			$row['avatar_tiny'] = Better_Registry::get('user')->getUserAvatar('tiny', $row);
		} else {
			$row['avatar_tiny'] = $row['avatar_thumb'] = $row['avatar_url'] = Better_Attachment::getInstance()->getConfig()->global->avatar->default_url;
		}

		return $row;
	}	
}