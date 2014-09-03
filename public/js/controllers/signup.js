/**
 * 注册按钮被点击
 * 
 * @return
 */
var errstr = "<br/><img src='../images/x.png' border='0' class='zoompng' style='margin:5px;vertical-align:middle'>";
var okstr = "<img src='../images/ok.png' border='0' class='zoompng' style='margin:5px;vertical-align:middle'>";
function Better_Signup_Go()
{
	agree = $('#agree').attr('checked') ? '1' : '0';
	
	if (agree) {
		err = false;
		username = $.trim($('#username').val());
		pwd = $.trim($('#password').val());
		repwd = $.trim($('#repassword').val());
		nickname = $.trim($('#nickname').val());
		code = $.trim($('#code').val());
		invitecode = Better_In_Test ? $.trim($('#invitecode').val()) : '';
		$('#signup_form .help').show();
		$('.error').empty();
		$('#btnSubmit').attr('disabled', true);		
		$.post('/signup/check', {
			email: $('#email').val(),
			password: pwd,
			repassword: repwd,
			agree: $('#agree').attr('checked') ? '1' : '0',
			username: username,
			nickname: nickname,
			code: $('#code').val(),
			invitecode : invitecode,
			signupbythird : $('#signupbythird').val(),
			login_type: 'local'
		}, function(ce){	
			$('span[id^="err_"]').html(okstr);			
			if (ce.has_error) {
				for(key in ce.err) {
					$('#'+key).html(errstr + ce.err[key]);
					$('#help_'+key.replace('err_', '')).hide();
				}
				$('#signup_btn').attr('disabled', false);
			} else {				
				Better_Notify_loading({
					msg_title: betterLang.global.signuping
				});				
				$('#signup_form').submit();
			}
	
		}, 'json');
	} else {
		Better_Notify({
			msg: betterLang.signup.error.agreement
		});
	}
	
	return false;

}

function Better_Singup_Validating(id)
{
	$('#'+id).empty().html('<img src="images/checking.gif" />').show();
}

function Better_Singup_Validate_Ok(id)
{
	$('#'+id).empty().hide();
}

function Better_Signup_Validate_Err(id)
{
	$('#'+id).empty().hide();
}

function Better_Signup_validateNickname()
{
	nickname = $('#nickname').val();
	$('#ok_nickname').empty().hide();
	$('#err_nickname').empty().hide();
	
	if (nickname!='' && $.trim(nickname)!='') {
		Better_Singup_Validating('ok_nickname');
		$('#tip_nickname').hide();
		
		$.getJSON('/ajax/validator/nickname', {
			nickname: nickname,
			uid: 0
		}, function(nJson){
			
			switch(nJson.code) {
				case nJson.codes.SUCCESS:
					Better_Singup_Validate_Ok('ok_nickname');
					$('#err_nickname').html(okstr).show();
					break;
				default:
					Better_Signup_Validate_Err('ok_nickname');
					$('#err_nickname').html(errstr+nJson.msg).show();
					break;					
			}
			
		});
	} else {
		Better_Signup_Validate_Err('ok_nickname');
		$('#err_nickname').html(errstr+betterLang.signup.error.nickname.empty).show();
	}
}

function Better_Signup_validateUsername()
{
	username = $('#username').val();
	$('#ok_username').empty().hide();
	$('#err_username').empty().hide();
	
	if (username!='' && $.trim(username)!='') {
		Better_Singup_Validating('ok_username');
		$('#tip_username').hide();
		
		$.getJSON('/ajax/validator/username', {
			username: username,
			uid: betterUser.uid
		}, function(uJson){
			
			switch(uJson.code) {
				case uJson.codes.SUCCESS:
					Better_Singup_Validate_Ok('ok_username');
					$('#err_username').html(okstr).show();
					break;
				default:
					Better_Signup_Validate_Err('ok_username');
					$('#err_username').html(errstr+uJson.msg).show();
					break;					
			}
			
		});
	} else {
		Better_Signup_Validate_Err('ok_username');
		$('#err_username').html(errstr+betterLang.signup.error.username.empty).show();
	}
}

function Better_Signup_validateCode()
{
	code = $('#code').val();
	if (code=='') {
		$('#err_code').html(errstr+betterLang.signup.error.scode).show();
	} else {
		Better_Singup_Validating('err_code');
		$.getJSON('/ajax/validator/code', {
			code: code
		}, function(cJson){
			if (cJson.result=='1') {
				$('#err_code').html(okstr).show();

			} else {
				$('#err_code').html('<br/>'+errstr+betterLang.signup.error.scode_incorrect).show();
			}
		});
	}
}

function Better_Signup_validateEmail()
{
	email = $('#email').val();
	$('#ok_email').empty().hide();
	$('#err_email').empty().hide();
	
	if (email!='' && $.trim(email)!='') {
		pat = /(\S)+[@]{1}(\S)+[.]{1}(\w)+/;
		
		if (pat.test(email)) {
			Better_Singup_Validating('ok_email');
			$('#tip_email').hide();
			
			$.getJSON('/ajax/validator/email', {
				email: email,
				uid: 0
			}, function(eJson){
				
				switch(eJson.code) {
					case eJson.codes.SUCCESS:
						Better_Singup_Validate_Ok('ok_email');
						$('#err_email').html(okstr).show();
						break;
					default:
						Better_Signup_Validate_Err('ok_email');
						$('#err_email').html(errstr+eJson.msg).show();
						break;					
				}
				
			});
		} else {
			$('#tip_email').hide();
			$('#err_email').html(errstr+betterLang.signup.error.email.invalid).show();
		}
	} else {
		Better_Signup_Validate_Err('ok_email');
		$('#err_email').html(errstr+betterLang.signup.error.email.empty).show();		
	}
}

