<?php

/**
 * 游戏
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Game
{
	
	/**
	 * 生成具体的游戏处理对象
	 * 
	 * @return Better_Game_Base
	 */
	public function factory($game, $uid=0)
	{
		$className = 'Better_Game_'.ucfirst($game);
		
		if (class_exists($className)) {
			return call_user_func($className.'::getInstance', $uid);
		} else {
			return null;
		}		
	}
}