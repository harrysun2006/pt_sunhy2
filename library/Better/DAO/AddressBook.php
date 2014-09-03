<?php

/**
 * 用户的电话地址簿
 *
 * @package Better.DAO
 * @author sunhy <sunhy@peptalk.cn>
 *
 */

class Better_DAO_AddressBook extends Better_DAO_Base
{
  
	private static $instance = null;
	private static $tbld = '';

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'addbook';
		$this->tbld = BETTER_DB_TBL_PREFIX.'addbookdt';
		$this->priKey = 'uid';
		$this->orderKey = &$this->priKey;
	}

	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');

			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}

	/**
	 * 取用户地址簿的条目
	 * @param $uid: 用户ID
	 */
	public function getItems($uid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbld.' AS d');
		$select->where('d.uid=?', $uid);
		$select->order('d.id ASC');
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		return $rows;
	}

	public function deleteAll($uid)
	{
		$this->delete($uid);
		$this->deleteItems($uid);
	}

	public function deleteItems($uid)
	{
		$this->wdb->delete($this->tbld, $this->wdb->quoteInto('uid=?', $uid));
	}

	public function insertItem($data)
	{
		$rows_affected = $this->wdb->insert($this->tbld, $data);
		return $this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : $rows_affected;
	}

	/**
	 * 查找addbookdt中content包含指定内容的项
	 * @param array $params
	 */
	public function findItems(array $params, $page, $count)
	{
		$page < 1 && $page = 1;
		$count <= 0 && $count = 50;
		$cell_no = $params && isset($params['cell_no']) ? $params['cell_no'] : '';
		$email = $params && isset($params['email']) ? $params['email'] : '';
		if (!$cell_no && !$email) return array();
		$select = $this->rdb->select();
		$select->from($this->tbld.' AS d', array('d.uid'));
		$select->where('1=0');
		if ($cell_no) $select->orWhere('d.content=?', $cell_no);
		if ($email) $select->orWhere('d.content=?', $email);
		$select->order('d.uid ASC');
		$select->limitPage($page, $count);
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		return array_unique($rows);
	}
}