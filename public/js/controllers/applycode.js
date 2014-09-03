/**
 * 注册按钮被点击
 * 
 * @return
 */
function Better_Signup_Go()
{
	
	try {

		email_to_user = $.trim($('#email').val());
		pat = /(\S)+[@]{1}(\S)+[.]{1}(\w)+/;

		if (email_to_user=='') {
			$('#err_email').html(addstr +betterLang.signup.error.email.empty);
			$('#help_email').hide();
			err = true;
		} else if (!pat.test(email_to_user)) {
			$('#err_email').html(addstr +betterLang.signup.error.email.invalid);
			$('#help_email').hide();
			err = true;			
		} else {
		$('#err_email').html(okstr);		
	}

		if (err==false) {			
			$('#btnSubmit').attr('disabled', true);
			$.post('/ajax/signup/check', {
				email: email_to_user,
				password: pwd,
				agree: $('#agree').attr('checked') ? '1' : '0',
				username: username,
				nickname: nickname,
				code: $('#code').val()
			}, function(ce){

				if (ce.has_error) {
					for(key in ce.err) {
						$('#'+key).html(ce.err[key]);
						$('#help_'+key.replace('err_', '')).hide();
					}
					$('#signup_btn').attr('disabled', false);
				} else {
					$('#loginform').submit();
				}

			}, 'json');

		}
		
	} catch(e) {
		alert(e.message);
	}
	
	return false;
	
}


$(function(){
	
	err =false;
	
	$('#email').focus(function(){
		$('#err_email').empty();
		$('#tip_email').fadeIn();
	});
	$('#email').blur(function(){
		$('#tip_email').fadeOut();
	});
	
	pat = /(\S)+[@]{1}(\S)+[.]{1}(\w)+/;
	
	
	$('#appcode_btn').click(function(){
		
		email_to_user = $.trim($('#email').val());
		
		if (email_to_user=='') {
			$('#err_email').html(betterLang.signup.error.email.empty);
			err = true;
		} else if (!pat.test(email_to_user)) {
			$('#err_email').html(betterLang.signup.error.email.invalid);
			err = true;			
		}
		
		if(!err){
			$('#loginform').submit();
		}
	});
	

});