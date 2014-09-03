<?php

/**
 * 宝物相关
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Treasure
{
	protected static $instance = array();
	protected $id = 0;
	protected $info = array();
	
	protected function __construct($id)
	{
		$this->id = $id;	
	}
	
	public static function getInstance($id)
	{
		if (!isset(self::$instance[$id])) {
			self::$instance[$id] = new self($id);
		}	
		
		return self::$instance[$id];
	}
	
	/**
	 * 判断宝物是否可兑换
	 * 
	 * @return bool
	 */
	public function canExchange()
	{
		return Better_DAO_Treasure_Exchange::getInstance()->canExchange($this->id);
	}
	
	/**
	 * 查看所有被兑换的宝物
	 * 
	 * @return array
	 */
	public function getExchange()
	{
		return Better_DAO_Treasure_Exchange::getCanExchangeTreasures();
	}
	
	/**
	 * 随机给出若干个宝物
	 * 
	 * @return array
	 */
	public static function randomThrow($nums=2)
	{
		$ts = Better_DAO_Treasure::getInstance()->randomThrow($nums);
		
		return $ts;
	}
	
	/**
	 * 取得宝物资料
	 * 
	 * @return array
	 */
	public function getInfo()
	{
		if (!isset($this->info['id'])) {
			$this->info = Better_DAO_Treasure::getInstance()->get($this->id);
			if ($this->info['id']<10) {
				$prefix = '00';
			} else if ($this->info['id']>=10 && $this->info['id']<100) {
				$prefix = '0';
			} else {
				$prefix = '';
			}
			
			$this->info['image_url'] = BETTER_STATIC_URL.'/images/treasures/'.$prefix.$this->info['id'].'.png';
		}
		
		return $this->info;
	}
	
	/**
	 * 取得当前所有可用的宝贝
	 * 
	 * @return array
	 */
	public static function &getAllTreasures(array $params=array())
	{
		$treasures = array();
		$params = (array)$params;
		
		$cacher = Better_Cache::remote();
		$treasures = $cacher->get('kai_treasures');
		
		if (!is_array($treasures) || count($treasures)==0) {
			$tmp = Better_DAO_Treasure::getInstance()->getAll($params);
			foreach ($tmp as $row) {
				if ($row['id']<10) {
					$prefix = '00';
				} else if ($row['id']>=10 && $row['id']<100) {
					$prefix = '0';
				} else {
					$prefix = '';
				}
				$row['image_url'] = BETTER_STATIC_URL.'/images/treasures/'.$prefix.$row['id'].'.png';
				$treasures[$row['id']] = $row;
			}
			
			$cacher->set('kai_treasures', $treasures);
		}

		return $treasures;
	}	
	
	
}