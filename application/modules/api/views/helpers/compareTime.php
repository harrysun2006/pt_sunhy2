<?php
/**
 *
 * @author leip
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * compareTime helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_compareTime {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function compareTime($dateline) {
		// TODO Auto-generated Zend_View_Helper_compareTime::compareTime() helper 
		$str = '';
		$now = time();
		
		if ($now-$dateline<(60*60)) {
			$str = ceil(($now-$dateline)/(60*60)).' '.$this->view->lang->global->minute.$this->view->lang->global->before;
		} elseif ($now-$dateline<(60*60*24)) {
			$str = ceil(($now-$dateline)/(60*60*24)).' '.$this->view->lang->global->hour.$this->view->lang->global->before;
		} elseif ($now-$dateline<(60*60*24*7)) {
			$str = ceil(($now-$dateline)/(60*60*24*7)).' '.$this->view->lang->gloable->day.$this->view->lang->global->before;
		} else {
			$str = Better_Functions::date('y-m-d');
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
