/**
 * 好友
 * 
 * @param page
 * @return
 */
function Better_User_Friends(page)
{
	page = page ? page : 1;
	var key = 'friends';
	
	Better_Pager({
		key: key,
		next:betterLang.noping.user.friend.more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		last:betterLang.user.friend.no_more_friends.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_Friends
	});
	
	Better_loadUsers({
		id: key,
		url: '/ajax/user/friends',
		posts: {
			nickname: dispUser.nickname,
			page: page
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(key, betterLang.user.friend.no_friends.toString().replace('{NICKNAME}',dispUser.nickname));	
				} else if (dispUser.uid!=betterUser.uid && (dispUser.priv=='public' || (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)>0))) {
					Better_EmptyResults(key, betterLang.user.friend.no_friends.toString().replace('{NICKNAME}',dispUser.nickname));	
				} else {
					msg = betterLang.user.must_be_friend_to_see_friends.toString().replace('{NICKNAME}', dispUser.nickname);
					Better_EmptyResults(key, msg);
				}				
			},
			afterUnfollowCallback: function(){
				$('div.tabs ul.tabNavigation a:first').trigger('click');
			}
		}
	});	
}


/**
 * 关注
 * 
 * @param page
 * @return
 */
function Better_User_loadFollowings(page)
{
	/*page = page ? page : 1;
	var key = 'followings';
	
	Better_Pager({
		key: key,
		next: betterLang.noping.user.following.more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		last: betterLang.user.following.no_more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_loadFollowings
	});
	
	Better_loadUsers({
		id: key,
		url: '/ajax/user/following',
		posts: {
		nickname: dispUser.nickname,
			page: page
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(key, betterLang.user.following.no_message.toString().replace('{NICKNAME}',dispUser.nickname));	
				} else if (dispUser.uid!=betterUser.uid && (dispUser.priv=='public' || (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)>0))) {
					Better_EmptyResults(key, betterLang.user.following.no_message.toString().replace('{NICKNAME}',dispUser.nickname));	
				} else {
					msg = betterLang.user.must_be_friend_to_see_followings.toString().replace('{NICKNAME}', dispUser.nickname);
					//msg = msg.toString().replace('{TA}', Better_getGenderCaller(dispUser.gender));

					Better_EmptyResults(key, msg);
				}
			},
			afterUnfollowCallback: function(){
				$('div.tabs ul.tabNavigation a:first').trigger('click');
			}
		}
	});*/
	return false;
}


/**
 * 粉丝
 * 
 * @param page
 * @return
 */
function Better_User_loadFollowers(page)
{
	/*page = page ? page : 1;
	var key = 'followers';
	
	Better_Pager({
		key: key,
		next: betterLang.noping.user.follower.more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		last: betterLang.user.follower.no_more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_loadFollowers
	});
	
	Better_loadUsers({
		id: key,
		url: '/ajax/user/follower',
		posts: {
			nickname: dispUser.nickname,
			page: page
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(key, betterLang.user.follower.no_message.toString().replace('{NICKNAME}',dispUser.nickname));	
				} else if (dispUser.uid!=betterUser.uid && (dispUser.priv=='public' || (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)>=0))) {
					Better_EmptyResults(key, betterLang.user.follower.no_message.toString().replace('{NICKNAME}',dispUser.nickname));	
				} else {
					msg = betterLang.user.must_be_friend_to_see_followers.toString().replace('{NICKNAME}', dispUser.nickname);
					//msg = msg.toString().replace('{TA}', Better_getGenderCaller(dispUser.gender));

					Better_EmptyResults(key, msg);
				}
			}
		}
	});*/
	return false;
}


/**
 * 掌门历史
 * 
 * @param page
 * @return
 */
function Better_User_MajorHistory(page)
{
	page = page ? page : 1;
	tblKey = 'majors';
	
	Better_Pager({
		key: tblKey,
		next: betterLang.user.more_majors.toString().replace('{NICKNAME}', dispUser.nickname),
		last: betterLang.user.no_more_majors.toString().replace('{NICKNAME}', dispUser.nickname),
		callback: Better_User_MajorHistory
	});		
	
	Better_User_loadMajors({
		id: tblKey,
		url: '/ajax/user/major_history',
		posts: {
			page: page,
			nickname: dispUser.nickname
		},
		callbacks: {
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(tblKey, betterLang.noping.user.no_majors.toString().replace('{NICKNAME}', dispUser.nickname));
				} else {
					if (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)<0) {
						msg = betterLang.noping.user.must_follow_to_see_majors.toString().replace('{NICKNAME}', dispUser.nickname);
						Better_EmptyResults(tblKey, msg);
					} else {
						Better_EmptyResults(tblKey, betterLang.noping.user.no_majors.toString().replace('{NICKNAME}', dispUser.nickname));
					}
				}					
			}
		}
	});
}


/**
 * 加载一个掌门列表
 * 
 * @param options
 * @return
 */
