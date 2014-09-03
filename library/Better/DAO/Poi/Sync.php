<?php

/**
 * poi同步统计
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Sync extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_sync';
		$this->priKey = 'id';
		$this->orderKey = &$this->priKey;
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}	
	
	public function getThis($poiId, $protocol, $type)
	{
		$row = $this->get(array(
			'poi_id' => $poiId,
			'protocol' => $protocol,
			'type' => $type
			));
			
		if (!$row['poi_id']) {
			$row = array(
				'protocol' => $protocol,
				'type' => $type,
				'poi_id' => $poiId,
				'number' => 0
				);
				try {
					$flag = $this->insert($row);
				} catch (Exception $e) {
					Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'psync');
				}
			
		}
		
		return $row;
	}
	
	public function increase($poiId, $protocol, $type)
	{
		$this->getThis($poiId, $protocol, $type);
		
		$sql = "UPDATE `{$this->tbl}` SET number=number+1 WHERE `poi_id`='{$poiId}' AND `protocol`='{$protocol}' AND `type`='{$type}'";
		$this->wdb->query($sql);
	}

}