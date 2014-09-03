
function Delpic_Ban(mixid, uid){
	var bids = new Array();
	var uids = new Array();
	tmp = mixid.split('@');
	id = tmp[0];
	type = tmp[1];
	if(type=='blog'){
		bids = [id];
	}else if(type=='user'){
		uids = [id];
	}
	
	
	Better_Confirm({
		msg: '确认要删除并封号?',
		onConfirm: function(){
			Better_Notify({
				msg: '请稍候 ...'
			});
			
			
			$.post(BETTER_ADMIN_URL+'/picture/del', {
				'bids[]' : bids,
				'uids[]': uids
			}, function(json){
				Better_Notify_clear();
				if (json.result==1) {
					Better_Notify({
						msg: '删除成功',
						close_timer: 1,
						closeCallback: function(){
							var dba = $('#delandban_account');
							dba.dialog('destroy');
							dba.dialog({
								width: 350,
								height: 350,
								bgiframe: true,
								autoOpen: true,
								modal: true,
								resizable: false
							});
							
							reason = $('#ban_reason_textarea').val();
							
							$('#ban_btn').click(function(){
								Better_Confirm({
									msg: '确认要封号?',
									onConfirm: function(){
									
									Better_Notify({
										msg: '请稍候 ...'
									});
									
									$.post('/admin/usermanage/ban',{
										uid: uid,
										reason: reason,
										act_type: 'ban_account'
										}, function(json){
											
											if(json.result==1){
												Better_Notify({
													msg: '操作成功',
													closeCallback: function(){
														$('#reload').val(1);
														$('#search_form').trigger('submit');
													}
												});
												
												
											}else if(json.result==2){
												Better_Notify({
													msg: '该账号已经被封了',
													closeCallback: function(){
													$('#reload').val(1);
													$('#search_form').trigger('submit');
												}
												});
												
											}else{
												Better_Notify({
													msg: '操作失败'
												});
											}
											dba.dialog("close");
										}
									,'json');
									
								}
									
								});
								
							});
							
						}
					});
					
					
					
					
					//$('#reload').val(1);
					//$('#search_form').trigger('submit');
				} else {
					Better_Notify({
						msg: '操作失败'
					});
				}		
				
			}, 'json');
		}
	});
}

function checkImages() {
	var ids  = new Array();
	var bids = new Array();
	var uids = new Array();
	var bi = 0;
	var ui = 0;
	$('tr.message_row input[type="checkbox"]').each(function(){
		mixid = $(this).val();
		tmp = mixid.split('@');
		rowid = tmp[0];
		id = tmp[1];
		type = tmp[2];
		ids.push(rowid);
		if ($(this).attr('checked')) {
			if(type=='blog'){
				bids[bi++] = id;
			}else if(type=='user'){
				uids[ui++] = id;
			}
		}
	});
	
	Better_Confirm({
		msg: '确认要执行操作?',
		onConfirm: function(){
			Better_Notify({
				msg: '请稍候 ...'
			});
			
			
			$.post(BETTER_ADMIN_URL+'/picture/check', {
				'bids[]' : bids,
				'uids[]': uids,
				'ids[]' : ids,
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

function deleteImages() {
	var bids = new Array();
	var uids = new Array();
	var bi = 0;
	var ui = 0;
	$('tr.message_row input[type="checkbox"]').each(function(){
		if ($(this).attr('checked')) {
			mixid = $(this).val();
			tmp = mixid.split('@');
			id = tmp[0];
			type = tmp[1];
			if(type=='blog'){
				bids[bi++] = id;
			}else if(type=='user'){
				uids[ui++] = id;
			}
		}
	});
	
	if (bids.length<=0 && uids.length<=0) {
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
				
				
				$.post(BETTER_ADMIN_URL+'/picture/del', {
					'bids[]' : bids,
					'uids[]': uids
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

$(function(){
	
	$('#btnReset').click(function(){
	
		window.location = BETTER_ADMIN_URL+'/picture';
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
	
	
	$('#btnCheck').click(function(){
		checkImages();	
	});
	
	$('#btnDel').click(function(){
		deleteImages();
	});
});	