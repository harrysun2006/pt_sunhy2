var wifiAlert = '';
var wifiLon = betterUser.lon;
var wifiLat = betterUser.lat;
var wifiRange = 0;
var wifiMsg = '';
var wifiError = false;
var wifiMessage = '';

var ipLon = 0;
var ipLat = 0;  

var days = 30;

Better_This_Script = '/'+dispUser.username;

var Better_Unblock_Callback = function(){
	$('#tbl_blocks').empty();
	Better_User_Blocks(1);
};

var Better_Auto_Scroll = false;

var Better_tarce_hasloaded =false;
var trace_map_hasloaded = false;

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
				
				$('#msgRow_'+event.data.msg_id_key+' img.mail_icon').attr('src', 'images/mail-readed.png');
			} else {
				Better_Notify({
					title: betterLang.global.error.title,
					msg: rdJson.error
				});
			}
		}
		
	}, 'json');

}


function Better_Messages_Readed2(options){
	events = options.events;
	callback = options.callback;
	$.post('/ajax/messages/readed', {
		msg_id: msg_id
	}, function(rdJson){
		
		if (Better_AjaxCheck(rdJson)) {
			if (rdJson.result=='1') {
				$('#msgRow_'+msg_id_key).removeClass('notReaded');
				$('#notReadedBtn'+msg_id_key).replaceWith('');
				$('#msgRow_'+msg_id_key+' img.mail_icon').attr('src', 'images/mail-readed.png');
				callback(events);
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
 * 用户勋章
 * 
 * @return
 */
function Better_User_Badges()
{
	id = 'badges';
	tbl = $('#tbl_'+id);
	
	window.location = Better_This_Script+'#x'+tbl;
	
	Better_Table_Loading(id);
	Better_WaitForLastAjax();

	Better_ajaxInProcess = true;	
	
	url = '/ajax/user/badges';
	posts = {
		nickname: dispUser.nickname
	};

	$.get(url, posts, function(badgeJson){
		if (Better_AjaxCheck(badgeJson)) {
			try {
				Better_Clear_Table_Loading(id);

				if (badgeJson.count && badgeJson.rows!=null) {
					for(i in badgeJson.rows) {
						badge_id = badgeJson.rows[i].id;
						tr = $(document.createElement('tr'));
						tr.attr('id', 'better_user_row_'+badge_id);
						tr.addClass('betterUserRow');	
		
						//	头像
						td = $(document.createElement('td'));
						td.html('<a href="/'+dispUser.username+'"><img src="'+dispUser.avatar_small+'" alt="'+dispUser.nickname+'" /></a>');
						td.attr('height', '84').find('img').addClass('avatar').addClass('avatar_small').error(function(){
							$(this).attr('src', Better_AvatarOnError);
						});
						tr.append(td);
						
						//	当前行用户状态
						td = $(document.createElement('td'));
						td.addClass('info');
						
						div1 = $(document.createElement('div'));
						div1.addClass('status');
						div1.addClass('badge_row');

						div1_html = '<a href="/'+dispUser.username+'" class="user">'+dispUser.nickname;

						div1_html += '</a> '+badgeJson.rows[i].badge_name;
						div1.html('<span>'+div1_html+'</span>');
						
						div2 = $(document.createElement('div'));
						div2.addClass('ext');
									
						if (badgeJson.rows[i].poi_name) {
							userthing =betterLang.noping.user.user_get_badge_havepoi.toString().replace('{TIME}',Better_compareTime(badgeJson.rows[i].get_time)).replace('{ANYPLACE}','<a class="place" href="/poi/'+badgeJson.rows[i].poi_id+'">'+$.trim(badgeJson.rows[i].poi_name)+'</a></span> ');							
						} else {
							userthing = betterLang.noping.user.user_get_badge_nopoi.toString().replace('{TIME}',Better_compareTime(badgeJson.rows[i].get_time));		
						}
						div2.append(userthing);						
						funcDiv = $(document.createElement('div')).addClass('action').addClass('userAction');
						funcDiv.attr('id', 'betterUserFuncDiv_'+dispUser.uid).addClass('betterUserFuncDiv').hide();
						div2.append(funcDiv);
						
						td.append(div1);
						td.append(div2);
						tr.append(td);
						
						td3 = $(document.createElement('td'));	
						badegeimg =  $('<a href="javascript:void(0)" title="'+badgeJson.rows[i].badge_name+'" class="badge_icons" id="userbigbadge_'+badge_id+'" badge_id="'+badge_id+'"><img src="'+badgeJson.rows[i].badge_picture+'" alt="'+badgeJson.rows[i].badge_name+'" /></a>');	
						badegeimg.click(function(){
							$('a[href="#bigbadge_'+$(this).attr('badge_id')+'"]').trigger('click');
						});	
						td3.append(badegeimg);
						tr.append(td3);
						tbl.append(tr);	
					
					}
	
				} else if (badgeJson.count==0) {

					if (dispUser.uid==betterUser.uid) {
						Better_EmptyResults(id, betterLang.noping.user.user_havnot_badges.toString().replace('{NICKNAME}',dispUser.nickname));	
					} else if (dispUser.uid!=betterUser.uid && (dispUser.priv=='public' || (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)>=0))) {
						Better_EmptyResults(id, betterLang.noping.user.user_havnot_badges.toString().replace('{NICKNAME}',dispUser.nickname));	
					} else {
						msg = betterLang.user.must_be_friend_to_see_badges.toString().replace('{NICKNAME}', dispUser.nickname);						
						Better_EmptyResults(id, msg);
					}
				}  else {
					Better_ajaxInProcess = false;
					tbl.empty();
				}
			} catch (e) {
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
 * 报道历史
 * 
 * @param page
 * @return
 */
function Better_User_CheckinHistory(page)
{
	page = page ? page : 1;
	tblKey = 'checkins';
	if (!Better_Kai_Spec) {
		window.location = Better_This_Script+'#x'+tblKey;
	}
	
	Better_Pager({
		key: tblKey,
		next: betterLang.user.more_checkins.toString().replace('{NICKNAME}', dispUser.nickname),
		last: betterLang.user.no_more_checkins.toString().replace('{NICKNAME}', dispUser.nickname),
		callback: Better_User_CheckinHistory
	});	
	
	Better_loadBlogs({
		id: tblKey,
		url: '/ajax/user/checkin_history',
		posts: {
			page: page,
			nickname: dispUser.nickname
		},
		callbacks: {
			beforeCallback: function(){
				if (Better_Auto_Scroll==true && page==1) {
					scroll(0, 0);
					Better_Auto_Scroll = false;
				}
			},
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(tblKey, betterLang.user.no_checkin_history.toString().replace('{NICKNAME}', dispUser.nickname));
				} else {
					if (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.followings)<0) {
						msg = betterLang.user.must_follow_to_see_checkin_history.toString().replace('{NICKNAME}', dispUser.nickname);					
						Better_EmptyResults(tblKey, msg);
					} else {
						Better_EmptyResults(tblKey, betterLang.user.no_checkin_history.toString().replace('{NICKNAME}', dispUser.nickname));
					}
				}
			}			
		}
	});
}

/**
 * 消息
 * 
 * @param page
 * @param renew
 * @return
 */
function Better_User_loadMine(page) 
{
	page = page ? page : 1;
	var tblKey = 'messages';
	
	if (!Better_Kai_Spec) {
		window.location = Better_This_Script+'#x'+tblKey;
	}

	Better_Pager({
		key: tblKey,
		next: betterLang.noping.user.user_more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		last: betterLang.user.no_more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_loadMine
	});
	
	Better_loadBlogs({
		id: tblKey,
		url: '/ajax/blog/listmine',
		posts: {
			page:page,
			nickname: dispUser.nickname
		},
		withAvatar: false,
		withHisFuncLinks: true,
		callbacks: {
			beforeCallback: function(){
				if (Better_Auto_Scroll==true && page==1) {
					scroll(0, 0);
					Better_Auto_Scroll = false;
				}
			},
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(tblKey, betterLang.user.no_message.toString().replace('{NICKNAME}',dispUser.nickname));
				} else {
					if (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.followings)<0) {
						Better_EmptyResults(tblKey, betterLang.noping.user.user_must_follow.toString().replace('{NICKNAME}',dispUser.nickname));
					} else {
						Better_EmptyResults(tblKey, betterLang.user.no_message.toString().replace('{NICKNAME}',dispUser.nickname));
					}
				}				
			}
		}
	});

}



function Better_User_loadTips(page)
{
	page = page? page : 1;
	var key = 'tips';
	
	window.location = Better_This_Script+'#x'+key;
	
	Better_Pager({
		key: key,
		next: betterLang.noping.user.tips.more_tips.toString().replace('{NICKNAME}',dispUser.nickname),
		last: betterLang.user.tips.no_more_tips.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_loadTips
	});

	Better_loadBlogs({
		id: key,
		url: '/ajax/user/tips',
		posts: {
			uid: dispUser.uid,
			page: page
		},
		page: page,
		withHisFuncLinks: false,
		callbacks: {
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(key, betterLang.user.no_tips.toString().replace('{NICKNAME}',dispUser.nickname));
				} else {
						Better_EmptyResults(key,betterLang.user.no_tips.toString().replace('{NICKNAME}',dispUser.nickname));
				}				
			}
		}
	});
}

function Better_User_loadDoing(page)
{
	page = page? page : 1;
	var key = 'doing';
	
	Better_loadBlogs({
		id: key,
		url: '/ajax/user/userdoing',
		posts: {
			uid: dispUser.uid,
			page: page
		},
		page: page,
		withHisFuncLinks: dispUser.uid==betterUser.uid ? false : true,
		callbacks: {
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(key, betterLang.user.no_doing.toString().replace('{NICKNAME}',dispUser.nickname));
				} else if (dispUser.uid!=betterUser.uid && (dispUser.priv=='public' || (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)>=0))) {
					Better_EmptyResults(key, betterLang.user.no_doing.toString().replace('{NICKNAME}',dispUser.nickname));
				} else if (dispUser.uid!=betterUser.uid && hasFriendRequest>0) {
					Better_EmptyResults(key, betterLang.user.no_doing.toString().replace('{NICKNAME}',dispUser.nickname));
				} else if(betterUser.uid>0){
						msg = betterLang.user.must_be_friend_to_see_doing.toString().replace('{NICKNAME}', dispUser.nickname);					
						msg += '，<a id="addfriend_now" href="javascript: void(0);">现在就加TA为好友</a>';
						Better_EmptyResults(key, msg);
						$('#addfriend_now').bind('click', {
							'nickname': dispUser.nickname,
							'uid': dispUser.uid
						}, Better_Friend_Request);
				} else {
					msg = betterLang.user.must_be_login_to_see_doing.toString();					
					msg += '，<a id="login_now" href="/login">现在就登录</a>';
					Better_EmptyResults(key, msg);
				}
			}
		}
	});

}


