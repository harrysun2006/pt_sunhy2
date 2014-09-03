<?php

/**
 * 后台错误控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class Admin_ErrorController extends Zend_Controller_Action
{

	public function init(){
		$this->view->css = 'default';
	}
	
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $this->view->error_code = '';

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->error_code= '404';
                $this->view->message = $errors->exception->getMessage();
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->error_code= '500';
                $this->view->message = $errors->exception->getMessage();
                break;
        }
        
        Better_Log::getInstance()->logEmerg($errors->exception->getMessage(), 'error');
        
        if (Zend_Controller_Front::getInstance()->getParam('noViewRenderer') || $this->getRequest()->isXmlHttpRequest()) {
        	echo json_encode(array(
        				'exception' => $errors->exception->getMessage(),
        				'request' => $errors->request->getParams(),
        				));
        	exit(0);
        }

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;

    }


}

