<?php

/**
 * BetterMicroblog
 *  
 * @author leip
 * @version 
 */

class Better_Table_Blog extends Zend_Db_Table_Abstract {
	/**
	 * 表主键
	 */
	protected $_primary = 'bid';
	
	/**
	 * The default table name 
	 */
	protected $_name = 'better_blog';
	
	/**
	 * 关系表
	 */
	protected $_dependentTables = array('better_profile');
	protected $_referenceMap = array(
														'User' => array(
																			'columns' => 'uid',
																			'refTableClass' => 'Profile',
																			'refColumns' => 'uid',
																			),
														);

}
