<?php

/**
 * 朋友类勋章
 * 
 * @package Better.Badge
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Badge_Friend extends Better_Badge_Base
{
	protected static $instance = array();
	
	public static function getInstance($badgeId)
	{
		if (!isset(self::$instance[$badgeId])) {
			self::$instance[$badgeId] = new self($badgeId);	
		}
		
		return self::$instance[$badgeId];
	}	
}