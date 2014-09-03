<?php
/**
 * 在视图中输出语言(需要替换某些字符的)
 *
 * @author leip <leip@peptalk.cn>
 * @version 
 */

/**
 * KLanguage helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_KLanguage {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function KLanguage($lang, $replace) 
	{
		return str_replace('%s', $replace, $lang);
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
