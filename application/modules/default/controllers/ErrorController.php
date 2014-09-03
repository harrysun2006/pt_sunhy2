<?php

/**
 * 错误控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class ErrorController extends Zend_Controller_Action
{

	public function init()
	{
		$this->view->css = 'default';
	}
	
    public function errorAction()
    {
		$this->view->lang = Better_Language::load();
		$this->lang = &$this->view->lang;
		    	
    	$module = $this->_getParam('module');
    	
    	$errors = $this->_getParam('error_handler');
        $this->view->error_code = '';

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->error_code= '404';
                $this->view->message = $errors->exception->getMessage();
                header("HTTP/1.1 404 Not Found");
                if (BETTER_CONTROLLER_MODULE=='web') {
                	$cacher = Better_Cache::remote();
                	$cacheKey = md5($_SERVER['REQUEST_URI']).'_IS_404';
                	$cacher->set($cacheKey, 1, 3600*24);
                }
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->error_code= '500';
                $this->view->message = $errors->exception->getMessage();
                break;
        }
       
        Better_Log::getInstance()->logEmerg($errors->exception->getMessage()."\n".$errors->exception->getTraceAsString(), 'error');

        if ($module=='api') {        	
        	$api = new Better_Controller_Api($this->getRequest(), $this->getResponse());
        	$api->outputed = true;
        	$api->error('error.request.server_error', 500);
        	exit(0);
        } else if ($module=='ajax') {
        	
        } else if (Zend_Controller_Front::getInstance()->getParam('noViewRenderer') || $this->getRequest()->isXmlHttpRequest()) {
        	$output = array(
        				'exception' => $errors->exception->getMessage(),
        				'request' => $errors->request->getParams(),
        				);
        	if (APPLICATION_ENV=='development') {
        		$output['trace'] = $errors->exception->getTraceAsString();
        	}
        	echo json_encode($output);
        	exit(0);
        }

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
        
    }


}

