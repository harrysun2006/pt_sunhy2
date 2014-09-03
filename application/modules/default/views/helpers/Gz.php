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
class Zend_View_Helper_Gz {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function Gz() 
	{
		if (strstr($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip')) {			
			$output = ob_get_contents();
			ob_clean();
			$output = gzencode($output, 1, FORCE_GZIP);
			header('Content-Encoding: gzip');
			header('Content-Length: '.strlen($output));			
			die($output);
		}
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
