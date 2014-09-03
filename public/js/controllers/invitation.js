/*
 * 发送邮件邀请好友加入k.ai
 */
function copyToClipboard(){
	var otext = document.getElementById('friend_email').value; 
	var txt = otext;
	  if(window.clipboardData) {   
	         window.clipboardData.clearData();   
	         window.clipboardData.setData('text',otext); 
	     } else if(navigator.userAgent.indexOf("Opera") != -1) {   
	    	 alert(betterLang.opera.copy_info);   
	    	 return;
	     } else if (window.netscape) {   
	          try {   
	               netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");   
	          } catch (e) { 	        	  
	        	  alert(betterLang.firefox.copy_info); 
	        	  return;
	          }   
	          var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);   
	          if (!clip)   
	               return;   
	          var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);   
	          if (!trans)   
	               return;   
	          trans.addDataFlavor('text/unicode');   
	          var str = new Object();   
	          var len = new Object();   
	          var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);   
	          var copytext = txt;   
	          str.data = copytext;   
	          trans.setTransferData("text/unicode",str,copytext.length*2);   
	          var clipid = Components.interfaces.nsIClipboard;   
	          if (!clip)   
	               return false;   
	          clip.setData(trans,null,clipid.kGlobalClipboard);   
	     }  
	  	alert(betterLang.copy_success);
} 



function Better_Invitation_SendMail_Go()
{
	
	err = false;
	var emailnum = 0;
	var namenum = 0;
	emailnum = 0;
	//emailArr = new Array(10);
	tempemail = $.trim($('#invitation_email').val()).toString();
	tempemail = tempemail.replace(/\,/g, ";");
	tempemail = tempemail.replace(/\，/g, ";");
	tempemail = tempemail.replace(/\；/g, ";");
	tempemail = tempemail.replace(/\n/g, ";");
	tempemail = tempemail.replace(/\s/g, ";");
	tempemail = tempemail.replace(/\;;/g, ";");
	emailArr = tempemail.split(";");
	pat = /(\S)+[@]{1}(\S)+[.]{1}(\w)+/;
	j = 0;
	newmaillist = new Array();
	//newfriendnamelist = new Array();
	newfriendnamelist = new Array();
	for(i=0;i<emailArr.length;i++) {		
		if (emailArr[i]!='' && pat.test(emailArr[i])) {
			newmaillist[j] = emailArr[i];
			tempfriendname = emailArr[i].toString().split("@");			
			newfriendnamelist[j]=tempfriendname[0];
			j++;
		}
	}	
	if(newmaillist.length==0 || emailArr.length !=j){
		err = true;			
		$('#errinfo').html(betterLang.invitation.error.emaillist).show();
	} else{
		mailliststr = newmaillist.join("{*}");
		friendnamestr = newfriendnamelist.join("{*}");
		$('#errinfo').empty().hide();
		
		$.post('/invitation/dosendmail', {
			newmaillist: mailliststr,
			newfriendnamelist : friendnamestr
		}, function(rjson){
			Better_Notify_clear();
			x = rjson.exists;			
			if (rjson.error==1) {
				hadregeemail = x.join("<br/>");
				var notifyheigth = 220+10*x.length;				
				errorMsg = hadregeemail+'<br/>'+ betterLang.people.all_registered;
				Better_Notify({
					height :notifyheigth,
					msg: errorMsg					
				});						
			} else {
				errorMsg =betterLang.people.invite_success;
				Better_Notify({
					msg: errorMsg
				});	
			}
		}, 'json');
		/*
		$('#newmaillist').val(newmaillist);
		$('#newfriendnamelist').val(newfriendnamelist);
		$('#invitation_sendmail').submit();
		*/
	}	
	return false;	
}


