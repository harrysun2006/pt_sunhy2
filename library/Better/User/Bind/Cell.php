<?php

/**
 * 绑定手机号
 * 
 * @package Better.User.Bind
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Bind_Cell extends Better_User_Bind_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	/**
	 * 获取自己的随机码
	 * 
	 * @return string
	 */
	public function getSeq()
	{
		$result = '';
		$row = Better_DAO_BindCellSeq::getInstance()->get(array(
			'uid' => $this->uid
			));					
		if ($row['seq']) {
			$result = $row['seq'];
		} else {
			$result = md5(uniqid(rand()));
			Better_DAO_BindCellSeq::getInstance()->insert(array(
				'uid' => $this->uid,
				'seq' => $result,
				'dateline' => time(),
				));
		}
		
		return $result;
	}	
	
	/**
	 * 清除自己的随机码
	 * 
	 * @return null
	 */
	public function clearSeq()
	{
		Better_DAO_BindCellSeq::getInstance()->deleteByCond(array(
			'uid' => $this->uid
			));
	}
	
	/**
	 * 检测用户是否有绑定请求
	 * 
	 * @param $uid
	 * @param $cell
	 * @return integer
	 */
	public static function hasRequest($cell)
	{
		$uid = null;
		
		$dao = Better_DAO_BindCell::getInstance();
		$row = $dao->get(array(
						'cell' => $cell
						));
		if (isset($row['uid'])) {
			$uid = $row['uid'];
		}
		
		return $uid;		
	}		

	/**
	 * 绑定手机
	 * 
	 * @param $cell
	 * @return unknown_type
	 */
	public function bind($cell, $force=false)
	{
		$user = null;
		$this->getUserInfo();
		
		if ($force==false && is_numeric($this->uid) && preg_match('/^([0-9]{1,20})$/', $cell)) {
			$row = Better_DAO_BindCell::getInstance()->get(array(
							'uid' => $this->uid,
							'cell' => $cell,
							));
			if (isset($row['uid'])) {
				$user = $this->user;
				Better_DAO_BindCell::getInstance()->deleteUid($this->uid);
				$this->user->updateUser(array(
					'cell_no' => $row['cell'],
					'enabled' => '1',
					'state' => 'enabled'
					), true);
					
				Better_User::getInstance(BETTER_SYS_UID)->notification()->directMessage()->send(array(
					'receiver' => $this->uid,
					'content' => str_replace('{CELL_NO}', $cell, Better_Language::loadIt($this->userInfo['language'])->global->cell_binded),
					));
					
				//发email
				$data = Better_DAO_User::getInstance($this->uid)->get($this->uid);
				Better_Hook::factory(array(
					'Email', 'Secret'
				))->invoke('Bindcell', array(
					'data' => &$data,
					'uid' => $this->uid
				));
				
				//	删除序列
				$this->clearSeq();
			}
		} else if ($force==true) {
			$user = $this->user;
			Better_DAO_BindCell::getInstance()->deleteUid($this->uid);

			Better_User::getInstance(BETTER_SYS_UID)->notification()->directMessage()->send(array(
				'receiver' => $this->uid,
				'content' => str_replace('{CELL_NO}', $cell, Better_Language::loadIt($this->userInfo['language'])->global->cell_binded),
				));
				
			//发email
			$data = Better_DAO_User::getInstance($this->uid)->get($this->uid);
			
			$this->user->updateUser(array(
				'cell_no' => $cell,
				'enabled' => '1',
				'state' => 'enabled'
				), true);

			Better_Hook::factory(array(
				'Email', 'Secret'
			))->invoke('Bindcell', array(
				'data' => &$data,
				'uid' => $this->uid
			));
			
			//	删除序列
			$this->clearSeq();			
		}
		
		return $user;		
	}
	
	/**
	 * 检查手机号是否唯一
	 * @return unknown_type
	 */
	public function checkCell($cell)
	{
		$user = $this->user;
		$this->getUserInfo();
		
		$data = Better_DAO_User::getInstance()->getByKey($cell, 'cell_no');
		
		if ( !$data['uid'] || $data['uid'] == $this->uid ) {
			return true;
		}
		
		Better_User::getInstance(BETTER_SYS_UID)->notification()->directMessage()->send(array(
			'receiver' => $this->uid,
			'content' => str_replace('{CELL_NO}', $cell, Better_Language::loadIt($this->userInfo['language'])->global->cell_bind_fail),
			));			
		
		return false;
	}
	
	/**
	 * 请求绑定手机
	 * 
	 * @param $cell
	 * @return unknown_type
	 */
	public function request($cell)
	{
		$return = 0;

		if (!Better_User_Exists::getInstance($this->uid)->cell_no($cell)) {
			$dao = Better_DAO_BindCell::getInstance();
			$dao->deleteUid($this->uid);
			$flag = $dao->insert(array(
				'uid' => $this->uid,
				'cell' => $cell,
				'dateline' => time(),
				));

			$flag && $return = 1;
		}

		return $return;		
	}
	
	/**
	 * 是否有某个序列
	 * 
	 * @param unknown_type $seq
	 * @return integer
	 */
	public static function hasSeq($seq)
	{
		$uid = 0;
		
		$row = Better_DAO_BindCellSeq::getInstance()->get(array(
			'seq' => $seq
			));	
		if ($row['uid']) {
			$uid = $row['uid'];
		}
			
		return $uid;
	}
}