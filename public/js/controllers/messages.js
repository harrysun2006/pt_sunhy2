/**
 * 收到的好友请求
 * 
 * @param event
 * @return
 */
function Better_Messages_FriendsRequests(page)
{
	page = page ? page : 1;
	key = 'friend_request';
	
	Better_Pager({
		key: key,
		callback: Better_Messages_FriendsRequests
	});	
	
	Better_loadRequest({
		page: page,
		key: key,
		url: '/ajax/messages/friends_requests',
		callbacks: {
			blockCallback: function(){
				$('a[href="#friend_request"]').trigger('click');
			}
		}
	});
}

/**
 * 收到的关注请求
 * 
 * @param page
 * @return
 */
function Better_Messages_FollowRequests(page)
{
	page = page ? page : 1;
	key = 'follow_request';
	
	Better_Pager({
		key: key,
		callback: Better_Messages_FollowRequests
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
					if (drJson.result=='1') {
						$('div.tabs ul.tabNavigation a[href="#received"]').trigger('click');
					}
				}

			}, 'json');			
		}
	});

}

/**
 * 删除发送的消息
 * 
 * @param event
 * @return
 */
function Better_Messages_DeleteSent(event)
{
	Better_Confirm({
		msg: betterLang.messages.delete_confirm,
		onConfirm: function(){
			Better_Notify_loading();
			
			$.post('/ajax/messages/deletesent', {
				msg_id: event.data.msg_id
			}, function(drJson){
				Better_Notify_clear();
				
				if (Better_AjaxCheck(drJson)) {
					if (drJson.result=='1') {
						$('div.tabs ul.tabNavigation a[href="#sent"]').trigger('click');
					}
				}
			}, 'json');			
		}
	});
	
}

/**
 * 标记私信为已读
 * 
 * @param event
 * @return
 */
