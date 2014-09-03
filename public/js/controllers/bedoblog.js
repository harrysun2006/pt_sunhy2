


$(function(){
	$('#commentShowbtn').click(function(){
		$('#commentShowbtn').hide();
		$('#commentHiddenbtn').show();
		$('#blogComment').hide();
	});
	
	$('#commentHiddenbtn').click(function(){
		$('#commentHiddenbtn').hide();
		$('#commentShowbtn').show();
		$('#blogComment').show();
	});
	
	//$('#commentbtn').click(function(){
		//alert('here');
//		$('#err_jid').html('贝多号不能为空');//betterLang.import.err.emptyjid
//		$('#err_password').html('密码不能为空');//betterLang.import.err.emptypassword
	//}
});