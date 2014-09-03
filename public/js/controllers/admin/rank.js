
$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/userrank';
	});
	
	$('td.pager select').change(function(){
		pageToJump = $(this).val();
		$('#page').val(pageToJump);
		$('#search_form').trigger('submit');
	});
	
});