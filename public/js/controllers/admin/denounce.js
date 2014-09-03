$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/denounce/'+$('#dtype').val();
	});
	
	
	$('#btnTodo').click(function(){
		var status = $('#todo').val();
		var dtype = $('#dtype').val();
		var i=0;
		var ids=new Array();
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				ids[i++] = $(this).val();
			}
		});
		
		if(ids.length<=0){
			Better_Notify('请选择要操作的举报内容');
		}else{
			Better_Notify('请稍候...');
			$.post(BETTER_ADMIN_URL+'/denounce/status',{
				'ids[]': ids,
				'status': status,
				'dtype': dtype
			}, function(json){
				Better_Notify_clear();
				if(json.result==1){
					Better_Notify('操作成功');
					$('#reload').val(1);
					$('#search_form').trigger('submit');
				}else{
					Better_Notify('操作失败');
				}
			},'json');
		}
	});
	
	
	$('#btnDel').click(function(){
		var i=0;
		var j=0;
		var ids = new Array();
		var bids=new Array();
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				ids[j++] = $(this).val();
				bids[i++] = $(this).attr('bid');
			}
		});
		
		if(bids.length<=0){
			Better_Notify('请选择要操作的举报内容');
		}else{
			Better_Confirm({
				msg: "确定要删除被举报的内容?",
				onConfirm: function(){
					Better_Notify('请稍候...');
					$.post(BETTER_ADMIN_URL+'/denounce/del',{
						'bids[]' : bids,
						'ids[]': ids
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
	
	
	$("a.xiugai, a.search").fancybox({
		'width'				: '75%',
		'height'			: '97%',
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe',
		'onStart' : function(){
			
		}
	});	

	
	
	$('#btn_send_msg').click(function(){

		$('#dlgSendMessage span.error').hide();
		
		x = $.trim($('#dlgSendMessage textarea').val());
		uid = $.trim($('#msg_receiver').val());

		if (uid.length==0) {
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
			
			$.post(BETTER_ADMIN_URL+'/denounce/sendmsg', {
				uid: uid,
				content: x
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
function Better_Admin_SendMessage(uid)
{
	$('#betterMessageBox').dialog('destroy');
	$('#dlgSendMessage').dialog('destroy');
	$('#dlgSendMessage').attr('title', '给'+uid+'发私信');
	
	$('#dlgSendMessage').find('#msg_content').val('');
	
	$('#msg_receiver').val(uid);
	
	$('#dlgSendMessage').dialog({
		bgiframe: true,
		autoOpen: true,
		modal: true,
		resizable: false,
		width: 480
	});
	
}
