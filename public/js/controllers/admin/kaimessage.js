$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/kaimessage';
	});
	
	$('#btnTodo').click(function(){
		var todo = $('#todo').val();
		var act_url = BETTER_ADMIN_URL+'/dmessage';
		
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
		}

	});
	
	
	$('#btn_send_msg').click(function(){

		$('#dlgSendMessage span.error').hide();
		
		x = $.trim($('#dlgSendMessage textarea').val());
		uids = $.trim($('#msg_receiver').val());
		msg_id = $.trim($('#msg_id').val());

		if (uids.length==0) {
			alert(' 没有指定收信人');
			$(this).attr('disabled', false);
			return false;
		} else if (x=='') {
			alert(' 请输入私信内容');
			$('#msg_content').focus();
			$(this).attr('disabled', false);
			return false;
		} else {
			Better_Notify_loading();
			$('#dlgSendMessage').dialog('close');
			
			$.post(BETTER_ADMIN_URL+'/kaimessage/send', {
				uids: uids,
				content: x,
				msg_id: msg_id
			}, function(dnJson){
				if (true) {
					if (dnJson.result!=1) {
						alert(dnJson.result);
					} else {
						Better_Notify('私信已经成功发送');
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					}
				}
				$('#dlgSendMessage input[type="button"]').attr('disabled', false);
				
			}, 'json');
		}
		
		return false;
	});		
	
});


/**
 * 管理员回复私信
 * 
 * @param uids
 * @return
 */
function Better_Admin_SendMessage(uids, nickname, msg_id)
{
	$('#betterMessageBox').dialog('destroy');
	$('#dlgSendMessage').dialog('destroy');
	$('#dlgSendMessage').attr('title', '回复'+nickname+'的私信');
	
	$('#dlgSendMessage').find('#msg_content').val('');
	
	$('#msg_receiver').val(uids);
	
	$('#msg_id').val(msg_id);

	$('#dlgSendMessage').dialog({
		bgiframe: true,
		autoOpen: true,
		modal: true,
		resizable: false,
		width: 480
	});
	
}