function Better_User_Pager1(page, pages)
{
	var text;
	
	//tbl.empty();	
	Better_User_loadDoing(page);
	Better_Ajax_processing = true;
	window.scrollTo(0,0);
	$("#pagination").empty();
	text = makePager(page, pages);
	$("#pagination").append(text);
	//$("#pagination").replaceWith("<b>Paragraph. </b>");
	
	return false;
}


function pageData(page, pageNum)
{
	var pageStr = '';
	if (pageNum < 7) {
		for(i = 1; i < pageNum; i ++) {
			pageStr += i + ','
		}
		pageStr += pageNum;
	} else if (page < 5) {
		pageStr = '1,2,3,4,5,*,' + pageNum;
	} else if (page + 4 >= pageNum) {
		page0 = pageNum - 5;
		page1 = pageNum - 4;
		page2 = pageNum - 3;
		page3 = pageNum - 2;
		page4 = pageNum - 1;
		pageStr = '1,*,' + page0 + ',' + page1 + ',' + page2 + ',' + page3 + ',' + page4 + ',' + pageNum;
	} else {
		page1 = page - 2;
		page2 = page - 1;
		page3 = page + 1;
		page4 = page + 2;
		pageStr = '1,*,' + page1 + ',' + page2 + ',' + page + ',' + page3 + ',' + page4 + ',*,' + pageNum;
	}
	return pageStr;
}


