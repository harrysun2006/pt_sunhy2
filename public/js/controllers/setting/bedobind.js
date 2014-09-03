/**
 * 导入贝多号设置
 */

$(function(){
	$('#btnBind').click(function(){
		jid = $.trim($('#jid').val());
		password = $('#password').val();
		err=false;
		if (jid.length==0) {
			$('#err_jid').html('贝多号不能为空');//betterLang.import.err.emptyjid
			err = true;
		} else {
			$('#err_jid').html('');
		}
		if (password.length==0) {
			$('#err_password').html('密码不能为空');//betterLang.import.err.emptypassword
			err = true;
		} else {
			$('#err_password').html('');
		}
		
		posts = {
			todo: 'bedobind',
			jid: jid,
			password: password
		}
		if(err==false){
			Better_Notify_loading();
			$.post('/setting/update', posts, function(privJson){
				if (privJson != '') {
					Better_Notify({
						msg: privJson,
						height:170
					});
				} else {
					Better_Notify_clear();
					window.location.href='/setting/bedoimport';
				}
			}, 'json');
		}
	});
	
	$('#setBtn').click(function(){});
});