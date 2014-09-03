
Better_This_Script = '/poi/'+Better_Poi_Id;

/**
 * 附近的消息
 * 
 * @param page
 * @param renew
 * @return
 */

function Better_Poi_Messages(page)
{
	/*page = page ? page : 1;

	window.location = Better_This_Script+'#xmessages';
	
	Better_Pager({
		key: 'messages',
		next: betterLang.poi.blogs.more_blogs,
		last: betterLang.poi.blogs.no_more_blogs,
		callback: Better_Poi_Messages
	});	

	Better_loadBlogs({
		id: 'messages', 
		url: '/ajax/poi/poi_shouts',
		posts: {
			page: page,
			poi_id: Better_Poi_Id,
			search_range: 'normal'
		},
		withHisFuncLinks: true,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults('messages', betterLang.poi.blogs.no_blogs);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#messages"]').trigger('click');
			}	
		}
	});	*/
}

function Better_Poi_Tips(page)
{
	page = page ? page : 1;
	Better_Pager({
		key: 'tips',
		next: betterLang.poi.tips.more_tips,
		last: betterLang.poi.tips.no_more_tips,
		callback: Better_Poi_Tips
	});	

	Better_loadBlogs({
		id: 'tips', 
		url: '/ajax/poi/poi_tips',
		posts: {
			page: page,
			poi_id: Better_Poi_Id
		},
		withHisFuncLinks: true,
		withFavLinks: true,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults('tips', betterLang.poi.tips.no_tips);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#tips"]').trigger('click');
			}
		}
	});	
}

/**
 * 初始化“雷达”的Google地图
 * 
 * @return
 */
function Better_Around_initializeGmap()
{
	lat = Better_Poi_Detail.lat ? Better_Poi_Detail.lat : 39.917;
	lon = Better_Poi_Detail.lon ? Better_Poi_Detail.lon : 116.397;

	if (GBrowserIsCompatible()) {
		amap = new GMap2(document.getElementById("map_canvas"));
//		var customUI = amap.getDefaultUI();
//		customUI.maptypes.hybrid = false;

//		amap.setUI(customUI);
//		amap.addControl(new GOverviewMapControl());
		amap.setMapType(G_DEFAULT_MAP_TYPES[0]);
		amap.setCenter(new GLatLng(lat, lon), gmapZomm);
		amap.enableScrollWheelZoom();
        amarker = new GMarker(amap.getCenter());
        amap.addOverlay(amarker);
	}	
}

/**
 * 本poi的checkin历史
 * @return
 */
function Better_Poi_Checkins(page)
{
	/*page = page ? page : 1;
	var key = 'users';
	window.location = Better_This_Script+'#xusers';
	
	lon = pageLon;
	lat = pageLat;
	Better_Pager({
		key: key,
		next: betterLang.poi.checkin.more_user,
		last: betterLang.poi.checkin.no_more_user,
		callback: Better_Poi_Checkins
	});	

	Better_loadBlogs({
		id: key,
		url: '/ajax/poi/poi_checkins',
		posts: {
			poi_id: Better_Poi_Id,
			page: page
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.poi.checkin.no_user);
			}
		}
	});	*/
}


/**
 * 本poi的好友动态
 * @return
 */
function Better_Poi_Friendstimeline(page)
{
	page = page ? page : 1;
	var key = 'friendstimeline';
	window.location = Better_This_Script+'#xfriendstimeline';
	
	lon = pageLon;
	lat = pageLat;
	Better_Pager({
		key: key,
		next: '更多好友动态',
		last: '没有更多好友动态了',
		callback: Better_Poi_Friendstimeline
	});	

	Better_loadBlogs({
		id: key,
		url: '/ajax/poi/poifriendstimeline',
		posts: {
			poi_id: Better_Poi_Id,
			page: page
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				Better_EmptyResults(key, '没有更多好友动态了');
			}
		}
	});	
}


/**
 * 想来这里的好友
 * @return
 */
