
$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/usercheckin';
	});

	/*$('#btnTodo').click(function(){
		var todo = $('#todo').val();
		var act_url = BETTER_ADMIN_URL+'/poi';
		
		if (todo=='') {
			Better_Notify({
				msg: '请选择操作'
			});
		} else {
			var POST = new Object();
			
			switch(todo) {
				case 'del_poi':
					act_url += '/del';
					break;
			}			
			
			var poids = new Array();
			var bi = 0;
			
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					poids[bi++] = $(this).val();
				}
			});
			
			if (poids.length<=0) {
				Better_Notify({
					msg: '请选择要操作的POI'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
							Better_Notify({
								msg: '请稍候 ...'
							});
							
							$.post(act_url, {
								'poids[]' : poids
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
		}

	}); */
	
});