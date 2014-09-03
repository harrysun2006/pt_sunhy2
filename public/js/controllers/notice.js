/**
 * 收到的私信
 * 
 * @param page
 * @return
 */
function Better_Messages_Received(page)
{
	page = page ? page : 1;
	url = '/ajax/messages/received';
	tblKey = 'received';
	
	Better_Pager({
		key: tblKey,
		callback: Better_Messages_Received
	});	
	
	Better_Messages_loadMessages(tblKey, url ,{
		page: page,
		withReplyLink: true,
		isReceived: true,
		widthReadIcon: true
	}, {
		completeCallback: function(){
			$('#msg_pager_'+tblKey).show();
		},
		emptyCallback: function(){
			Better_EmptyResults(tblKey, betterLang.messages.no_message_received);
		},
		deleteCallback: Better_Messages_DeleteReceived
	});	
}
/**
 * 加载私信
 * 
 * @param id
 * @param url
 * @param posts
 * @param callbacks
 * @return
 */
function Better_Messages_loadMessages(id, url, posts, callbacks)
{
	callbacks11 = typeof(callbacks)=='object' ? callbacks : null;
	id11 = id;
	
	tbl = $('#tbl_'+id);
	withReplyLink = typeof(posts.withReplyLink)!='undefined' ? posts.withReplyLink : false;
	page = posts.page;
	isReceived = typeof(posts.isReceived)!='undefined' && posts.isReceived==true ? true : false;
	widthReadIcon = typeof(posts.widthReadIcon)!='undefined' ? posts.widthReadIcon : false;
	
	Better_Table_Loading(id);
	Better_WaitForLastAjax();
	Better_ajaxInProcess = false;
	if (id == 'received' && page == 1 && needRef_msg != true ) {
		_callbackM(_msg_page1);
		needRef_msg = true;
		//window.location = Better_This_Script + '#' + id;
	} else {
		Better_ajaxInProcess = true;
		$.get(url, posts, _callbackM, 'json');	
	}
	
	
}
/**
 * 回调函数
 * @param msgJson
 * @return
 */
