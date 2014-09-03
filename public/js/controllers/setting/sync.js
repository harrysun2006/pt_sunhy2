/**
 * 抄送到第三方设置
 * 
 */
$(function(){
	
	$('#form_set_sync a').click(function(){
		
		if (syncing==true) {
			Better_Notify(betterLang.setting.sync.syncing);
			return false;
		}
		
		$(this).find('.err').empty().hide();
		var id = $(this).attr('ref');
		
		if ($(this).attr('id')=='bind_139') {
			return false;
		}
		
		//|| $(this).attr('id')=='bind_kaixin00111'
		if ($(this).attr('id')=='bind_douban' || $(this).attr('id')=='new_bind_facebook' || $(this).attr('id')=='bind_163' || $(this).attr('id')=='bind_qq' || $(this).attr('id')=='bind_renren' || $(this).attr('id')=='bind_4sq' || $(this).attr('id')=='bind_qqsns') {
			return true;
		}
		
		if ($(this).attr('id')=='bind_'+id) {
			//$('tr[id^="func_"]').hide();
			//$('a[id^=restore_]').hide();
			/*if($('#cancel_'+id).attr('display')=='none'){
				$('a[id^=bind_]').show();
			}*/
			$('#status_'+id).hide();
			$('#func_'+id).show();
			
			$('#btn_'+id).click(function(){

				//$(this).attr('disabled', true);
				
				var username = $.trim($('#username_'+id).val());
				var password = $('#password_'+id).val();
				var followkai = $('#followkai').attr("checked");
				if (username=='' || password=='') {
					$('#err_'+id).html(betterLang.setting.sync.input_user_pass).fadeIn();
					//$('#btn_'+id).attr('disabled', false);
				} else if ($(this).attr('id')=='btn_msn' && password.length > 16) {
					$('#err_'+id).html('密码最长支持16位').fadeIn();
					//$('#btn_'+id).attr('disabled', false);					
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
						protocol: id,
						followkai: followkai
					}, function(syncJson){
						Better_Notify_clear();
						if (syncJson.logined == 1) {
							$('#status_'+id).html(betterLang.noping.setting.sync.already_bind.toString().replace('{USERNAME}',username)).fadeIn();
							$('#err_'+id).hide();
							$('#func_'+id).hide();
							//$('#btn_'+id).attr('disabled', false);
							
							$('#cancel_'+id).show();
							$('#bind_'+id).hide();		
							$('#restore_'+id).hide();
							$('#sync_'+id).show();
							$('#sync_badge_'+id).attr('checked', true);
						} else if(syncJson.logined == 2) {
							
							$('#err_'+id).html(betterLang.setting.sync.failed_bind_2).fadeIn();
							//$('#btn_'+id).attr('disabled', false);	
						} else {
							$('#err_'+id).html(betterLang.setting.sync.failed_bind).fadeIn();
							//$('#btn_'+id).attr('disabled', false);							
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
				
				//$(this).unbind('click');
			});
			
			$('#restore_'+id).show();
			$('#bind_'+id).hide();
			$('#cancel_'+id).hide();
			
		} else if ($(this).attr('id')=='restore_'+id) {
			
			$('#status_'+id).show();
			$('#func_'+id).hide();			
			
			$('#restore_'+id).hide();
			$('#bind_'+id).show();
			$('#cancel_'+id).hide();
			$('#sync_'+id).hide();
			
		} else if ($(this).hasClass('blank')) {
			return true;
		} else {
			if ($(this).attr('id')=='cancel_139') return false;
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
							
							$('#cancel_'+id).hide();
							$('#bind_'+id).show();
							$('#restore_'+id).hide();
							$('#sync_'+id).hide();
						}
						//$('#btn_'+id).attr('disabled', false);
						syncing = false;

					}, 'json');				
				}
			});
		}
		
		return false;
	});
	
	
	//勋章同步
	$('input[id^=sync_badge_][type=checkbox]').click(function(){
		var checkbox = $(this);
		var protocol = checkbox.attr('ref');
		var sync= 0;
		if(checkbox.attr('checked')){
			sync = 1;
		}
		
		Better_Notify_loading();
		$.post('/ajax/user/syncbadge', {
			'protocol': protocol,
			'sync': sync
		}, function(json){
			Better_Notify_clear();
			if(json.result==1){
				Better_Notify('设置成功');
			}else{
				Better_Notify('设置失败');
			}
		}, 'json');
	});
});