<?php

/**
 * API出错提示
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_ErrorController extends Better_Controller_Api
{
	
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');

		switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->error('error.request.not_found', 404);
            	break;
            default:
            	$this->error('error.request.server_error', 500);
            	break;
		}
	}
}