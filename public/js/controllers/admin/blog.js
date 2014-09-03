function Better_Admin_loadBlogs(options)
{
	
	$.post(BETTER_ADMIN_URL+'/ajax/blog/do/list', options, function(json){
		
	}, 'json');
}

$(function(){
	
	Better_Admin_loadBlogs({
		page: 1
	});
});