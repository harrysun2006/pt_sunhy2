<?php

/**
 * Blog缓存
 * 
 * @package Better.Cache.Module
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Cache_Module_Blog
{
	
	public static function load($bid)
	{
		$data = array();
		
		if ($bid) {
			$key = md5('blog_bid_'.$bid);
			$cacher = Better_Cache::remote();
			
			$data = $cacher->get($key);
			
			if (!$data) {
				$data = self::build($bid);
			}
		}
		
		return $data;
	}
	
	public static function delete($bid)
	{
		if ($bid) {
			$key = md5('blog_bid_'.$bid);
			Better_Cache::remote()->set($key);
		}
	}
	
	public static function build($bid)
	{
		$data = array();
		if ($bid) {
			$key = md5('blog_bid_'.$bid);
			list($uid, $cnt) = explode('.', $bid);
			$data = Better_DAO_Blog::getInstance($uid)->preCacheFetch($bid);		
			Better_Cache::remote()->set($key, $data);
		}
		
		return $data;
	}
}