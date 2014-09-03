
$(function(){
	
	$('#btnReset').click(function(){
	
		window.location = BETTER_ADMIN_URL+'/text';
	});

	$('td.messages div.msg_row').each(function(){
		html = $(this).html();
		$(this).html(Better_parseMessage({
			message: html
		}));
	});
	
	
	$('#btnDel').click(function(){
		var bids = new Array();
		var mids = new Array();
		var fids = new Array();
		var bi = 0;
		var mi = 0;
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				mixid = $(this).val();
				tmp = mixid.split('@');
				id = tmp[0];
				type = tmp[1];
				if(type=='blog'){
					bids[bi++] = id;
				}else if(type=='dmessage'){
					mids[mi++] = id;
					
					fid = $(this).attr('fromid');
					fids[fi++] = fid;
				}
			}
		});
		
			if (bids.length<=0 && mids.length<=0) {
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
						
						
						$.post(BETTER_ADMIN_URL+'/text/del', {
							'bids[]' : bids,
							'ids[]': mids,
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
	
	
	$('#btnDelandBan').click(function(){
		var bids = new Array();
		var mids = new Array();
		var bi = 0;
		var mi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				mixid = $(this).val();
				tmp = mixid.split('@');
				id = tmp[0];
				type = tmp[1];
				if(type=='blog'){
					bids[bi++] = id;
				}else if(type=='dmessage'){
					mids[mi++] = id;
				}
			}
		});
		
		uid = $(this).attr('uid');
		
			if (bids.length<=0 && mids.length<=0) {
				Better_Notify({
					msg: '请选择操作对象'
				});					
			} else {
				Better_Confirm({
					msg: '确认要删除并封号?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						
						$.post(BETTER_ADMIN_URL+'/text/del', {
							'bids[]' : bids,
							'ids[]': mids
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
										
										$('#ban_btn').click(function(){
											Better_Confirm({
												msg: '确认要封号?',
												onConfirm: function(){
												
												Better_Notify({
													msg: '请稍候 ...'
												});
												
												$.post('/admin/usermanage/ban',{
													uid: uid,
													reason: $('#ban_reason_textarea').val(),
													act_type: 'ban_account'
													}, function(json){
														
														if(json.result==1){
															Better_Notify({
																msg: '操作成功',
																closeCallback: function(){
																	$('#reload').val(1);
																	$('#btnSearch').trigger('click');
																}	
															});
															
														}else if(json.result==2){
															Better_Notify({
																msg: '该账号已经被封了',
																closeCallback: function(){
																$('#reload').val(1);
																$('#btnSearch').trigger('click');
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
	
	//转发邮件
	$('#btn_rtemail').click(function(){
		
		$('#admin_rtemail').dialog('destroy');
		$('#admin_rtemail').attr('title', '转发问题');
		
		$('#reciver').val('');
		
		var all_msg='';
		$('tr.message_row input[type="checkbox"]').each(function(){
			checkbox = $(this);
			if (checkbox.attr('checked')) {
				tr = checkbox.parent('td').parent('tr');
				msg_html = tr.children('td.messages').children('div.msg_row').children();
				
				if(msg_html.length==1){
					msg = msg_html.text();
				}else if(msg_html.length==0){
					msg = tr.children('td.messages').children('div.msg_row').html();
				}
				
				//msg = msg.split('来源');
				//msg = msg[0];
				
				all_msg = all_msg +'<hr>'+ msg;
			}
		});
		
		
		$('#admin_rtemail').find('#msg_content').val(all_msg);
		
		$('#admin_rtemail').dialog({
			bgiframe: true,
			autoOpen: true,
			modal: true,
			resizable: true,
			height: 430,
			width: 600
		});
		
	});
	
	
	$('#rt_btn').click(function(){
		content = $('#admin_rtemail').find('#msg_content').val();
		receiver = $('#reciver').val();
		
		if(receiver.length==0){
			alert('请输入收件人');
			return false;
		}
		
		Better_Notify('请稍候...');
		
		$.post(BETTER_ADMIN_URL+'/text/rtemail',{
			'receiver': receiver,
			'content' : content
		}, function(json){
			if (json.result==1) {
				Better_Notify({
					msg: '操作成功'
				});
				$('#admin_rtemail').dialog('close');
				$('#chooseNone').trigger('click');
			} else {
				Better_Notify({
					msg: '操作失败'
				});
			}	
		}, 'json');
		
	});
	
	
	$('#whitelist a').dblclick(function(){
		
		tmp = $('#reciver').val();
		$('#reciver').val(tmp+$(this).text()+';');
		
		return false;
	});
	
});	