<?php

/**
 * 用户收到的站内私信数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_DmessageReceive extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = null)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'dmessage_receive';
    	$this->priKey = 'msg_id';
    	$this->orderKey = 'dateline';
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function get($val)
	{
		if (is_array($val)) {
			return parent::get($val);
		} else {
			$select = $this->rdb->select();
			$select->from($this->tbl, '*');
			$select->where($this->priKey.'=?', $val);
			$select->limit(1);
	
			$result = self::squery($select, $this->rdb);
			
			return $result->fetch();
		}
	}
	/**
	 * 计数
	 * 
	 * @return integer
	 */
	public function getMinesCount(array $params=array())
	{
		$count = 0;
		
		$since = isset($params['since']) ? (int)$params['since'] : 0;
		$type = isset($params['type']) ? (array)$params['type'] : array();
		$delived = isset($params['delived']) ? ($params['delived'] ? true : false) : null;
		$readed = isset($params['readed']) ? ($params['readed'] ? true : false) : null;
		$actResult = isset($params['act_result']) ? (int)$params['act_result'] : null;
						
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $this->identifier);
	
		if ($since>0) {
			$select->where('msg_id>=?', $since);
		}

		if ($delived!==null) {
			if ($delived) {
				$select->where('delived=?', 1);
			} else {
				$select->where('delived=?', 0);	
			}
		}
		
		if ($readed!==null) {
			if ($readed) {
				$select->where('readed=?', 1);
			} else {
				$select->where('readed=?', 0);
			}
		}
		
		if ($actResult!==null) {
			$select->where('act_result=?', $actResult);
		}		
		
		if (count($type)>0 && $type[0]!='') {
			$select->where('type IN (?)', $type);
		}		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}

	/**
	 * 我的所有消息
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function getMines(array $params)
	{
		$result = array(
			'rows' => array(),
			'count' => 0,
			);
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$count = isset($params['count']) ? (int)$params['count'] : BETTER_PAGE_SIZE;
		$desc = isset($params['desc']) && $params['desc']==false ? false : true;
		$since = isset($params['since']) ? (int)$params['since'] : 0;
		$type = isset($params['type']) ? (array)$params['type'] : array();
		$delived = $params['delived']===null ? null : ($params['delived'] ? true : false);
		$readed = isset($params['readed']) ? ($params['readed'] ? true : false) : null;
		$imDelived = isset($params['im_delived']) ? ($params['im_delived'] ? true : false) : null;
		$actResult = isset($params['act_result']) ? (int)$params['act_result'] : null;
		$withoutNr = isset($params['without_nr']) ? (bool)$params['without_nr'] : true;

		$select = $this->rdb->select();
		$cSelect = $this->rdb->select();
		
		$select->from($this->tbl);
		$cSelect->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		
		if ($since>0) {
			$select->where('msg_id>=?', $since);
			$cSelect->where('msg_id>=?', $since);
		}
		
		if ($withoutNr) {
			$select->where('type!=?', 'notification_readed');
			$cSelect->where('type!=?', 'notification_readed');
		}

		if ($delived!==null) {
			if ($delived) {
				$select->where('delived=?', 1);
				$cSelect->where('delived=?', 1);
			} else {
				$select->where('delived=?', 0);
				$cSelect->where('delived=?', 0);				
			}
		}
		
		if ($imDelived!==null) {
			if ($imDelived) {
				$select->where('im_delived=?', 1);
				$cSelect->where('im_delived=?', 1);
			} else {
				$select->where('im_delived=?', 0);
				$cSelect->where('im_delived=?', 0);				
			}			
		}
		
		if ($actResult!==null) {
			$select->where('act_result=?', $actResult);
			$cSelect->where('act_result=?', $actResult);
		}
		
		if ($readed!==null) {
			if ($readed) {
				$select->where('readed=?', 1);
				$cSelect->where('readed=?', 1);
			} else {
				$select->where('readed=?', 0);
				$cSelect->where('readed=?', 0);
			}
		}
		
		if (count($type)>0 && $type[0]!='') {
			$select->where('type IN (?)', $type);
			$cSelect->where('type IN (?)', $type);
		}
		
		$select->where('uid=?', $this->identifier);
		$cSelect->where('uid=?', $this->identifier);

		$rs = self::squery($cSelect, $this->rdb);
		$row = $rs->fetch();
		$total = (int)$row['total'];
		$result['count'] = $total;

		if ($total>0) {
			$desc ? $select->order('dateline DESC') : $select->order('dateline ASC');
			$select->limitPage($page, $count);

			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
			
			foreach ($rows as $row) {
				$row['userInfo'] = Better_User::getInstance($row['from_uid'])->getUserInfo();

				$result['rows'][] = $row;
			}
		}

		return $result;
	}
	
	/**
	 * （同步）我的所有消息
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function syncGetMines(array $params)
	{
		$result = array(
			'rows' => array(),
			'count' => 0,
			);
		$page = isset($params['page']) ? (int)$params['page'] : 1;
		$count = isset($params['count']) ? (int)$params['count'] : BETTER_PAGE_SIZE;
		$desc = isset($params['desc']) && $params['desc']==false ? false : true;
		$since = isset($params['since']) ? (int)$params['since'] : 0;
		$type = isset($params['type']) ? (array)$params['type'] : array();
		$delived = $params['delived']===null ? null : ($params['delived'] ? true : false);
		$readed = isset($params['readed']) ? ($params['readed'] ? true : false) : null;
		$imDelived = isset($params['im_delived']) ? (isset($params['im_delived']) ? $params['im_delived'] : null) : (isset($params['delived']) ? $params['delived'] : null);
		$actResult = isset($params['act_result']) ? (int)$params['act_result'] : null;

		$select = $this->rdb->select();
		
		$select->from($this->tbl);
		
		if ($since>0) {
			$select->where('msg_id>=?', $since);
		}

		if ($delived!==null) {
			if ($delived) {
				$select->where('delived=?', 1);
			} else {
				$select->where('delived=?', 0);			
			}
		}
		
		if ($imDelived!==null) {
			if ($imDelived) {
				$select->where('im_delived=?', 1);
			} else {
				$select->where('im_delived=?', 0);		
			}			
		}
		
		if ($actResult!==null) {
			$select->where('act_result=?', $actResult);
		}
		
		if ($readed!==null) {
			if ($readed) {
				$select->where('readed=?', 1);
			} else {
				$select->where('readed=?', 0);
			}
		}
		
		if (count($type)>0 && $type[0]!='') {
			$select->where('type IN (?)', $type);
		}
		
		$select->where('uid=?', $this->identifier);

		$total = 1;
		$result['count'] = $total;

		if ($total>0) {
			$select->limitPage($page, $count);

			$rs = self::squery($select, $this->rdb);
			$result['rows'] = $rs->fetchAll();
		}

		return $result;
	}	

	public function getLastGame($sid, $uid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('uid=?', $uid);
		$select->where('sid=?', $sid);
		$select->where('type!=?', 'game_interactive');
		$select->where('type LIKE ?', 'game_%');
		$select->order('dateline DESC');

		$rs = self::squery($select, $this->rdb);

		return $rs->fetch();
	}
	
	public function getDirectmesssageInfo($msgid)
	{
		$sql = "SELECT * FROM {$this->tbl} WHERE msg_id='{$msgid}'";
				
		$rs = $this->query($sql);
		$row = $rs->fetchAll();
		return $row;
	}
}