function makePager(page, pages)
{
	var str1, str2, a1, cls, html, a, i;
	html = "";
	str1 = pageData(page, pages);
	a1 = str1.split(",");
	
	for (i=0; i<a1.length; i++) {
		str2 = a1[i];
		if (str2 != '*') {
			if (str2 == page) {
				cls = 'currentpage';
				t = "<li class='" + cls + "'>" + str2 + "</li>";			
			} else {
				cls = '';
				a = "<a href='#' onClick='return Better_User_Pager1(" + str2 + ", " + pages + ")'>" + str2 + "</a>";
				t = "<li class='" + cls + "'>" + a + "</li>";			
			}
		} else {
			t = "<li>...</li>";
		}
		
		html = html + t;
	}
	
	html = "<ul>" + html + "</ul>";
	return html;	
}


function Better_User_loadTodo(page)
{
	page = page? page : 1;
	var key = 'todo';
	

	Better_Pager({
		key: key,
		next: betterLang.noping.user.doing.more_doing.toString().replace('{NICKNAME}',dispUser.nickname),
		last: betterLang.user.doing.no_more_doing.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_loadDoing
	});

	Better_loadBlogs({
		id: key,
		url: '/ajax/user/usertodo',
		posts: {
			uid: dispUser.uid,
			page: page
		},
		page: page,
		withHisFuncLinks: dispUser.uid==betterUser.uid ? false : true,
		callbacks: {
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(key, betterLang.user.no_todo.toString().replace('{NICKNAME}',dispUser.nickname));
				} else if (dispUser.uid!=betterUser.uid && (dispUser.priv=='public' || (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)>=0))) {
					Better_EmptyResults(key, betterLang.user.no_todo.toString().replace('{NICKNAME}',dispUser.nickname));	
				} else {
					msg = betterLang.user.must_be_friend_to_see_todo.toString().replace('{NICKNAME}', dispUser.nickname);					
					msg += '，<a id="addfriend_now" href="javascript: void(0);">现在就加TA为好友</a>';
					Better_EmptyResults(key, msg);
					$('#addfriend_now').bind('click', {
						'nickname': dispUser.nickname,
						'uid': dispUser.uid
					}, Better_Friend_Request);
				}			
			}
		}
	});
	
}

