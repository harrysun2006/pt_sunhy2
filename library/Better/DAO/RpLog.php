<?php

/**
 * 用户Rp值变化相关数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_RpLog extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = null)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_rp_log';
    	$this->priKey = 'uid';
    	$this->orderKey = 'dateline';
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	/**
	 * 获取某个用户一天内被拒绝的次数
	 * 
	 * @param $uid
	 * @return integer
	 */
	public function getOnedayFriendRequestRefuseCount()
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $this->identifier);
		$select->where('dateline<=?', time());
		$select->where('dateline>?', time()-3600*24);
		$select->where('category=?', 'friend_request_refused');
		
		$rs = self::squery($select, $this->rdb);
		$r = $rs->fetch();
		
		return (int)$r['total'];
	}	

	/**
	 * 获取某个用户24小时内某种理由的karma变化次数
	 * 
	 * @param $category
	 * @return integer
	 */
	public function getOnedayCount($category='')
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $this->identifier);
		$select->where('dateline<=?', time());
		$select->where('dateline>?', time()-3600*24);
		
		if (is_array($category) && count($category)>0) {
			$select->where('category IN (?)', $category);
		} else if (!is_array($category) && $category!='') {
			$select->where('category=?', $category);
		}
		
		$rs = self::squery($select, $this->rdb);
		$r = $rs->fetch();
		
		return (int)$r['total'];
	}		
	
	/**
	 * 获取某个用户一个自然天内某种理由的karma变化次数
	 * 
	 * @param $category
	 * @return integer
	 */
	public function getNativedayCount($category='')
	{
		$offset = (defined('BETTER_USER_TIMEZONE') ? BETTER_USER_TIMEZONE : 8)*3600;
		
		$now = time();
		$dayStart = $now - date('H', $now+$offset)*3600 - date('i', $now+$offset)*60 - date('s', $now+$offset);
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $this->identifier);
		$select->where('dateline<=?', $now);
		$select->where('dateline>?', $dayStart);
		
		if (is_array($category) && count($category)>0) {
			$select->where('category IN (?)', $category);
		} else if (!is_array($category) && $category!='') {
			$select->where('category=?', $category);
		}
	
		$rs = self::squery($select, $this->rdb);
		$r = $rs->fetch();
		
		return (int)$r['total'];		
	}
	
	/**
	 * 	获取某个用户最后登录获得karma的时间
	 * 
	 * @return integer
	 */
	public function getLastCheckin()
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'dateline',
			));
		$category = array('login','login_byapi','login_bycell','login_bycell_byapi');
		$select->where('uid=?', $this->identifier);
		$select->where('category in (?)',$category);
		$select->order('dateline DESC');
		$select->limit(1);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return isset($row['dateline']) ? $row['dateline'] : 0;
		
	}
	
	public function cangetNativedayTotalrp($category='')
	{
		$offset = (defined('BETTER_USER_TIMEZONE') ? BETTER_USER_TIMEZONE : 8)*3600;
		
		$now = time();
		$dayStart = $now - date('H', $now+$offset)*3600 - date('i', $now+$offset)*60 - date('s', $now+$offset);
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('sum(rp) AS total')
			));
		$select->where('uid=?', $this->identifier);
		$select->where('dateline<=?', $now);
		$select->where('dateline>?', $dayStart);
		$select->where('active=?',0);
		$select->where('rp>?',0);
		$select->where('category!=?', 'Admin');
		if (is_array($category) && count($category)>0) {
			$select->where('category IN (?)', $category);
		} else if (!is_array($category) && $category!='') {
			$select->where('category=?', $category);
		}
	
		$rs = self::squery($select, $this->rdb);
		$r = $rs->fetch();
		$tmptotal = (int)$r['total'];
		$end = Better_Config::getAppConfig()->rp->everydaymax - $tmptotal;
		$end = ($end>0) ? $end:0;
		Better_Log::getInstance()->logInfo("TMP TOTAL".$tmptotal."The End is".$end."SQL:".$select,'xxxsql');
		return (int)$end;		
	}
}


?>