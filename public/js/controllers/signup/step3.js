var syncing = false;

$(function(){
	
	$('#step3_confirm_btn').click(function(){
		window.location='/home';
	});
	$('#step3_ignore_btn').click(function(){
		window.location='/home';
	});	

	//	同步
	$('#form_set_sync a').click(function(){
		if (syncing==true) {
			Better_Notify(betterLang.setting.sync.syncing);
			return false;
		}
		
		$(this).find('.err').empty().hide();
		var id = $(this).attr('ref');
		
		if ($(this).attr('id')=='bind_douban' || $(this).attr('id')=='new_bind_facebook' || $(this).attr('id')=='bind_163' || $(this).attr('id')=='bind_qq' || $(this).attr('id')=='bind_renren' || $(this).attr('id')=='bind_4sq' || $(this).attr('id')=='bind_qqsns') {
			return true;
		}
		
		if ($(this).attr('id')=='bind_'+id) {
			$('tr[id^="func_"]').hide();
			$('#status_'+id).hide();
			$('#func_'+id).show();
			
			$('#btn_'+id).click(function(){

				$(this).attr('disabled', true);
				
				username = $.trim($('#username_'+id).val());
				password = $('#password_'+id).val();
				
				if (username=='' || password=='') {
					$('#err_'+id).html(betterLang.setting.sync.input_user_pass).fadeIn();
					$('#btn_'+id).attr('disabled', false);
				} else {
					Better_Notify_loading({
						closeCallback: function(){
							syncing = false;
						}
					});
					$('#err_'+id).empty();
					syncing = true;
					$.post('/ajax/service/sync', {
						username: username,
						password: password,
						protocol: id
					}, function(syncJson){
						Better_Notify_clear();
						if (syncJson.logined == 1) {
							$('#status_'+id).html(betterLang.noping.setting.sync.already_bind.toString().replace('{USERNAME}',username)).fadeIn();
							$('#err_'+id).hide();
							$('#func_'+id).hide();
							$('#btn_'+id).attr('disabled', false);
							
							$('#link_cancel_'+id).show();
							$('#link_binded_'+id).hide();		
							$('#link_restore_'+id).hide();
						} else if(syncJson.logined == 2) {
							
							$('#err_'+id).html(betterLang.setting.sync.failed_bind_2).fadeIn();
							$('#btn_'+id).attr('disabled', false);	
						} else {
							$('#err_'+id).html(betterLang.setting.sync.failed_bind).fadeIn();
							$('#btn_'+id).attr('disabled', false);
						}
							
						syncing = false;
						
						if (Better_parseAchievement(syncJson)) {
							Better_Notify({
								msg: Better_parseAchievement(syncJson, betterLang.global.add_sync_site),
								close_timer: 1.5
							});
						}
					}, 'json');
				}
				
				$(this).unbind('click');
			});
			
			$('#link_restore_'+id).show();
			$('#link_binded_'+id).hide();
			$('#link_cancel_'+id).hide();
			
		} else if ($(this).attr('id')=='restore_'+id) {
			
			$('#status_'+id).show();
			$('#func_'+id).hide();			
			
			$('#link_restore_'+id).hide();
			$('#link_binded_'+id).show();
			$('#link_cancel_'+id).hide();
			
		} else if ($(this).hasClass('blank')) {
			return true;
		} else {

			Better_Confirm({
				msg: betterLang.setting.sync.cancel_bind,
				onConfirm: function(){
					Better_Confirm_clear();
					Better_Notify_loading();

					$.post('/ajax/service/unsync', {
						protocol: id
					}, function(unsyncJson){
						Better_Notify_clear();
						
						if (unsyncJson.result=='1') {
							$('#username_'+id).val('');
							$('#password_'+id).val('');
							$('#status_'+id).html(betterLang.setting.sync.had_cancel_bind).fadeIn();						
							
							$('#link_cancel_'+id).hide();
							$('#link_binded_'+id).show();
							$('#link_restore_'+id).hide();
						}
						$('#btn_'+id).attr('disabled', false);
						syncing = false;

					}, 'json');				
				}
			});
		}
		
		return false;
	});		
	
});