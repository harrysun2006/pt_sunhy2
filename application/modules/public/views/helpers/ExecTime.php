<?php
/**
 *
 * @author pysche
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * ExecTime helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_ExecTime {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function execTime() {
		$mtime = explode(' ', microtime());
		$end = $mtime[1]+$mtime[0];
		
		$tmp = explode(' ', BETTER_START_TIME);
		$start = $tmp[1]+$tmp[0];

		return round(($end-$start), 5);
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
