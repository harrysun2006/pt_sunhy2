
/**
 * 关注
 * 
 * @param page
 * @return
 */
function Better_User_loadFollowings(page)
{
	page = page ? page : 1;
	var key = 'followings';
	
	Better_Pager({
		key: key,
		next: betterLang.noping.user.following.more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		last: betterLang.user.following.no_more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_loadFollowings
	});
	
	Better_Table_Loading(key);
	
	$.getJSON('/ajax/user/addfollowing', {
		'nickname': dispUser.nickname,
		'page': page
	}, function(json){
		if(json.count>0){
			$('#count_followings').text(json.count);
			$('#div_count_followings').show();
		}
		
		//	从取回的ajax结果设置分页
		Better_Pager_setPages(key, json.pages);
		//	清除Table的loading
		Better_Clear_Table_Loading(key);

		var pages = typeof(json.pages)!='undefined' ? json.pages : 0;
		
		if (page==pages) {
			Better_Pager_Reach_Last(key);
		}
		
		var id=key;
		var tbl =  $('#tbl_'+key);
		if(json.count>0){
			var rows = json.rows;
			for(var i=0; i<=rows.length; i++){
				var thisRow = rows[i];
				if(typeof thisRow!='undefined'){
					var button_text = thisRow.hasRequest==1 ? '等待确认':'加为好友';
					
					var trHtml = new Array();
					trHtml.push('<tr id="better_user_row_'+thisRow.uid+'_'+id+'" nickname="'+thisRow.nickname+'" uid="'+thisRow.uid+'" id_key="'+id+'" gender="'+thisRow.gender+'" class="betterUserRow">');
					trHtml.push('<td width="56">');
					trHtml.push('<a href="/'+thisRow.username+'"><img class="avatar pngfix avatar_small" src="'+thisRow.avatar_small+'" alt="'+thisRow.nickname+'" onerror="this.src=Better_AvatarOnError" /></a>');
					trHtml.push('</td>');
					trHtml.push('<td class="info" style="vertical-align:middle;">');
					trHtml.push('<a href="/'+thisRow.username+'" class="user left" style="font-size: 14px;">'+thisRow.nickname+'</a>');
					if(thisRow.hasRequest==1){
						trHtml.push('<span class="right" style="margin-right: 13px;">'+button_text+'</span>');
					}else{
						trHtml.push('<a class="right button" href="javascript: void(0);" id="betterUserFriendBtn_'+thisRow.uid+'" uid="'+thisRow.uid+'" nickname="'+thisRow.nickname+'">'+button_text+'</a>');
					}
					
					if(typeof thisRow.follow_eachother!='undefined' && thisRow.follow_eachother==1){
						trHtml.push('<a href="javascript: void(0);" title="互相关注中" ><img style="margin-right: 20px;" src="/images/fo_each.png" class="right" alt="互相关注中"/></a>');
					}
					trHtml.push('<div class="clearfix"></div>');
					
					trHtml.push('<div class="status message_row">');
					trHtml.push('<p>'+(typeof(thisRow.status.message)=='undefined' ? '' : Better_parseMessage(thisRow.status))+'</p>');
					trHtml.push('</div>');
					trHtml.push('<div class="ext">');
					var div2Html = Better_locationTips({
						lon: thisRow.lon,
						lat: thisRow.lat,
						dateline: thisRow.lbs_report,
						isUser: true,
						poi: thisRow.poi
					});					
					trHtml.push('<span class="time">'+$.trim(div2Html)+'</span>');
					trHtml.push('</td>');
					trHtml.push('</tr>'); 
					
					tbl.append(trHtml.join(''));
				}
			}
			
			$('a[id^=betterUserFriendBtn_]').click(function(){
				var data={
					'uid': $(this).attr('uid'),
					'nickname': $(this).attr('nickname'),
					'widthConfirm': false
				};
				Better_Friend_Request({
					'data': data,
					'currentTarget':{'id': $(this).attr('id')}
				});
			});
		}else{
			Better_EmptyResults(key, betterLang.user.following.no_message.toString().replace('{NICKNAME}',dispUser.nickname));	
		}
	});
	
}



$(function() {
	
	Better_User_loadFollowings(1);
	
});