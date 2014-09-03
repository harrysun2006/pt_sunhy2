$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/dmessage';
	});
	
	$('#btnTodo').click(function(){
		var act_url = BETTER_ADMIN_URL+'/dmessage/del';
			
			var ids = new Array();
			var bi = 0;
			var fids= new Array();
			var fi = 0;
			
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					ids[bi++] = $(this).val();
					
					fid = $(this).attr('fromid');
					fids[fi++] = fid;
				}
			});
			
			if (ids.length<=0) {
				Better_Notify({
					msg: '请选择要操作的私信'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						$.post(act_url, {
							'ids[]' : ids,
							'fids[]': fids
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