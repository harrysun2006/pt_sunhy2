
/**
 * 加载一个找朋友列表
 * @param options
 * @return
 */
function Better_loadFindFriends(options)
{
	id = options.id;
	url = options.url;
	callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};
	posts = typeof(options.posts)=='object' ? options.posts : {};
	btns = typeof(options.btns)=='array' ? options.btns : ['friend', 'follow'];
	
	Better_Table_Loading(id);

	var withRealname = false;
	var isFollowRequest = false;
	
	typeof(posts.withRealname)!='undefined' ? withRealname = posts.withRealname : withRealname = false;
	typeof(posts.isFollowRequest)!='undefined' ? isFollowRequest = posts.isFollowRequest : isFollowRequest = false;

	$.get(url, posts, function(luJson){
		
		//清除Table的loading
		Better_Clear_Table_Loading(id);
		
		code = typeof(luJson.code)!='undefined' ? luJson.code : '';
			
			if (code==2) {
				Better_EmptyResults(id, betterLang.people.invalid_user_pass);
			} else if (code==4) {
				Better_EmptyResults(id, betterLang.people.no_kai_results);
			} else if (code==3) {
				Better_EmptyResults(id, betterLang.people.no_account_contacts);
			} else if (code==5) {
				Better_EmptyResults(id, betterLang.people.server_error);
			}else{
				if (luJson.count==0) {
					Better_EmptyResults(id, betterLang.people.no_contacts);
				}else{
					
					var page = typeof(posts.page)!='undefined' ? posts.page : 0;
					
					tbl = $('#tbl_'+id);
					
					//	是否有异常回调函数
					exceptionCallback = $.isFunction(callbacks.exceptionCallback) ? callbacks.exceptionCallback : function(){};
					
					if (Better_AjaxCheck(luJson, exceptionCallback)) {
						
						var pages = typeof(luJson.pages)!='undefined' ? luJson.pages : 0;
				
						if (luJson.count>0) {
							//	渲染结果行
							var x = 0;
							for(i in luJson.rows) {
								uid = luJson.rows[i].uid;
								gender = luJson.rows[i].gender;
								nickname = luJson.rows[i].nickname;
								
								if(x%3==0){
									tr = $(document.createElement('tr'));
									tr.attr('id', id+'_find_friends_row_'+(parseInt(x/3)+1));
									tbl.append(tr);		
								}
								
								td = $(document.createElement('td'));
								td.css('width','33%');
								td.html('<div class="left" style="width: 64px;"><img width="50" height="50" src="'+luJson.rows[i].avatar_small+'" alt="'+luJson.rows[i].nickname+'" class="left user_avatar"/></div>');
								div = $(document.createElement('div')).addClass('left').css('margin-left', '10px').css('width','94px');
								div.append('<a href="/'+luJson.rows[i].username+'">'+luJson.rows[i].nickname+'</a><br>');
								td.append(div);
								
								
								/*if ($.inArray('follow', btns)>=0 && $.inArray(uid, betterUser.blockedby)<0) {
									//	关注此人的链接
									if ($.inArray(uid, betterUser.blockedby)<0) {
										aFollow = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterUserBtn_'+uid);
										afterUnfollowCallback = (typeof(callbacks.afterUnfollowCallback)!='undefined' && $.isFunction(callbacks.afterUnfollowCallback)) ? callbacks.afterUnfollowCallback : function(){};
										
										if ($.inArray(uid, betterUser.followings)>=0) {
											aFollow.html(betterLang.global.follow.cancel).bind('click', {
												uid:uid, 
												id:'betterUserBtn_',
												gender: gender,
												nickname: nickname,
												afterUnfollowCallback: afterUnfollowCallback
											}, Better_Unfollow);
										} else {
											aFollow.html(betterLang.global.follow.title).bind('click', {
												uid:uid, 
												id:'betterUserBtn_',
												nickname: nickname,
												gender: gender,
												afterUnfollowCallback: afterUnfollowCallback
											}, Better_Follow);					
										}
										div.append(aFollow);
										div.append('<br>');
									}
								}*/
								
								
								if ($.inArray('friend', btns)>=0 && $.inArray(uid, betterUser.blockedby)<0) {
									//	加为好友的链接
									aFriend = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterUserFriendBtn_'+uid);
									if ($.inArray(uid, betterUser.friends)>=0) {
										aFriend.html(betterLang.global.friend.request.remove).bind('click', {
											uid: uid,
											gender: gender,
											nickname: nickname,
											id: 'btnUserFriendBtn_'
										}, Better_Friend_Remove);
									} else {
										aFriend.html(betterLang.global.friend.request.title).bind('click', {
											uid: uid,
											gender: gender,
											id: 'btnUserFriendBtn_'
										}, Better_Friend_Request);									
									}
									div.append(aFriend);
								}
								
								$('tr#'+id+'_find_friends_row_'+(parseInt(x/3)+1)).append(td);
								x++;
							}
					
						} 
						
					} else { // if (Better_AjaxCheck
						$('#tbl_'+id).empty();
						if ($.isFunction(callbacks.errorCallback)) {
							code = typeof(luJson.code)!='undefined' ? luJson.code : '';
							callbacks.errorCallback(code);
						}			
					}
					
				}
			}
			
			//	如果有完成事件的回调函数
			if ($.isFunction(callbacks.completeCallback)) {
				callbacks.completeCallback();
			}
			
	}, 'json');
	
}

