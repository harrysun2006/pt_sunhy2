function showBadges()
{
	key = 'badges';
	Better_Table_Loading(key);
	
	Better_Clear_Table_Loading(key);
	
	var a= '<li> <img src="http://127.0.0.4/images/badges/96/13.png" alt="狮子吼"/> <br/> <span>狮子吼</span></li><li> <img src="http://127.0.0.4/images/badges/96/52.png" alt="菁菁校园"/> <br/> <span>菁菁校园</span></li>';
	$('#badge_album').append(a);
}

function Better_Space_allBadges(url)
{
	$('.pager').addClass('ajax_loading');
	$('.pager').html('&nbsp;');
	$.get(url, {}, function(more) {
		$('#badge_album').append(more);
		$('.pager').removeClass('ajax_loading');
		Better_Space_BadgeEvent();
	}, 'html');

}

function Better_Space_BadgeEvent()
{
	 $('#badge_box a.badge_icons').unbind("click");
	 $('#badge_box a.badge_icons').click(function(){
		BETTER_BIG_BADGE_ID = parseInt($(this).attr('hash').replace('#bigbadge_', ''));
		BETTER_BIG_BADGE_UID = parseInt($(this).attr('uid'));
		}).fancybox({
				autoDimensions: true,
				scrolling: 'no',
				centerOnScroll: true,
				titleShow: false,
				'onStart' : function(){
						var _url = '/ajax/badge/getdiv?id=' + BETTER_BIG_BADGE_ID + '&uid=' + BETTER_BIG_BADGE_UID;
						var _html = $.ajax({
										  url: _url,
										  async: false
										 }).responseText;
						var divId = 'hidden_b_' + BETTER_BIG_BADGE_ID;
						$('#' + divId).html(_html);
			
						$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
						$('#fancybox-outer').css('background-color', '#1db8ee');
						if ($('#badge_users_'+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
							Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'badge_users_');
						}
				},
				'onClosed': function(){
					  $('#fancybox-outer').css('background-color', '#fff');
				}
	 });		
}

$(function() {
	$('#more_button').click(function() {
		var url = $(this).attr("href");
		Better_Space_allBadges(url);
		return false;
	});
});