function _callbackM(msgJson) {
	var id = id11;
	var callbacks= callbacks11;
	if (Better_AjaxCheck(msgJson)) {
		if($.isFunction(callbacks.beforeCallback)){
			callbacks.beforeCallback();
		}
		
		try {
			Better_Pager_setPages(id, msgJson.pages);
			
			Better_Clear_Table_Loading(id);
			nowPage = typeof(msgJson.page)!='undefined' ? msgJson.page : page;
			$('#'+id+'_count').text(msgJson.count);
			
			// 渲染结果行
			if (msgJson.rows!=null) {
				for(i=0;i<msgJson.rows.length;i++) {
					
					msg_id = msgJson.rows[i].msg_id;
					muid = typeof(msgJson.rows[i].from_uid)!='undefined' ? msgJson.rows[i].from_uid : msgJson.rows[i].to_uid;
					msg_id_key = msg_id+'_'+msgJson.rows[i].uid+'_'+msgJson.rows[i].nickname+'_'+muid+'_'+msgJson.rows[i].gender;
					tr = $(document.createElement('tr'));
					tr.attr('id', 'msgRow_'+msg_id_key);
					tr.attr('msg_id', msg_id);
					tr.attr('uid', msgJson.rows[i].uid);
					tr.attr('nickname', msgJson.rows[i].nickname);
					tr.attr('muid', muid);
					tr.attr('gender', msgJson.rows[i].gender);
					if(msgJson.rows[i].isinvited){
						tr.attr('poiid', msgJson.rows[i].invitedpoi.poi_id);
						tr.attr('poiname',msgJson.rows[i].invitedpoi.poi_name);
						tr.attr('isinvited','1');
						if(msgJson.rows[i].isreply!=0){
							tr.attr('isreplied','1');	
						}else{
							tr.attr('isreplied','0');
						}
					}
					tr.addClass('msgRow');
					if (msgJson.rows[i].readed==0) {
						tr.addClass('notReaded');
						tr.attr('notreaded','1');
					}					
					if(widthReadIcon){
						img_mail = "";
						if(msgJson.rows[i].readed==0){
							img_mail = "mail-notreaded.png"
						}else{
							img_mail = "mail-readed.png"
						}
						td_mail_icon = $(document.createElement('td'));
						td_mail_icon.css('width', '20px');		
						td_mail_icon.css('vertical-align', 'middle');
						td_mail_icon.attr('id','msgRow_icon_'+msg_id_key)
						td_mail_icon.html('<img src="images/'+img_mail+'" class="mail_icon" />');
						tr.append(td_mail_icon);
					}
					
					td0 = $(document.createElement('td'));
					td0.addClass('avatar');
					td0.addClass('icon');
					td0.html('<a href="/'+msgJson.rows[i].username+'"><img src="'+msgJson.rows[i].avatar_small+'" alt="" width="48" /></a>');
					tr.append(td0);
				
					td1 = $(document.createElement('td'));
	
					div1 = $(document.createElement('div'));
					div1.addClass('text');
					div2 = $(document.createElement('div'));
					div2.addClass('status');
					div2.addClass('dmessage_row');
					
					if(msgJson.rows[i].isinvited){//渲染邀请
						if(msgJson.rows[i].isreply){
							if (msgJson.rows[i].content) {
								message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">:'+msgJson.rows[i].content+' ('+ Better_compareTime(msgJson.rows[i].dateline)+')</span>';
							} else {
								message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">&nbsp;</span>';
							}
						}else{
							if (msgJson.rows[i].content) {
								message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">'+msgJson.rows[i].content+' ('+ Better_compareTime(msgJson.rows[i].dateline)+')</span>';
							} else {
								message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">&nbsp;</span>';
							}
						}
					}else{ //渲染普通私信
						if (msgJson.rows[i].content) {
							message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">'+msgJson.rows[i].content+' ('+ Better_compareTime(msgJson.rows[i].dateline)+')</span>';
						} else {
							message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">&nbsp;</span>';
						}
					}
					div2.html(message);			
					/*div2.find('a.confirmFollow').click(function(){
						request_uid = parseInt($(this).attr('ref'));
						if ($.inArray(request_uid, betterUser.followers)<0) {
							Better_Allow_Follow({
								data: {
									uid: request_uid,
									closeCallback: function(){
										$('div.dmessage_row').find('a.confirmFollow[ref="'+request_uid+'"]').unbind('click');
									}
								}
							});
						}
					});*/
					if (msgJson.rows[i].attach && msgJson.rows[i].attach_thumb) {
						div3 = $(document.createElement('div'));
						div3.addClass('info');
						div3.html('<a href="'+msgJson.rows[i].attach_url+'" class="attach_href"><img id="attach_'+msg_id_key+'" class="attach" src="'+msgJson.rows[i].attach_thumb+'" alt="" width="100" ref="'+msgJson.rows[i].attach+'" /></a>');
						div3.find('a').fancybox();
						div2.append(div3);
					}		
					div4 = $(document.createElement('div')).addClass('ext');
					
					funcDiv = $(document.createElement('div'));
					funcDiv.attr('id', 'msgRowFuncDiv_'+id+msg_id_key);
					
					funcDiv.addClass('action')
					funcDiv.hide();
					funcDiv.empty();
					div4.append(funcDiv);
					
					div1.append(div2);			
					div1.append(div4);
					td1.append(div1);
					
					tr.append(td1);
		
					tbl.append(tr);
				}
				if (msgJson.count<=0 && typeof(callbacks.emptyCallback)=='function') {
					try {
						callbacks.emptyCallback();			
					} catch (e) {
						if (Better_InDebug) {
							alert('In emptyCallback : '+e.message);
						}
					}
				}
				
				// 设置消息行鼠标上移效果
				$('#tbl_'+id+' .msgRow').mouseenter(function(){
		
					thisObj = $(this);
					if (betterUser.uid>0) {
						tmp = thisObj.attr('id').split('_');
						
						msg_id = tmp[1];
						uid = tmp[2];
						nickname = tmp[3];
						from_uid = tmp[4];
						gender = tmp[5];
						
						msg_id = thisObj.attr('msg_id');
						uid = thisObj.attr('uid');
						nickname = thisObj.attr('nickname');
						from_uid = thisObj.attr('muid');
						gender = thisObj.attr('gender');
						isinvited = typeof(thisObj.attr('isinvited')!="undefined")?thisObj.attr('isinvited'):0;
						isreplied = typeof(thisObj.attr('isreplied')!="undefined")?thisObj.attr('isreplied'):0;
						poiid =  typeof(thisObj.attr('poiid')!="undefined")?thisObj.attr('poiid'):0;
						poiname =  typeof(thisObj.attr('poiname')!="undefined")?thisObj.attr('poiname'):0;
						msg_id_key = msg_id+'_'+uid+'_'+nickname+'_'+from_uid+'_'+gender;
						
						funcObj = thisObj.find('[id^="msgRowFuncDiv_"]');
		
						if (funcObj.html()=='') {
							funcObj.empty();
							
							//	标记为已读
//							if (isReceived && $(this).hasClass('notReaded')) {
//								a = $(document.createElement('a'));
//								a.html(betterLang.messages.mark_readed).attr('href', 'javascript:void(0)').attr('id', 'notReadedBtn'+msg_id_key).bind('click', {
//									msg_id: msg_id,
//									msg_id_key: msg_id_key,
//									id: 'msgRow_'
//								}, Better_Messages_Readed);
//								funcObj.append(a);
//							}
							/**普通私信的时候有回复、删除和阻止此人三个功能，而在邀请中只有同意邀请和忽略邀请**/
							if(isinvited==1){
								//同意
								notreaded = typeof(thisObj.attr('notreaded')!="undefined")?thisObj.attr('notreaded'):0;
								if(notreaded == 1 && isreplied==0){
									a = $(document.createElement('a'));
									a.html("同意邀请").attr('href', 'javascript:void(0)').addClass('invitationAction').bind('click', {
										msg_id: msg_id
									}, function(event){
										$(".invitationAction").hide();
										Better_Notify_loading();	
										$.post('/ajax/messages/agreeinvitation', {
											msg_id: event.data.msg_id
										}, function(drJson){
											Better_Notify_clear();										
											if (Better_AjaxCheck(drJson)) {
												if (drJson.result==1) {
													thisObj.removeClass('notReaded');
													thisObj.attr('notreaded','0');
													$(".invitationAction").show();
													$('#msgRow_icon_'+msg_id_key+' img').attr('src','images/mail-readed.png');
													funcObj.empty();
													a = $(document.createElement('a'));
													a.html("删除").attr('href', 'javascript:void(0)').bind('click', {
														msg_id: msg_id									
														}, function(event){
															Better_Confirm({
																msg: "确认要删除这一次邀请吗？",
																onConfirm: function(){
																	Better_Notify_loading();												
																	$.post('/ajax/messages/refuseinvitation', {
																		msg_id: event.data.msg_id
																	}, function(drJson){
																		Better_Notify_clear();
																		
																		if (Better_AjaxCheck(drJson)) {
																			if (drJson.result==1) {
																				$('tr[id^="msgRow_'+event.data.msg_id+'"]').fadeOut();
																			}
																		}
																	}, 'json');			
																}
															});
														});
													funcObj.append(a);	
												}else{
													Better_Notify(drJson.error);
												}
											}
										}, 'json');		
									});
									funcObj.append(a);
									//忽略
									a = $(document.createElement('a'));
									a.html("忽略邀请").attr('href', 'javascript:void(0)').addClass('invitationAction').bind('click', {
										msg_id: msg_id									
										}, function(event){
											$(".invitationAction").hide();
											Better_Confirm({
												msg: "确认要忽略这一次邀请吗？",
												onConfirm: function(){
													Better_Notify_loading();												
													$.post('/ajax/messages/refuseinvitation', {
														msg_id: event.data.msg_id
													}, function(drJson){
														Better_Notify_clear();
														
														if (Better_AjaxCheck(drJson)) {
															if (drJson.result==1) {
																$('tr[id^="msgRow_'+event.data.msg_id+'"]').fadeOut();
															}
														}
													}, 'json');			
												},
												onCancel:function(){
													Better_Confirm_clear();
													$(".invitationAction").show();
												}
											});
										});
									funcObj.append(a);		
								}	else{
									a = $(document.createElement('a'));
									a.html("删除").attr('href', 'javascript:void(0)').bind('click', {
										msg_id: msg_id									
										}, function(event){
											Better_Confirm({
												msg: "确认要删除这一次邀请吗？",
												onConfirm: function(){
													Better_Notify_loading();												
													$.post('/ajax/messages/refuseinvitation', {
														msg_id: event.data.msg_id
													}, function(drJson){
														Better_Notify_clear();
														
														if (Better_AjaxCheck(drJson)) {
															if (drJson.result==1) {
																$('tr[id^="msgRow_'+event.data.msg_id+'"]').fadeOut();
															}
														}
													}, 'json');			
												}
											});
										});
									funcObj.append(a);	
								}
							}
							else{
								if (withReplyLink) {
									a = $(document.createElement('a'));
									a.html(betterLang.messages.reply).attr('href', 'javascript:void(0)').bind('click', {
										uid: from_uid,
										msg_id: msg_id,
										nickname: nickname
									}, function(event){
										if(isReceived && $('#msgRow_'+msg_id_key).hasClass('notReaded')){
											Better_Messages_Readed2({events:event, callback: function(event){Better_SendMessage(event);}});
										}else{
											Better_SendMessage(event);
										}
									});
									funcObj.append(a);
								}							
								//	删除
								if (uid==betterUser.uid) {
									a = $(document.createElement('a'));
									a.html(betterLang.messages.delete_it).attr('href', 'javascript:void(0)').bind('click', {
										msg_id: msg_id, 
										id:'msgRow_',
										nickname: nickname,
										gender: gender,
										uid: from_uid,
										muid: muid
										}, callbacks.deleteCallback);
									funcObj.append(a);
								}
								//阻止
								ab = $(document.createElement('a'));
								ab.html(betterLang.global.block.title).attr('href', 'javascript:void(0)').bind('click', {
									gender: '',
									uid: from_uid,
									nickname: nickname,
									from: 'direct_message',
									callbacks: {
										completeCallback: function(){
											$('a[href="#received"]').trigger('click');
										}
									}
								}, function(event){
									if(isReceived && $('#msgRow_'+msg_id_key).hasClass('notReaded')){
										Better_Messages_Readed2({events:event, callback: function(event){Better_Block(event);}});
									}else{
										Better_Block(event);
									}
								});
								
								funcObj.append(ab);
							}
						}
						funcObj.show();
		
					}
				});
		
				// 设置消息行鼠标移出效果
				$('#tbl_'+id+' .msgRow').mouseleave(function(){
					if (betterUser.uid>0) {
						$(this).find('div[id^=msgRowFuncDiv_]').hide();
					}
				});
				
				Better_ajaxInProcess = false;				
			}
			
			if ($.isFunction(callbacks.completeCallback)) {
				callbacks.completeCallback();
			}
		} catch(e) {
			alert('catch');
			if (Better_InDebug) {
				alert('In Foreach : '+e.message);
			}
			Better_ajaxInProcess = false;
			tbl.empty();
		}
		
	} else {
		Better_ajaxInProcess = false;
		tbl.empty();
	}
}
/**
 * 删除收到的私信
 * 
 * @param event
 * @return
 */
