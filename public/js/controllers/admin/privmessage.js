$(function(){
	
	$("#msg_form").submit(function(){
		
		msg_content =$.trim( $('#msg_content').val());
		
		var i=0;
		$('#whitelist input[type="checkbox"]').each(function(){
			if($(this).attr('checked')){
				i++;
			}
		});
		
		if(i==0){
			alert('请输入收信人');
			return false;
		}
		
		if(msg_content.length==0){
			alert('请输入内容');
			return false;
		}
		
		
	}); 

});