/**
 * 更新页面上方的地址文字
 * 
 * @param city
 * @param address
 * @param time
 * @return
 */
function Better_User_UpdateAddressString(city, address, time)
{
	city = city ? city : '';
	city_str = city ? ' '+city+' ' : '';
	
	str = betterLang.noping.user.user_location.toString().replace('{TIME}',Better_compareTime(time)+' <br /> ').replace('{CITY}',city_str).replace('{ADDRESS}',address);
	return str;
}

/**
 * 顶部的“关注”按钮
 * 
 * @param event
 * @return
 */
function Better_User_Follow(event)
{

	/*if ($.inArray(dispUser.uid, betterUser.blocks)>=0) {
		Better_Notify({
			msg: betterLang.user.follow.failed.blocked
		});
	} else {
		Better_Confirm({
			msg: betterLang.user.confirm_follow,
			onConfirm: function(){
				Better_Notify_loading();
				
				$.post('/ajax/user/follow', {
					uid: dispUser.uid
				}, function(frjson){
					Better_Notify_clear();
					
					if (Better_AjaxCheck(frjson)) {
						if (Better_AjaxCheck(frjson)) {			
							followed_uid = frjson.followed_uid;
							codes = frjson.codes;
							switch(frjson.result) {
								case codes.INSUFFICIENT_KARMA:
									msg = betterLang.global.follow.karma_too_low;
									break;
								case codes.PENDING:
									$('#'+event.currentTarget.id).html(betterLang.global.follow.plz_wait_for_confirm).unbind('click', Better_User_Follow);
									msg = betterLang.global.follow.plz_wait_for_confirm_long.toString().replace('{NICKNAME}', event.data.nickname);
									
									$('#btnFollow').unbind('click', Better_User_Follow);
									break;
								case codes.DUPLICATED_REQUEST:
									$('#'+event.currentTarget.id).html(betterLang.global.follow.plz_wait_for_confirm).unbind('click', Better_User_Follow);
									msg = betterLang.global.follow.request.duplicated;	
									
									$('#btnFollow').unbind('click', Better_User_Follow);
									break;
								case codes.ALREADY:
									msg = betterLang.global.follow.request.already.toString().replace('{NICKNAME}', event.data.nickname);
									break;
								case codes.ALREADYGEO:
									msg = betterLang.global.follow.request.already_geo.toString().replace('{NICKNAME}', event.data.nickname);
									break;
								case codes.CANTSELF:
									msg = betterLang.global.follow.request.cant_self;
									break;
								case codes.BLOCKED:
									msg = betterLang.global.follow.request.blocked.toString().replace('{NICKNAME}', event.data.nickname);
									break;
								case codes.BLOCKEDBY:
									msg = betterLang.global.follow.request.blockedby.toString().replace('{NICKNAME}', event.data.nickname);
									break;
								case codes.INVALIDUSER:
									msg = betterLang.global.follow.request.invalid_user;
									break;
								case codes.SUCCESS:
									msg = betterLang.global.follow.success;
									$('#'+event.currentTarget.id).html(betterLang.global.follow.cancel).unbind('click', Better_Follow).bind('click', event.data, Better_Unfollow);
									
									betterUser.followings.push(followed_uid);
									
									if (typeof(dispUser)!='undefined' && dispUser.uid==betterUser.uid) {
										$('#disp_followings').text($.makeArray(betterUser.followings).length);
									}		
									
									$('#btnFollow').text(betterLang.global.follow.cancel).unbind('click', Better_User_Follow).bind('click', {
										uid: dispUser.uid,
										nickname: dispUser.nickname
									}, Better_User_Unfollow);
									break;
								case codes.FAILED:
								default:
									msg = betterLang.global.system.error;
									break;
							}
							
							Better_Notify(msg);
						}						
					}
				}, 'json');				
			}
		});
	}*/
	
	return false;
}

