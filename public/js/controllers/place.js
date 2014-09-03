/**
 * 附近的消息
 * 
 * @param page
 * @param renew
 * @return
 */
function Better_Place_Messages(page)
{
	page = page ? page : 1;
	var key = 'messages';

	Better_Pager({
		key: key,
		next: betterLang.place.message.more_messages,
		last: betterLang.place.message.no_more_messages,		
		callback: Better_Place_Messages
	});
	
	Better_loadBlogs({
		id: key,
		url: '/ajax/blog/searchqbs',
		posts: {
			page: page,
			lon: pageLon,
			lat: pageLat
		},
		withHisFuncLinks: false,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.place.message.no_message);
			},
			afterDeleteCallback: function(){
				$('a[href="#messages"]').trigger('click');
			}
		}
	});

}

/**
 * 附近的照片
 * 
 * @param page
 * @param renew
 * @return
 */
function Better_Place_Photos(page)
{
	page = page ? page : 1;
	var key = 'photos';

	Better_Pager({
		key: key,
		next: betterLang.place.photo.more_messages,
		last: betterLang.place.photo.no_more_messages,
		callback: Better_Place_Photos
	});	
	
	Better_loadBlogs({
		id: key,
		url:  '/ajax/blog/searchqbs',
		posts: {
			page: page,
			lon: pageLon,
			lat: pageLat,
			withPhoto: 1
		},
		withHisFuncLinks: false,
		callbacks: {
			emptyCallback: function() {
				Better_EmptyResults(key, betterLang.place.photo.no_message);
			},
			afterDeleteCallback: function(){
				$('a[href="#messages"]').trigger('click');
			}
		}
	});

}

/**
 * 附近的人
 * 
 * @param page
 * @return
 */
function Better_Place_Users(page)
{
	var key = 'users';
	page = page ? page : 1;

	Better_Pager({
		key: key,
		next: betterLang.place.user.more_messages,
		last: betterLang.place.user.no_more_messages,
		callback: Better_Place_Users
	});	
	
	Better_loadUsers({
		id: key,
		url: '/ajax/user/searchqbs',
		posts: {
			lon: pageLon,
			lat: pageLat,
			page: page
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.place.user.no_message);
			}
		}
	});

}

/**
 * 初始化google地图
 * 
 * @return
 */
function Better_Place_initializeGmap()
{
	lat = pageLat ? pageLat : 39.917;
	lon = pageLon ? pageLon : 116.397;

	if (GBrowserIsCompatible()) {
		map = new GMap2(document.getElementById("map_canvas"));
		map.addControl(new GSmallMapControl());
		map.setMapType(G_DEFAULT_MAP_TYPES[0]);
		map.setCenter(new GLatLng(lat, lon), 14);
		map.enableScrollWheelZoom();
		
		Gmap = map;
        marker = new GMarker(Gmap.getCenter(), {draggable: true});

        map.addOverlay(marker);

	}	
}

