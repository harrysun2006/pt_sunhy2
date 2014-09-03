<?php

/**
 * 邀请朋友首页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class InvitationController extends Better_Controller_Front 
{
	
	public function init()
	{
		parent::init();
		$this->needLogin();
		$this->commonMeta(); 
		
		
		$this->appendScript($this->jsUrl.'/functions/peoples.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
    	$this->appendScript($this->jsUrl.'/controllers/invitation.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			)); 
    		   	
	}
	
	public function indexAction()
	{	
		$this->appendScript($this->jsUrl.'/controllers/invitation/clientaddressbook.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));	
		$userInfo = Better_Registry::get('user')->getUser();
			
		$linkInfo = $this->lang->global->invitation->im_sendinfo;	
		$linkInfo = str_replace('{NICKNAME}',$userInfo['nickname'],$linkInfo);
		$linkInfo = str_replace('{URL}',BETTER_BASE_URL."/signup?ref=".$userInfo['uid'],$linkInfo);
		$this->view->imsignupurl = $linkInfo;		
	}

	public function sendmailAction()
	{
		$userInfo = Better_Registry::get('user')->getUser();
		
	}
	
	public function dosendmailAction(){
		
		$output = array(
			'error' => 1,
			);
		$post = $this->getRequest()->getPost();
		$emails = explode('{*}',$post['newmaillist']);
		$userInfo = Better_Registry::get('user')->getUser();	
		$exists = array();	
		foreach ($emails as $email) {			
			if (Better_User_Exists::getInstance($this->uid)->email($email) || $userInfo['email']==$email) {
				$exists[] = $email;
			}
		}
		if (count($exists)>0) {
			$output['exists'] = &$exists;
		} else {
			$output['error'] = 0;
			$friendsnames =  explode('{*}',$post['newfriendnamelist']);
			for($i=0;$i<count($emails);$i++){
				$email =array();
				$email['mail']=$emails[$i];
				$email['name']=$friendsnames[$i];
				Better_Email_Invite::sendbyname($email, $userInfo);	
			}
			/*
			foreach ($emails as $email) {			
				Better_Email_Invite::send($email, $userInfo);						
			}	
			*/		
		}		
		echo json_encode($output);
		exit(0);		
	}
	
	public function mailaddressbookAction()
	{
		$this->view->email_domains = array(
			'sina.com', 'sohu.com', 'tom.com', '163.com', 'yeah.net', 'gmail.com',
		// 'yahoo.com', 'yahoo.com.cn', 'yahoo.cn','126.com',
			);
			
		$userInfo = Better_Registry::get('user')->getUser();
		
		$data = Better_User_Follow::getInstance($userInfo['uid'])->getFollowingsWithDetail(1, 18);
		$this->view->following = $data['rows'];
				
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		
		$results = Better_Search::factory(array(
			'what' => 'user',
			'lon' => $lon,
			'lat' => $lat,
			'range' => 5000,
			'page' => $this->page
			))->search();

		$this->view->aroundUsers = $results['rows'];
	}
	
	public function clientaddressbookAction(){
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/invitation/clientaddressbook.js?ver='.BETTER_VER_CODE);		
	}
	
	public function uploadbookAction(){

		$post = $this->getRequest()->getPost();
		$avatar = $post['avatar'];		
        $id = Better_Attachment_Save_Csv::getInstance('myfile')->upload();
        $return = array('has_err'=>1);
        if ($id) {        	
        	$file = Better_Config::getAppConfig()->attachment->attach_tmp."/".$id.".csv";    
        	$handle=fopen("$file","r");
	        $newdata = array();		
			while($data[]=fgetcsv($handle,10000,","))
			{			
				$newdata = &$data;
			}
			fclose($handle);
			unlink($file);
			$emailrow = array();
			$namerow = array();
			for($j=0;$j<count($newdata);$j++){		
				for($i=0;$i<count($newdata[0]);$i++){
					$check_email_row = Better_Functions::checkEmail($newdata[$j][$i]);
					if($check_email_row){
						$emailrow[] = $i;
					}	
				}
				if(count($emailrow)==1){
					break ;
				} else {
					unset($emailrow);
				}
			}
			for($i=0;$i<count($newdata[0]);$i++){
					$check_name_row = mb_convert_encoding($newdata[0][$i],'utf-8','gb2312');
					if($check_name_row=='姓名' || strtolower($check_name_row)=='name'){
						$namerow[] = $i;
						break;
					}				
			}
			$emaillist = array();
			$email_row = $emailrow[0];
			$name_row = $namerow[0];
			$i = 0;
			$mailliststr = "";
			$nameliststr ="";
			$breakstr = "";
			for($j=0;$j<count($newdata);$j++){
				$check_email_row = Better_Functions::checkEmail($newdata[$j][$email_row]);
				if($check_email_row){
					$emaillist[$i]['mail'] = $newdata[$j][$email_row];				
					if(strlen($newdata[$j][$name_row])>0){
						$emaillist[$i]['name'] = mb_convert_encoding($newdata[$j][$name_row],'utf-8','gb2312');
					} else {
						$emaillist[$i]['name'] = $newdata[$j][$email_row];
					}
					$mailliststr .=$breakstr.$emaillist[$i]['mail'];
					$nameliststr .=$breakstr.$emaillist[$i]['name'];
					$breakstr = "{*}";
					$i++;
				}
			}
			//$return['data'] = $emaillist;
        	$return['has_err'] = 0;
        	$return['mailliststr'] = $mailliststr;
        	$return['nameliststr'] = $nameliststr;
        } else {
        	$return['err'] = $id;
        }         
        echo json_encode($return);
        exit(0);
	}
	
	public function findAction()
	{
		$this->view->email_domains = array(
			'sina.com', 'sohu.com', 'tom.com', '163.com', 'yeah.net', 'gmail.com',
		// 'yahoo.com', 'yahoo.com.cn', 'yahoo.cn','126.com',
			);
			
		$bindSites = $this->user->syncsites()->getSites();		
		$bindmsn = $bindsina = $bindkaixin = $bindfanfou = 'false';
		if($bindSites['sina.com']){
			$bindsina = 'true';
			$this->view->headScript()->prependScript('
				var username_sina = "'.$bindSites['sina.com']['username'].'";
				var pass_sina = "'.$bindSites['sina.com']['password'].'"
			');		
		}
		if($bindSites['kaixin001.com']){
			$bindkaixin = 'true';
			$this->view->headScript()->prependScript('
				var username_kaixin = "'.$bindSites['kaixin001.com']['username'].'";
				var pass_kaixin = "'.$bindSites['kaixin001.com']['password'].'"
			');	
		}
		if($bindSites['fanfou.com']){
			$bindfanfou = 'true';
			$this->view->headScript()->prependScript('
				var username_fanfou = "'.$bindSites['fanfou.com']['username'].'";
				var pass_fanfou = "'.$bindSites['fanfou.com']['password'].'"
			');	
		}
		if($bindSites['msn.com']){
			$bindmsn = 'true';
			$this->view->headScript()->prependScript('
				var username_msn = "'.$bindSites['msn.com']['username'].'";
				var pass_msn = "'.$bindSites['msn.com']['password'].'"
			');	
		}
		$this->view->bindsina = $bindsina;
		$this->view->bindkaixin = $bindkaixin;
		$this->view->bindfanfou = $bindfanfou;
		$this->view->bindmsn = $bindmsn;
	}
	
	public function recommendAction()
	{
		$userInfo = Better_Registry::get('user')->getUser();
		//Karma值最高的20个用户
		/*
		$topkarma = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => 21
			))->searchByTop20Karma();
		//粉丝最多的
		$topfollowers = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => 21
			))->searchByTop20Followers();
		//好友最多的
		$topfriends = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => 21
			))->searchByTop20Friends();
		//发帖最多的
		$topblogs = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => 21
			))->searchByTop20Blogs();
		*/
		
		$hotusers = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => 21
			))->searchHotUser();
		$newusers = Better_Search::factory(array(
			'what' => 'user',
			'page' => $this->page,
			'count' => 21
			))->searchNewUser();
		$this->view->hotUsers = $hotusers;
		$this->view->newUsers = $newusers;
		
		//附近的人
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		
//		$results = Better_Search::factory(array(
//			'what' => 'user',
//			'lon' => $lon,
//			'lat' => $lat,
//			'range' => 50000,
//			'page' => 1,
//			'count' => 21,
//			'order_key' => 'rp',
//			'has_avatar' => true
//			))->search();
		$results['rows'] = array(); 
		$this->view->aroundUsers = $results['rows'];
	}
	
	
	
}

?>