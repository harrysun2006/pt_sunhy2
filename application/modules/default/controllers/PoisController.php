<?php

/**
 * POI页
 *
 * @package Controllers
 * @author yangl
 */

class PoisController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		//$this->needLogin();
    	$this->commonMeta();

		$this->view->myfollowing = $this->uid ? $this->user->follow()->getFollowings() : array();
		$this->view->myblocking = $this->uid ? Better_User_Block::getInstance($this->uid)->getBlocks() : array();

		$this->view->headScript()->prependScript('
		betterUser.blocks = '.Better_Functions::toJsArray($this->view->myblocking).';'
		);
		
   		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/pois.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
   		$this->view->needCheckinJs = true;
	}

	public function indexAction()
	{
		//	我去过的和我想去的
		if ($this->uid) {
			$rows = $this->user->checkin()->fuckingCheckedPois(1, 30);
			$this->view->iCheckined = $rows['rows'];
			$todoPois = Better_DAO_Blog::getUserTodoPois($this->user->uid,0,15);
			if(is_array($todoPois) && count($todoPois)>0){
				foreach($todoPois as $key=>$todo){
					$_poiId = $todo['poi_id'];
					$poi = Better_Poi_Info::getInstance($_poiId)->getBasic();
					$poi['todo_time'] = $todo['dateline'];
					$todoPois[$key] = $poi;
				}
			}
			$this->view->toDoPois = $todoPois;			
			$poi_categories=Better_Poi_Category::getAvailableCategories();
			$this->view->categories = $poi_categories;
		} else {
			$this->view->iCheckined = array();
			$this->view->toDoPois = array();
		}
	}
}