/**
 * 重设密码
 * 
 */
$(function(){

	$('#pass').val('');
	$('#repass').val('');
	
	$('#btnPwd').click(function(){
		$('#set_password_error').empty();		
		//$('#set_password_success').empty();
		oldpwd = BETTER_RESETPWD_FROM_HASH ? '' : $.trim($('#old_pass').val());
		pwd = $.trim($('#pass').val());
		repwd = $.trim($('#repass').val());
		
		oldpwd = oldpwd.toString();
		pwd = pwd.toString();
		repwd = repwd.toString();		
		if (pwd!='') {

			if (pwd.length<6 || pwd.length>20) {
				$('#err_pass').text(betterLang.password.length_invalid).show();
			} else if (pwd!=repwd) {
				$('#err_pass').text(betterLang.password.mismatch).show();
			} else {
				if (oldpwd.length==0 && !BETTER_RESETPWD_FROM_HASH) {
					$('#err_old_pass').text(betterLang.password.old_password_required).show();
				} else {
					!BETTER_RESETPWD_FROM_HASH && $('#old_pass').val(Better_md5(oldpwd));
					$('#pass').val(Better_md5(pwd));
					$('#repass').val(Better_md5(repwd));
					
					$.post('/setting/update', {
						todo: 'password',
						pass: $('#pass').val() ,
						repass: $('#repass').val(),
						oldpass: !BETTER_RESETPWD_FROM_HASH ? $('#old_pass').val() : '',
						from_hash: $('#from_hash').val()
					}, function(pwd_json){
						if (pwd_json.has_err=='1') {
							$('#set_password_success').hide();
							$('#set_password_error').text(pwd_json.err);
							$('#set_password_error').fadeIn();
							$('#pass').val('');
							$('#repass').val('');
							$('#old_pass').val('');
						} else {
							
							$('#err_pass').empty().hide();
							$('#pass').val('');
							$('#repass').val('');
							$('#old_pass').val('');
							$('#set_password_error').hide();
							$('#set_password_success').fadeIn();
						}
					}, 'json');
				}
			}
		} else {			
			$('#set_password_error').hide();
			$('#set_password_success').hide();
			$('#err_pass').text(betterLang.password.invalid_pass).show();
		}
		
	});
	
});