/**
 * 注册按钮被点击
 * 
 * @return
 */
function Better_Signup_Go()
{
	err = false;
	username = $.trim($('#username').val());
	pwd = $.trim($('#password').val());
	repwd = $.trim($('#repassword').val());
	nickname = $.trim($('#nickname').val());
	code = $.trim($('#code').val());

	$('#loginform .help').show();
	$('.error').empty();
    var addstr = "<img src='../images/x.png' border='0'>&nbsp;";
    var okstr = "<img src='../images/ok.png' border='0'>&nbsp;";
    
	$('#btnSubmit').attr('disabled', true);
	
	$.post('/register/check', {
		email: $('#email').val(),
		password: pwd,
		repassword: repwd,
		agree: $('#agree').attr('checked') ? '1' : '0',
		username: username,
		nickname: nickname,
		code: $('#code').val()
	}, function(ce){

		$('span[id^="err_"]').html(okstr);
		
		if (ce.has_error) {
			for(key in ce.err) {
				$('#'+key).html(addstr + ce.err[key]);
				$('#help_'+key.replace('err_', '')).hide();
			}
			$('#signup_btn').attr('disabled', false);
		} else {
			Better_Notify_loading({
				msg_title: betterLang.global.signuping
			});
			$('#loginform').submit();
		}

	}, 'json');
	return false;
	
}


$(function(){
    
	$('#switch_scode').click(function(){
		$('#img_code').attr('src', '/Scode?r='+Math.round(Math.random()*10000));
	});

	$('#signup_btn').click(function(){
		return Better_Signup_Go();
	});
	
	$('#loginform input').keypress(function(e){
		Better_TriggerClick(e, $('#btnSubmit'));
	});

	
	
	$('#username').focus(function(){
		$('#err_username').empty();
		$('#tip_username').fadeIn();
	});
	$('#username').blur(function(){
		$('#tip_username').fadeOut();
	});
	
	$('#nickname').focus(function(){
		$('#err_nickname').empty();
		$('#tip_nickname').fadeIn();
	});
	$('#nickname').blur(function(){
		$('#tip_nickname').fadeOut();
	});
	
	$('#password').focus(function(){
		$('#err_password').empty();
		$('#tip_password').fadeIn();
	});
	$('#password').blur(function(){
		$('#tip_password').fadeOut();
	});
	
	$('#repassword').focus(function(){
		$('#err_repassword').empty();
		$('#tip_repassword').fadeIn();
	});
	$('#repassword').blur(function(){
		$('#tip_repassword').fadeOut();
	});
	
	$('#email').focus(function(){
		$('#err_email').empty();
		$('#tip_email').fadeIn();
	});
	$('#email').blur(function(){
		$('#tip_email').fadeOut();
	});

});