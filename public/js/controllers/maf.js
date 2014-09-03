var BMAF_INITED = false;
function Better_MafFilterStatus()
{
	len = Better_GetPostLength();
	slen = betterLang.maf.postmessagemaxlength - len;
	if (len>slen) {
		$('#txtCount').removeClass('green').addClass('red');
	} else if (len<Better_PostMessageMinLength && len>0) {
		$('#txtCount').removeClass('green').addClass('red');
	} else {
		$('#txtCount').removeClass('red').addClass('green');
	}
	
	$('#txtCount').html(slen);
}
function Better_Maf_Enableshout(){	
    $('#havegetcard').unbind('click').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'height' : 189,
		'width' : 696,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){    	
				$('#fancybox-wrap, #fancybox-outer').css('height', '219px');				
			},
		'onClosed': function(){
				
			}
	});
}
function Better_Maf_Hadcard(){	
    $('#ahadcard').unbind('click').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'height' : 189,
		'width' : 696,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){    	
				$('#fancybox-wrap, #fancybox-outer').css('height', '185px');	

				if($.browser.msie==true){
					$('#hasflash').hide();
				}
			},
		'onShow': function(){
			},
		'onClosed': function(){
				if($.browser.msie==true){
					$('#hasflash').show();
				}				
			}
	});
}
function Better_Maf_Cannotcard(){
    $('#acannotcard').unbind('click').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'height' : 289,
		'width' : 696,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){    	
				$('#fancybox-wrap, #fancybox-outer').css('height', '260px');				

				if($.browser.msie==true){
					$('#hasflash').hide();
				}				
			},
		'onShow': function(){
		},
		'onClosed': function(){

			if($.browser.msie==true){
				$('#hasflash').show();
			}				
			}
	});
}
function Better_Maf_Mustlogin(){
    $('#amustlogin').unbind('click').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'height' : 289,
		'width' : 696,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){    	
				$('#fancybox-wrap, #fancybox-outer').css('height', '169px');				

				if($.browser.msie==true){
					$('#hasflash').hide();
				}				
			},
		'onShow': function(){
		},
		'onClosed': function(){

			if($.browser.msie==true){
				$('#hasflash').show();
			}				
			}
	});
}

