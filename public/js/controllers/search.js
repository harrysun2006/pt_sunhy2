/**
 * 搜索消息 
 * 
 * @param page
 * @return
 */
function Better_Search_loadBlogs(page)
{
	page = page ? page : 1;
	var key = 'messages';
	text = $.trim($('#search_key').text());
	
	if (text!='') {
		Better_Pager({
			key: key,
			next: betterLang.search.more_messages,
			last: betterLang.search.no_more_messages,
			callback: Better_Search_loadBlogs
		});
		
		Better_loadBlogs({
			id: key,
			url: '/ajax/blog/search',
			posts: {
				page: page,
				search_text: text,
				search_range: $('#search_range').text()
			},
			withHisFuncLinks: false,
			callbacks: {
				emptyCallback: function(){
					Better_EmptyResults(key, betterLang.search.no_result);
				},
				completeCallback: function(result){
					$('#result_count').text(betterLang.noping.search.result.info.toString().replace('{COUNT}',result.count))
				},
				afterDeleteCallback: function(){
					$('#tbl_messages').empty();
					Better_Search_loadBlogs(1);
				}
			}
		});

	}
	
}

function Better_Search_Poi(page)
{
	page = page ? page : 1;
	var key = 'messages';
	text = $.trim($('#search_key').text());
	
	Better_Pager({
		key: key,
		callback: Better_Search_Poi
	});

	Better_loadPois({
		key: key,
		url: '/ajax/poi/search',
		page: page,
		keyword: text,
		without_mine: true,
		order: 'force_distance',
		lon: betterUser.lon,
		lat: betterUser.lat,
		callbacks: {
			completeCallback: function(result){
				$('#result_count').text(betterLang.noping.search.result.info.toString().replace('{COUNT}',result.count))
			},
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.search.no_result);
			}
		}
	});		
}

function Better_Search_User(page)
{
	page = page ? page : 1;
	var key = 'messages';
	
	Better_Pager({
		key: key,
		callback: Better_Search_User
	});
	
	Better_loadUsers({
		id: key,
		url: '/ajax/user/search',
		posts: {
			keyword: $.trim($('#search_key').text()),
			page: page
		},
		btns: ['dmessage', 'follow', 'block', 'friend'],
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.search.no_result);
			},
			completeCallback: function(result){
				$('#result_count').text(betterLang.noping.search.result.info.toString().replace('{COUNT}',result.count))
			}
		}
	});	
}

$(function(){
	$('#search_text').val($.trim($('#search_key').text()));
	if ($('#search_range').text()=='poi') {
		Better_Search_Poi(1);		
	} else if ($('#search_range').text()=='user') {
		$('#global_search_user').closest('ul.dropdown').prevAll('a#search_type').text($('#global_search_user').text());
		$('#global_search_range').val('user');
		Better_Search_User(1);		
	} else {
		Better_Search_loadBlogs(1);
		$('#global_search_blog').closest('ul.dropdown').prevAll('a#search_type').text($('#global_search_blog').text());
		$('#global_search_range').val('blog');
	}
	
});