function Better_Messages_DeleteReceived(event)
{
	Better_Confirm({
		msg: betterLang.messages.delete_confirm,
		onConfirm: function(){
			Better_Notify_loading();
			
			$.post('/ajax/messages/deletereceived', {
				msg_id: event.data.msg_id
			}, function(drJson){
				Better_Notify_clear();
				
				if (Better_AjaxCheck(drJson)) {
					if (drJson.result==1) {
						$('tr[id^="msgRow_'+event.data.msg_id+'"]').fadeOut()
					}
				}
			}, 'json');			
		}
	});
}
/**
 * 收到的好友请求
 * 
 * @param event
 * @return
 */
function Better_User_FriendsRequests(page)
{
	page = page ? page : 1;
	key = 'friend_request';
	Better_Pager({
		key: key,
		callback: Better_User_FriendsRequests
	});		
	Better_loadRequest({
		page: page,
		key: key,
		url: '/ajax/messages/friends_requests'
	});
}
/**
 * 收到的关注请求
 * 
 * @param page
 * @return
 */
function Better_User_FollowRequests(page)
{
	page = page ? page : 1;
	key = 'follow_request';
	
	Better_Pager({
		key: key,
		callback: Better_User_FollowRequests
	});	
	
	Better_loadRequest({
		page: page,
		key: key,
		url: '/ajax/messages/follow_requests',
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.messages.no_follow_request);
			}
		}
	});	
}
$(function() {
	
	var tabContainers = $('div.tabs > div');
	tabContainers.hide().filter(':first').show();
	
	$('div.tabs ul.tabNavigation a').click(function () {
		if(typeof($(this).attr('disabled'))!='undefined' && $(this).attr('disabled')=='true'){
			return false;
		}
		
		tabContainers.hide();
		tabContainers.filter(this.hash).show();
		
		$('div.tabs ul.tabNavigation li a').removeClass('selected');
		$(this).addClass('selected');
		
            switch(this.hash) {
	            case '#received':
	            	$('#tbl_received').empty();
					$('#msg_pager_received').hide();
					$('#btn_table').show();
					Better_Pager_Reset('received');
					Better_Messages_Received();
					break;
	            	break;
	            case '#friend_request':
	            	$('#tbl_friend_request').empty();
					Better_User_FriendsRequests(1);
	            	break;
            	case '#follow_request':
            		$('#tbl_follow_request').empty();
					Better_User_FollowRequests(1);
            		break;
            }
            
            return false;
	}).ajaxStart(function(e, q, o){
    	$(this).attr('disabled', true);
    	Better_Ajax_processing = true;
    }).ajaxComplete(function(e, q, o){
    	$(this).attr('disabled', false);
    	Better_Ajax_processing = false;
    });
	var location = window.location.toString();
	if(location.indexOf('#')!=-1){
		var tmp = location.split('#');
		if(tmp[1]){
			var type = tmp[1];
		}
		if(type == 'friends_request'){
			 $('div.tabs ul.tabNavigation li a[href=#friend_request]').trigger('click');
		}else{
			 $('div.tabs ul.tabNavigation li a:first').trigger('click');
		}
	}else{
		 $('div.tabs ul.tabNavigation li a:first').trigger('click');
	}
	$('#readall').click(function(){
		Better_Confirm({
			msg: betterLang.global.confirm_read_msg,
			onConfirm: function(){
				$.post('/ajax/messages/readall', {}, function(json){
					if(json.result==1){
						Better_Notify(betterLang.global.do_sucess);
						url = window.location.toString().split('#');
						window.location.href = url[0]+'#received';
						window.location.reload();
						
					}else{
						Better_Notify(json.error);
					}
				}, 'json');	
			}
		});
	
	});
	
	
	$('#delall').click(function(){
		Better_Confirm({
			msg: betterLang.global.confirm_del_msg,
			onConfirm: function(){
				
				$.post('/ajax/messages/deleteallreceived', {}, function(json){
					if(json.result==1){
						Better_Notify(betterLang.global.do_sucess);
						url = window.location.toString().split('#');
						window.location.href = url[0]+'#received';
						window.location.reload();
						
					}else{
						Better_Notify(json.error);
					}
				}, 'json');	
			}
		});
	
	});
	
	
	$('#friq_agreeall').click(function(){
		Better_Confirm({
			msg: betterLang.global.confirm_agree_req,
			onConfirm: function(){
				Better_Notify_loading();
				$.post('/ajax/user/friendrequests', {}, function(json){
					Better_Notify_clear();
					if(json.result==1){
						karmaMsg = Better_parseAchievement(json);						
						if (karmaMsg!='') {
							karmaMsg =  ', '+karmaMsg;												
						}
						Better_Notify(betterLang.global.do_sucess+karmaMsg);
						url = window.location.toString().split('#');
						window.location.href = url[0]+'#friend_request';
						window.location.reload();
						
					}else{
						Better_Notify(json.error);
					}
				}, 'json');	
			}
		});
	
	});
	
	$('#friq_rejectall').click(function(){
		Better_Confirm({
			msg: betterLang.global.confirm_reject_req,
			onConfirm: function(){
				$.post('/ajax/user/rejectfriends', {}, function(json){
					if(json.result==1){
						Better_Notify(betterLang.global.do_sucess);
						url = window.location.toString().split('#');
						window.location.href = url[0]+'#friend_request';
						window.location.reload();
						
					}else{
						Better_Notify(json.error);
					}
				}, 'json');	
			}
		});
	
	});
	
	$('#floq_agreeall').click(function(){
		Better_Confirm({
			msg: betterLang.global.confirm_agree_req,
			onConfirm: function(){
				$.post('/ajax/user/confirmallfollow', {}, function(json){
					if(json.result==1){
						Better_Notify(betterLang.global.do_sucess);
						url = window.location.toString().split('#');
						window.location.href = url[0]+'#follow_request';
						window.location.reload();
						
					}else{
						Better_Notify(json.error);
					}
				}, 'json');	
			}
		});
	
	});
	
	$('#floq_rejectall').click(function(){
		Better_Confirm({
			msg: betterLang.global.confirm_agree_req,
			onConfirm: function(){
				$.post('/ajax/user/rejectallfollow', {}, function(json){
					if(json.result==1){
						Better_Notify(betterLang.global.do_sucess);
						url = window.location.toString().split('#');
						window.location.href = url[0]+'#follow_request';
						window.location.reload();
						
					}else{
						Better_Notify(json.error);
					}
				}, 'json');	
			}
		});
	
	});
	
});