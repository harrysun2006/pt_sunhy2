/**
 * 绑定聊天软件
 * 
 */
$(function(){

	$('#btnBindGtalk').click(function(){
		
	});
	
	$('#btnBindMsn').click(function(){
		$(this).attr('disabled', true);
		var msn = $.trim($('#msn_to_bind').val());
		$('#errBindMsn').empty().hide();
		
		if (!Better_isEmail(msn)) {
			$('#errBindMsn').html(betterLang.setting.im.invalid_msn).fadeIn();
			$('#btnBindMsn').attr('disabled', false);	
		} else {
			Better_Notify_loading();
			
			$.post('/ajax/user/bindim', {
				im: msn,
				protocol : 'msn'
			}, function(bmJson){
				Better_Notify_clear();
				
				if (Better_AjaxCheck(bmJson)) {
					switch(bmJson.result) {
						case 'success':
							msg = betterLang.noping.setting.im.howtosucess.toString().replace('{ROBOT}',bmJson.robot);
							break;
						case 'exists':
							msg = betterLang.noping.setting.im.exits.toString().replace('{MSN}',msn);
							break;
						case 'service_unavailable':
							msg = betterLang.setting.im.no_msnrobot;
							break;
						case 'failed':
							msg = betterLang.setting.cell.error_unknow;
							break;						
					}
					
					Better_Notify({
						msg: msg,
						height: 250
					});
				}
				$('#btnBindMsn').attr('disabled', false);	
			}, 'json');
		}
	});
	
});