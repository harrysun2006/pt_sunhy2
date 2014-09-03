<?php

/**
 * feedback 处理类
 * @author yanglei
 *
 */
class Better_DAO_Admin_Feedback extends Better_DAO_Admin_Base{
	
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'feedback';
			$this->priKey = 'id';
			$this->orderKey = &$this->priKey;
		}
	
	
		
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function getAllFeedbacks($params){
		
		$results = $data = array();
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword']? trim($params['keyword']): '';
		$typekeyword = $params['type_keyword'] ? trim($params['type_keyword']) : '';
		$reload = $params['reload'] ? intval($params['reload']) : 0;
		$cacheKey = $params['cachekey'] ? trim($params['cacheKey']) : '';
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		$status = isset($params['status'])? $params['status']: '';
		
		Better_Cache_Lock::getInstance()->wait($cacheKey);
		if(!parent::getDbCacher()->test($cacheKey) || $reload==1){
				Better_Cache_Lock::getInstance()->lock($cacheKey);
				
				$rdb = $this->rdb;
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'feedback AS f', '*');
				
				if ($from>0) {
					$select->where('f.dateline>=?', $from);
				}
				
				if ($to>0) {
					$select->where('f.dateline<=?', $to);
				}
				
				if ($keyword!='') {
					$select->where($rdb->quoteInto('f.content LIKE ?', '%'.$keyword.'%'));
				}
				
				if ($typekeyword!='') {
					$select->where($rdb->quoteInto('f.type LIKE ?', '%'.$typekeyword.'%'));
				}
				
				if($status!=''){
					if($status=='1'){
						$select->where($rdb->quoteInto('f.status=?', '1'));
					}else{
						$select->where($rdb->quoteInto('f.status!=?', '1'));
					}
					
				}
				
				$select->order('f.dateline DESC');
				$sql=$select->__toString();
					
				$results = $rdb->fetchAll($sql);
				
				parent::getDbCacher()->set($cacheKey, $results, 300);
			
				Better_Cache_Lock::getInstance()->release($cacheKey);
			}else{
				$results = parent::getDbCacher()->get($cacheKey);
			}
				
			return $results;
		
	}
	
	
	public function replyFeedback($params){
		$result = 0;
		$receiver = $params['receiver'] ? $params['receiver'] : '';
		$content = $params['content'] ? $params['content'] : '';
		$admin_uid = Better_Registry::get('sess')->admin_uid;
		$id = $params['id'] ? $params['id']: '';
		$lang = $params['lang']? $params['lang']: 'zh-cn';
		
		if($id){
			$feedback = $this->get($id);
		}
		
		if($receiver && $content && $admin_uid && $feedback['id']){
			
			$reply = $feedback['reply'].'<br>'.$admin_uid.' 的回复：<br>'.$content;
			$this->update(array('reply'=>$reply, 'status'=>'1'), $id);
			
			$content = str_replace('[question]', '"'.$feedback['content'].'"', $content);
			$content = nl2br($content);
			
			$data = array(
				'receiver'=>$receiver,
				'CONTENT'=> $content
			);

			Better_Email_Feedback::send($data, $lang) && $result = 1;
			
			if($result){
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('回复反馈：<br>'.$content, 'reply_feedback');
			}
		}
		
		return $result;
	}
}

?>