function Better_Messages_Readed(event)
{
	Better_Notify_loading();
	
	$.post('/ajax/messages/readed', {
		msg_id: event.data.msg_id
	}, function(rdJson){
		Better_Notify_clear();
		
		if (Better_AjaxCheck(rdJson)) {
			if (rdJson.result=='1') {
				$('#msgRow_'+event.data.msg_id_key).removeClass('notReaded');
				$(event.target).replaceWith('');
			} else {
				Better_Notify({
					title: betterLang.global.error.title,
					msg: rdJson.error
				});
			}
		}
		
	}, 'json');

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
	callbacks = typeof(callbacks)=='object' ? callbacks : null;

	var tbl = $('#tbl_'+id);
	var withReplyLink = typeof(posts.withReplyLink)!='undefined' ? posts.withReplyLink : false;
	var page = posts.page;
	var isReceived = typeof(posts.isReceived)!='undefined' && posts.isReceived==true ? true : false;
	
	Better_Table_Loading(id);
	Better_WaitForLastAjax();
	Better_ajaxInProcess = true;	
	
	$.get(url, posts, function(msgJson){

		if (Better_AjaxCheck(msgJson)) {
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
						tr.addClass('msgRow');
						if (msgJson.rows[i].readed==0) {
							tr.addClass('notReaded');
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
						
						if (msgJson.rows[i].content) {
							message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">'+msgJson.rows[i].content+' ('+ Better_compareTime(msgJson.rows[i].dateline)+')</span>';
						} else {
							message = '<a href="/'+msgJson.rows[i].username+'" class="user" id="nickname_'+msg_id_key+'">'+msgJson.rows[i].nickname+'</a> '+'<span id="message_'+msg_id_key+'">&nbsp;</span>';
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
						div4.append(funcDiv).append(Better_locationTips({
							lon: msgJson.rows[i].lon,
							lat: msgJson.rows[i].lat,
							dateline: msgJson.rows[i].lbs_report,
							isUser: true,
							poi: msgJson.rows[i].poi							
						}));
						
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
							msg_id_key = msg_id+'_'+uid+'_'+nickname+'_'+from_uid+'_'+gender;
							
							funcObj = thisObj.find('[id^="msgRowFuncDiv_"]');
			
							if (funcObj.html()=='') {
								funcObj.empty();
								
								//	标记为已读
								if (isReceived && $(this).hasClass('notReaded')) {
									a = $(document.createElement('a'));
									a.html(betterLang.messages.mark_readed).attr('href', 'javascript:void(0)').bind('click', {
										msg_id: msg_id,
										msg_id_key: msg_id_key,
										id: 'msgRow_'
									}, Better_Messages_Readed);

									funcObj.append(a);
								}

								if (withReplyLink) {
									a = $(document.createElement('a'));
									a.html(betterLang.messages.reply).attr('href', 'javascript:void(0)').bind('click', {
										uid: from_uid,
										msg_id: msg_id,
										nickname: nickname
									}, Better_SendMessage);

									funcObj.append(a);
								}
								
								//	删除
								if (uid==betterUser.uid) {
									a = $(document.createElement('a'));
									a.html(betterLang.messages.delete_it).attr('href', 'javascript:void(0)').bind('click', {msg_id: msg_id, id:'msgRow_'}, callbacks.deleteCallback);
									funcObj.append(a);
								}
								
								ab = $(document.createElement('a'));
								ab.html(betterLang.global.block.title).attr('href', 'javascript:void(0)').bind('click', {
									gender: '',
									uid: from_uid,
									msgid: msg_id,
									nickname: nickname,
									from: 'direct_message',
									callbacks: {
										completeCallback: function(){
											$('a[href="#received"]').trigger('click');
										}
									}
								}, Better_Block);
								
								funcObj.append(ab);

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

			}catch(e) {
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
	}, 'json');		
}

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
		isReceived: true
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
 * 发出的私信
 * 
 * @param page
 * @return
 */
function Better_Messages_Sent(page)
{
	page = page ? page : 1;
	url = '/ajax/messages/sent';
	
	Better_Pager({
		key: 'sent',
		callback: Better_Messages_Sent
	});	
	
	Better_Messages_loadMessages('sent', url ,{
		page: page
	}, {
		completeCallback: function() {
		},
		emptyCallback: function(){
			Better_EmptyResults('sent', betterLang.messages.no_message_sent);
		},
		deleteCallback: Better_Messages_DeleteSent
	});		
}

$(function() {

	$('#half_right .box h3').click(function(){
		$(this).next().toggle();
		return false;
	});
		
	var tabContainers = $('div.tabs > div');
	tabContainers.hide().filter(':first').show();
        
	$('div.tabs ul.tabNavigation a').click(function () {
		tabContainers.hide();
		tabContainers.filter(this.hash).show();
		$('div.tabs ul.tabNavigation a').removeClass('selected');

        $(this).addClass('selected');
		
		switch(this.hash) {
			case '#received':
				$('#tbl_received').empty();
				$('#msg_pager_received').hide();
				Better_Pager_Reset('received');
				Better_Messages_Received();
				break;
			case '#sent':
				$('#tbl_sent').empty();
				$('#msg_pager_sent').hide();
				Better_Pager_Reset('sent');
				Better_Messages_Sent();
				break;
			case '#friend_request':
				$('#tbl_friend_request').empty();
				$('#msg_pager_friend_request').hide();
				Better_Pager_Reset('friend_request');
				Better_Messages_FriendsRequests();				
				break;
			case '#follow_request':
				$('#tbl_follow_request').empty();
				$('#msg_pager_follow_request').hide();
				Better_Pager_Reset('follow_request');
				Better_Messages_FollowRequests();				
				break;
		}
		
		return false;
	}).filter(':first').click();
	
	
	tmp = window.location.toString().split('#');
	if (tmp.length>1) {
		selectedTab = tmp[1];
		Better_Auto_Scroll = true;
		$('div.tabs ul.tabNavigation a[href="#'+selectedTab+'"]:first').trigger('click');
	} else {
		if (dispUser.uid==10000) {
			$('div.tabs ul.tabNavigation ul.dropdown a[href="#messages"]:first').trigger('click');
		} else {
			$('div.tabs ul.tabNavigation ul.dropdown a[href="#checkins"]:first').trigger('click');
		}
	}
        
});