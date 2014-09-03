<?php

/**
 * msn机器人列表数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_MsnRobots extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct()
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'msn_robots';
    	$this->priKey = 'id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct (0);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_MsnRobots();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}

	public function truncate()
	{
		return parent::truncate();
	}
	
	/**
	 * 随机取得一个msn机器人
	 * 
	 * @param $time
	 * @return array
	 */
	public function rand($time=0)
	{
		$where = $time==0 ? '' : 'WHERE `last_update`>'.(time()-$time).'';
		$sql = 'SELECT `robot` FROM `'.$this->tbl.'` '.$where.' ORDER BY RAND() LIMIT 1';
		$rs = self::squery($sql, $this->rdb);
		return $rs->fetch();
	}
		
}
