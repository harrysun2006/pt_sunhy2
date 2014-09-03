/**
 * 评论列表
 * 
 * @param page
 * @param renew
 * @return
 */
function Better_comments(page) 
{
	page = page ? page : 1;
	var key = 'comments';
	
	Better_Table_Loading(key);
	
	Better_Pager({
		key: key,
		next: '浏览更多',
		last: '没有更多评论了',
		callback: Better_comments,
		need_last: true
	});

	$.getJSON('/ajax/blog/replies', {
		'bid': $('#bid').val(),
		'only_list': true,
		'page': page
	}, function(json){
		Better_Clear_Table_Loading(key);
		if(json.count>0){
			Better_Pager_setPages(key, json.pages);
			$('#comments_count').text(json.count);
	
			$('#div_'+key).append(json.rows);
		}else{
			Better_EmptyResults(key, '没有评论');
		}
	});

}


function Better_Comments_Fav(option){
	
	var t = option.t ? option.t : 'fav';
	var successCallback = typeof option.successCallback!='undefined' ? option.successCallback: function(){};
	
	if(t=='fav'){
		Better_Notify_loading();
		$.getJSON('/ajax/blog/favorite', {
			bid: option.bid,
			type: option.type
		}, function(json){
			Better_Notify_clear();
			if(json.data=='success'){
				Better_Notify('收藏成功');
				successCallback();
			}else if(json.exception){
				Better_Notify(json.exception);
			}
		});
	}else if(t=='unfav'){
		Better_Confirm({
			msg: betterLang.global.unfavorite.confirm_title,
			onConfirm: function(){
				Better_Notify_loading();
				$.getJSON('/ajax/blog/unfavorite', {
					bid: option.bid
				}, function(json){
					Better_Confirm_clear();
					if(json.data=='success'){
						Better_Notify('取消收藏成功');
						successCallback();
					}else if(json.exception){
						Better_Notify(json.exception);
					}
				});
			}
		});
	}
}


$(function(){
	Better_Pager_Reset('comments');
	Better_comments(1);
	
	$('#to_shout').click(function(){
		var nextspan = $(this).nextAll('span');
		if($(this).attr('checked')){
			nextspan.show();
		}else{
			nextspan.hide();
			$('#sync').attr('checked', false);
		}
	});
	
	
	$('#post_commment').click(function(){
		Better_postComment(this, $('#bid').val(), function(json){
			$('#div_comments').prepend(json.msg);
			$('#comment_content').val('');
			$('#comments_count').text(parseInt($('#comments_count').text())+1);
		}, 0);
	});
	
	//计数
	$('#comment_content').keyup(function(){
		Better_Commment_setRemainCounts($(this));
	}).mousedown(function(){
		Better_Commment_setRemainCounts($(this));
	});
	
	
	//收藏动作
	$('#comment_fav').click(function(){
		var a = $(this);
		var t = a.attr('t');
		Better_Comments_Fav({
			t: t,
			bid: $('#bid').val(),
			type: $('#btype').val(),
			successCallback: function(){
				if(t=='fav'){
					a.attr('t', 'unfav');
					a.html('取消收藏');
				}else{
					a.attr('t', 'fav');
					a.html('收藏');
				}
			}
		});
	});
	
	//转发
	$('#comment_rt').bind('click', blog, Better_Transblog);
	
	
});
