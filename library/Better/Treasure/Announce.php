<?php

/**
 * 宝物兑换公告
 * 
 * @package Better.Treasure
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Treasure_Announce
{
	
	public static function getAnnounce($page=1, $count=BETTER_PAGE_SIZE)
	{
		return Better_DAO_Treasure_Announce::getIntance()->getAnnounce($page, $count);
	}
}