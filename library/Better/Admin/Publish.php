<?php

class Better_Admin_Publish
{
	
	public static function getProducts(array $params)
	{
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;

		$rows = Better_DAO_Admin_Product::getInstance()->getAll(array(
			'page' => $page,
			'page_size' => $pageSize
			));

		return $rows;			
	}
	
	public static function delProducts($pids){
	
		if($pids && is_array($pids)){
			foreach ($pids as $pid){
				Better_DAO_Admin_Product::getInstance()->delete($pid);
			}
			return true;
		}else{
			return false;
		}
	}
	
	
	public static function getPhones(array $params)
	{
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$name_keyword = $params['name_keyword']? $params['name_keyword']: '';
		$brand = $params['brand']? $params['brand']: 0;
		$os = $params['os']? $params['os']: 0;

		$rows = Better_DAO_Admin_Phone::getInstance()->getAll(array(
			'page' => $page,
			'page_size' => $pageSize,
			'name_keyword'=> $name_keyword,
			'brand'=> $brand,
			'os'=> $os
			));

		return $rows;			
	}
	
	
	public static function delPhones($pids){
	
		if($pids && is_array($pids)){
			foreach ($pids as $pid){
				Better_DAO_Admin_Phone::getInstance()->delete($pid);
			}
			return true;
		}else{
			return false;
		}
	}
}