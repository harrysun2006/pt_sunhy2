/**
 * 隐私设置
 * 
 */
$(function(){
	/*$('#btnPrivacy').click(function(){
		priv_blog = $('#priv_blog').attr('checked') ? 1 : 0;
		
		if ($('#priv_location_1').attr('checked')) {
			priv_location = 1;
		} else if ($('#priv_location_2').attr('checked')) {
			priv_location = 2;
		} else {
			priv_location = 0;
		}

		if ($('#priv_place_1').attr('checked')) {
			priv_place = 1;
		} else if ($('#priv_place_2').attr('checked')) {
			priv_place = 2;
		} else {
			priv_place = 0;
		}


		$.post('/setting/update', {
			todo: 'privacy',
			priv_blog: priv_blog,
			priv_location: priv_location,
			priv_place: priv_place
		}, function(privJson){
			if(privJson.has_err==1){				
				if(priv_blog){					
					$('#priv_blog').attr('checked',false);
				} else {
					$('#priv_blog').attr('checked',true);
				}
				$('#set_privacy_success').html(privJson.err);
			}
			$('#set_privacy_success').fadeIn();
		}, 'json');
	});*/
	
	
	$('#btnPrivacy').click(function(){
		var allow_rt = $('#allow_rt').attr('checked') ? 0 : 1;
		var friend_sent_msg = $('#friend_sent_msg').attr('checked') ? 1 : 0;
		var sync_badge = $('#sync_badge').attr('checked') ? 1 : 0;
		
		$.post('/setting/update', {
			todo: 'privacy',
			allow_rt: allow_rt,
			friend_sent_msg: friend_sent_msg,
			sync_badge: sync_badge
		}, function(privJson){
			if(privJson.has_err==1){				
				/*if(allow_rt){					
					$('#allow_rt').attr('checked',false);
				} else {
					$('#allow_rt').attr('checked',true);
				}*/
				$('#set_privacy_success').html(privJson.err);
			}
			$('#set_privacy_success').fadeIn();
		}, 'json');
	});
});