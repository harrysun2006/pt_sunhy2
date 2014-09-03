<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_FilterController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/filter.js?ver='.BETTER_VER_CODE);
		$this->view->title="可疑文章管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		
		if($params['act_type']){
			$this->view->headScript()->appendScript("
    		var act_type= '{$params['act_type']}';
    		var need_check= '{$params['need_check']}';
    		var menu_show = '{$params['menu_show']}';
    	");
		}
		/**
		 * 是否是通过bedo号精确查找
		 */
		if(isset($params['bedo_no']) && $params['bedo_no']!=""){
			//精确查找
			$uid = Better_DAO_Bedo::getInstance()->getUidByJid($params['bedo_no']);
			$params['uid']=$uid;
		}
		if($params['uid'] || !(isset($params['bedo_no'])&& $params['bedo_no']!="")){
			$result = Better_Admin_Filter::getAll($params);	
			$this->view->params = $params;
			$this->view->rows = $result['rows'];
			$this->view->count = $result['count'];
		}else{
			$this->view->params = $params;
			$this->view->rows = array();
			$this->view->count = 0;
		}		
		
	}
	
	
	public function passAction(){
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		$fids = &$post['fids'];
		
		if (is_array($bids) && count($bids)>0) {
			foreach($bids as $bid){
				$blog = Better_Blog::getBlog($bid);	
				if($blog['blog']['checked']=='0'){
					Better_Blog::setNeedCheck($bid, false);
					Better_Hook::$hookResults['BlogPosted'] = 0;
					$blog['blog']['checked'] = 1;
										
					if ($bid) {
						Better_Hook::factory(
							array(
								'Syncsites', 'Queue', 'Mentionme'
							)
						)->invoke('BlogPosted', array(
							'blog' => $blog['blog'],
							'data' => $blog['blog'],
							'bid' => $bid,				
							'uid' => $blog['blog']['uid'],
							'need_sync' => $blog['blog']['need_sync']
							));
					}
				}
			
				$result = 1;
				
				if($result){
					$content = '通过审核：<br>'.$blog['blog']['message'];
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'pass_blog');
				}
			}
		}
		
		if (is_array($fids) && count($fids)>0) {
			foreach($fids as $fid){
				Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0, 'need_check'=>0), $fid);
			}
		}
		
		$this->sendAjaxResult($result);
		
	}
	
	
	public function delAction(){
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		$fids = &$post['fids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::delBlogs($bids) && $result = 1;
		}
		
		if (is_array($fids) && count($fids)>0) {
			foreach($fids as $fid){
				Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), $fid);
			}
		}
		
		$this->sendAjaxResult($result);
	
	}
	
	/**
	 * 删除可疑评论
	 */
	public function redelAction(){
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			foreach($bids as $nbid){
				list($bid, $comment_id) = explode('-', $nbid);
				Better_Blog::deleteReply($comment_id, $bid);
			}
			$result = 1;
		}
		
		$this->sendAjaxResult($result);
	
	}
	
	public function delsecretmsgAction()
	{
		$result = 0;
		$params = $this->getRequest()->getParams();
		$ids = $params['ids'];
		$fids = $params['fids'];
		
		if (is_array($ids) && count($ids)>0) {
			Better_Admin_Dmessage::delReceived($ids) && $result = 1;
		}
		if(is_array($fids) && count($fids)>0){
			//Better_Admin_Dmessage::delSended($fids) && $result = 1;
		}
		if($result==1){
			foreach($ids as $bid){
				Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), array('bid'=>$bid));
			}
		}
		
		$this->sendAjaxResult($result);
	}
	
	public function passmsgAction()
	{
		$params = $this->getRequest()->getParams();
		$ids = $params['ids'];
		$fids = $params['fids'];
		foreach($ids as $bid){
				Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), array('bid'=>$bid));
		}
		$this->sendAjaxResult(1);
	}
	
	
	public function delmsgAction(){
		$result = 0;
		$post = $this->getRequest()->getPost();
		$mids = &$post['mids'];
		$fids = &$post['fids'];
		
		if (is_array($mids) && count($mids)>0) {
			Better_Admin_Blog::delBlogs($mids) && $result = 1;
		}
		
		if (is_array($fids) && count($fids)>0) {
			foreach($fids as $fid){
				Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), $fid);
			}
		}
		
		$this->sendAjaxResult($result);
	
	}
	
	/**
	 * 通过评论审核
	 */
	public function repassAction(){
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		$fids = &$post['fids'];		
		if (is_array($fids) && count($fids)>0) {
			foreach($fids as $fid){
				$row = Better_DAO_Admin_Filter::getInstance()->get($fid);
				list($bid, $comment_id) = explode('-', $row['bid']);
				list($blog_uid, $i) = explode('.', $bid);	
				if(strpos($row['word_type'], '3')!== false)	{					
					Better_DAO_Blogreply::setNeedCheck($comment_id, $blog_uid, false);

					$cacher = Better_Cache::remote();
					$cacheKey = md5('kai_blog_bid_'.$bid);	
					$cacher->set($cacheKey, null);
					
					//提到我的hook
					Better_Hook::$hookResults['ReplyPosted'] = 0;
					$comment_data = Better_DAO_Blogreply::getInstance($blog_uid)->get($comment_id);
					Better_Hook::factory('Mentionme')->invoke('ReplyPosted', array(
							'blog_uid'=> $blog_uid,
							'data'=> array('bid'=>$bid),
							'id'=> $comment_id,
							'uid'=> $row['uid'],
							'comment_data'=> $comment_data
					));
				}			
				$bf = Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0, 'need_check'=>0), $row['id']);				
				$result = 1;
				if($result){
					$content = '评论通过审核：<br>'.$row['reftext'];
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'pass_reply');
				}
				
			}
		}
		
		$this->sendAjaxResult($result);
		
	}
}