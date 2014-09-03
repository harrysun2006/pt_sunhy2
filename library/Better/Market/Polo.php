<?php

/**
 * 
 * 大众汽车New Polo活动
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Market_Polo extends Better_Market_Base
{
	private static $_instance = null;
	public $poiIds = array(
		'bj' => 0,
		'sh' => 0,
		'gz' => 0
		);	
	public $startTime = 0;
	public $endTime = 0;
	public $sales = array();
	public $salesAll = array();
	public $shopAll = array();
	
	private function __construct()
	{
		$config = Better_Config::getAppConfig();
		
		$this->poiIds['bj'] = $config->market->polo->poi->bj;
		$this->poiIds['sh'] = $config->market->polo->poi->sh;
		$this->poiIds['gz'] = $config->market->polo->poi->gz;
		
		$this->startTime = (int)$config->market->polo->start_time;
		$this->endTime = (int)$config->market->polo->end_time;
		
		$this->sales['bj'] = array(
			4418020, 4343947, 4420697, 4434331, 4437640, 5767328, 7040117, 19056519, 6940957,
			19056521, 18145842, 4407083, 6927497, 19056525, 4408071, 4415419, 6863697, 4405509,
			4418766, 5774220, 19056526, 19056645, 19056527, 13542854, 810080
			);
		$this->sales['gz'] = array(
			17893974, 7926222, 19056530, 16923020, 4765748, 15826422
			);
		$this->salves['sh'] = array(
			19056531, 844815, 4069708, 18119120, 1029930, 459843, 1337035, 4042402, 19056533, 
			293225, 4037056, 4089729, 4083597, 397119, 19056534, 19056535, 4090629, 4072003,
			19056536, 4055493, 19056538, 459840, 19056539, 4039427, 19056540, 4094243,4061152,
			4085353, 663596, 4067267, 4053609, 4081623
			);

		$_allshop = $config->market->polo->poi->food . ',' . $config->market->polo->poi->film;
		$this->shopAll = explode(',', $_allshop);

	}
		
	public static function getInstance()
	{
		if (self::$_instance==null) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
}