$(function() {

	Better_InitGMap('Better_Place_initializeGmap');
	
	$('#post_btn').click(function(){
		len = Better_GetPostLength();
		if (len>Better_PostMessageMaxLength) {
			alert(betterLang.blog.post_size_to_large.replace('%s', Better_PostMessageMaxLength));
		} else if (len<Better_PostMessageMinLength) {
			alert(betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength));
		} else {
			
			status_text = $('#status_text').val();
			upbid = $('#upbid').val();
			Better_Notify_loading();

			$.post('/ajax/blog/post', {
				message: status_text, 
				upbid: upbid,
				lon: pageLon,
				lat: pageLat,
				address: pageAddress,
				city: pageCity,
				attach: $('#attach').val()
				}, function(json){
					Better_Notify_clear();
					
					if (Better_AjaxCheck(json)) {
						if (json.code=='success') {
							$('#status_text').val('');
							$('#attach').val('');
							$('#upbid').val(0);
							$('#fileDesc').empty().hide();
							$('#divFileDesc').hide();
							$('#txtCount').html(Better_PostMessageMaxLength);
							
							$('a[href="#messages"]').trigger('click');
						}  else if (json.code=='need_check') {
							$('#status_text').val('');
							$('#attach').val('');
							$('#upbid').val(0);
							$('#fileDesc').empty().hide();
							$('#divFileDesc').hide();
							$('#txtCount').html(Better_PostMessageMaxLength);
							
							Better_Notify({
								msg: betterLang.post.need_check
							});					
						} else if (json.code=='you_r_muted') {
							$('#status_text').val('');
							$('#attach').val('');
							$('#upbid').val(0);
							$('#fileDesc').empty().hide();
							$('#divFileDesc').hide();
							$('#txtCount').html(Better_PostMessageMaxLength);
							
							Better_Notify({
								msg: betterLang.post.forbidden
							});								
						}
					}
			}, 'json');
		}
	});

	$('#fileDesc').addClass('notice');
	$('#fileDesc').css('margin-left', '0px');

	var ajaxUploader = new AjaxUpload('btnUpload', {
		action: '/ajax/attach/upload',
		name: 'myfile',
		data: {
			attach: $('#attach').val()
		},
		autoSubmit: true,
		onSubmit: function(file, ext){
			$('#divFileDesc').show();
			
			if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){
				$('#fileDesc').html(betterLang.post.invalid_file_format).fadeIn();			
				return false;
			}
			
			$('#fileDesc').removeClass('notice').html('<img src="images/loading.gif" alt="'+betterLang.post.uploading+'" />').show();
			$('#btnUpload').attr('disabled','disabled');
			$('#btnPostNew').attr('disabled','disabled');

		},
		onComplete: function(file, response){
			eval('rt='+response);

			$('#btnUpload').attr('disabled','');
			$('#btnPostNew').attr('disabled','');
			
			if (rt.has_err=='1') {
				$('#divFileDesc').show();
				$('#fileDesc').addClass('notice').html(rt.err);
			} else {
				$('#divFileDesc').show();
				$('#fileDesc').addClass('notice').html(betterLang.blog.upload_success+'&nbsp;');
				$('#attach').val(rt.attach);

				del = $(document.createElement('a'));
				del.attr('href', 'javascript:void(0)');
				del.text(betterLang.blog.deleteit);
				del.click(function(){
					Better_Confirm({
						msg: betterLang.blog.upload_delete_confirm,
						onConfirm: function(){
							Better_Notify_loading();
							
							$.post('/ajax/attach/delete', {
								attach: $('#attach').val()
							}, function(da){
								Better_Notify_clear();
								$('#divFileDesc').show();
								
								if (da.err!='') {
									$('#fileDesc span').html(da.err);
								} else {
									$('#fileDesc').html(betterLang.blog.upload_delete_success);
									$('#attach').val('');
								}
							}, 'json');							
						}
					});
				});
				$('#fileDesc').append(del);
			}
			$('#fileDesc').fadeIn();

		}
	});

	$('#fileDesc').mouseover(function(){
		ajaxUploader.disable();
		ajaxUploader.destroy();
	}).mouseout(function(){
		ajaxUploader.enable();
		ajaxUploader._createInput();
		ajaxUploader._rerouteClicks();
	});

	$('#status_text').keyup(Better_FilterStatus).mousedown(Better_FilterStatus);
	$('#txtCount').html( Better_PostMessageMaxLength );		

	var tabContainers = $('div.tabs > div');
	tabContainers.hide().filter(':first').show();
        
	 $('div.tabs ul.tabNavigation a').click(function () {
	    	tabContainers.hide();
	    	tabContainers.filter(this.hash).show();

	    	$('div.tabs ul.tabNavigation a').removeClass('selected');

            $(this).addClass('selected');
	    	switch(this.hash) {
		    	case '#messages':
		    		$('#tbl_messages').empty();
		    		Better_Place_Messages();
		    		break;
		    	case '#users':
		    		$('#tbl_users').empty();
		    		Better_Place_Users();
		    		break;
		    	case '#photos':
		    		$('#tbl_photos').empty();
		    		Better_Place_Photos();
		    		break;
	    	}
	    	
	    		return false;
	    }).filter(':first').click();
        
});