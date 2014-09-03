<?php

/**
 * 绑定第三方帐号
 *
 * @package Better.Service
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Service_ThirdBinding
{
	protected $uid = 0;
	protected $bindings = array();
	
	protected static $instance = array();
	
	private function __construct($uid)
	{
		$this->uid = $uid;
	}
	
	public static function getInstance($uid=0)
	{
		if (!array_key_exists($uid, self::$instance) || self::$instance[$uid]==null) {
			self::$instance[$uid] = new Better_Service_ThirdBinding($uid);
		}
		
		return self::$instance[$uid];
	}
	
	public function getBindings($withsyncbadge=false)
	{
		//if (count($this->bindings)==0) {
			if($withsyncbadge){
				$params = array('uid' => $this->uid, 'sync_badge'=>1);
			}else{
				$params = array('uid' => $this->uid);
			}
			$this->bindings = Better_DAO_ThirdBinding::getInstance($this->uid)->getAll($params);
		//}
		return $this->bindings;
	}
	
	public function bind($message, $bid='', $attach='', $poiId=0, $type='', $withsyncbadge=false)
	{
		$binds = 0;
		$this->getBindings($withsyncbadge);
		$message = $bid ? '' : $message;

		$_site = $_POST['site'] ? $_POST['site'] : $_GET['site'];
		$sync_site = array();
		if ($_site) {
			$sync_site = explode(',', $_site);
		}
		
		$sql = <<<EOT
INSERT INTO better_sync_queue 
(`bid`, `uid`, `poi_id`, `protocol`, `username`, `password`, `queue_time`, `attach`, `content`, `sync_time`)
VALUES
		
EOT;
		$tm = time();
		$_insert = $_sql = '';
		
		foreach($this->bindings as $bind) {
			$protocol = $bind['protocol'];
			if ( in_array( $protocol, array('9911.com', 'digu.com') ) ) continue;
			if ($sync_site && !in_array($protocol, $sync_site)) {
				continue;
			}
			$username = $bind['username'];
			$password = $bind['password'];

			$_date = array(
				'bid' => $bid,
				'uid' => $this->uid,
				'poi_id' => (int)$poiId,
				'protocol' => $protocol,
				'username' => addslashes($username),
				'password' => addslashes($password),
				'queue_time' => $tm,
				'attach' => $attach,
				'content' => $message,
				'sync_time' => 0
				);

			$_sql = implode("','", $_date);
			$_insert .= "('" . $_sql . "'),";
				
			//Todo: 同步时 写入poi同步统计
			if ($poiId && $protocol && $type) {
				//Better_DAO_Poi_Sync::getInstance()->increase($poiId, $protocol, $type);
			}
		}
		
		$sql .= rtrim($_insert, ',');
		$_sql && Better_DAO_SyncQueue::getInstance($this->uid)->execSql($sql);
		
		return $binds;
	}

	public function unbind($third_id, $third_protocol, $uid)
	{
		$binds = 0;
		$this->getBindings();

		foreach($this->bindings as $bind) {
			$protocol = $bind['protocol'];
			if ( in_array( $protocol, array('9911.com', 'digu.com') ) ) continue;
			$username = $bind['username'];
			$password = $bind['password'];
			
			if ($third_protocol != $protocol) continue;
			
			$flag = Better_DAO_SyncQueue::getInstance($uid)->insert(array(
				'bid' => $third_id,
				'uid' => $uid,
				'poi_id' => 0,
				'protocol' => $protocol,
				'username' => $username,
				'password' => $password,
				'queue_time' => time(),
				'attach' => '',
				'content' => 'unbind',
				'sync_time' => 0
				));
				
		}
		
		return $binds;
	}	
	
	protected function logFailed($protocol, $bid='')
	{
		
	}
	/*
	public function checkmailbinded()
	{
		$result = false;
		$result = Better_DAO_SyncQueue 
	}
	*/
}