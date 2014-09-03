/**
 * 邮件设置
 * 
 */
$(function(){
	$('#btnEmail').click(function(){
		email_person = $('#email_person').attr('checked') ? 1 : 0;
		email_community = $('#email_community').attr('checked') ? 1 : 0;
		email_product = $('#email_product').attr('checked') ? 1 : 0;
		
		$.post('/setting/update', {
			todo: 'email',
			email_person: email_person,
			email_community: email_community,
			email_product: email_product
		}, function(privJson){
			$('#set_email_success').fadeIn();
		}, 'json');
	});
});