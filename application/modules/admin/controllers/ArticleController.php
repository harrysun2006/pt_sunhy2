<?php

/**
 * 后台围脖管理
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_ArticleController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/article.js?ver='.BETTER_VER_CODE);
		$this->view->title="微博管理";		

		$todayFrom = Better_Functions::date('Y-m-d', BETTER_NOW);
		$todayTo = Better_Functions::date('Y-m-d', BETTER_NOW+3600*24);
		
		$this->view->headScript()->appendScript("
    	var Better_Today_From = '{$todayFrom}';
    	var Better_Today_To = '{$todayTo}';
    	");
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$params['checked'] = '';
		
		if($params['type']){
			$this->view->headScript()->appendScript("
    		var blog_type= '{$params['type']}';
    	");
		}
		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		$params['filteruser']= $params['filteruser']? $params['filteruser']: 0;
		if($params['filteruser']){
			$params['filter_uids']=array('168671');
		}
		$params['page_size'] = $params['page_size'] ?$params['page_size'] :100;
		/**
		 * 是否是通过bedo号精确查找
		 */
		if(isset($params['bedo_no']) && $params['bedo_no']!=""){
			//精确查找
			$uid = Better_DAO_Bedo::getInstance()->getUidByJid($params['bedo_no']);
			$params['uid']=$uid;
		}
		if($params['uid'] || !(isset($params['bedo_no'])&& $params['bedo_no']!="")){
			$result = Better_Admin_Blog::getBlogs($params);
			$this->view->params = $params;
			$this->view->rows = $result['rows'];
			$this->view->count = $result['count'];
		}else{
			$this->view->params = $params;
			$this->view->rows = array();
			$this->view->count = 0;
		}	
	}
	
	public function delAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::delBlogs($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);
	}
	
	public function resetplaceAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::resetPlace($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);		
	}
	
	public function resetplace2Action()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::resetPlace2($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);		
	}
	
	public function delattachAction()
	{
		$result = 0;	
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::delAttach($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);				
	}
	
	
	public function changetypeAction(){
		$result = 0;	
		$post = $this->getRequest()->getPost();
		$bid = $post['bid'];
		$uid = $post['uid'];
		$type = $post['type'];
		
		if ($bid && $uid && $type) {
			Better_Admin_Blog::changeBlogType($bid, $type, $uid) && $result = 1;
		}
		
		$this->sendAjaxResult($result);	
	}
	
	
	public function changetypesAction(){
		$result = 0;	
		$post = $this->getRequest()->getPost();
		$bids = $post['bids'];
		$type = $post['type'];
		
		if ($bids && $type) {
			Better_Admin_Blog::changeBlogType($bids, $type) && $result = 1;
		}
		
		$this->sendAjaxResult($result);	
	}
	
	public function settopAction()
	{
		$result = 0;	
		$post = $this->getRequest()->getPost();
		$bid = $post['bid'];
		$istop = $post['is_top'];//判断是置顶还是取消置顶
		$uid = $post['uid'];
		if ($bid) {
			Better_Admin_Blog::setTipTop($bid, $istop,$uid) && $result = 1;
		}
		$this->sendAjaxResult($result);	
	}

	/**
	 * 设置/取消优质贴士
	 * 
	 * 2011-07-26 后续需求:
	 * # 奖励
	 * 优质贴士的作者每个贴士加5个Karma，并且不受每日上限限制。
	 * 每天集中私信提醒一次，时间在下午3点，内容：
	 * “您获得了{5x?}Karma的奖励，因为你在{{POI_NAME}}等地点发表的贴士被评为优质贴士。”
	 * 1. 优质贴士的作者每个贴士加5个Karma，并且不受每日上限限制。
	 * 2. 取消优质不加Karma也不扣
	 * 3. 集中私信提醒使用脚本, 见scripts/notify_featured_tips.php
	 * 
	 * ## 勋章修改
	 * 贴士类勋章改为和优质贴士关联
	 * 存在一些逻辑上的矛盾:
	 * 1. 用户刚发的贴士都是普通贴士
	 * 2. 优质贴士必须由人工挑选, 在后台进行管理
	 * 3. 原先的贴士勋章都是前台发完贴士后如获得立即得到提示
	 * 经讨论后暂定:
	 * 1. 原先的贴士类勋章仍保持和普通贴士关联
	 * 2. 优质贴士勋章另外设定, 并在后台管理程序中调用Hook计算
	 * 3. 优质贴士勋章如获取通过通知方式告知用户
	 * 目前贴士类勋章的计算类有:
	 * Guanqianjie, Gudaorechang, Huozidian, Leifengjingshen, 
	 * Tianlaizhisheng, Xintiandi, Yuyinraoliang, Zhongguancun
	 * 以下代码暂时不用:
	 * $use_featured = Better_Config::getAppConfig()->badge->tips->use_featured;
	 * isset($use_featured) || $use_featured = 1;
	 * if ($use_featured == 1) $select->where('featured', 1);
	 * 
	 * ## 贴士排序
	 **/
	public function featureAction()
	{
		$result = 0;	
		$post = $this->getRequest()->getPost();
		$bid = $post['bid'];
		$uid = $post['uid'];
		$poi_id = $post['poi_id'];
		$featured = $post['featured'];//判断是优评还是取消优评
		$rp = Better_Config::getAppConfig()->tips->featured->gotkarma;
		isset($rp) || $rp = 5;
		if ($bid) {
			Better_Admin_Blog::setTipFeatured($bid, $featured, $uid) && $result = 1;
			if ($featured == 1) {
				Better_User_Rp::getInstance($uid)->update(array(
					'rp' => $rp,
					'category' => 'featuredtips',
					'poi_id' => $poi_id,
				));
			}
		}
		$this->sendAjaxResult($result);	
	}
}
