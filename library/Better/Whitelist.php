<?php

/**
 * 取出白名单
 * 
 * @package Better
 * @author yangl
 *
 */

class Better_Whitelist
{
	
	protected $file_path;
	protected static $instance = null;
	
	public function __construct($file_path)
	{
		$this->file_path = $file_path;
	}
	
	public static function getInstance($file_path){
		if(!self::$instance){
			self::$instance = new Better_Whitelist($file_path);
		}
		
		return self::$instance;
	}
	
	public function getFile()
	{   
         try {
         	$file = file_get_contents($this->file_path);
         }catch (Exception $e){
         	die($e);
         }
         
         return $file;
    }
    
    public function getEmails()
    {
    	$emails = array();
    	$file = $this->getFile();
    	
    	$tmp = split('\\[message\\]', $file);
    	$emails_str = str_replace('[email]', '', $tmp[0]);
    	
    	$emails = explode(';', $emails_str);
    	
    	return $emails;
    }
    
	public function getMsgs()
	{
    	$msgs = array();
    	$file = $this->getFile();
    	
    	$tmp = split('\\[message\\]', $file);
    	$mess_str = $tmp[1];
    	
    	$msgs = explode(';', $mess_str);
    	
    	return $msgs;
    }
    
}