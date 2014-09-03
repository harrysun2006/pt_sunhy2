$(function() {

	var tabContainers = $('div.tabs > div');
	tabContainers.hide().filter(':first').show();
        
	$('div.tabs ul.tabNavigation a').click(function () {
		tabContainers.hide();
		$(this.hash).show();
		$('div.tabs ul.tabNavigation a').removeClass('selected');

        $(this).addClass('selected');
        
		$(this.hash).find('a').filter(':first').trigger('click');
		
		return false;
	}).filter(':first').click();

	
	/**
	 * 找开开用户
	 */
	$('#btnFindBetter').click(function(){
		$('#errFindBetter').hide().empty();
		$(this).attr('disabled', true);
		
		uname = $.trim($('#better_name').val());
		if (uname!='') {

			$('#better_result').show();
			$('#people_around').hide();
			$('#email_result').hide();	
			$('#tbl_findBetter').empty();
			
			Better_loadFindFriends({
				id: 'findBetter',
				url: '/ajax/user/search',
				posts: {
					keyword: uname,
					page: 1,
					withRealname: true
				},
				callbacks: {
					errorCallback: function(){},
					emptyCallback: function(){
						Better_EmptyResults('findBetter', betterLang.people.better.no_user);
					}
				},
				btns: ['friend']
			});

			$('#btnFindBetter').attr('disabled', false);
		} else {
			$('#errFindBetter').html(betterLang.people.better.invalid_word).fadeIn();
			$(this).attr('disabled', false);
		}
	});


	 /**
	  * 找Email联系人
	  */
	 $('#btnFindEmail').click(function(){
		 	$('#tbl_findEmail').empty();	
		 	$('#tbl_revertEmail').empty();
			$('#errFindEmail').hide().empty();
			$(this).attr('disabled', true);
			
			username = $.trim($('#email_user').val());
			domain = $.trim($('#email_domain').val());
			password = $('#email_pass').val();

			if (username=='' || domain=='') {
				$('#errFindEmail').html(betterLang.people.email.invalid_email).fadeIn();
				$(this).attr('disabled', false);
			} else if (password=='') {
				$('#errFindEmail').html(betterLang.people.email.invalid_password).fadeIn();
				$(this).attr('disabled', false);
			} else {
				var tbl = $('#tbl_findEmail');
				tbl.empty();
				$('#tbl_revertEmail').empty();
				$('#email_result').show();	
				$('#better_result').hide();
				$('#people_around').hide();
				
				Better_loadFindFriends({
					id: 'findEmail',
					url: '/ajax/service/emailcontacts',
					posts: {
						username: username,
						password: password,
						domain: domain
					},
					callbacks: {
						errorCallback: function(){},
						emptyCallback: function(){
							Better_EmptyResults('findEmail', betterLang.people.better.no_user);
						},
						completeCallback: function(){
							
							$('#tbl_revertEmail').empty();
							 
							Better_loadFriendsList({
								id: 'revertEmail',
								url: '/ajax/service/inviteemailfriends',
								posts: {
									username: username,
									password: password,
									domain: domain,
									use_last_cache: 1
								},
								callbacks: {
									errorCallback: function(){},
									emptyCallback: function(){
										Better_EmptyResults('revertEmail', betterLang.people.better.no_user);
									}
								}
							});
						}
					}
				});
				
				/*Better_loadFriendsList({
					id: 'revertEmail',
					url: '/ajax/service/emailrevertcontacts',
					posts: {
						username: username,
						password: password,
						domain: domain
					},
					callbacks: {
						errorCallback: function(){},
						emptyCallback: function(){
							Better_EmptyResults('revertEmail', betterLang.people.better.no_user);
						}
					}
				});*/
				
				$(this).attr('disabled', false);
			}
	 });
	 
	 //	找better用户回车事件
	 $('#better_name').keypress(function(e){ 
		 return Better_TriggerClick(e, $('#btnFindBetter'));
	 });
	 
	 //	找MSN用户回车事件
	 $('#find_msn input[type="text"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindMsn'));
	 });
	 $('#find_msn input[type="password"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindMsn'));
	 });
	 
	 //	找Email联系人回车事件
	 $('#find_email input[type="text"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindEmail'));
	 });
	
	 $('#find_email input[type="password"]').keypress(function(e){
		 return Better_TriggerClick(e, $('#btnFindEmail'));
	 });
	 
	 
	 //附近的人  加好友 链接
	 $('a.add_friend').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 Better_Friend_Request({data:{uid: uid, gender: gender, id: 'btnUserFriendBtn_'}, currentTarget: {id: id}});
	 });
	 
	 
	 //附近的人  删好友 链接
	 $('a.remove_friend').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 nickname=tmpId[3];
		 Better_Friend_Remove({data:{uid: uid, gender: gender, id: 'btnUserFriendBtn_', nickname: nickname}, currentTarget: {id: id}});
	 });
	 
	 
	 //加关注
	 $('a.add_follow').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 nickname=tmpId[3];
		 Better_Follow({data:{uid: uid, gender: gender, id: 'betterUserBtn_', nickname: nickname}, currentTarget: {id: id}});
	 });
	 
	 //删关注
	 $('a.remove_follow').click(function(e){
		 id = $(this).attr('id');
		 tmpId = id.split('_');
		 uid=tmpId[1];
		 gender=tmpId[2];
		 nickname=tmpId[3];
		 Better_Unfollow({data:{uid: uid, gender: gender, id: 'betterUserBtn_', nickname: nickname}, currentTarget: {id: id}});
	 });
});