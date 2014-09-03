<?php

/**
 * 短信猫池
 * 
 * @package Better.Modem
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Modem_Pool
{
	protected static $pools = array();
	
	/**
	 * 随机给出一个短信modem号码
	 * 
	 * @return string
	 */
	public static function randomOne()
	{
		self::saveCache();
		
		$result = '';
		if (count(self::$pools)) {
			$random = rand(0, count(self::$pools)-1);
			$result = self::$pools[$random];
		}
		
		return $result;
	}
	
	protected static function saveCache()
	{
		$cacher = Better_Cache::remote();
		$pools = $cacher->get('modem_pool');
		if (!$pools) {
			$rows = Better_DAO_Modem_Pool::getInstance()->getAll();
			foreach ($rows as $row) {
				$pools[] = $row['cell_no'];
			}
			
			$cacher->set('modem_pool', $pools);
		}
		
		self::$pools = &$pools;
	}
	
	/**
	 * 更新池内猫的号码
	 * 
	 * @param unknown_type $xml
	 * @return null
	 */
	public static function updatePool($xml)
	{
		
		if ($xml) {
			$dao = Better_DAO_Modem_Pool::getInstance();
			$dom = new DOMDocument();
			$dom->loadXML($xml);
			$items = $dom->getElementsByTagName('item');
			if ($items->length>0) {
				$modems = array();
				for ($i=0;$i<$items->length;$i++) {
					$modem = trim($items->item($i)->getAttribute('no'));
					$dao->deleteByCond(array(
						'cell_no' => $modem,
					));
					
					$dao->insert(array(
						'cell_no' => $modem,
						'last_update' => time()
						));
					$modems[] = $modem;
				}
				
				Better_Cache::remote()->set('modem_pool', $modems);
			}
		}		
	}
	
	public static function updateSinglePool($cell, $status='on')
	{
		$dao = Better_DAO_Modem_Pool::getInstance();
		$dao->deleteByCond(array(
			'cell_no' => $cell
			));
		if ($status=='on') {
			$dao->insert(array(
				'cell_no' => $cell,
				'last_update' => time()
				));
		}
		
		Better_Cache::remote()->set('modem_pool', null);
		self::saveCache();
	}
}