function Better_Poi_Friendstodo(page)
{
	page = page ? page : 1;
	var key = 'friendstodo';
	window.location = Better_This_Script+'#xfriendstodo';
	
	lon = pageLon;
	lat = pageLat;
	Better_Pager({
		key: key,
		next: '更多好友动态',
		last: '没有更多好友动态了',
		callback: Better_Poi_Friendstodo
	});	

	Better_loadBlogs({
		id: key,
		url: '/ajax/poi/poifriendstodo',
		posts: {
			poi_id: Better_Poi_Id,
			page: page
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				Better_EmptyResults(key, '没有更多好友动态了');
			}
		}
	});	
}

//选择好友和邀请好友去某地的JS
//关于选择好友处理后提交到后台的格式：没有选择任何好友的时候提交的fuids是',',选择了好友的格式是',uid1,uid2,uid3,'
//因此选择好友的数量为','的数量减去1，替换的原则是把',uid,'替换成','.
function selectUserHandler(uid,nickname){
	var oldcount = parseInt($('#invitation-count').html());
	var oldnames = ','+$('#invitation-names').html();
	if ( $('#row_'+uid).hasClass("selected-item") ){
		$('#row_'+uid).removeClass('selected-item');
		var oldstr = $('#fuids').val();
		var rep = ','+uid+','
		var newstr =  oldstr.replace(rep,',');
		oldcount = oldcount-1;
		var repname = ','+nickname+',';
		var newnames = oldnames.replace(repname,',');
		newnames = newnames.substr(1,newnames.length-1);
		$('#invitation-names').html(newnames);
		$('#invitation-count').html(oldcount);
		$('#fuids').val(newstr);
	}else{
		if(oldcount == 50 || oldcount > 50){
			Better_Notify("一次最多只能邀请50个好友！");	
			return false;
		}
		var addstr = $('#fuids').val()+uid+",";
		var addname = nickname+","+$('#invitation-names').html();
		//addname = addname.substr(0,addname.length-1);
		$('#invitation-names').html(addname);		
		oldcount = oldcount+1;
		$('#invitation-count').html(oldcount);
		$('#row_'+uid).addClass('selected-item');
		$('#fuids').val(addstr);
	}
}

