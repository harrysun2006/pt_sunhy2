<?php

class Better_Service_WindowsLive_Contacts extends Better_Service_WindowsLive
{
	protected $liveId = '';
	
	
	public function __constuct($liveId)
	{
		$this->liveId = $liveId;	
		$this->serviceUrl = 'livecontacts.services.live.com';
		
		parent::__construct();
	}
}