/**
 * 加载一个未加入开开的朋友列表
 */
function Better_loadFriendsList(options)
{
	id = options.id;
	url = options.url;
	callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};
	posts = typeof(options.posts)=='object' ? options.posts : {};
	
	Better_Table_Loading(id);
	
	$.get(url, posts, function(luJson){
		//清除Table的loading
		Better_Clear_Table_Loading(id);
		
		code = typeof(luJson.code)!='undefined' ? luJson.code : '';
			
			if (code==2) {
				Better_EmptyResults(id, betterLang.people.invalid_user_pass);
			} else if (code==4) {
				Better_EmptyResults(id, betterLang.people.kai_results);
			} else if (code==3) {
				Better_EmptyResults(id, betterLang.people.no_account_contacts);
			} else if (code==5) {
				Better_EmptyResults(id, betterLang.people.server_error);
			} else{
				if (luJson.count==0) {
					Better_EmptyResults(id, betterLang.people.no_contacts);
				}else{
					
					tbl = $('#tbl_'+id);
					
					//	是否有异常回调函数
					exceptionCallback = $.isFunction(callbacks.exceptionCallback) ? callbacks.exceptionCallback : function(){};
					
					if (Better_AjaxCheck(luJson, exceptionCallback)) {
				
						if (luJson.count>0) {
							tro=$(document.createElement('tr'));
							tdo=$(document.createElement('td'));
							tro.append(tdo);
							tdo.append('<input type="checkbox" id="checkall_'+id+'"/> '+betterLang.people.choose_all);
							tdo.append('<div class="friends_list"><table class="list_table" id="tb_'+id+'"></table></div>');
							tdo.append('<input type="button" id="btnInvite_'+id+'" value="'+betterLang.people.button.inviteaddfriends+'" class="btn" style="margin-top: 16px; width: 100px;"/>');
							tbl.append(tro);
							
							th = $(document.createElement('tr'));
							tdd1 = $(document.createElement('td'));
							tdd1.html(betterLang.people.name);
							tdd2 = $(document.createElement('td'));
							tdd2.html('Email');
							tdd3 = $(document.createElement('td'));
							tdd3.html('');
							th.append(tdd3);
							
							if (typeof(posts.is_msn)!='undefined' && posts.is_msn=='1') {
							} else {
								th.append(tdd1);
							}
							
							th.append(tdd2);
							$('#tb_'+id).append(th);
							
							//	渲染结果行
							var I = 0;
							for(i in luJson.rows) {
								tr = $(document.createElement('tr'));
								td1 = $(document.createElement('td'));
								td1.html(luJson.rows[i].name ? luJson.rows[i].name : betterLang.people.no+'  ');
								td2 = $(document.createElement('td'));
								td2.html(luJson.rows[i].email ? luJson.rows[i].email : betterLang.people.no);
								td3 = $(document.createElement('td'));
								td3.html('<input type="checkbox" value="'+luJson.rows[i].email+'" />  ');
								tr.append(td3);
								if (typeof(posts.is_msn)!='undefined' && posts.is_msn=='1') {
								} else {
									tr.append(td1);
								}								
								tr.append(td2);
								$('#tb_'+id).append(tr);
							
							}
							
							//邀请按钮
							$('#btnInvite_'+id).bind('click',{},function(event){
								$(this).attr('disabled', true);
								var emails=new Array();
								var j=0;
								$('#tb_'+id+' input[type="checkbox"]').each(function(){
									if($(this).attr('checked')){
										emails[j++]=$(this).val();
									}
								});
								
								if (j==0) {
									alert(betterLang.people.invite.no_choose);
									$(this).attr('disabled', false);
									return false;
								} else {
									$.post('/ajax/service/sendinviteemail', {
										emails: emails.join('|')
									}, function(diJson){
										if (Better_AjaxCheck(diJson)) {
											if (diJson.has_err) {
												Better_Notify(betterLang.people.invite_failed);
											} else {
												Better_Notify(betterLang.people.invite_success);
											}
										}
										
									}, 'json');
									$(this).attr('disabled', false);
								}
								
							});
							
							
							//全选事件
							 $('#checkall_'+id).click(function(){
								 if($(this).attr('checked')==true){
									 $('#tb_'+id+' input[type="checkbox"]').attr('checked', true);
								 }else{
									 $('#tb_'+id+' input[type="checkbox"]').attr('checked', false);
								 }
							 });
							 
							 
						} 
				}else { // if (Better_AjaxCheck
					$('#tbl_'+id).empty();
					if ($.isFunction(callbacks.errorCallback)) {
						code = typeof(luJson.code)!='undefined' ? luJson.code : '';
						callbacks.errorCallback(code);
					}			
				}	
			  }
			}

			//	如果有完成事件的回调函数
			if ($.isFunction(callbacks.completeCallback)) {
				callbacks.completeCallback();
			}
			
				
	}, 'json');
		
}
