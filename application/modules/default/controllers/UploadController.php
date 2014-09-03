<?php

/**
 * 接收ajax上传请求（发博客／贴个人照片等）的控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class UploadController extends Better_Controller_Front 
{

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	$this->needLogin();
    }

    public function indexAction()
    {
        // action body
        $at = new Better_Attachment();
        $result = $at->uploadFile('myfile');
        $return = array(
        	'err' => '',
        	'file_id' => '',
        	);
        	
       	if (is_array($result) && $result['file_id']) {
       		$return['file_id'] = $result['file_id'];
       		$data = $at->parseAttachment($result);
       		$return['url'] = $data['url'];
       		$return['thumb'] = $data['thumb'];
       	} else {
       		$return['err'] = $result;
       	}
        
        echo json_encode($return);
        exit(0);
    }
}

?>