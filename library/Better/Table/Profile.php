<?php

/**
 * BetterProfile
 *  
 * @author leip
 * @version 
 */

class Better_Table_Profile extends Zend_Db_Table_Abstract {
	/**
	 * 表主键
	 */
	protected $_primary = 'uid';
	
	/**
	 * The default table name 
	 */
	protected $_name = 'better_profile';
}
