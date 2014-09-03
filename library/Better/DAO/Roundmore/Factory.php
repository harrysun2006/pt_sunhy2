<?php

/**
 * 附近消息工厂
 * 
 * @package Better.DAO.Poi
 * @author yangl
 */

class Better_DAO_Roundmore_Factory
{

	public static function create($what){
		return call_user_func('Better_DAO_Roundmore_'.ucfirst($what).'::getInstance');
	}
	
	
}