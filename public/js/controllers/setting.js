// load pages
function load(url) {
	$('#tab_content').addClass('loading');
	$('#tab_content').html('<span>'+betterLang.global.loading+'</span>');
	$('#tab_content').load(url, 
			function() {
				$('#tab_content').removeClass('loading');
			});
}

function getArchor() {
	url = window.location.href;
	results = url.split('#');
	if( results.length == 1 )
		return false;
	else
		return results[1];
}

var syncing = false;

$(function() {
	
	$('ul.tabNavigation li:first a').css('width', '110px');
	
	if (betterUser.cell_no) {
		$('#cell_tips').show();
	}
	
	$('#resend_enable_email').click(function(){
		$('#err_email').html('<img src="images/loading.gif" alt="" />');

		$.post('/setting/renable', {
			
		}, function(ree_json){
			if (ree_json.ok==1) {
				$('#err_email').html(betterLang.setting.has_send_email);
			}
		}, 'json');

		return false;
	});

	
	$('#mobile').keypress(function(e){
		if (e.which==13) {
			$('#btnBindCell').trigger('click');
		 }
		 return true;
	});

});
