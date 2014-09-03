<?php
/**
 *
 * @author leip
 * @version 
 */

/**
 * compareTime helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_UserLink {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function UserLink() {
		// TODO Auto-generated Zend_View_Helper_compareTime::compareTime() helper 

		if ($this->view->uid>0) {
			$filename = $this->view->user['username'];
		} else {
			$filename = 'user';
		}
		
		return $filename;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
