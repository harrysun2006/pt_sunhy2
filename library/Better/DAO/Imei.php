<?php

/**
 * IMEI记录
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Imei extends Better_DAO_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct()
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'imei';
    	$this->priKey = 'imei';
    	$this->orderKey = 'last_active';
    	
		parent::__construct (0);
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
	
	public function save(array $params)
	{		
		$logData = array(
			'imei' => $params['imei'],
			'dateline' => time(),
			'action' => $params['action']
			);
		$toInsert = array(
			'imei' => $params['imei'],
			'action' => $params['action'],
			);
		$isReg = false;
		
		if (isset($params['uid']) && isset($params['partner'])) {
			$logData['uid'] = (int)$params['uid'];
			$logData['partner'] = $params['partner'];
			$logData['version'] = $params['version'];
			$logData['platform'] = $params['platform'];
			$logData['model'] = $params['model'];	
			
			$toInsert['uid'] = (int)$params['uid'];
			$toInsert['partner'] = $params['partner'];
			$toInsert['version'] = $params['version'];
			$toInsert['platform'] = $params['platform'];
			$toInsert['model'] = $params['model'];	
			$toInsert['last_active'] = time();
		} else if (isset($params['reg_uid']) && isset($params['reg_partner'])) {
			$logData['uid'] = (int)$params['reg_uid'];
			$logData['partner'] = $params['reg_partner'];
			$logData['version'] = $params['reg_version'];
			$logData['platform'] = $params['reg_platform'];
			$logData['model'] = $params['reg_model'];				
			
			$toInsert['reg_uid'] = (int)$params['reg_uid'];
			$_reg_partner = substr($params['reg_partner'], 3, 4);
			$_reg_partner = str_pad($_reg_partner, 4, "0", STR_PAD_LEFT);
			$toInsert['reg_partner'] = $_reg_partner;
			$toInsert['reg_version'] = $params['reg_version'];
			$toInsert['reg_platform'] = $params['reg_platform'];
			$toInsert['reg_model'] = $params['reg_model'];		
			$toInsert['reg_last_active'] = time();				
			
			$isReg = true;
		}

		Better_DAO_Imei_Logs::getInstance()->insert($logData);
		
		$row = Better_DAO_Imei::getInstance()->get($params['imei']);
		if (isset($row['imei']) && $row['imei']) {
			if (!$isReg) {
				$result = $this->update($toInsert, array(
					'imei' => $params['imei'],
					));
			}
		} else {
			if ($isReg) {
				$result = $this->insert($toInsert);
			}
		}
			
		return $result;
	}
}
