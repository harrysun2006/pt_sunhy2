<?php

class Better_User_RssImport extends Better_User_Base
{
	protected static $instance = array();
	protected $rssSetting = array(
		'url' => '',
		);
	
	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	public function get()
	{
		$setting = Better_DAO_RssImport::getInstance($this->uid)->getArray(array(
			'uid' => $this->uid,
			));
		if (isset($setting['uid'])) {
			$this->rssSetting = $setting;
		}
		
		return $this->rssSetting;
	}
	
	public function delete()
	{
		return Better_DAO_RssImport::getInstance($this->uid)->delete($this->uid);
	}
	
	public function modify($url)
	{
		return Better_DAO_RssImport::getInstance($this->uid)->update(array(
			'url' => $url,
			), $this->uid);
	}
}