function lockAccount(uid){
	
	if(uid){
		Better_Confirm({
			msg: '确认要执行该操作?',
			onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				
				$.post(BETTER_ADMIN_URL+'/usermanage/lock', {
					'uid': uid
				}, function(json){
					Better_Notify_clear();
					if (json.result==1) {
						Better_Notify({
							msg: '操作成功'
						});
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					} else {
						Better_Notify({
							msg: '操作失败'
						});
					}		
					
				}, 'json');
			}
		});
		
	}else{
		return false;
	}
	
}


function unlockAccount(uid){
	
	if(uid){
		Better_Confirm({
			msg: '确认要执行该操作?',
			onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				
				$.post(BETTER_ADMIN_URL+'/usermanage/unlock', {
					'uid': uid
				}, function(json){
					Better_Notify_clear();
					if (json.result==1) {
						Better_Notify({
							msg: '操作成功'
						});
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					} else {
						Better_Notify({
							msg: '操作失败'
						});
					}		
					
				}, 'json');
			}
		});
		
	}else{
		return false;
	}
	
}


function muteAccount(uid, type){
	var url ='';
	if(uid){
		if(type=='mute'){
			url = BETTER_ADMIN_URL+'/usermanage/mute';
		}else if(type=='unmute'){
			url = BETTER_ADMIN_URL+'/usermanage/unmute';
		}
		
		Better_Confirm({
			msg: '确认要执行该操作?',
			onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				
				$.post(url, {
					'uid': uid
				}, function(json){
					Better_Notify_clear();
					if (json.result==1) {
						Better_Notify({
							msg: '操作成功'
						});
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					} else {
						Better_Notify({
							msg: '操作失败'
						});
					}		
					
				}, 'json');
			}
		});
		
	}else{
		return false;
	}
	
}



$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/usermanage';
	});

	$('table.kai_table a[href^=#ban_account]').fancybox();
	
	
	$('table.kai_table input[id^=banbtn_]').click(function(){
		
		sid = $(this).attr('id').split('_');
		uid = sid[1];
		reason = $.trim($('#textarea_'+uid).val());
		state = $('#state_'+uid).val();
		
		if(state!='banned'){
			act_type = 'ban_account';
			url = '/admin/usermanage/ban';
			msg='确认要封号并重置用户信息?',
			confirmText="封号并重置";
			cancelText="封号但不重置";
		}else{
			act_type = 'unban_account';
			url = '/admin/usermanage/unban';
			msg = "确认要解封用户?";
			confirmText="Yes";
			cancelText="No";
		}
		
		if(reason==''){
			alert('请输入原因');
		}else{
			Better_Confirm({
				msg: msg,
				confirmText:confirmText,
				cancelText:cancelText,
				onCancel: function(){
					if(act_type == 'ban_account'){
						Better_Notify({
							msg: '请稍候 ...'
						});
						$.post(url,{
							uid: uid,
							reason: reason,
							old_state: state,
							act_type: act_type
							}, function(json){
								
								if(json.result==1){
									Better_Notify({
										msg: '操作成功'
									});
									
									$('#reload').val(1);
									$('#search_form').trigger('submit');
								}else{
									Better_Notify({
										msg: '操作失败'
									});
								}
							}
						,'json');						
					}else{
						Better_Confirm_clear();
					}
				},
				onConfirm: function(){
				
				Better_Notify({
					msg: '请稍候 ...'
				});
				if(act_type == 'ban_account'){
					resetinfo=1;
				}else{
					resetinfo=0;
				}
				$.post(url,{
					uid: uid,
					reason: reason,
					old_state: state,
					act_type: act_type,
					resetinfo:resetinfo
					}, function(json){
						
						if(json.result==1){
							Better_Notify({
								msg: '操作成功'
							});
							
							$('#reload').val(1);
							$('#search_form').trigger('submit');
						}else{
							Better_Notify({
								msg: '操作失败'
							});
						}
					}
				,'json');
				
			}
				
			});
			
		}
		
		
	});

	
});