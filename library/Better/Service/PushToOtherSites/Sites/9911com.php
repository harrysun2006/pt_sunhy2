<?php 
//9911 不会返回id
class Better_Service_PushToOtherSites_Sites_9911com extends Better_Service_PushToOtherSites_Common
{

	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.9911.com/account/verify_credentials.xml';
		$this->_api_url = 'http://api.9911.com/statuses/update.json';
		$this->_login_find_key = 'name';
			
		$this->_protocol = '9911.com';
		}
		

	public function post($msg, $attach='')
	{
		$this->_request = array(
			'status' => $msg,
			'source' => 'Better',
			);
		return parent::post($msg, $attach);
	}
	
	/**
	 * 删除
	 * @param $id
	 * @return unknown_type
	 */
	public function delete($id)
	{
		
		//http://api.9911.com/statuses/destroy/3822734.xml
		$this->_api_url = "http://api.9911.com/statuses/destroy/$id.xml";
					
		return parent::delete($id);		
	}	
	
	public function checkPost($return)
	{
		$flag = false;
		$json = json_decode($return);
		
		if ($json->update) {
			$flag = true;
		}
		
		return $flag;
	}
	
	public function get3rdId()
	{
		return 0;
	}	
}