$(function() {	
	$('#btnFindBetter').click(function(){
		$('#errFindBetter').hide().empty();
		$(this).attr('disabled', true);
		
		uname = $.trim($('#better_name').val());
		if (uname!='') {

			$('#better_result').show();
			
			$('#tbl_findBetter').empty();
			
			Better_loadFindFriends({
				id: 'findBetter',
				url: '/ajax/user/search',
				posts: {
					keyword: uname,
					page: 1,
					withRealname: true
				},
				callbacks: {
					errorCallback: function(){},
					emptyCallback: function(){
						Better_EmptyResults('findBetter', betterLang.people.better.no_user);
					}
				},
				btns: ['friend']
			});

			$('#btnFindBetter').attr('disabled', false);
		} else {
			$('#errFindBetter').html(betterLang.people.better.invalid_word).fadeIn();
			$(this).attr('disabled', false);
		}
	});
	
	/**
	 *通过发送邮件方式邀请自己的好友加入k.ai
	 */
	$('#btnSendMail').click(function(){		
		return Better_Invitation_SendMail_Go();
	});

	/**
	 *	找MSN联系人
	 */
	 $('#btnFindMsn').click(function(){
		 $('#tbl_findMsn').empty();
		 $('#tbl_revertMsn').empty();
		$('#errFindMsn').hide().empty();
		$(this).attr('disabled',true);
		
		email = $.trim($('#msn_user').val());
		password = $('#msn_pass').val();
		
		if (!Better_isEmail(email)) {
			$('#errFindMsn').html(betterLang.people.msn.invalid_account).fadeIn();
			$(this).attr('disabled', false);
		} else if (password=='') {
			$('#errFindMsn').html(betterLang.people.msn.invalid_password).fadeIn();
			$(this).attr('disabled', false);
		} else {
			var tbl = $('#tbl_findMsn');
			tbl.empty();
			$('#tbl_revertMsn').empty();

			$('#msn_result').show();				
			
			Better_loadFindFriends({
				id: 'findMsn',
				url: '/ajax/service/msnfriends',
				posts: {
					msn: email,
					password: password
				},
				callbacks: {
					errorCallback: function(){},
					emptyCallback: function(){
						Better_EmptyResults('findMsn', betterLang.people.better.no_user);
					},
					completeCallback: function(){
						
						var tb = $('#tbl_revertMsn');
						tb.empty();
						
						Better_loadFriendsList({
							id: 'revertMsn',
							url: '/ajax/service/invitemsnfriends',
							posts: {
								msn: email,
								password: password,
								use_last_cache: 1,
								is_msn: 1
							},
							callbacks: {
								errorCallback: function(){},
								emptyCallback: function(){
									Better_EmptyResults('revertMsn', betterLang.people.better.no_user);
								}
							}
						});
					}
				}
			});
			
			/*Better_loadFriendsList({
				id: 'revertMsn',
				url: '/ajax/service/msnrevertfriends',
				posts: {
					msn: email,
					password: password
				},
				callbacks: {
					errorCallback: function(){},
					emptyCallback: function(){
						Better_EmptyResults('revertMsn', betterLang.people.better.no_user);
					}
				}
			});*/
			
			$(this).attr('disabled', false);
		}
	 });
	 
	 /**
	  * 找Email联系人
	  */
	 $('#btnFindEmail').click(function(){
		 	$('#tbl_findEmail').empty();	
		 	$('#tbl_revertEmail').empty();
			$('#errFindEmail').hide().empty();
			$(this).attr('disabled', true);
			
			username = $.trim($('#email_user').val());
			domain = $.trim($('#email_domain').val());
			password = $('#email_pass').val();

			if (username=='' || domain=='') {
				$('#errFindEmail').html(betterLang.people.email.invalid_email).fadeIn();
				$(this).attr('disabled', false);
			} else if (password=='') {
				$('#errFindEmail').html(betterLang.people.email.invalid_password).fadeIn();
				$(this).attr('disabled', false);
			} else {
				var tbl = $('#tbl_findEmail');
				tbl.empty();
				$('#tbl_revertEmail').empty();
				$('#email_result').show();				
				
				Better_loadFindFriends({
					id: 'findEmail',
					url: '/ajax/service/emailcontacts',
					posts: {
						username: username,
						password: password,
						domain: domain
					},
					callbacks: {
						errorCallback: function(){},
						emptyCallback: function(){
							Better_EmptyResults('findEmail', betterLang.people.better.no_user);
						},
						completeCallback: function(){
							
							$('#tbl_revertEmail').empty();
							 
							Better_loadFriendsList({
								id: 'revertEmail',
								url: '/ajax/service/inviteemailfriends',
								posts: {
									username: username,
									password: password,
									domain: domain,
									use_last_cache: 1
								},
								callbacks: {
									errorCallback: function(){},
									emptyCallback: function(){
										Better_EmptyResults('revertEmail', betterLang.people.better.no_user);
									}
								}
							});
						}
					}
				});
				$(this).attr('disabled', false);
			}
	 });
	 
	 //	找better用户回车事件
	 $('#better_name').keypress(function(e){ 
		 return Better_TriggerClick(e, $('#btnFindBetter'));
	 });
	 
	 //	找MSN用户回车事件
	 $('#find_msn input[type="text"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindMsn'));
	 });
	 $('#find_msn input[type="password"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindMsn'));
	 });
	 
	 //	找Email联系人回车事件
	 $('#find_email input[type="text"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindEmail'));
	 });
	
	 $('#find_email input[type="password"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindEmail'));
	 });
	 
	 
	 //附近的人  加好友 链接
	 $('a.add_friend').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 Better_Friend_Request({data:{uid: uid, gender: gender, id: 'btnUserFriendBtn_'}, currentTarget: {id: id}});
	 });
	 
	 
	 //附近的人  删好友 链接
	 $('a.remove_friend').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 nickname=tmpId[3];
		 Better_Friend_Remove({data:{uid: uid, gender: gender, id: 'btnUserFriendBtn_', nickname: nickname}, currentTarget: {id: id}});
	 });
	 
	 
	 //加关注
	/* $('a.add_follow').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 nickname=tmpId[3];
		 Better_Follow({data:{uid: uid, gender: gender, id: 'betterUserBtn_', nickname: nickname}, currentTarget: {id: id}});
	 });*/
	 
	 //删关注
	/* $('a.remove_follow').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 nickname=tmpId[3];
		 Better_Unfollow({data:{uid: uid, gender: gender, id: 'betterUserBtn_', nickname: nickname}, currentTarget: {id: id}});
	 });*/
	 
	 
	 $('#addressbook_help').click(function(){
		 $('#clientbook_howtodo').slideToggle(1000);
	 });
	 
	 
	 //寻找SNS好友
	 $('#snss a').click(function(){
		 var type = $(this).attr('type');
		 var title='';
		 var domain = 'sina.com';
		 var binded = $(this).attr('binded');
		 var user = pass = '';
		 
		 $('#sns_bind').attr('checked', false);
		 
		 if(binded=='true'){
			 $('#sns_form').hide();
			 
			 switch (type){
			 	case 'sina':
			 		user = username_sina;
			 		pass = pass_sina;
			 		domain = 'sina.com';
			 		break;
			 	case 'kaixin':
			 		user = username_kaixin;
			 		pass = pass_kaixin;
			 		domain = 'kaixin001.com';
			 		break;
			 	case 'fanfou':
			 		user = username_fanfou;
			 		pass = pass_fanfou;
			 		domain = 'fanfou.com';
			 		break;
			 	case 'msn':
			 		user = username_msn;
			 		pass = pass_msn;
			 		domain = 'msn.com';
			 		break;
			 	default:
			 		break;
			 }
			 $('#sns_user').val(user);
			 $('#sns_pass').val(pass);
			 $('#sns_type').val(domain);
			 $('#btnFindSns').trigger('click');
		 }else{
			 switch (type){
			 	case 'sina':
			 		title = '新浪微博';
			 		domain = 'sina.com';
			 		break;
			 	case 'kaixin':
			 		title = '开心001';
			 		domain = 'kaixin001.com';
			 		break;
			 	case 'fanfou':
			 		title = '饭否';
			 		domain = 'fanfou.com';
			 		break;
			 	case 'msn':
			 		title = 'MSN';
			 		domain = 'msn.com';
			 		break;			 		
			 	default:
			 		break;
			 }
			 $('#sns_title').text(title+'账号：');
			 $('#sns_text').text(title);
			 $('#sns_type').val(domain);
			 $('#sns_form').show();
			 $('#sns_result').hide();
		 }
		 
	 });
	 
	 /**
	  * 找sns联系人
	  */
	 $('#btnFindSns').click(function(){
		 	$('#tbl_findSns').empty();	
			$('#errFindSns').hide().empty();
			$(this).attr('disabled', true);
			
			var username = $.trim($('#sns_user').val());
			var domain = $.trim($('#sns_type').val());
			var password = $('#sns_pass').val();

			if (username=='' || domain=='') {
				$('#errFindSns').html('用户名不能为空').fadeIn();
				$(this).attr('disabled', false);
			} else if (password=='') {
				$('#errFindSns').html('密码不能为空').fadeIn();
				$(this).attr('disabled', false);
			} else {
				var tbl = $('#tbl_findSns');
				tbl.empty();
				$('#sns_result').show();				
				
				Better_loadFindFriends({
					id: 'findSns',
					url: '/ajax/service/snscontacts',
					posts: {
						username: username,
						password: password,
						domain: domain,
						bind: $('#sns_bind').attr('checked')
					},
					callbacks: {
						errorCallback: function(){},
						emptyCallback: function(){
							Better_EmptyResults('findSns', betterLang.people.better.no_user);
						},
						completeCallback: function(){}
					}
				});
				$(this).attr('disabled', false);
			}
	 });
});