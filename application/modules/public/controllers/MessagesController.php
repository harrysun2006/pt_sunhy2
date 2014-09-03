<?php

/**
 *  站内私信控制器
 *
 * @package 
 * @author 
 */

class Public_MessagesController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();

		
		$this->xmlRoot = 'messages';
		$this->auth();
	}
	
	public function receivedAction()
	{
		$msg = $this->user->notification()->directMessage();
		$results = $msg->getReceiveds(array(
			'page' => $this->page,
			'count' => $this->count
			));

		foreach ($results['rows'] as $k=>$v) {
			$data['msg_id'] = $v['msg_id'];
			$data['from_uid'] = $v['from_uid'];
			$data['content'] = $v['content'];
			$data['dateline'] = $v['dateline'];
			
			$this->data[$this->xmlRoot][$k]['msg'] = $data;
		}


		$this->output();
	}

	public function sentAction()
	{
		$msg = $this->user->notification()->directMessage();
		$results = $msg->getSents($this->page, $this->count);
	
		foreach ($results['msgs'] as $k=>$v) {
			$data['msg_id'] = $v['msg_id'];
			$data['to_uid'] = $v['to_uid'];
			$data['content'] = $v['content'];
			$data['dateline'] = $v['dateline'];
			
			$this->data[$this->xmlRoot][$k]['msg'] = $data;
		}
		
		$this->output();
	}	
	
}
