<?php

class Better_DAO_Invitecode extends Better_DAO_Base{
	
	private static $instance = null;
  

    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'invitecode';
    	$this->priKey = 'id';
    	$this->orderKey = 'id';
    	
		parent::__construct($identifier);
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
	
	
	public function getOne(){
		
		$select = $this->rdb->select();
		$select->from($this->tbl.' as a', '*');
		$select->where('a.enable=?', '1');
		$select->limit(1);

		$result = self::squery($select, $this->rdb);

		return $result->fetch();
		
	}
	
}
