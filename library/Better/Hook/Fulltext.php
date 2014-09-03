<?php

/**
 * 全文搜索引擎
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Fulltext extends Better_Hook_Base
{
	protected static $updated = array();
	
	/**
	 * 用户帐号激活
	 * 
	 */
	public function onEmailBinded(array $params)
	{
		
	}

	/**
	 * Karma值变化时
	 * 
	 */
	public function onKarmaChange(array $params)
	{
	
	}

	/**
	 * 兑换宝物
	 * 
	 * @param array $params
	 */
	public function onExchangeTreasure(array $params)
	{
	
	}
	
	/**
	 * 捡起宝物
	 * 
	 * 
	 */
	public function onPickupTreasure(array $params)
	{
		
	}
	
	/**
	 * 新的同步站点
	 * 
	 * @param $params
	 */
	public function onNewSyncSites(array $params)
	{

	}
	
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		$poiId = (int)$params['poi_id'];
		Better_DAO_Poi_Fulltext::getInstance()->updateItem($poiId, 0);
	}
	
	public function onPoiUpdated(array $params)
	{
		$poiId = (int)$params['poi_id'];
		$upParams = (array)$params['poi_info'];
		
		if (!in_array($poiId, self::$updated)) {
			$needUpdate = false;
			foreach (Better_DAO_Poi_Fulltext::$fulltextKeys as $key) {
				if (isset($upParams[$key])) {
					$needUpdate = true;
					break;
				}
			}
			
			if ($needUpdate==true) {
				Better_DAO_Poi_Fulltext::getInstance()->updateItem($poiId, 1);
				self::$updated[] = $poiId;
			}
		}		
	}
	
	public function onPoiDeleted(array $params)
	{
		$poiId = (int)$params['poi_id'];
		Better_DAO_Poi_Fulltext::getInstance()->updateItem($poiId, 2);		
	}
	
	public function onBlogPosted(array $params)
	{
	}
	
	public function onBlogDeleted(array $params)
	{
	}
	
	public function onBeforeQbsQuery(array $params)
	{
	}
	
	public function onAfterQbsQuery(array $params)
	{
	}
	
	public function onFollowSomebody(array $params)
	{
	
	}
	
	public function onBlockedSomebody(array $params)
	{
	}
	
	public function onUserCreated(array $params)
	{

	}
	
	public function onUserChanged(array $params)
	{
	}
	
	public function onAttachmentUploaded(array $params)
	{
	}
	
	public function onFollowRequest(array $params)
	{
	}
	
	public function onDirectMessageSent(array $params)
	{
	}
	
	public function onBlogReplyPosted(array $params)
	{
	}
	
	public function onAddedFavorite(array $params)
	{

	}
	
	public function onUserDeleted(array $params)
	{
		
	}

	public function onUserLogin(array $params)
	{
	}
	
	public function onUserLogout(array $params)
	{
	}	
	
	public function onUnfollowSomebody(array $params)
	{
		
	}
	
	public function onUserCheckin(array $params)
	{
	
	}
	
	public function onFriendRequest(array $params)
	{
		
	}
	
	public function onFriendWithSomebody(array $params)
	{
	}
	
	public function onUnblockSomebody(array $params)
	{
		
	}
	
	public function onUnfriendWithSomebody(array $params)
	{		
	}
	
	public function onRejectFriendRequest(array $params)
	{
		
	}
}