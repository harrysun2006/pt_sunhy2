<?php
/**
 *
 * @author pysche
 * @version 
 */

/**
 * Highlight helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_Highlight {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function highlight($str, $keyword) {
		$keyword = trim($keyword);
		
		if ($keyword!='') {
			$str = eregi_replace($keyword, '<span class="highlight">'.$keyword.'</span>', $str);
		}
		
		return $str;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
