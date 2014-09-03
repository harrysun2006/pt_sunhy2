
$(function(){
	
	$('td.pager select').change(function(){
		pageToJump = $(this).val();
		$('#page').val(pageToJump);
		$('#search_form').trigger('submit');
	});
	
	$('table.kai_table input[name=change_type]').click(function(){
		$('#view_type').val($(this).val());
		window.location = BETTER_ADMIN_URL+'/useravatar?avatar=1&advance=1&view_type='+$(this).val()+'&from='+$('#from').val()+'&to='+$('#to').val();
	});
	
	$('td.message_col a.msg_attach').fancybox();
	
	//tooltip
	$('img.avatar').tooltip({
		delay: 0,
		showURL: false,
		bodyHandler: function() {
			
			return $("#"+$(this).attr('id')+"_div").html();
		}
	});
	
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/useravatar?avatar=1';
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
			if ($(this).parent().hasClass('selected')) {
				$(this).parent().removeClass('selected');
			} else {
				$(this).parent().addClass('selected');
			}
		});
	});
	

	$('tr.message_row input[type="checkbox"]').click(function(){
		if ($(this).attr('checked')) {
			$(this).parent().addClass('selected');
		} else {
			$(this).parent().removeClass('selected');
		}
	});
	$('table.kai_table input[name=btnTodo]').click(function(){
		var todo =$(this).attr('actionurl');
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
				
				case 'reset_selfintro':
					act_url += '/resetselfintro';
					break;
					
				case 'recommend_avatar':
					act_url += '/recommended';
					break;
					
				case 'unrecommend_avatar':
					act_url += '/unrecommended';
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
								$('#avatar').val(1);						
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