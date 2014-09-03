<?php

/**
 * 工具首页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class ToolsController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		//$this->needLogin();
		$this->commonMeta();
    	
    	$this->view->headScript()->prependFile($this->jsUrl.'/ajax.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));		
	}
	
	public function indexAction()
	{
		$id = $this->getRequest()->getParam('id', 26);
		
		$this->view->headScript()->prependScript('
			var id = '.$id.';
		');
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/tools.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		$p = $this->getRequest()->getParam('tools_mail_partner', '');
        Better_Registry::get('sess')->set('tools_mail_partner', $p);
	}
	
	public function lbsAction()
	{
		
	}
	
	public function shareAction()
	{
		
	}
	
	public function flashAction()
	{
		
	}
	
	public function apiAction()
	{
		
	}
	
	public function iphonedownloadAction(){
		
	}
	
	public function s60downloadAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/tools/s60download.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		$mp = Better_Registry::get('sess')->get('tools_mail_partner');
        $v5d = Better_Download::getPhoneByOid(19);
        $v3d = Better_Download::getPhoneByOid(6);
        $v23d = Better_Download::getPhoneByOid(5);
        $v22d = Better_Download::getPhoneByOid(4);
        $v21d = Better_Download::getPhoneByOid(3);
        $v20d = Better_Download::getPhoneByOid(2);
       if ($mp) {
           $v5d = str_replace('files/clients', 'files/clients_mail_partner', $v5d);
           $v3d = str_replace('files/clients', 'files/clients_mail_partner', $v3d);
           $v23d = str_replace('files/clients', 'files/clients_mail_partner', $v23d);
           $v22d = str_replace('files/clients', 'files/clients_mail_partner', $v22d);
           $v21d = str_replace('files/clients', 'files/clients_mail_partner', $v21d);
           $v20d = str_replace('files/clients', 'files/clients_mail_partner', $v20d);
        }
		$this->view->s60v5download =  $v5d;
		$this->view->s60v3download =  $v3d;
		$this->view->s60v23download = $v23d;
		$this->view->s60v22download = $v22d;
		$this->view->s60v21download = $v21d;
		$this->view->s60v20download = $v20d;	
		$postdate = Better_DAO_DownloadProduct::getInstance()->getPostInfoByOs(6);
		$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
		$this->view->postver = $postdate['version'];
	}
	
	public function androiddownloadAction(){
		$postdata = Better_DAO_DownloadProduct::getInstance()->getPostInfoByOs(8);
		$mp = Better_Registry::get('sess')->get('tools_mail_partner');
     	$androiddownload = Better_Download::getPhoneByOid(8);
     	if ($mp) {
           $androiddownload = str_replace('files/clients', 'files/clients_mail_partner', $androiddownload);
        }
		$this->view->androiddownload =  $androiddownload;
		$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdata['postdate']));
		$this->view->postver = $postdata['version'];
	}
	
	public function blackberrydownloadAction(){
		$postdate = Better_DAO_DownloadProduct::getInstance()->getPostInfoByOs(20);
		$mp = Better_Registry::get('sess')->get('tools_mail_partner');
     	$v50d = Better_Download::getPhoneByOid(20);
        $v45d = Better_Download::getPhoneByOid(21);
        $v46d = Better_Download::getPhoneByOid(22);
          
		$this->view->v50download =  $v50d;
		$this->view->v46download =  $v46d;
		$this->view->v45download = $v45d;		
		$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
		$this->view->postver = $postdate['version'];
	}
	public function wmdownloadAction(){
		$postdate = Better_DAO_DownloadProduct::getInstance()->getPostInfoByOs(14);
		$mp = Better_Registry::get('sess')->get('tools_mail_partner');
    	$wmdownload =  Better_Download::getPhoneByOid(14);
     	if ($mp) {
            $wmdownload = str_replace('files/clients', 'files/clients_mail_partner', $wmdownload);
        }
		$this->view->wmdownload =  $wmdownload;
		$this->view->postdate = Better_Functions::date("Y-m-d", intval($postdate['postdate']));
		$this->view->postver = $postdate['version'];
	}
	
}

?>