$(function() {
	$('#_shouta').click(function(){
		Better_Switch_Shout_Form('tips');
		$('#shouta').trigger('click');
		return false;
	});	
	if ($.browser.opera) {
		$('.activityimage').attr('target', '_blank');
	} else {
		$('.activityimage').fancybox();
		$('.activityimage').click(function(){
			$('#fancybox-outer').css('width','100%');//调整todo弹出窗口的高度
		});
	}
	$('#checkina').click(function(){
		Better_CheckinGotLL();
		return false;
	});

	$('#todoa').click(function(){
		Better_Switch_Shout_Form('todo');
		$('#shouta').trigger('click');
		$('#fancybox-wrap, #fancybox-outer').css('height','200px');//调整todo弹出窗口的高度
	});


	$('#switch_scode').click(function(){
		$('#img_code').attr('src', '/Scode?r='+Math.round(Math.random()*1000000));
	});	
	
    $('#checkin_btn').click(function(){
    	ready_to_checkin_poi = $('#ready_to_checkin_poi').val();
    	priv = Better_getPostCheckinPriv();
    	lon = wifiLon;
    	lat = wifiLat;
    	range = wifiRange;
    	abid = $('#ready_to_checkin_ab_poi').val();	
    	//scode = $('#code').val();
    	
    	checkin_need_sync = $('#checkin_need_sync').attr('checked')? 1 : 0;

    	if (ready_to_checkin_poi || abid) {
    		Better_Notify_loading();

    		$.post('/ajax/location/poi_checkin', {
    			message: $('#checkin_status_text').val(),
    			attach: $('#checkin_attach').val(),
    			poi_id: ready_to_checkin_poi,
    			lon: lon,
    			lat: lat,
    			range: range,
    			priv: priv,
    			abid: abid,
    			checkin_need_sync: checkin_need_sync,
    			scode: $('#code').val()
    		}, function(ckiJson){
    			Better_Notify_clear();

    			if (Better_AjaxCheck(ckiJson)) {
    				codes = ckiJson.codes;
    				$('#code').val('');
    				switch(ckiJson.code) {
	    				case codes.SCODE_ERROR:
	    					Better_Notify({
	    						msg: betterLang.post.scode_error
	    					});
	    					$('#fancybox-wrap, #fancybox-outer').height(282);
	    					$('#height_fb').val(282);
	    					$("#scode").removeClass("hide");
	    					$('#img_code').attr('src', '/Scode?r='+Math.round(Math.random()*1000000));
	    					break;
    					case codes.SCODE:
    						Better_Notify({
	    						msg: betterLang.post.scode
	    					});
    						$('#fancybox-wrap, #fancybox-outer').height(282);
    						$('#height_fb').val(282);
    	    				$("#scode").removeClass("hide");
    	    				$('#img_code').attr('src', '/Scode?r='+Math.round(Math.random()*1000000));
    	    				break;
    					case codes.YOU_R_MUTED:
    						Better_Notify({
	    						msg: betterLang.post.forbidden
	    					});
	    					break; 
	    				case codes.DUPLICATED_CHECKIN:
	    					Better_Notify({
	    						msg: betterLang.global.checkin.duplicated_checkin
	    					});
	    					break;    				
	    				case codes.INVALIDPOI:
	    					Better_Notify({
	    						msg: betterLang.global.checkin.invalid_poi
	    					});
	    					break;
	    				case codes.KARMA_TOO_LOW:
	    					Better_Notify({
	    						msg: betterLang.global.checkin.karma_too_low
	    					});
	    					break;
	    				case codes.TOO_FAST_CHECKIN:
	    					Better_Notify_clear();
	    					Better_Notify({
	    						msg: betterLang.global.too_fast_checkin
	    					});
	    					break;	    					
	    				case codes.SUCCESS:
	    					
	    					$("#scode").addClass("hide");
	    					if($("#checkina").hasClass('checkin-l')){
		    					$("#checkina").removeClass("checkin-l");
		    					$("#checkina").addClass("checkin");
		    					$("#action-todo").hide();
								$("#todoa").show();		
	    					}
	    					Better_Notify({	    						
	    						msg: betterLang.noping.global.checkin.success.toString().replace('{WHATHAPPEND}',Better_parseAchievement(ckiJson, betterLang.global.this_checkin)),
	    						height:180
	    					});

	    					$.fancybox.close();

	    					betterUser.last_checkin_poi = ready_to_checkin_poi;
	    					betterUser.lbs_report = Date.parse(new Date());
	    					betterUser.address = Better_Poi_Detail.address;
	    					betterUser.last_checkin_poi_name = Better_Poi_Detail.name;
	    					betterUser.city = Better_Poi_Detail.city;
	    					
	    					Better_EnableShout();

	    					if(ready_to_checkin_poi==Better_Poi_Id){
	    						if (window.location.toString().indexOf('#xusers')>0) {
	    							$('a[href="#users"]').attr('disabled', false).trigger('click');
	    						} else {
	    							window.location = Better_This_Script+'#xusers';
	    						}
	    						
	    						$('#ready_to_shout_poi').val(Better_Poi_Id);
	    						$('#ready_to_shout_address').text(Better_Poi_Detail.address);
	    						$('#ready_to_shout_city').text(Better_Poi_Detail.city);
	    						$('#ready_to_shout_poi_name').text(Better_Poi_Detail.name);	
	    					}else{
	    						window.location.href = '/poi/'+ready_to_checkin_poi;
	    					}
	    					break;
	    				case codes.INVALIDLL:
	    					Better_Notify({
	    						msg: betterLang.global.checkin.invalid_ll
	    					});
	    					break;
	    				case codes.ERROR:
	    				default:
	    					Better_Notify({
	    						msg: betterLang.global.check.failed
	    					});
	    					break;
    				}
    			}
    		}, 'json');

    	} else { 
    		Better_Notify({
    			msg: betterLang.global.checkin.no_valid_poi
    		});
    	}
    });	

	$('#cancel_checkin, #close_check').click(function(){
		$('#checkindlg div.poi_div').hide();
		
		$('#priv_sel').attr('priv', 'public').text(betterLang.global.priv_public);
		$('#checkin_public').trigger('click');
		
		$.fancybox.close();

		return false;
	});	
	$('#close_todo').click(function(){
		$.fancybox.close();
	});

	var tabContainers = $('div.tabs > div');
	tabContainers.hide().filter(':first').show();
	$('#tbl_tips').empty();
	Better_Poi_Tips(1);
//    $('div.tabs ul.tabNavigation a').click(function () {
//    	if(typeof($(this).attr('disabled'))!='undefined' && $(this).attr('disabled')=='true'){
//			return false;
//		}
//
//    	tabContainers.hide();
//    	tabContainers.filter(this.hash).show();
//
//    	$('div.tabs ul.tabNavigation a').removeClass('selected');
//        $(this).addClass('selected');
//
//    	switch(this.hash) {
//	    	/*case '#messages':
//	    		$('#tbl_messages').empty();
//	    		Better_Poi_Messages(1);
//	    		break;
//	    	case '#users':
//	    		$('#tbl_users').empty();
//	    		Better_Poi_Checkins(1);
//	    		break;*/
//	    	case '#tips':
//	    		$('#tbl_tips').empty();
//	    		Better_Poi_Tips(1);
//	    		break;
//	    	case '#friendstimeline':
//	    		$('#tbl_friendstimeline').empty();
//	    		Better_Poi_Friendstimeline(1);
//	    		break;
//	    	case '#friendstodo':
//	    		$('#tbl_friendstodo').empty();
//	    		Better_Poi_Friendstodo(1);
//	    		break;
//	    		
//    	}
//
//    	return false;
//    }).ajaxStart(function(e, q, o){
//    	$(this).attr('disabled', true);
//    	$('#change_place_checkin').attr('disabled', true);
//    	Better_Ajax_processing = true;
//    }).ajaxComplete(function(e, q, o){
//    	$(this).attr('disabled', false);
//    	$('#change_place_checkin').attr('disabled', false);
//    	Better_Ajax_processing = false;
//    });	

	//	ajax history
    $.history.init(function(tab){
    	Better_Load_Tab(tab);
    });
    
	if (Better_Poi_Id) {
		$('#ready_to_checkin_poi').val(Better_Poi_Id);
		$('#ready_to_checkin_address').text(Better_Poi_Detail.address);
		$('#ready_to_checkin_city').text(Better_Poi_Detail.city);
		$('#ready_to_checkin_poi_name').text(Better_Poi_Detail.name);

		$('#ready_to_shout_poi').val(Better_Poi_Id);
		$('#ready_to_shout_address').text(Better_Poi_Detail.address);
		$('#ready_to_shout_city').text(Better_Poi_Detail.city);
		$('#ready_to_shout_poi_name').text(Better_Poi_Detail.name);		
	}

	$('#poi_checkins').click(function(){
		$('a[href=#users]').trigger('click');
	});

	$('#poi_shouts').click(function(){
		$('a[href=#messages]').trigger('click');
	});

	$('#tbl_checkined').show();
	$('#tbl_not_checkined').hide();

	$('#ready_to_shout_at, #ready_to_shout_city, #ready_to_shout_poi_name').show();
	$('#ready_to_shout_not_checkined').hide();

	if (Better_Poi_Favorited) {
		$('#fav_this_poi').bind('click', {
			poi_id: Better_Poi_Id
		}, Better_Unfavorite_Poi);
	} else {
		$('#fav_this_poi').bind('click', {
			poi_id: Better_Poi_Id
		}, Better_Favorite_Poi);
	}
	
	
	$('#report_this_poi').click(function(){
		$('#poi_report_dlg').dialog('destroy');
		$('#poi_report_dlg').attr('title', betterLang.report.poi.title);
		$('#poi_report_dlg').dialog({
			bgiframee: true,
			modal : true,
			autoOpen: true,
			resizable: false,
			zIndex: 4999
		});
		
	});
	$("#reason_report").change(function(){
		$('.detailreason textarea').val('');
		if($("#reason_report option:selected").val() == 'other'){
			$('.detailreason').show();			
		}else{
			$('.detailreason').hide();			
		}
	});
	$('#btn_report_poi').click(function(){
		var poi_id = $('#poi_id_report').val();
		var reason = $('#reason_report option:selected').val();
		var content= $('.detailreason textarea').val();
		
		Better_Notify(betterLang.global.action.please_wait);
		$.getJSON('/ajax/poi/report',{
			poi_id: poi_id,
			reason: reason,
			content:content
		}, function(json){
			Better_Notify_clear();
			$('#poi_report_dlg').dialog('close');
			
			if(json.result.code==1){
				Better_Notify(betterLang.report.poi.success);
			}else if(json.result.code==-3){
				Better_Notify(betterLang.report.poi.duplicate);
			}else{
				Better_Notify(betterLang.denounce.failed);
			}
		});
	});
	
	
	$('#checkin_small').mouseover(function(){
		$(this).attr('src', '/images/checkin_small_blue.png?v=2222');
	}).mouseout(function(){
		$(this).attr('src', '/images/checkin_small.png?v=2222');
	});
	
	$('#tips_small').mouseover(function(){
		$(this).attr('src', '/images/tips_small_blue.png?v=2222');
	}).mouseout(function(){
		$(this).attr('src', '/images/tips_small.png?v=2222');
	});

	$('#todo_small').mouseover(function(){
		$(this).attr('src', '/images/todo_small_blue.png?v=2222');
	}).mouseout(function(){
		$(this).attr('src', '/images/todo_small.png?v=2222');
	});

	//2011-3-7
	$('#checkin_txtCount').html( Better_PostMessageMaxLength );
	$('#checkin_status_text').keydown(function(e){
		try {
			if (e.which==37) {
				this.setSelectionRange(this.selectionStart-1, this.selectionStart-1)
			} else if (e.which==39) {
				this.setSelectionRange(this.selectionStart+1, this.selectionStart+1)
			}
		} catch (se) {
			len = getCaret(this);

			if (e.which==37) {
		        var range = this.createTextRange();
		        range.move("character", len-1);
		        range.select(); 
			} else if (e.which==39) {
		        var range = this.createTextRange();
		        range.move("character", len+1);
		        range.select(); 				
			}
		}
	}).keyup(function(){Better_FilterStatus('checkin_');}).mousedown(function(){Better_FilterStatus('checkin_');});
	$('#tips_txtCount').html( Better_PostMessageMaxLength );
	$('#tips_status_text').keydown(function(e){
		try {
			if (e.which==37) {
				this.setSelectionRange(this.selectionStart-1, this.selectionStart-1)
			} else if (e.which==39) {
				this.setSelectionRange(this.selectionStart+1, this.selectionStart+1)
			}
		} catch (se) {
			len = getCaret(this);

			if (e.which==37) {
		        var range = this.createTextRange();
		        range.move("character", len-1);
		        range.select(); 
			} else if (e.which==39) {
		        var range = this.createTextRange();
		        range.move("character", len+1);
		        range.select(); 				
			}
		}
	}).keyup(function(){;Better_FilterStatus('tips_');
	}).mousedown(function(){Better_FilterStatus('tips_');
	}).focus(function (){
		if(this.value=='写个贴士分享给大家吧'){
			this.value="";
			this.style.color="#000000"
		}
		Better_FilterStatus('tips_');
	}).blur(function (){
		if(this.value==''){
			this.value="写个贴士分享给大家吧";
			this.style.color="#CBCBCB"
		}
		Better_FilterStatus('tips_');
	});
	


	$('#morebutton a').click(function(){	
		$('#moreactivities').show();
		$('#morebutton a').hide();
		$('#lessbutton a').show();
		$('#activitytitle').hide();
	});
	$('#lessbutton').click(function(){	
		$('#moreactivities').hide();
		$('#morebutton a').show();
		$('#lessbutton a').hide();
		$('#activitytitle').show();
	});
	$('#showalltips').click(function(){
		$('#showalltips').hide();
		$('tr.hide').show();
		$('#pager_tips').show();
	});
	
	
	$('#checkin-list').click(function(){
		$('#checkin-list').hide();
		$('#checkin-list-content').hide();
		$('#checkin-list-hide').show();
	});
	
	$('#checkin-list-hide').click(function(){
		$('#checkin-list-hide').hide();
		$('#checkin-list-content').show();
		$('#checkin-list').show();
	});
	
	$('#todo-list').click(function(){
		$('#todo-list').hide();
		$('#todo-list-content').hide();
		$('#todo-list-hide').show();
	});
	
	$('#todo-list-hide').click(function(){
		$('#todo-list-hide').hide();
		$('#todo-list-content').show();
		$('#todo-list').show();
	});
	
	$('#related-poi').click(function(){
		$('#related-poi').hide();
		$('#related-poi-content').hide();
		$('#related-poi-hide').show();
	});
	
	$('#related-poi-hide').click(function(){
		$('#related-poi-hide').hide();
		$('#related-poi-content').show();
		$('#related-poi').show();
	});
	
	$('#recommend-poi').click(function(){
		$('#recommend-poi').hide();
		$('#recommend-poi-content').hide();
		$('#recommend-poi-hide').show();
	});
	
	$('#recommend-poi-hide').click(function(){
		$('#recommend-poi-hide').hide();
		$('#recommend-poi-content').show();
		$('#recommend-poi').show();
	});
	
	$('#post_tip_btn').click(function(){		
		len = Better_GetPostLength('tips_');
		attach = $('#attach').val();
		if(!attach && $.trim($('#tips_status_text').val()) == "写个贴士分享给大家吧"){
			Better_Notify("您还没有输入贴士的内容");
		}else if (len>Better_PostMessageMaxLength) {
			Better_Notify({
				msg: betterLang.blog.post_size_to_large.replace('%s', Better_PostMessageMaxLength)
			});
		}else if (!attach && len<Better_PostMessageMinLength) {
			Better_Notify({
				msg: betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength)
			});
		}else{
//			Better_Confirm({
//				msg: '确认要发表贴士？',
//				onConfirm: function(){
					Better_Notify_loading();
					if (attach && $.trim($('#tips_status_text').val()) == "写个贴士分享给大家吧") status_text = "";
					else status_text = $('#tips_status_text').val();
					attach = $('#attach').val();
					upbid = $('#upbid').val();
					need_sync = $('#need_sync').attr('checked') ? 1 : 0;
					var len = Better_GetPostLength();
					var real_upbid = $('#real_upbid').val();
					var ready_to_shout_poi = parseInt($('#ready_to_shout_poi').val());
					$.post('/ajax/blog/post', {
						message: status_text, 
						upbid: upbid,
						real_upbid: real_upbid,
						attach: attach,
						priv: Better_getPostBlogPriv(),
						lon: wifiLon,
						lat: wifiLat,
						range: wifiRange,
						poi_id: ready_to_shout_poi,
						type: 'tips',
						need_sync: need_sync
					}, function(json){
						Better_Notify_clear();
						Better_Confirm_clear();
						if (Better_AjaxCheck(json)) {
							if(json.code=='success'){
								success_notify = betterLang.global.post.success;
								Better_Notify({
									msg: success_notify+' '+Better_parseAchievement(json),
									close_timer: 2
								});
								//清除刚刚发布的贴士的痕迹
								$('div#btnUpload .phototext').html('上传图片');
								$('#tipcontainer').css('height','46px');
								$('#tipcontainer .textinput #tips_status_text').removeClass('tipinput');
								$('#tipcontainer .textinput #tips_status_text').addClass('tipinput-noimg');
								$('#attach').val('');
								$('#tips_status_text').val('写个贴士分享给大家吧').css('color','#CBCBCB');
								$('#need_sync').attr('checked','checked');
								$('#tipcontainer .previewimg').hide();
								$('#tbl_tips').empty();
								Better_Poi_Tips(1);
							}else if (json.code=='need_check') {
//								Better_ResetPostForm();
								
								Better_Notify({
									msg: betterLang.post.need_check
								});					
							} else if (json.code=='post_too_fast') {
								Better_Notify({
									msg: betterLang.antispam.too_fast
								});										
							} else if (json.code=='post_same_content') {
								Better_Notify({
									msg: betterLang.antispam.shout
								});										
							} else if (json.code=='you_r_muted') {
//								Better_ResetPostForm();
								
								Better_Notify({
									msg: betterLang.post.forbidden
								});								
							} else if (json.code=='words_r_banned') {
//								Better_ResetPostForm();
								
								Better_Notify({
									msg: betterLang.post.ban_words
								});
							} else if (json.code=='too_short') {
								Better_Notify({
									msg: betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength)
								});
							} else if(json.code=='not_allow_rt'){
								Better_Notify({
									msg: '原文消息已不允许被转发'
								});	
							}else {
								Better_Notify({
									msg: 'Failed'
								});										
							}		
						}
					},'json');
//				}
//			});
		}
	});
	Better_InitGMap('Better_Around_initializeGmap');
	Better_GetW3cLL();
	
});
//if(typeof checkin_time && checkin_time>0){
//	$('.checkin_time').html(Better_compareTime(checkin_time));
//}
//checkin upload
var ajaxUploader = new AjaxUpload('#checkin_btnUpload', {
	action: '/ajax/attach/upload',
	name: 'myfile',
	data: {
		attach: $('#checkin_attach').val()
	},
	autoSubmit: true,
	onSubmit: function(file, ext){
		ext = ext.toString().toLowerCase();
		
		if (! (ext && /^(jpg|png|jpeg|gif|zip)$/.test(ext))){
			Better_Notify({
				msg: betterLang.post.invalid_file_format,
				close_timer: 3
			});			
			return false;
		} else {
		
			Better_Notify_loading({
				msg_title: betterLang.post.uploading
			});
			$('#checkin_btnUpload').attr('disabled',true);
		}
		
	},
	onComplete: function(file, response){
		Better_Notify_clear();

		try {
			eval('rt='+response);
		} catch (rte) {
			rt = {
				has_err: '1',
				err: rte.discription
			};
		}

		$('#checkin_btnUpload').attr('disabled', false);
		
		if (rt.has_err=='1') {
			switch (rt.err) {
				case 1001:
				case 1003:
					errorMsg = betterLang.global.upload.too_large
					break;
				case 1006:
					errorMsg = betterLang.global.upload.image_not_supported;
					break;
				default:
					errorMsg = betterLang.global.upload.failed;
					break;
			}
			
			Better_Notify({
				msg: 'Error:'+ errorMsg
			});
		} else {
			Better_Notify({
				msg: betterLang.blog.upload_success,
				close_timer: 2
			});
			
			$('#checkin_attach').val(rt.attach);
			if (typeof(rt.new_file_url)!='undefined' && rt.new_file_url!='') {
				$('#checkin_btnUpload').attr('src', rt.new_file_url).addClass('avatar').css('height', '76px').css('width', '80px').load(function(){
					w = $(this).width();
					h = $(this).height();
					
					if (h>80) {
						$(this).css('height', '76px');
						$(this).css('width', (w*76/h)+'px');
					}
				});
			}

		}
	}
});
