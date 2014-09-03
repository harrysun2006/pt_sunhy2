
$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/article';
	});

	$('td.messages div.msg_row').each(function(){
		html = $(this).html();
		$(this).html(Better_parseMessage({
			message: html
		}));
	});
	
	$('td.messages div.attach_row a.msg_attach').fancybox();

	$('#btnQuickToday').click(function(){
		$('#from').val(Better_Today_From);
		$('#to').val(Better_Today_To);
		$('#search_form').trigger('submit');
	});
	
	$('#btnQuickPhoto').click(function(){
		
		$('#photo').find('option').each(function(){
			if ($(this).val()=='1') {
				$(this).attr('selected', true);
			}
		});
		$('#search_form').trigger('submit');
		
	});
	
	$('tr.message_row input[type="checkbox"]').click(function(){
		if ($(this).attr('checked')) {
			$(this).parent().parent().addClass('selected');
		} else {
			$(this).parent().parent().removeClass('selected');
		}
	});

	$('#btnTodo').click(function(){
		var todo = $('#todo').val();
		var act_url = BETTER_ADMIN_URL+'/check';
		
		if (todo=='') {
			Better_Notify({
				msg: '请选择操作'
			});
		} else {
			var POST = new Object();
			
			switch(todo) {
				case 'pass':
					act_url += '/check';
					break;
				case 'del':
					act_url += '/del';
					break;
				case 'reset_place':
					act_url += '/resetplace';
					break;
				case 'reset_place2':
					act_url += '/resetplace2';
					break;
				case 'del_attach':
					act_url += '/delattach';
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
					msg: '请选择要操作的微博'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
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
				});
			}
		}

	});
	
});