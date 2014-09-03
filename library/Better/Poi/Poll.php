<?php

/**
 * POI内投票
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Poll extends Better_Poi_Base
{
	protected static $instance = array();
	protected $statusId = 0;
	protected $data = array();
	
	protected function __construct($statusId)
	{
		$this->statusId = $statusId;
		$this->data = Better_Blog::getBlog($statusId);
	}
	
	public static function getInstance($statusId)
	{
		if (!isset(self::$instance[$statusId])) {
			self::$instance[$statusId] = new self($statusId);
		}
		
		return self::$instance[$statusId];
	}		
	
	/**
	 * 某用户是否对本贴士投票过了
	 * 
	 * @return bool
	 */
	public function isPolled($uid)
	{
		$row = Better_DAO_Poi_Poll::getInstance($uid)->get(array(
			'blog_id' => $this->statusId,
			'uid' => $uid,
			));
		
		return isset($row['uid']) ? true : false;
	}
	
	/**
	 * 对本POI进行投票
	 * 
	 * @param $action
	 * @return boll
	 */
	public function poll(array $params)
	{
		$result = array(
			'codes' => array(
				'SUCCESS' => 1,
				'FAILED' => 0,
				'DUPLICATED' => -1,
				),
			);
		$result['code'] = $result['codes']['FAILED'];
		
		$uid = (int)$params['uid'];
		$row = Better_DAO_Poi_Poll::getInstance($uid)->get(array(
			'blog_id' => $this->statusId,
			'uid' => $uid,
			));

		if (isset($row['uid']) && $row['uid']==$uid) {
			$result['code'] = $result['codes']['DUPLICATED'];
		} else {
			$flag = Better_DAO_Poi_Poll::getInstance($uid)->insert(array(
				'blog_id' => $this->statusId,
				'uid' => $uid,
				'poi_id' => $this->data['blog']['poi_id'],
				'poll_time' => time(),
				'poll_type' => $params['poll_type'],
				));
				
			if ($flag) {
				Better_Hook::factory(array(
					'Blog', 'Poi', 'Karma', 'Badge',
				))->invoke('PoiPollSubmitted', array(
					'bid' => $this->statusId,
					'uid' => $uid,
					'option' => $params['poll_type'],
				));
				
				$result['code'] = $result['codes']['SUCCESS'];
			}
		}
			
		return $result;
	}
	
	/**
	 * 浏览该POI的所有投票
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function all($page=1, $count=20)
	{
		
	}
}