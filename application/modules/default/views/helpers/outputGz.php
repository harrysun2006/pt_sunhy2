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
class Zend_View_Helper_outputGz {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function outputGz() {
		if (strstr($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip')) {
			$output = @ob_get_content();
			@ob_end_clean();
			header('Content-Encoding: gzip');
			$output = gzencode($output, 2, FORCE_GZIP);
			header('Content-Length: '.strlen($output));			
			echo $output;
		} else {
			@ob_end_flush();
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