/**
 * 顶部的“取消关注”按钮
 * 
 * @param event
 * @return
 */
function Better_User_Unfollow(event)
{
	/*Better_Confirm({
		msg: betterLang.global.unfollow.confirm.title,
		onConfirm: function(){
			Better_Notify_loading();
			
			$.post('/ajax/user/unfollow', {
				uid: dispUser.uid
			}, function(fjson){
				Better_Notify_clear();
				
				if (Better_AjaxCheck(fjson)) {
					if (fjson.result==0) {
						Better_Notify({
							msg_title: betterLang.global.action.notify.title,
							msg: betterLang.user.fail_follow
						});
					} else {
						Better_Notify({
							msg_title: betterLang.global.action.notify.title,
							msg: betterLang.global.unfollow.success
						});
						$('#btnFollow').text(betterLang.user.follow.title).unbind('click', Better_User_Unfollow).bind('click', {
							uid: dispUser.uid,
							nickname: dispUser.nickname
						}, Better_User_Follow)
						
						betterUser.followings = Better_Array_Remove(betterUser.followings, dispUser.uid);
					}
				}
			}, 'json');			
		}
	});*/
	
	return false;
}

/**
 * 顶部的“阻止”按钮
 * 
 * @param event
 * @return
 */
function Better_User_Block(event)
{	
	Better_Confirm({
		msg: betterLang.global.block.confirm_title.toString().replace('{HE}',dispUser.nickname),
		height : ($.cookie('lan')=='zh-cn') ? '180' : '240',
		onConfirm: function(){
			Better_Notify_loading();
			
			$.post('/ajax/user/block', {
				uid: dispUser.uid,
				has_friend_request: hasFriendRequest
			}, function(bJson){
				Better_Notify_clear();
				
				if (Better_AjaxCheck(bJson)) {
					if (bJson.result=='1') {
						Better_Notify({
							msg: betterLang.global.block.success,
							msg_title: betterLang.global.action.notify.title
						});
						$('#btnBlock').text(betterLang.global.block.cancel).unbind('click', Better_User_Block).bind('click', {}, Better_User_Unblock);
						$('#btnFollow').text(betterLang.user.follow.title).unbind('click', Better_User_Unfollow).unbind('click', Better_User_Follow).bind('click', {}, Better_User_Follow);
						
						$('#btnFriendRequest').text(betterLang.global.friend.request.title).bind('click', {nickname: dispUser.nickname,uid: dispUser.uid}, Better_Friend_Request);
						betterUser.blocks = Better_Array_Push(betterUser.blocks, dispUser.uid);
						betterUser.followings = Better_Array_Remove(betterUser.followings, dispUser.uid);
					} else if (bJson.result=='-1') {
						Better_Notify({
							msg: betterLang.global.block.cant_block_sys_user
						});						
					}
				}
			}, 'json');			
		}
	});
	
	return false;
}

