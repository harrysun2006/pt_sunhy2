<?php

/**
 * 优惠
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_CouponController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'coupon';
		$this->auth();
	}	
	
	public function showAction()
	{
		$this->xmlRoot = 'coupon';
		
		$platform = '0';
		$_array_temp = $this->user->cache()->get('client');	
	   
		if ( in_array( $_array_temp['platform'], array(1, 'S60', 's60') ) ) {
			$platform = '1';
		}
		
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id) {
			if($id>100000){
				$id= $id-100000;
				$data = Better_Poi_Notification::getPoloCoupon($id);
				if ($platform == '1') {
					$data['image_url'] = '';	
				}
				
			} else {			
				$data = Better_Poi_Notification::getCoupon($id);
			}	
			
			if ($data['nid']) {
				$this->data[$this->xmlRoot] = $this->api->getTranslator('coupon')->translate(array(
					'data' => &$data
					));
			} else {
				$this->error('error.coupon.id_invalid');
			}
			
		} else {
			$this->error('error.coupon.id_invalid');
		}
		
		$this->output();
	}

}