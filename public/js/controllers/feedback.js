
$(function() {
	
	$('#btnSubmitFeedback').click(function(){
		
		var type=$('#feedback_type').val();
		var content=$.trim($('#feedback_content').val());
		var contact=$.trim($('#feedback_contact').val());
		var uid = $.trim($('#feedback_uid').val());
		var url='/feedback/submit';
		
		if(type==''){
			Better_Notify(betterLang.feedback.err.feedbacktype.empty);
		}
		else if(content==''){
			Better_Notify(betterLang.feedback.err.content.empty);
		}else if(contact==''){
			Better_Notify(betterLang.feedback.err.contact.empty);
		}else{
			Better_Notify({msg: betterLang.global.action.please_wait});
			$.post(url,{
				type: type,
				content: content,
				contact: contact,
				uid: uid
			}, function(feedbackJson){
				if(feedbackJson.has_err==0){
					Better_Notify({msg: betterLang.feedback.thankyou, 
						closeCallback: function(){
							window.location.href=BASE_URL+'/home';	
					}
					});
				}
				else{
					Better_Notify({msg: feedbackJson.has_err});
				}
				
			}, 'json');
		}
		
		return false;
	});
	
	
});