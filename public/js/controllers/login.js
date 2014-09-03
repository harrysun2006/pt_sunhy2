	$(function() {
		//$('.tabs').tabs();
		if(navigator.userAgent.indexOf("Opera") != -1)
		{
			$('#email').css('font-family', 'sans-serif');			
		}
		$('#loginform').submit(function(){
			$('.err').hide().empty();
			hasError = false;			
			email = $.trim($('#email').val());
			password = $.trim($('#password').val());
			login_type = $.trim($('#login_type').val());
			if (email=='') {
				$('#err_email').html(betterLang.login.plz_input_account).fadeIn();
				hasError = true;
			}			
			if (password=='') {
				$('#err_password').html(betterLang.login.plz_input_password).fadeIn();
				hasError = true;
			}			
			if(login_type!='local' && !Better_isEmail(email)){
				$('#err_email').html(betterLang.login.third_must_email).fadeIn();
				hasError = true;
			}			
			if (!hasError) {
				pat = /(Chrome)/;
		        if ($.browser.safari==true && pat.test(navigator.userAgent.toString())) {
		        	$('#pwd_plain').val('1');	
		        } else {
		        	//$('#password').val(Better_md5($('#password').val()));
		        	$('#password').val($('#password').val());
		        }
			}	        	
	        return !hasError;
		});

		/*$('#email').click(function(){
			if ($(this).val()==betterLang.login.username_tips || $(this).val()==betterLang.login.thirdusername_tips) {
				if($(this).val()==betterLang.login.thirdusername_tips){
					thirdparty = 1;
				} else {
					thirdparty = 0;
				}
				$(this).val('');				
			}   	
		}).blur(function(){
			if ($(this).val()=='') {
				if(thirdparty){
					$(this).val(betterLang.login.thirdusername_tips);
				} else {
					$(this).val(betterLang.login.username_tips);
				}
			}
		});	*/
		/*
		
		$('#loginfromsina').click(function(){
			$('#login_third_guide_list').hide();
			$('#thirdpartyaccount').hide();
			$('#loginthird').hide();
			$('#err_email').hide();
			$('#err_password').hide();
			$('#loginmore').html('');			
			$('#email_title').text(betterLang.login.thirdusername_title);
			$('#email').val(betterLang.login.thirdusername_tips);
			$('#login_type').val('sina');
			$('#password').val('');
			$('#thirdpartylogo').html(betterLang.login.sinauser);	
			$('#thirdpartyaccount').hide();
			$('.login_left').show();
			$('#thirdpartylogo').show();
		});
		*/
		$('#loginfromsohu').click(function(){
			$('#login_third_guide_list').hide();
			$('#thirdpartyaccount').hide();
			$('#loginthird').hide();
			$('#err_email').hide();
			$('#err_password').hide();
			$('#loginmore').html('');	
			$('#email_title').text(betterLang.login.thirdusername_title);
			$('#email').val(betterLang.login.thirdusername_tips);
			$('#login_type').val('sohu');
			$('#password').val('');
			$('#thirdpartylogo').html(betterLang.login.sohuuser);
			
			$('#thirdpartyaccount').hide();
			$('.login_left').show();
			$('#thirdpartylogo').show();
		});
		$('#loginfromkaixin001').click(function(){	
			$('#login_third_guide_list').hide();
			$('#thirdpartyaccount').hide();
			$('#loginthird').hide();
			$('#err_email').hide();
			$('#err_password').hide();
			$('#loginmore').html('');	
			$('#email_title').text(betterLang.login.thirdusername_title);
			$('#email').val(betterLang.login.thirdusername_tips);
			$('#login_type').val('kaixin001');
			$('#password').val('');
			$('#thirdpartylogo').html(betterLang.login.kaixin001user);			
			$('#thirdpartyaccount').hide();
			$('.login_left').show();
			$('#thirdpartylogo').show();
		});
        if (typeof(Better_LoginMsg)!='undefined') {
        	Better_Notify({
        		msg_title: betterLang.login.failed_title,
        		height:240,
        		msg: Better_LoginMsg       		
        	});
        }
        if(typeof(Better_Login_From)!='undefined' && Better_Login_From=='sina'){			
			$('#loginfromsina').trigger('click');			
		} else if(typeof(Better_Login_From)!='undefined' && Better_Login_From=='sohu'){			
			$('#loginfromsohu').trigger('click');			
		} else if(typeof(Better_Login_From)!='undefined' && Better_Login_From=='kaixin001'){			
			$('#loginfromkaixin001').trigger('click');				
		} else{
			$('#login_third_guide_list').show();
			$('#thirdpartyaccount').show();
			$('.login_left').show();
			$('#thirdpartylogo').show();
		}
	});