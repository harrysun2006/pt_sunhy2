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
class Zend_View_Helper_Images {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function Images($filename) {
		// TODO Auto-generated Zend_View_Helper_compareTime::compareTime() helper 

		switch (Better_Registry::get('language')) {
			case 'en':
				$filename = 'en.'.$filename;
				break;
			default:
				
				break;
		}
		
		return $filename.'?ver='.BETTER_VER_CODE;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
