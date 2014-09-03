<?php

/**
 * msn机器人处理
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_MsnRobots
{
	
	/**
	 * 更新MSN机器人列表
	 *
	 * @param string $xml
	 * @return null
	 */
	public static function updateList($xml)
	{
		$dao = Better_DAO_MsnRobots::getInstance();
		if ($xml) {
			$dom = new DOMDocument();
			$dom->loadXML($xml);
			$items = $dom->getElementsByTagName('item');
			if ($items->length>0) {
				for ($i=0;$i<$items->length;$i++) {
					$robot = trim($items->item($i)->getAttribute('id'));
					$dao->deleteByCond(array(
						'robot' => $robot,
					));
					
					$dao->insert(array(
						'robot' => $robot,
						'last_update' => time()
						));
				}
			}
		}
	}
	
}