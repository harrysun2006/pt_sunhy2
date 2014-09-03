<?php

/**
 * 
 * 所有最新/随便看看单独处理
 * 
 * @package Better.DAO.Blog
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Blog_Public extends Better_DAO_Base
{
	
	protected static $instance = array();
	
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'blog_listall';
		
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
	
	public function save($uid, $bid)
	{
		return $this->replace(array(
			'uid' => $uid,
			'bid' => $bid
			), $this->tbl);
	}
}