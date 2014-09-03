
$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/photo?photo=1';
	});
	
	$('td.pager select').change(function(){
		pageToJump = $(this).val();
		$('#page').val(pageToJump);
		$('#search_form').trigger('submit');
	});
	
	$('td.messages div.msg_row').each(function(){
		html = $(this).html();
		$(this).html(Better_parseMessage({
			message: html
		}));
	});
	
	//单独处理全选，全不选，反选css
	$('#chooseAll1').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', true);
		$('tr.message_row td.message_col').addClass('selected');
	});
	
	$('#chooseNone1').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', false);
		$('tr.message_row td.message_col').removeClass('selected');
	});
	
	$('#chooseReverse1').click(function(){
		$('tr.message_row input[type="checkbox"]').each(function(){
			$(this).attr('checked', !$(this).attr('checked'));
			if ($(this).parent().parent().parent().parent().parent().hasClass('selected')) {
				$(this).parent().parent().parent().parent().parent().removeClass('selected');
			} else {
				$(this).parent().parent().parent().parent().parent().addClass('selected');
			}
		});
	});
	

	$('tr.message_row input[type="checkbox"]').click(function(){
		if ($(this).attr('checked')) {
			$(this).parent().parent().parent().parent().parent().addClass('selected');
		} else {
			$(this).parent().parent().parent().parent().parent().removeClass('selected');
		}
	});
	
	
	$('td.messages div.attach_row a.msg_attach').fancybox();


	$('#btnTodo').click(function(){
		var todo = $('#todo').val();
		var act_url = BETTER_ADMIN_URL+'/article';
		
		if (todo=='') {
			Better_Notify({
				msg: '请选择操作'
			});
		} else {
			var POST = new Object();
			
			switch(todo) {
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
	
	
	//tooltip
	$('img.attach').tooltip({
		delay: 0,
		showURL: false,
		bodyHandler: function() {
			return $("#"+$(this).attr('id')+"_div").html();
		}
	});
	
});