function Better_User_loadMajors(options)
{
	id = options.id;
	tbl = $('#tbl_'+id);
	url = options.url;
	page = options.page;
	callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};
	posts = typeof(options.posts)!='undefined' ? options.posts : {};
	
	Better_Table_Loading(id);
	Better_WaitForLastAjax();
	
	if (page<=1) {
		Better_Pager_Reset(id);
	}

	Better_ajaxInProcess = true;	
	
	$.get(url, posts, function(majorJson){
		
		if (Better_AjaxCheck(majorJson)) {
			try {
				Better_Pager_setPages(id, majorJson.pages);
				Better_Clear_Table_Loading(id);
				
				nowPage = typeof(majorJson.page)!='undefined' ? majorJson.page : page;
				rows = majorJson.rows;
				if (rows!=null) {
					for(i=0; i<rows.length; i++){
						row = rows[i];
						tr = $(document.createElement('tr')).addClass('poi_row').attr('poi_id', row.poi_id);					
						td0 = $(document.createElement('td')).css('width', '48px');
						td0.append('<img src="'+row.logo_url.toString().replace('101', '48')+'" width="48" class="avatar" />');
						tr.append(td0);
						
						td1 = $(document.createElement('td')).addClass('poi_intro');
						pname = '<div class="poi_row_name">';							
						pname += betterLang.noping.user.loadmajors.toString().replace('{TIME}',Better_compareTime(row.major_time)).replace('{ANYPLACE}','<a href="/poi/'+row.poi_id+'">'+row.name+'</a>').replace('{CITY}',row.city).replace('{ADDRESS}',row.address);						
						pname += '</div>';
						td1.append(pname);
						paddress = ' ';
						td1.append(paddress);
						tr.append(td1);
						
						td2 = $(document.createElement('td')).css('width', '48px');
						if (parseFloat(row.major)>0 && typeof(row.major_detail)!='undefined') {
							td2.append('<a href="/'+row.major_detail.username+'"><img src="'+row.major_detail.avatar_small+'" class="avatar" width="48" /></a>');
						}
						tr.append(td2);
						
						tr.mouseenter(function(){
							if (betterUser.uid>0) {
							poiId = $(this).attr('poi_id');
							funcDiv = $('#poi_list_row_'+poiId);
							
							if (funcDiv.html()=='') {
								a = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'poi_favorite_link_'+poiId);
								a.css('font-weight', 'normal');

								if ($.inArray(poiId, betterUser.poi_favorites)>=0) {
									a.bind('click', {
										poi_id: poiId
									}, Better_Unfavorite_Poi).text(betterLang.global.favorite.cancel);
								} else {
									a.bind('click', {
										poi_id: poiId
									}, Better_Favorite_Poi).text(betterLang.global.favorite.title);
								}

								funcDiv.append(a);
							}
							
							funcDiv.show();
						}});
						
						tr.mouseleave(function(){
							$('#poi_list_row_'+$(this).attr('poi_id')).hide();
						});
						tbl.append(tr);
					}
				}
				
				if (majorJson.count<=0 && $.isFunction(callbacks.emptyCallback)) {
					callbacks.emptyCallback();
				}
		
			} catch (e) {
				if (Better_InDebug) {
					alert(e.message);
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
 * 黑名单
 * 
 * @param page
 * @return
 */
function Better_User_Blocks(page)
{
	page = page ? page : 1;
	var key = 'blocks';
	
	Better_Pager({
		key: key,
		callback: Better_User_Blocks
	});

	Better_loadUsers({
		id: key,
		url: '/ajax/user/blocks?uid='+dispUser.uid,
		uid: dispUser.uid,
		page: page,
		callbacks: {
			completeCallback: function(){},
			unblockedCallback: function(){
				Better_User_Blocks(1);
			},
			emptyCallback: function(){
				if (dispUser.uid==betterUser.uid) {
					Better_EmptyResults(key, betterLang.user.no_blocks);	
				} else if ($.inArray(dispUser.uid, betterUser.blockedby)>=0) {
					Better_EmptyResults(key, betterLang.user.you_r_blocked.toString().replace('{NICKNAME}', dispUser.nickname));
				} else if (dispUser.uid!=betterUser.uid && (dispUser.priv=='public' || (dispUser.priv=='protected' && $.inArray(dispUser.uid, betterUser.friends)>0))) {
					Better_EmptyResults(key, dispUser.nickname+betterLang.user.no_blocks);
				} else {
					msg = betterLang.user.must_be_friend_to_see_blocks.toString().replace('{NICKNAME}', dispUser.nickname);
					Better_EmptyResults(key, msg);
				}		
				
				
			}
		}
	});	
}
//首页显示动态function Better_Set_Homeshow(params){	var show = typeof params.show!='undefined' ? params.show : false;	var fuid = typeof params.fuid!='undefined' ? params.fuid : 0;		if(fuid){		Better_Notify_loading();		$.getJSON('/ajax/user/homeshow',{			'show': show,			'fuid': fuid		}, function(json){			Better_Notify_clear();			if(json.result==0){				Better_Notify('设置失败!');				}		});	}	return false;}

$(function() {
	
	$('div.tabs ul.tabNavigation a').click(function () {

		if(typeof($(this).attr('disabled'))!='undefined' && $(this).attr('disabled')=='true'){
			return false;
		}
		
			var id=$(this).attr('id');
            switch(id) {
	            case 'showfriends':
	            	$('#tbl_friends').empty();
	            	Better_User_Friends(1);
	            	break;
	            case 'showmajors':
	            	$('#tbl_majors').empty();
	            	Better_User_MajorHistory(1);
	            	break;
            	/*case 'showfollowings':
            		$('#tbl_followings').empty();
            		Better_User_loadFollowings(1);
            		break;
            	case 'showfollowers':
            		$('#tbl_followers').empty();
            		Better_User_loadFollowers(1);
            		break;*/
            	case 'showblocks':
            		$('#tbl_blocks').empty();
            		Better_User_Blocks(1);
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
		

    $('div.tabs ul.tabNavigation li a:first').trigger('click');
	});