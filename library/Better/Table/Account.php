<?php

/**
 * BetterAccount
 *  
 * @author leip
 * @version 
 */

class Better_Table_Account extends Zend_Db_Table_Abstract {
	/**
	 * The default table name 
	 */
	protected $_name = 'better_account';
		
	/**
	 * 表主键
	 */
	protected $_primary = 'uid';

}
