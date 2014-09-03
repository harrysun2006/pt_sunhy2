<?php

/**
 * 用户收藏消息
 *
 * @package Better.User
 * @author  leip <leip@peptalk.cn>
 *
 */
class Better_User_Favorites extends Better_User_Base
{

	private static $instance = array();
	private $dao = null;
	
	private $favorites = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
			self::$instance[$uid]->dao = Better_DAO_Favorites::getInstance($uid);
		}
		
		return self::$instance[$uid];
	}	
    
    /**
     * 根据消息id查找哪些人收藏了这条消息
     *
     * @param string $bid
     * @return array
     */
    public static function callbackInBlogDelete($bid)
    {
		Better_DAO_Favorites::getInstance()->decreaseUsersFavorites($bid);
    }

    /**
     * 增加一个收藏
     *
     * @param string $bid
     * @param integer $fuid
     * @return misc
     */
	public function add($bid, $fuid, $type='normal')
	{
		$flag = false;

		$row = $this->dao->get(array(
						'bid' => $bid,
						'fuid' => $fuid,
						'uid' => $this->uid,
						));
		if (!isset($row['bid'])) {
			$flag = $this->dao->insert(array(
							'bid' => $bid,
							'uid' => $this->uid,
							'fuid' => $fuid,
							'dateline' => time(),
							'type' => $type,
							));

			if ($flag) {
				Better_Hook::factory(array(
					'User', 'Blog', 'Karma'
				))->invoke('AddedFavorite', array(
					'uid' => $this->uid,
					'bid' => $bid
				));
				$flag = true;
			}
		} 
						
		return $flag;
	}
	
	/**
	 * 删除一个收藏
	 *
	 * @param string $bid
	 * @return misc
	 */
	public function delete($bid)
	{
		$flag = $this->dao->delete($this->uid, $bid);

		if ($flag) {
			
			Better_Hook::factory(array(
				))->invoke('DeleteFavorite', array(
					'uid' => $this->uid,
					'bid' => $bid
				));
			
			$userInfo = Better_User::getInstance($this->uid)->getUser();
			
			Better_User::getInstance($this->uid)->updateUser(array(
							'favorites' => $userInfo['favorites']-1,
							));
			Better_Registry::get('sess')->set('userChanged', time());
			
			Better_Blog::addFavorited($this->uid, $bid,-1);
		}
		
		return $flag;
	}
	
	/**
	 * 获取某人的所有收藏（返回消息id数组）
	 *
	 * @return array
	 */
	public function getAllBids()
	{
		if (count($this->favorites)==0) {
			$rows = $this->dao->getAll(array(
							'uid' => $this->uid,
							));
			foreach ($rows as $row) {
				$this->favorites[] = $row['bid'];
			}
		}
		
		return $this->favorites;
	}
	
	/**
	 * 获取某人的所有收藏（返回完整数据）
	 *
	 * @param $page 页码
	 * @param $pageSize
	 * @return array
	 */
	public function all($page=1, $pageSize=BETTER_PAGE_SIZE, array $type=array('normal'))
	{
		$rows = $this->dao->getAll(array(
						'uid' => $this->uid,
						'type' => $type,
						));
		$bids = array();
		foreach ($rows as $v) {
			$bids[] = $v['bid'];
		}

		$rows = $this->dao->getFavorites($bids, $type);
		$return = array('count'=>0, 'rows'=>array(), 'rts' => array());
		
		if (count($rows)>0) {
			$data = array_chunk($rows, $pageSize);
			$return['count'] = count($rows);
			$return['rows'] = isset($data[$page-1]) ? $data[$page-1] : array();
			unset($data);
			
			foreach ($return['rows'] as $k=>$row) {
				$return['rows'][$k] = Better_Blog::parseBlogRow($row);
			}
		}
				
		return $return;
	}
	
	/**
	 * 获取某人的所有贴士收藏（返回完整数据）
	 *
	 * @param $page 页码
	 * @param $pageSize
	 * @return array
	 */
	public function allTips($page=1, $pageSize=BETTER_PAGE_SIZE)
	{
		$rows = $this->dao->getAll(array(
						'uid' => $this->uid,
						'type' => 'tips',
						));
		$bids = array();
		foreach ($rows as $v) {
			$bids[$v['dateline'].'.'.$v['bid']] = $v['bid'];
		}

		$rows = $this->dao->getTipsFavorites($bids);
		$return = array('count'=>0, 'rows'=>array(), 'rts'=>array());
		
		if (count($rows)>0) {
			$data = array_chunk($rows, $pageSize);
			$return['count'] = count($rows);
			$return['rows'] = isset($data[$page-1]) ? $data[$page-1] : array();
			unset($data);
			
			foreach ($return['rows'] as $k=>$row) {
				$return['rows'][$k] = Better_Blog::parseBlogRow($row);
			}
		}
				
		return $return;
	}	

}