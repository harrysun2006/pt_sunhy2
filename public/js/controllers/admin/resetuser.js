
$(function(){
	var type = $('#reset_type').val();
	$('#btnReset').click(function(){
	
		window.location = BETTER_ADMIN_URL+'/'+type;
	});

	
	
	$('#btnTodo').click(function(){
		var uids = new Array();
		var ui = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				uids[ui++] =$(this).val();
			}
		});
		
			if (uids.length<=0) {
				Better_Notify({
					msg: '请选择操作对象'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						switch(type)
						{
							case 'username':
								url = BETTER_ADMIN_URL+'/user/resetname';
								break;
							
							case 'usernickname':
								url = BETTER_ADMIN_URL+'/user/resetnickname';
								break;
								
							case 'userselfintro':
								url = BETTER_ADMIN_URL+'/user/resetselfintro';
								break;
							default:
								
								break;
						
						}
						
						$.post(url, {
							'bids[]' : uids
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
			}

	});
});	