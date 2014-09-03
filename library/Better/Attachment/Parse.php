<?php

/**
 * 附件分析操作
 * 
 * @package Better.Attachment
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Attachment_Parse extends Better_Attachment_Base
{
	protected $fid = '';
	protected static $instance = array();
	protected static $parsed = array();
	protected $info = array();
	protected static $dbInfo = array();
	
	protected function __construct($fid, $dbInfo=array())
	{
		$this->fid = $fid;
		self::$dbInfo = &$dbInfo;
		parent::__construct();
		
		if ($this->fid) {
			$this->info = self::parse($this->fid);
		}
	}
	
	public static function getInstance($fid)
	{
		if (is_array($fid)) {
			self::$dbInfo = $fid;
			$fid = self::$dbInfo['file_id'];
		}
		
		if (self::$instance[$fid]==null) {
			self::$instance[$fid] = new self($fid);
		}
		
		return self::$instance[$fid];
	}
	
	public function __get($key)
	{
		return isset($this->info[$key]) ? $this->info[$key] : '';
	}
	
	/**
	 * 获取当前实例分析结果
	 * 
	 * @return array
	 */
	public function result()
	{
		return $this->info;
	}
	
	/**
	 * 分析附件信息
	 * 
	 * @param $fid 附件id
	 * @return misc
	 */
	public static function parse($fid)
	{
		if (isset(self::$parsed[$fid])) {
			return self::$parsed[$fid];
		}
		
		$cacher = Better_Cache::remote();		
		list($uid, $seq) = explode('.', $fid);
		$cacheKey = 'kai_attach_'.$uid.'_'.$seq;
		self::$parsed[$fid] = $cacher->get($cacheKey);
		
		if (!self::$parsed[$fid]) {
			self::$parsed[$fid] = isset(self::$dbInfo['file_id']) ? self::$dbInfo : Better_DAO_Attachment::getInstance($uid)->findFile_id($fid);
	
			if (isset(self::$parsed[$fid]['file_id'])) {
				self::$parsed[$fid]['server_id'] = $serverId = Better_DAO_AttachAssign::getInstance()->getSid(self::$parsed[$fid]['file_id']);
				list($uid, $seq) = explode('.', self::$parsed[$fid]['file_id']);
				$ext = self::$parsed[$fid]['ext'];
				$serverUrl = self::$config->{'attach_server_'.$serverId}->url;
				$hash = self::hashDir($seq);
				$basePath = self::$config->{'attach_server_'.$serverId}->save_path;
				$name = $fid.'.'.$ext;
	
				if (self::$config->global->use_rewirte) {
					self::$parsed[$fid]['url'] = $serverUrl.'/normal/'.$uid.'/'.$seq.'.'.$ext;
					self::$parsed[$fid]['thumb'] = $serverUrl.'/thumb/'.$uid.'/'.$seq.'.'.$ext;
					self::$parsed[$fid]['tiny'] = $serverUrl.'/tiny/'.$uid.'/'.$seq.'.'.$ext;
				} else {
					self::$parsed[$fid]['url'] = $serverUrl.'/files'.$hash.'/'.$name;
					self::$parsed[$fid]['thumb'] = $serverUrl.'/files'.$hash.'/'.self::$config->global->image->thumb_prefix.$name;
					self::$parsed[$fid]['tiny'] = $serverUrl.'/files'.$hash.'/'.self::$config->global->image->tiny_prefix.$name;
				}
	
				self::$parsed[$fid]['save_path'] = $basePath.$hash.'/'.$name;
				self::$parsed[$fid]['thumb_save_path'] = $basePath.$hash.'/'.self::$config->global->image->thumb_prefix.$name;
				self::$parsed[$fid]['tiny_save_path'] = $basePath.$hash.'/'.self::$config->global->image->tiny_prefix.$name;
			}
			
			self::$dbInfo = array();
			$cacher->set($cacheKey, self::$parsed[$fid]);
		}
		
		return self::$parsed[$fid];
	}
	
}