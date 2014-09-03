function reset(type, uid){
	
	if(type && uid){
		
		var act_url = BETTER_ADMIN_URL+'/user';
		var typeText='';
		switch(type)
		{
			case 'username':
				act_url += '/resetname';
				typeText = '用户名';
				break;
			case 'nickname':
				act_url += '/resetnickname';
				typeText = '姓名';
				break;
			case 'selfintro':
				act_url += '/resetselfintro';
				typeText = '自我介绍';
				break;
		}
		
		var bids = [uid];
		
		Better_Confirm({
			msg: '确认要重置'+typeText+'?',
			onConfirm: function(){

					Better_Notify({
						msg: '请稍候 ...'
					});
					
					$.post(act_url, {
						'bids[]' : bids
					}, function(json){
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
		Better_Notify({
			msg: 'Error'
		});
	}
	return false;
}

$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/user';
	});

	$('#btnTodo').click(function(){
		var todo = $('#todo').val();
		var act_url = BETTER_ADMIN_URL+'/user';
		
		if (todo=='') {
			Better_Notify({
				msg: '请选择操作'
			});
		} else {
			var POST = new Object();
			
			switch(todo) {
				case 'reset_place':
					act_url += '/resetplace';
					break;
				
				case 'del_avatar':
					act_url += '/delavatar';
					break;
				
				case 'reset_name':
					act_url += '/resetname';
					break;
				
				case 'reset_nickname':
					act_url += '/resetnickname';
					break;
				
				case 'reset_selfintro':
					act_url += '/resetselfintro';
					break;
				
				case 'send_msg':
					act_url += '/sendmsg';
					break;
			}			
			
			var bids = new Array();
			var bi = 0;
			
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					bids[bi++] = $(this).val();
				}
			});
			
			if (bids.length<=0) {
				Better_Notify({
					msg: '请选择要操作的用户'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						if(todo=='send_msg'){
							Better_Notify({
								msg: ''
							});
							Better_Notify_clear();
							Better_Admin_SendMessage(bids);
						}
						else{
							Better_Notify({
								msg: '请稍候 ...'
							});
							
							$.post(act_url, {
								'bids[]' : bids
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
					}
				});
			}
		}

	});
	
});