/**
 * 顶部的“取消阻止”按钮
 * 
 * @param event
 * @return
 */
function Better_User_Unblock(event)
{
	Better_Confirm({
		msg: betterLang.global.unblock.confirm_title.toString().replace('{HE}',dispUser.nickname),
		onConfirm: function(){
			Better_Notify_loading();
			
			$.post('/ajax/user/unblock', {
				uid: dispUser.uid
			}, function(bJson){
				Better_Notify_clear();
				
				if (Better_AjaxCheck(bJson)) {
					if (bJson.result=='1') {
						Better_Notify({
							msg_title: betterLang.global.action.notify.title,
							msg: betterLang.global.unblock.success
						});
						$('#btnBlock').text(betterLang.user.block.title).unbind('click', Better_User_Unblock).bind('click', {}, Better_User_Block);
						
						betterUser.blocks = Better_Array_Remove(betterUser.blocks, dispUser.uid);
					}
				}
			}, 'json');			
		}
	});
	
	return false;
}


/**
 * 通过加好友的请求
 * 
 * @param event
 * @return
 */
function Better_User_Agree_Friend_Request(event)
{
	var uid = event.data.uid;
	var nickname = event.data.nickname;
	
	if (hasFriendRequest) {
		Better_Confirm({
			msg: betterLang.user.friend.confirm.agree.toString().replace('{NICKNAME}', nickname),
			onConfirm: function(){
				Better_Notify_loading();
				
				$.post('/ajax/user/friend_request', {
					uid: uid
				}, function(afr_json){
					Better_Confirm_clear();
					
					result = afr_json.result;
					codes = afr_json.codes;
					switch(result) {
						case codes.SUCCESS:
							msg = betterLang.global.friend.request.success;
							$('#friend_msg').hide();
							betterUser.friends.push(uid);
							$('#btnFriendRequest').text(betterLang.global.friend.request.remove).unbind('click', event.data, Better_Friend_Request).bind('click', event.data, Better_Friend_Remove);
							hasFriendRequest = '0';
							break;
						case codes.CANTSELF:
							msg = betterLang.global.friend.request.cantself;
							break;
						case codes.CANTSYS:
							msg = betterLang.global.friend.request.cantsys;
							break;
						case codes.FAILED:
						default:
							msg = betterLang.global.freind.request.failed;
							break;
					}					
					
					Better_Notify(msg);
				}, 'json');
			}
		});
	}
	
	return false;
}


function Better_loadmapscript(){
	if(!Better_tarce_hasloaded){
		 var script = document.createElement("script");
		 script.type = "text/javascript";
		 script.src = "http://ditu.google.cn/maps?file=api&v=2&key="+gmap_key+"&hl=zh-CN&async=2&callback=Better_getTraceData";
		 
		 document.body.appendChild(script);
	}
}

/**
 * 加载地图所需script
 */
function Better_User_LoadTraceScript(){
	if(!Better_tarce_hasloaded){
		
		 var script2 = document.createElement("script");
		 script2.src="/js/labeledmarker.js";
		 
		 var script3 = document.createElement("script");
		 script3.src="/js/markermanager.js";
		 
		 var script4 = document.createElement("script");
		 script4.src="/js/controllers/trace.js";
		 
		 document.body.appendChild(script2);
		 document.body.appendChild(script3);
		 document.body.appendChild(script4);
		 
		 Better_tarce_hasloaded = true;
	}
}