function Better_Maf_Hadsendoff(){
    $('#ahadsendoff').unbind('click').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'height' : 289,
		'width' : 696,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){    	
				$('#fancybox-wrap, #fancybox-outer').css('height', '169px');				

				if($.browser.msie==true){
					$('#hasflash').hide();
				}				
			},
		'onShow': function(){
		},
		'onClosed': function(){

			if($.browser.msie==true){
				$('#hasflash').show();
			}				
			}
	});
}
function Better_Maf_Shout(){	
	$.post('/ajax/blog/post', {
		message: betterLang.maf.shout_mess, 
		upbid: '',
		attach: '',
		priv: 'public',
		lon: '',
		lat: '',
		range: '',
		poi_id: '',
		type: 'normal',
		need_sync: 1,
		is_maf: 1
		}, function(json){
			
			if (Better_AjaxCheck(json)) {							
				if (json.code=='success') {

					$('#cancel_shout').trigger('click');//关闭dialog
					
					if (Better_Shout_Result_Title=='') {
						if (Better_Shout_Type=='normal') {
							
							if (BETTER_HOME_LAST_STATUS_TIPS) {
								$('#home_last_status_tips').text(status_text);
							}
							
							if (upbid!=0) {
								success_notify = betterLang.global.rt.success;
							} else {
								success_notify = betterLang.global.shout.success;
							}
							
							if(typeof(personpage)=='undefined'){
								$('a[href="#followings"]').attr('disabled', false);
								window.scrollTo(0, 0);
								$('a[href="#followings"]').trigger('click');
							}
							
						} else if (Better_Shout_Type=='tips') {
							success_notify = betterLang.global.tips.success;
							
							$('a[href="#tips"]').attr('disabled', false);
							$('a[href="#tips"]').trigger('click');
						} else {
							success_notify = betterLang.global.post.success;
						}
					} else {
						success_notify = Better_Shout_Result_Title;
					}

					Better_Notify({
						msg: success_notify+' '+Better_parseAchievement(json, Better_Shout_Type=='normal' ? betterLang.global.this_shout : betterLang.global.this_tips),
						close_timer: 2
					});

				} else if (json.code=='need_check') {
					Better_ResetPostForm();
					
					Better_Notify({
						msg: betterLang.post.need_check
					});					
				} else if (json.code=='post_too_fast') {
					Better_Notify({
						msg: betterLang.antispam.too_fast
					});										
				} else if (json.code=='post_same_content') {
					Better_Notify({
						msg: betterLang.antispam.shout
					});										
				} else if (json.code=='you_r_muted') {
					Better_ResetPostForm();
					
					Better_Notify({
						msg: betterLang.post.forbidden
					});								
				} else if (json.code=='words_r_banned') {
					Better_ResetPostForm();
					
					Better_Notify({
						msg: betterLang.post.ban_words
					});
				} else if (json.code=='too_short') {
					Better_Notify({
						msg: betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength)
					});
				} else {
					Better_Notify({
						msg: 'Failed'
					});										
				}							
				
				$('#privacy_public').attr('checked', true);
			}
	}, 'json');
}
$(function() {
		Better_Maf_Enableshout();
		Better_Maf_Hadcard();
		Better_Maf_Cannotcard();
		Better_Maf_Mustlogin();
		Better_Maf_Hadsendoff();
		Better_MafFilterStatus();
		$('#status_text').load(function(){
			this.selectionStart = 0;
			this.selectionEnd = 0;
		}).keydown(function(e){
			try {
				if (e.which==37) {
					this.setSelectionRange(this.selectionStart-1, this.selectionStart-1)
				} else if (e.which==39) {
					this.setSelectionRange(this.selectionStart+1, this.selectionStart+1)
				}
			} catch (se) {
				len = getCaret(this);	
				if (e.which==37) {
			        var range = this.createTextRange();
			        range.move("character", len-1);
			        range.select(); 
				} else if (e.which==39) {
			        var range = this.createTextRange();
			        range.move("character", len+1);
			        range.select(); 				
				}
			}
		}).keyup(Better_MafFilterStatus).mousedown(Better_MafFilterStatus).keypress(function(e){
			//	快捷键提交
			if ($.browser.msie && ((e.ctrlKey && e.which==10) || (e.altKey && e.which==10) || (e.shiftKey && e.which==10))) {
				$('#login_btn').trigger('click');
				return false;
			} else if ($.browser.mozilla && ((e.ctrlKey && e.which==13 || e.which==10) || (e.altKey && e.which==13))) {
				$('#login_btn').trigger('click');
				return false;
			} else {
				return true;
			}
		});
		$('#email').click(function(){
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
		});
		
		
		//$('.tabs').tabs();
        if (typeof(Better_LoginMsg)!='undefined') {
        	Better_Notify({
        		msg_title: betterLang.login.failed_title,
        		height:240,
        		msg: Better_LoginMsg       		
        	});
        }        
        $('a#_mustlogin').click(function(){
        	$('a#amustlogin').trigger('click');
        });
        $('a#_getcard').click(function(){
        	switch (mafcardnum){
				case 5:						
					$('a#acannotcard').trigger('click');
					//$('a#havegetcard').trigger('click');
					/*
					Better_Notify({
						height:220,
						msg: betterLang.maf.cannot_card						
					});	
					*/		
				 break;
				case 3:	
					$('a#ahadcard').trigger('click');					
					/*
					Better_Notify({
						msg: betterLang.maf.had_card					
					});	
					*/		
				 break; 
        	}        	
    		return false;
    	});      
        $('a#sendcard').click(function(){   
          	$.post('/ajax/mafcard/index', {
          		receive_name: cardInfo.receive_name,
          		receive_address:cardInfo.receive_address,
          		receive_zipcode:cardInfo.receive_zipcode,
          		post_name:cardInfo.post_name,
          		post_address:cardInfo.post_address,
          		post_zipcode:cardInfo.post_zipcode,
          		message:cardInfo.message,
          		uid:cardInfo.uid
          	} , function(json){          		    	
				codes = json.has_err;
				if(codes==0 || codes==4){
					$('a#havegetcard').trigger('click');
				} else if(codes==1){
					Better_Notify({
						msg: betterLang.maf.theend						
					});	
				} else if(codes==2){
					$('a#ahadsendoff').trigger('click');		
				} else if(codes==3){
					$('a#ahadcard').trigger('click');	
				} else if(codes==5){
					$('a#acannotcard').trigger('click');
				}
    		}, 'json');
    		return false;
    	});
        
        $('#maf_colse').click(function(){
        	
        	$.fancybox.close();        	
        	//window.location = '/maf';
        });
        
        $('#maf_colse2').click(function(){
        	
        	$.fancybox.close();        	
        	//window.location = '/maf';
        });  
		 $('#maf_colse3').click(function(){
		        	
		        	$.fancybox.close();        	
		        //	window.location = '/maf';
		        }); 
		 $('#maf_colse4').click(function(){
		 	
		 	$.fancybox.close();        	
		 	//window.location = '/maf';
		 }); 
        
        $('#maf_end').click(function(){
        	if($('#send_maf_shout').attr('checked')){
				Better_Maf_Shout();			
			}
        	$.fancybox.close();        	
        	window.location = '/maf';
        	return false;
        });
	});