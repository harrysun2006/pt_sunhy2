<?php

/**
 * 绑定第三方帐号操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_ThirdBinding extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . '3rdbinding';
    	$this->priKey = '';
    	$this->orderKey = 'dateline';
    	
		parent::__construct ($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_ThirdBinding($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	/**
	 * 按照第三名称 和 ID 搜索绑定的用户
	 */
	public static function getBindUser($protocol, $id)
	{
		$row = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid, true);
			$rdb = &$cs['r'];
			
			$tbn = BETTER_DB_TBL_PREFIX . '3rdbinding';
			$sql = "SELECT * FROM $tbn WHERE protocol='$protocol' AND tid='$id' LIMIT 1";
			$rs = self::squery($sql, $rdb);
			$row = $rs->fetch();
			if ($row) return $row;
		}

		return $row;		
	}

	/**
	 * 得到所有绑定的用户信息
	 */
	public static function getAllBind($protocols = array() )
	{
		$rows = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid, true);
			$rdb = $cs['r'];
			
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX . '3rdbinding', array('*'));
			$protocols && $select->where('protocol in (?)', $protocols);
			$rs = self::squery($select, $rdb);
			$row = $rs->fetchAll();
			$rows = array_merge($rows, $row);
		}
		
		return $rows;
	}
	
	/**
	 * 
	 */
	public static function getAllBindUser($protocol='')
	{
		$rows = array();
		
		$where = $protocol ? " AND protocol='$protocol' " : '';
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid, true);
			$rdb = $cs['r'];
			
			$sql = <<<EOT
			SELECT p.nickname, p.avatar, b . * 
			FROM better_3rdbinding b, better_profile p
			WHERE b.uid = p.uid
EOT;
			$sql .= $where;
			$rs = self::squery($sql, $rdb);
			$row = $rs->fetchAll();
			$rows = array_merge($rows, $row);	
		}
		return 	$rows;
	}
	
	
	
	/**
	 * 设置是否同步勋章
	 */
	public function setSyncBadge($protocol, $sync_badge){
		$result = false;
		if(isset($protocol) && isset($sync_badge)){
			$result = $this->updateByCond(array(
				'sync_badge'=> $sync_badge? 1: 0
			), array(
				'uid'=> $this->identifier,
				'protocol'=> $protocol
			));
		}
		
		return $result;
	}
	
}