/**
 * 踪迹地图
 */
function Better_User_traceMap()
{
	if(!trace_map_hasloaded){
	tbl = $('#tbl_user_trace').empty();
	Better_Table_Loading('user_trace');
	
		Better_loadmapscript();
			
	}
	
}


var Better_getTraceData = function(){
	Better_User_LoadTraceScript();
	
		$.post('/ajax/user/usertrace', {
		uid: dispUser.uid
		}, function(listJson){
			Better_Clear_Table_Loading('user_trace');
			$('div.tabs ul.tabNavigation a').attr('disabled', false);
			
		$('#trace_map').show();
		$('#trace_ul').show();
				
				rows = listJson.rows;
		user = listJson.user;
		clusters = listJson.clusters;
		Better_User_initializeGmap();

		trace_map_hasloaded = true;
			
		}, 'json');
};


function Better_User_Trace(row, marker){
	tr = $(document.createElement('tr')).addClass('user_trace_row').attr('lon', row.poi.lon).attr('lat', row.poi.lat);
	td = $(document.createElement('td')).append(row.poi.city+' <a href="/poi/'+row.poi_id+'">'+row.poi.name+'</a> '+Better_compareTime(row.checkin_time)+' &nbsp;&nbsp;&nbsp;出现过'+row.checkin_count+'次');
	tr.append(td);
	
	tr.click(function(){
		window.scroll(0, 10);
		//map.panTo(new GLatLng($(this).attr('lat'), $(this).attr('lon')));
		GEvent.trigger(marker, "click");
	});
	return tr;
}

function Better_User_Load_Tab(tab)
{
    tab = tab ? tab : '';
    
    if (!Better_Ajax_processing) {
	    if (tab=='') {
		    tmp = window.location.toString().split('#');
		    defaultTab = Better_Kai_Spec ? 'messages' : 'doing';
			if (tmp.length>1) {
				selectedTab = tmp[1];
				Better_Auto_Scroll = true;
				
				if (selectedTab.indexOf('x')==0) {
					selectedTab = selectedTab.substr(1, selectedTab.length);
				}

				if (selectedTab!='') {
					$('div.tabs ul.tabNavigation a[href="#'+selectedTab+'"] a:first').trigger('click');
				} else {
					$('div.tabs ul.tabNavigation a[href="#'+defaultTab+'"]').filter(':first').trigger('click')
				}
			} else{
				$('div.tabs ul.tabNavigation a[href="#'+defaultTab+'"]').filter(':first').trigger('click')
			}	
	    } else {
			if (tab.indexOf('x')==0) {
				tab = tab.substr(1, tab.length);
			}
			
			if (tab!='') {
				$('div.tabs ul.tabNavigation a[href="#'+tab+'"]:first').trigger('click');
			} else {
				$('div.tabs ul.tabNavigation a').filter(':first').trigger('click')
			}
	    }    	
    }
}


