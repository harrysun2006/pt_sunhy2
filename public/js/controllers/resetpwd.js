function better_resetPwd()
{
	email = $.trim($('#email').val());
	pat = /(\S)+[@]{1}(\S)+[.]{1}(\w)+/;

	$('#err_email').html('');
	
	if (!pat.exec(email)) {
		Better_Notify({
			msg: betterLang.resetpwd.invalid_email
		});
	} else {
		Better_Notify_loading();
		
		$.post('/resetpwd/do', {
			email: $.trim($('#email').val())
		}, function(rjson){
			Better_Notify_clear();
			
			Better_Notify({
				msg: rjson.err
			});			
			
			$('#email').val('');
		}, 'json');

	}
}


$(function(){

	$('#btnSubmit').click(function(){
		better_resetPwd();
	});

	$('#loginform').submit(function(){
		$('#btnSubmit').trigger('click');
		return false;
	});

});