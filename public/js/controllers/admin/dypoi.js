
function resetMajor(poi_id){
	poids = [poi_id];
	Better_Confirm({
		msg: '确认重置掌门?',
		onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				$.post(BETTER_ADMIN_URL+'/poi/resetmajor', {
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


$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/dynamicpoi';
	});

	
	$('#btnDel').click(function(){	
			
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
							
							$.post(BETTER_ADMIN_URL+'/poi/del', {
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

	});
	
});