$(function() {

	$('#userFuncs').show();
	$('#userFuncsLine').show();
	
	var tabContainers = $('div.tabs > div');
	//tabContainers.hide().filter(':first').show();
    
	$('div.tabs ul.tabNavigation a').click(function () {
		if(typeof($(this).attr('disabled'))!='undefined' && $(this).attr('disabled')=='true'){
			return false;
		}
			tabContainers.hide();
			tabContainers.filter(this.hash).show();
			$('div.tabs ul.tabNavigation a').removeClass('selected');

	        $(this).addClass('selected');
            switch($(this).attr('href')) {
            	case '/'+dispUser.username+'#user_trace':
				case '#user_trace':
					Better_User_traceMap();
					break;
				case '/'+dispUser.username+'#doing':
				case '#doing':
					$('#tbl_doing').empty();
	            	Better_User_loadDoing(1);
					//_pages = Math.ceil(_page_1.cnt / 20);
	            	//Better_User_Pager1(1, _pages);
	            	break;
				case '/'+dispUser.username+'#todo':
					$('#tbl_todo').empty();
	            	Better_User_loadTodo(1);
	            	break;
	            default: 
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
		
	//	ajax history
    $.history.init(function(tab){
    	Better_User_Load_Tab(tab);
    });

    $('div.tabs ul.tabNavigation li a:first').trigger('click');

		
		if ($.inArray(dispUser.uid, betterUser.blocks)>=0) {
			$('#btnBlock').text(betterLang.global.block.cancel).bind('click', {}, Better_User_Unblock);
		} else {
			$('#btnBlock').text(betterLang.user.block.title).bind('click', {}, Better_User_Block);
		}
		
		if (hasFriendRequest) {
			$('#friend_msg').show();
			
			$('#btnConfirmFriend').bind('click', {
				uid: dispUser.uid,
				nickname: dispUser.nickname,
				gender: dispUser.gender
			}, Better_User_Agree_Friend_Request);
			
			$('#btnRejectFriend').bind('click', {
				uid: dispUser.uid,
				nickname: dispUser.nickname,
				gender: dispUser.gender
			}, Better_Reject_Friend_Request);

		}
		
		if (hasMyFriendRequest>0) {
			$('#btnFriendRequest').text(betterLang.global.friend.request.btn.pending);
		} else {

			if ($.inArray(dispUser.uid, betterUser.friends)>=0) {
				btnFreindRequestEvent = Better_Friend_Remove;
				btnFreindRequestText = betterLang.global.friend.request.remove;
				
				closeCallback = function(){
					//$('#btnFollow').text(betterLang.user.follow.title).unbind(Better_User_Follow).unbind(Better_User_Unfollow).bind('click', {}, Better_User_Follow);
				};
			} else {
				btnFreindRequestEvent = Better_Friend_Request;
				btnFreindRequestText = betterLang.global.friend.request.title;
				
				closeCallback = function(){};
			}

			$('#btnFriendRequest').text(btnFreindRequestText).bind('click', {
				nickname: dispUser.nickname,
				uid: dispUser.uid,
				closeCallback: closeCallback
			}, btnFreindRequestEvent);			
		}

	
	$('#btnSendMsg').bind('click', {
		uid: dispUser.uid,
		nickname: dispUser.nickname,
		id: 'betterMsgBtn_',
		text: ''
	}, Better_SendMessage);


	$('#btnDenounce').bind('click', {
		nickname: dispUser.nickname,
		text: ''
	}, Better_Denounce);
	
	
	$('#post_btn_trace').click(function(){
			static_map = '';
			var len = Better_GetPostLength('trace_');
			if (len>Better_PostMessageMaxLength) {
				alert(betterLang.blog.post_size_to_large.replace('%s', Better_PostMessageMaxLength));
			} else if (len<Better_PostMessageMinLength) {
				alert(betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength));
			} else {
				Better_Trace_post();
	}
	});
	
});




$(document).ready(function(){

	$("ul.subnav").parent().append("");

	$("#btnMore").click(function() {

		$(this).parent().find("ul.subnav").slideDown('fast').show();

		$(this).parent().hover(function() {
		}, function(){
			$(this).parent().find("ul.subnav").slideUp('slow');
		});

		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
	});
	$("#btnIcon").click(function() {

		$(this).parent().parent().find("ul.subnav").slideDown('fast').show();

		$(this).parent().parent().hover(function() {
		}, function(){
			$(this).parent().parent().find("ul.subnav").slideUp('slow');
		});

		}).hover(function() {
			$(this).addClass("subhover");
		}, function(){
			$(this).removeClass("subhover");
	});
	//是否显示动态
	$('#btnShowAction').click(function(){
		if($(this).attr('isshow') == 'show'){
			var show = true;
		}else{
			var show = false;
		}
		fuid = $(this).attr('uid');
		if(fuid){
			Better_Notify_loading();
			$.post('/ajax/user/homeshow',{
				'show': show,
				'fuid': fuid
			}, function(json){
				Better_Notify_clear();
				if(json.result==0){
					Better_Notify('设置失败!');	
				}else{
					if(show == true){
					   text = '首页隐藏状态';
					   isshow = 'hidden';
					}else{
						text = '首页显示状态';
						isshow='show';
					}
					$('#btnShowAction').html(text);
					$('#btnShowAction').attr('isshow',isshow);
				}
			});
		}
	});
});