function Better_Signup_validatePassword()
{
	password = $('#password').val();
	$('#ok_password').empty().hide();
	$('#err_password').empty().hide();	
	
	if (password=='') {
		$('#err_password').html(errstr+betterLang.signup.error.password_empty).show();
	} else if (password.length<6 || password.length>20) {
		$('#err_password').html(errstr+betterLang.signup.error.password_length).show();
	} else {
		$('#err_password').html(okstr).show();
	}
}

function Better_Signup_validateRepassword()
{
	repassword = $('#repassword').val();
	
	if (repassword=='') {
		$('#err_repassword').html(errstr+betterLang.signup.error.password_empty).show();
	} else if (repassword!=$('#password').val()) {
		$('#err_repassword').html(errstr+betterLang.signup.error.password_confirm).show();
	} else if (repassword.length<6 || repassword.length>20) {
		$('#err_repassword').html(errstr+betterLang.signup.error.password_length).show();
	} else {
		$('#err_repassword').html(okstr).show();
	}
}


$(function(){
	$('#now_btn').click(function(){
		email = BETTER_REGISTED_EMAIL;
		if (typeof(email)!='undefined') {
			tmp = email.split('@');
			domain = tmp[1];
			
			switch (domain) {
				case '163.com':
				case '126.com':
				case 'yeah.net':
				case 'sohu.com':
				case 'sina.com':
				case 'vip.sina.com':
				case 'sina.cn':
				case 'tom.com':
				case 'chinaren.com':
				case 'qq.com':
				case 'foxmail.com':
					window.open('http://mail.'+domain);
					break;
				case 'gmail.com':
					window.open('http://mail.google.com');
					break;
				case 'msn.com':
				case 'live.com':
				case 'live.cn':
				case 'hotmail.com':
					window.open('http://www.hotmail.com');
					break;
				default:
					flag = domain.split('.');
					if (flag.length>1) {
						window.open('http://www.'+domain);
					} else {
						window.location = '/signup/step2';
					}					
					break;
			}
		} else {
			window.location = '/signup/step2';
		}
	});
	
	$('#switch_scode').click(function(){
		$('#img_code').attr('src', '/Scode?r='+Math.round(Math.random()*1000000));
	});

	
	$('#onkeysignup_btn').click(function(){
		Better_Notify_loading({
			msg_title: betterLang.global.signuping
		});				
		$('#signup_form').submit();
	});
	
	$('#signup_btn').click(function(){
		return Better_Signup_Go();
	});
	
	$('#signup_form input').keypress(function(e){
		Better_TriggerClick(e, $('#btnSubmit'));
	});
	
	$('#btnResend').click(function(){
		email = $.trim($('#email').val());
		err = '';
		$('#btnResend').attr('disabled', true);
		$('#err_email').html('');
		$('#div_resenderr').hide();
		$('#div_resendok').hide();
		$('#resend_loading').html('<img src="images/loading.gif" alt="" />');
		
		if (email=='') {
			$('#err_email').html(betterLang.signup.error.email.empty);
			$('#div_resenderr').show();
			$('#div_resendok').hide();
			$('#btnResend').attr('disabled',false);
			$('#resend_loading').empty();
		} else {
			if (!Better_isEmail(email)) {
				$('#err_email').html(betterLang.signup.error.email.invalid);
				$('#div_resenderr').show();
				$('#div_resendok').hide();
				$('#btnResend').attr('disabled',false);
				$('#resend_loading').empty();
			} else {
				$.ajax({
					type: 'POST',
					url: '/signup/doresend',
					dataType: 'json',
					cache: false,
					async: false,
					data: {
						email: email
					},
					success: function(rce) {
						$('#resend_loading').empty();
						if (rce.err!='') {
							$('#err_email').html(rce.err);
							$('#div_resenderr').show();
							$('#div_resendok').hide();
						} else {
							$('#email').val('');
							$('#resendok').html(rce.result);
							$('#div_resenderr').hide();
							$('#div_resendok').show();
						}
						$('#btnResend').attr('disabled',false);
					}
				});
			}
		}
	});

	$('#resendform').submit(function(){
		$('#btnResend').trigger('click');
		return false;
	});
	$('#email').focus(function(){		
		$('#err_email').empty();
		if ($('#ok_email').html()=='') {
			$('#tip_email').show();
		}
	}).blur(function(){
		$('#tip_email').hide();
		
		Better_Signup_validateEmail();
	}).keypress(function(e){
		if (e.which==13) {
			Better_Signup_validateEmail();
		}
	});
	$('#nickname').focus(function(){
		$('#err_nickname').empty();
		$('#tip_nickname').show();
	}).blur(function(){
		$('#tip_nickname').hide();		
		Better_Signup_validateNickname();		
	});
	
	$('#username').focus(function(){
		$('#err_username').empty();
		$('#tip_username').show();
	}).blur(function(){		
		Better_Signup_validateUsername();		
	});	
	
	$('#password').focus(function(){
		$('#err_password').empty();
		$('#tip_password').show();
	}).blur(function(){
		$('#tip_password').hide();		
		Better_Signup_validatePassword();
	});
	
	$('#repassword').focus(function(){
		$('#err_repassword').empty();
		$('#tip_repassword').show();
	}).blur(function(){
		$('#tip_repassword').hide();		
		Better_Signup_validateRepassword();
	});
	
	
	
	$('#invitecode').focus(function(){
		$('#err_invitecode').empty();
		$('#tip_invitecode').show();
	});
	$('#invitecode').blur(function(){
		$('#tip_invitecode').hide();
	});	
	/*
	$('#code').blur(function(){
		Better_Signup_validateCode();
	});	
	*/
});