$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/feedback';
	});
//回复
	$('table.kai_table a[href=#replyFeedback]').fancybox();	
	$('table.kai_table a[href=#replyFeedback]').click(function(){		
			$('#replyEmail').val($(this).attr('email'));
			$('#feedback_id').val($(this).attr('fid'));
	});
//发送私信
	$('table.kai_table a[href=#sendSecretMsg]').fancybox();	
	$('table.kai_table a[href=#sendSecretMsg]').click(function(){		
		$('#comment').html("发送私信给<b>"+$(this).attr('nickname')+"</b>");
		$('#send_id').val($(this).attr('fid'));	
	});
	$('#send_btn').click(function (){
		var uid = $('#send_id').val();
		var content = $('#send_content').val(); 
		if(content == ''){
			alert(' 请输入私信内容');
			$('#msg_content').focus();
		}else{
		$.post(BETTER_ADMIN_URL+'/feedback/send', {
			uid:uid,
			content:content
		}, function(dnJson){
			if (dnJson.result!=1) {
				alert(dnJson.result);
			} else {
				Better_Notify('私信已经成功发送');
			}
			},'json');
		}
	});
//历史记录
		
	$("a.viewHistory").fancybox({
		'width'				: '75%',
		'height'			: '98%',
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe',
		'onStart' : function(){
		}
	});	
//查看版本

	$('a.deleteFeedback').click(function(){		
		var act_url = BETTER_ADMIN_URL+'/feedback/del';
		var ids = new Array();
		ids[0] = $(this).attr('fid');
		Better_Confirm({
			msg: '确认要删除该反馈?',
			onConfirm: function(){
					Better_Notify({
						msg: '请稍候 ...'
					});
					
					$.post(act_url, {
						'ids[]' : ids
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
	});
	
//用户主页
//删除
	$('#reply_btn').click(function(){
		receiver = $.trim($('#replyEmail').val());
		content = $.trim($('#feedback_content').val());
		id =  $.trim($('#feedback_id').val());
		lan = $('#lang').val();
		if(receiver.length==0){
			alert('请输入收件人Email');
			return false;
		}
		
		if(content.length==0){
			alert('请输入内容');
			return false;
		}
		Better_Confirm({
			msg: '确认要执行操作?',
			onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				$.post(BETTER_ADMIN_URL+'/feedback/reply', {
					'receiver' : receiver,
					'content' : content,
					'id': id,
					'lang' : lan
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

	});
	
	$('#btnTodo').click(function(){
		var todo = $('#todo').val();
		var act_url = BETTER_ADMIN_URL+'/feedback';
		
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
			}			
			
			var ids = new Array();
			var bi = 0;
			
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					ids[bi++] = $(this).val();
				}
			});
			
			if (ids.length<=0) {
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
								'ids[]' : ids
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
	$('#btnSendSecretMsg').click(function (){
		alert('tre');
	}) ;
});