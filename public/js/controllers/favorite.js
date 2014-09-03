/**
 * 收藏
 * 
 * @param page
 * @return
 */
function Better_User_loadFavorites(page)
{
	page = page ? page : 1;
	var key = 'blog_favorites';
	
	Better_Pager({
		key: key,
		next: betterLang.noping.user.favorite.more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		last: betterLang.user.favorite.no_more_messages.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_loadFavorites
	});
	
	Better_loadBlogs({
		id: key,
		url: '/ajax/blog/favorites',
		posts: {
			nickname: dispUser.nickname,
			page: page
		},
		inFavList: true,
		withHisFuncLinks: false,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.user.favorite.no_message.toString().replace('{NICKNAME}',dispUser.nickname));
			},
			afterUnfavoriteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').trigger('click');
			}
		}
	});

}


/**
 * 所有地点收藏
 * 
 * @param page
 * @return
 */
function Better_User_PoiFavorites(page)
{
	page = page ? page : 1;
	var key = 'poi_favorites';
	
	Better_Pager({
		key: key,
		callback: Better_User_PoiFavorites
	});
	
	Better_loadPois({
		key: key,
		url: '/ajax/user/poi_favorites',
		uid: dispUser.uid,
		page: page,
		callbacks: {
			completeCallback: function(json){
				if (json.rows.length > 0) {
					for (i = 0; i < json.rows.length; i++) {
						betterUser.poi_favorites.push(json.rows[i].poi_id);
					}
				}
			},
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.user.favorite.no_message.toString().replace('{NICKNAME}',dispUser.nickname));
			}
		}
	});
}


/**
 * 所有贴士收藏
 * 
 * @param page
 * @return
 */
function Better_User_TipsFavorites(page)
{
	page = page ? page : 1;
	var key = 'tips_favorites';
	
	Better_Pager({
		key: key,
		next:betterLang.noping.user.tipsfav_next.toString().replace('{NICKNAME}',dispUser.nickname),
		last:betterLang.noping.user.tipsfav_last.toString().replace('{NICKNAME}',dispUser.nickname),
		callback: Better_User_TipsFavorites
	});
	
	Better_loadBlogs({
		id: key,
		url: '/ajax/blog/favorites?type=tips',
		posts: {
			nickname: dispUser.nickname,
			page: page
		},
		inFavList: true,
		withHisFuncLinks: false,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.user.favorite.no_message.toString().replace('{NICKNAME}',dispUser.nickname));
			},
			afterUnfavoriteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').trigger('click');
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
	            case '#blog_favorites':
	            	$('#tbl_blog_favorites').empty();
					Better_User_loadFavorites(1);
					break;
	            	break;
	            case '#poi_favorites':
	            	$('#tbl_poi_favorites').empty();
					Better_User_PoiFavorites(1);
	            	break;
            	case '#tips_favorites':
            		$('#tbl_tips_favorites').empty();
					Better_User_TipsFavorites(1);
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