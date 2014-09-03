<?php

/**
 * 用户空间首页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class SpaceController extends Better_Controller_Front 
{
	private $_badge_page = 30;
	
	public function init()
	{
		parent::init();
		$this->needLogin();
		$this->commonMeta();
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/space.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		
		$uid = intval( $this->getRequest()->getParam('uid', 0 ) );
		if ($uid) {
    		$this->dispUser = Better_User::getInstance($uid);
    		$this->dispUserInfo = $this->dispUser->getUser();
    		
    		if ($this->dispUserInfo['uid']<=0) {
    			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    		}
    		$this->view->dispUser = $this->dispUser;
    		$this->view->dispUserInfo = $this->dispUserInfo;
    	} else {
			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
    	}
	}
	
	
	public function badgesAction()
	{
		$pageSize = $this->_badge_page;
		$family = trim($this->getRequest()->getParam('family', 'normal'));
		
		$album  = $this->dispUser->badge()->getAlbum(false);
		$badges = $this->dispUser->badge()->getByFamily($family);

		$this->view->album = $album;
		$this->view->badges = array_slice($badges, 0, $pageSize);
		$this->view->family_id = $family;
		$this->view->family = Better_Badge::$families[$family];
		$this->view->more = count($badges) > $pageSize;
	}
	
	public function familyAction()
	{
		$pageSize = $this->_badge_page;
		$family = trim($this->getRequest()->getParam('family', 'normal'));
				
		$album  = $this->dispUser->badge()->getAlbum(false);
		$badges = $this->dispUser->badge()->getByFamily($family);
		foreach (array_slice($badges, $pageSize) as $badge) {
			$badgePicture = str_replace('big','96',$badge['badge_big_picture']);
			$badgeName = $badge['badge_name'];
			$tips = trim($badge['got_tips']);
			$time = $_COOKIE["offset_time"];
			$btime = date('Y-m-d H:i:s', intval($badge['get_time'])+intval($time)).' '.$this->lang->global->got_badge;
			echo <<<EOT
			<li>
      	 <a href='#bigbadge_{$badge['id']}' class='badge_icons' uid='{$this->dispUserInfo['uid']}'>
      	 <img title="{$tips}" src="{$badgePicture}" alt="{$badgeName}" class="pngfix" /></a>
      	 <span>{$badgeName}</span>
      </li>
      <div style="display:none" id='hidden_b_{$badge['id']}'><div id="bigbadge_{$badge['id']}" ></div></div>
EOT;
		}
		exit;
	}
}
