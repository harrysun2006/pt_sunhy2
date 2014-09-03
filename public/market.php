<?php
/**
 * 
 * 市场部活动配置
 * @var unknown_type
 */

	$now = time();	
	
	$market = array();
	
	$diebatm = gmmktime(6, 0, 0, 4, 23, 2011);

	$whgl_begtm = gmmktime(2, 0, 0, 5, 16, 2011);
	$whgl_endtm = gmmktime(16, 30, 0, 5, 21, 2011);
	
	
	
	$afu_begtm = gmmktime(2, 0, 0, 8, 10, 2011);
	$afu_endtm = gmmktime(16, 0, 0, 7, 10, 2011);
	
	$tde_begtm = gmmktime(2, 0, 0, 6, 24, 2011);
	$tde_endtm = gmmktime(16, 0, 0, 6, 26, 2011);
	
	
	$moto_begtm = gmmktime(4, 0, 0, 7, 15, 2011);
	$moto_endtm = gmmktime(16, 0, 0, 8, 15, 2011);

 
	if($now>=$moto_begtm && $now<=$moto_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1871', 'img'=>'w_moto.png');
	}
	
	
	
	$gangdeqin_begtm = gmmktime(1, 0, 0, 7, 15, 2011);
	$gangdeqin_endtm = gmmktime(16, 0, 0, 7, 25, 2011);
	
	if($now>=$gangdeqin_begtm && $now<=$gangdeqin_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1901', 'img'=>'w_gangdeqin.png');
	}
	
	
	$chengdu_begtm = gmmktime(1, 0, 0, 7, 14, 2011);
	$chengdu_endtm = gmmktime(16, 0, 0, 8, 7, 2011);
	if($now>=$chengdu_begtm && $now<=$chengdu_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1848', 'img'=>'w_chengdu.png');
	}
	
	
	$gshock_begtm = gmmktime(2, 0, 0, 7, 9, 2011);
	$gshock_endtm = gmmktime(16, 0, 0, 7, 19, 2011);
	if($now>=$gshock_begtm && $now<=$gshock_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1795', 'img'=>'w_gshockzhzy.png');
	}
	
	
	$thsc_begtm = gmmktime(1, 0, 0, 7, 7, 2011);
	$thsc_endtm = gmmktime(16, 0, 0, 7, 17, 2011);	
	if($now>=$thsc_begtm && $now<=$thsc_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1776', 'img'=>'w_tonghuasecai.png');
	}
	
	$tuniu_begtm = gmmktime(2, 0, 0, 7, 6, 2011);
	$tuniu_endtm = gmmktime(16, 0, 0, 7, 31, 2011);	
	
	if($now>=$tuniu_begtm && $now<=$tuniu_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1721', 'img'=>'w_tuniucom.png');
	}
	$qisui_begtm = gmmktime(2, 0, 0, 7, 11, 2011);
	$qisui_endtm = gmmktime(17, 0, 0, 7, 23, 2011);	
	if($now>=$qisui_begtm && $now<=$qisui_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1832', 'img'=>'attach/480/31.png');
	}
	
	$market[]= array('href'=>'http://blog.k.ai/?p=1813', 'img'=>'w_bj38.jpg');
	$market[]= array('href'=>'http://blog.k.ai/?p=1723', 'img'=>'w_yaolaixintiandi.jpg');

	
	
	
	if($now>=$afu_begtm && $now<=$afu_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1526', 'img'=>'w_afu.jpg');
	}
	
	$market[]= array('href'=>'http://blog.k.ai/?p=1647', 'img'=>'w_stq.png');
	
	$pxds_endtm = gmmktime(9, 30, 0, 6, 15, 2011);
	if($now<=$pxds_endtm){
		$market[]= array('href'=>'http://k.ai/poi/865038', 'img'=>'pxds_webbanner.png');
	}
	
	$market[]= array('href'=>'http://blog.k.ai/?p=1546', 'img'=>'w_muqingshijue.png');
	$market[]= array('href'=>'http://blog.k.ai/?p=1574', 'img'=>'w_floso.jpg');
	
	$kuxiayizu_begtm = gmmktime(1, 0, 0, 6, 3, 2011);
	$kuxiayizu_endtm = gmmktime(4, 0, 0, 8, 30, 2011);
	if($now>=$kuxiayizu_begtm && $now<=$kuxiayizu_endtm){
		$market[]= array('href'=>'http://blog.k.ai/?p=1501', 'img'=>'w_kuxiayizu.png');
	}
	
	
	


	
	if($now>=$diebatm){
		$market[]= array('href'=>'http://k.ai/poi/19067627', 'img'=>'diebar_webbanner.png');
	}	
	
	$market[]= array('href'=>'http://k.ai/poi/3408', 'img'=>'webbanner-zynw.png');
	
	return $market;
?>