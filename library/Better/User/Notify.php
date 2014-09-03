<?php

/**
 * 用户通知
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Notify extends Better_User_Base
{
	protected static $instance = array();
	
	/**
	 * 通知的类型
	 * 
	 * @var array
	 */
	protected static $types = array(
		'direct_message', 'normal', 'friend_request', 'follow_request', 'game','invitation_todo'
		);

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	public function __construct($uid)
	{
		parent::__construct($uid);
	}
	
	/**
	 * 获取当前实例的所有通知
	 * 
	 * @param $params
	 * @return array
	 */
	public function get(array $params)
	{
		$notifies = array();
		
		$cond = array(
			'uid' => $this->uid,
			'order' => array('readed'=>'DESC', 'dateline'=>'DESC'),
			'type' => (array)$params['type']		
			);
		isset($params['readed']) && $cond['readed'] = $params['readed'] ? '1' : '0';
		
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$count = isset($params['count']) ? (int)$params['count'] : BETTER_PAGE_SIZE;
		$count>BETTER_PAGE_SIZE && $count = BETTER_PAGE_SIZE;
		
		$rows = Better_DAO_Notify::getInstance($this->uid)->getAll($cond, $page.','.$count, 'limitPage');
		
		foreach ($rows as $row) {
			$notifies[$row['id']] = $this->parse($row);
		}
		
		return $notifies;
	}
	
	/**
	 * 解析一个通知内容
	 * 
	 * @param $id
	 * @return array
	 */
	protected function parse(&$id)
	{
		if (!is_array($id)) {
			$row = Better_DAO_Notify::getInstance($this->uid)->get(array(
				'id' => $id,
				'uid' => $this->uid
				));
		} else {
			$row = &$id;
		}
		
		$result = &$row;
		
		switch ($row['type']) {
			case 'direct_message':
				break;
			case 'follow_request':
				break;
			case 'friend_request':
				break;
			case 'normal':
				break;
			case 'blog':
				break;
			case 'game':
				break;
		}
		
		return $result;
	}
	
	/**
	 * 存储一个属于当前用户实例的通知
	 * 
	 * @return null
	 */
	public function store(array $params)
	{
		$seq = $this->getSeq();
		
		Better_DAO_Notify::getInstance($this->uid)->insert(array(
			'id' => $seq,
			'uid' => $this->uid,
			'dateline' => time(),
			'type' => isset($params['type']) && in_array($params['type'], self::$types) ? $params['type'] : 'normal',
			'content' => isset($params['content']) ? $params['content'] : '',
			'readed' => '0',
			'data' => $params['data']
			));
	}
	
	/**
	 * 标记通知为已读
	 * 
	 * @return null
	 */
	public function read($id)
	{
		Better_DAO_Notify::getInstance($this->uid)->update(array(
			'readed' => '1'
		), array(
			'id' => $id,
			'uid' => $this->uid
		));
	}
	
	/**
	 * 删除通知
	 * 
	 * @return null
	 */
	public function delete($id)
	{
		Better_DAO_Notify::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'id' => $id
			));
		Better_DAO_Notify_Assign::getInstance()->deleteByCond(array(
			'uid' => $this->uid,
			'seq' => $id
			));
	}

	/**
	 * 获取序列并写入分配表
	 * 
	 * @return integer
	 */
	protected function getSeq()
	{
		$seq = Better_DAO_Notify_Sequence::getInstance()->get();
		
		Better_DAO_Notify_Assign::getInstance()->insert(array(
			'seq' => $seq,
			'uid' => $this->uid
			));
		
		return $seq;
	}
}