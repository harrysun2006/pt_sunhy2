<?php

class Better_Admin_Dmessage
{
	
	public static function delReceived(array $ids)
	{
		$result = false;
		
		foreach($ids as $id) {
			list($uid, $msg_id) = explode('.', $id);
			
			$data = Better_DAO_DmessageReceive::getInstance($uid)->get($msg_id);
		
			if ($data['msg_id']) {
				$message = $data['content'];
				$userInfo = Better_User::getInstance($uid)->getUser();
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($userInfo, 'del_received_dmsg', '删除私信:<br>'.$message);
				Better_DAO_DmessageReceive::getInstance($uid)->delete($msg_id);
				
				$updata = array(
					'received_msgs' => $userInfo['received_msgs'] - 1
					);
				if ($data['readed']=='0') {
					$updata['new_msgs'] = $userInfo['new_msgs'] - 1;
				}
				Better_User::getInstance($uid)->updateUser($updata);
				
				Better_Hook::factory(array(
				'Admin_DirectMessage'
				))->invoke('RmessageDeleted', array(
				'message' => $data,
				'userInfo' => $userInfo
				));
			}
		}
		
		//Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('删除用户私信', 'delete');
		
		$result = true;
		
		return $result;
	}
		
	public static function getReceived(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
			
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';	
		$reload = $params['reload'] ? $params['reload'] : 0;
		$uid = $params['uid'] ? $params['uid']:'';
		$kuid = $params['kuid']? $params['kuid']:'';
		$msgid = $params['msgid'] ? $params['msgid']: '';
		
		if ($params['from']) {
			$from = $params['from'];
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-BETTER_8HOURS;
		}
		
		if ($params['to']) {
			$to = $params['to'];
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-BETTER_8HOURS;
		}			
			
		$rows = Better_DAO_Admin_DmessageReceived::getAllReceived(array(
			'from'=>$from,
			'to'=>$to,
			'keyword'=>$keyword,
			'user_keyword'=>$user_keyword,
			'reload'=>$reload,
			'page'=>$page,
			'uid'=>$uid,
			'msgid' => $msgid,
			'kuid'=> $kuid
		));
		
		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		foreach($data[$page-1] as $row) {
			$sender = Better_User::getInstance($row['from_uid']);
			$senderUserInfo = $sender->getUser();
			$row['sender_username'] = $senderUserInfo['username'];
			$row['sender_nickname'] = $senderUserInfo['nickname'];
			
			$return['rows'][] =$row;
		}

		unset($data);		
			
		return $return;
	}
	
	
	public static function delSended(array $ids)
	{
		$result = false;
		
		foreach($ids as $id) {
			list($uid, $msg_id) = explode('.', $id);
			
			$data = Better_DAO_DmessageSend::getInstance($uid)->get($msg_id);
		
			if ($data['msg_id']) {
				$message = $data['content'];
				$userInfo = Better_User::getInstance($uid)->getUser();
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($userInfo, 'del_sended_dmsg', '删除私信:<br>'.$message);
				Better_DAO_DmessageSend::getInstance($uid)->delete($msg_id);
				
				$updata = array(
					'sent_msgs' => $userInfo['sent_msgs'] - 1
					);
				
				Better_User::getInstance($uid)->updateUser($updata);
				
				Better_Hook::factory(array(
				'Admin_DirectMessage'
				))->invoke('SmessageDeleted', array(
				'message' => $data,
				'userInfo' => $userInfo
				));
			}
		}
		
		$result = true;
		
		return $result;
	}
	
}