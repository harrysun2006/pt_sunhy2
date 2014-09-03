$(function(){

	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/wlanblog';
	});
	
	$('#btnPass').click(function(){
		var bids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				id = $(this).val();				
				bids[bi++] = id;				
			}
		});
		
			if (bids.length<=0) {
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
						
						
						$.post(BETTER_ADMIN_URL+'/wlanblog/pass', {
							'bids[]' : bids,							
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
	
	
	$('#btnFalse').click(function(){
		var bids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				if ($(this).attr('checked')) {
					id = $(this).val();				
					bids[bi++] = id;				
				}
			}
		});
		
			if (bids.length<=0) {
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
						
						
						$.post(BETTER_ADMIN_URL+'/wlanblog/false', {
							'bids[]' : bids,							
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