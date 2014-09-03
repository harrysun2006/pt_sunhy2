$(function(){
	
	$('#step4_btn').click(function(){
		window.location='/home';
	});	
	$('img.avatar').error(function(){
		$(this).attr('src', Better_AvatarOnError);
	});
	
});