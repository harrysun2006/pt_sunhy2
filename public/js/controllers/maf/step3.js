
/**
 * 获得卡片第三步
 * 
 * @param page
 * @param renew
 * @return
 */

$(function() {
	$('#receive_name').focus(function(){
		$('#receive_name_err').empty();		
	});
	$('#receive_address').focus(function(){
		$('#receive_address_err').empty();		
	});
	$('#receive_zipcode').focus(function(){
		$('#receive_zipcode_err').empty();		
	});
	$('#post_name').focus(function(){
		$('#post_name_err').empty();		
	});
	$('#post_address').focus(function(){
		$('#post_address_err').empty();		
	});
	$('#post_zipcode').focus(function(){
		$('#post_zipcode_err').empty();		
	});
	$('#status_text').focus(function(){
		$('#status_text_err').empty();		
	});
		$('#step3_btn').click(function(){
		receive_name = $.trim($('#receive_name').val());
		receive_address = $.trim($('#receive_address').val());
		receive_zipcode = $.trim($('#receive_zipcode').val());
		post_name = $.trim($('#post_name').val());
		post_address = $.trim($('#post_address').val());
		post_zipcode = $.trim($('#post_zipcode').val());
		status_text = $.trim($('#status_text').val());
		result = true;
		if(receive_name.length<1){
			//$('#receive_name_err').html(betterLang.maf.inputnotempty);
			result = false;
		}
		if(receive_address.length<1){
			//$('#receive_address_err').html(betterLang.maf.inputnotempty);
			result = false;
		} 
		if(receive_zipcode.length<1){
		//	$('#receive_zipcode_err').html(betterLang.maf.inputnotempty);
			result = false;
		} 
		if(post_name.length<1){
		//	$('#post_name_err').html(betterLang.maf.inputnotempty);
			result = false;
		}
		if(post_address.length<1){
		//	$('#post_address_err').html(betterLang.maf.inputnotempty);
			result = false;
		} 
		if(post_zipcode.length<1){
		//	$('#post_zipcode_err').html(betterLang.maf.inputnotempty);
			result = false;
		}			
		if(status_text.length>50){
			//	$('#post_zipcode_err').html(betterLang.maf.inputnotempty);
				result = false;
		}
		return result;
	});
});