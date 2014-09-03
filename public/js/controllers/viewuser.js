
Better_This_Script = '/poi/viewuserlist?id='+Better_Poi_Id;

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
		amap.setMapType(G_DEFAULT_MAP_TYPES[0]);
		amap.setCenter(new GLatLng(lat, lon), gmapZomm);
		amap.enableScrollWheelZoom();
        amarker = new GMarker(amap.getCenter());
        amap.addOverlay(amarker);
	}	
}


/**
 * 想来这里的好友
 * @return
 */
function Better_Poi_Friendstobe(page)
{
	page = page ? page : 1;
	var key = 'friendstobe';
	//window.location = Better_This_Script+'#xfriendstobe';
	
	lon = pageLon;
	lat = pageLat;
	Better_Pager({
		key: key,
		next: '更多想来的好友',
		last: '没有更多想来好友了',
		callback: Better_Poi_Friendstobe
	});	

	Better_loadUserBlogs({
		id: key,
		url: '/ajax/poi/poifriendstodo',
		posts: {
			poi_id: Better_Poi_Id,
			page: page,
			count:20
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				Better_EmptyResults(key, '还没有好友想来这里');
			}
		}
	});	
}

/**
 * 来过这里的好友
 * @return
 */
function Better_Poi_Friendshere(page)
{
	page = page ? page : 1;
	var key = 'friendshere';	
	lon = pageLon;
	lat = pageLat;
	Better_Pager({
		key: key,
		next: '更多来过这里的好友',
		last: '没有更多来过这里的好友了',
		callback: Better_Poi_Friendshere
	});	

	Better_loadUserBlogs({
		id: key,
		url: '/ajax/poi/poifriendshere',
		posts: {
			poi_id: Better_Poi_Id,
			page: page,
			count:20
		},
		callbacks: {
			errorCallback: function(){},
			emptyCallback: function(){
				Better_EmptyResults(key, '还没有好友来过这里');
			}
		}
	});	
}
function Better_loadUserBlogs(options){
		id = options.id;
		$('div#'+id).show();
		 tbl = $('#tbl_'+id);
		 url = options.url;
		 page = options.page;
		 callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};
		 posts = typeof(options.posts)!='undefined' ? options.posts : {};
		 needloading = typeof(options.needloading)!='undefined' ? options.needloading : true;
		 parseRt = typeof(options.parseRt)!='undefined' ? options.parseRt : true;
		 inFavList = typeof(options.inFavList)!='undefined' ? options.inFavList : false;
		
		if(needloading){
			Better_Table_Loading(id);
		}
	
		 withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;;
		 withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
		 withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
		 withPhoto = typeof(options.withPhoto)!='defined' ? options.withPhoto : true;	
		 withFavLinks = typeof options.withFavLinks!='undefined' ? options.withFavLinks: false;
		if (page<=1) {
			Better_Pager_Reset(id);	
		}	
		Better_Ajax_processing = true;
		$.getJSON(url, posts, _callbackuserblog);	
	}
function _callbackuserblog(listJson){
	var tr;
	var array_tr = new Array();	
	if ($.isFunction(callbacks.beforeCallback)) {
		callbacks.beforeCallback(listJson);
	}	
	if (Better_AjaxCheck(listJson)) {		
		try {
			Better_Pager_setPages(id, listJson.pages);	
			Better_Clear_Table_Loading(id);			
			nowPage = typeof(listJson.page)!='undefined' ? listJson.page : page;	
			if (nowPage==listJson.pages) {
				Better_Pager_Reach_Last(id);
			}			
			if(nowPage==1){
				had_load_id = new Array();
			}
			rows = listJson.rows;
			if (rows!=null) {				
				for(i=0; i<rows.length; i++){	
					tr = Better_parsePoiUserRow(listJson.type,rows[i], {
						id: id,
						withAvatar: withAvatar,
						withMyFuncLinks: withMyFuncLinks,
						withHisFuncLinks: withHisFuncLinks,
						withPhoto: withPhoto,
						inFavList: inFavList
					});
					tbl.append(tr);					
				} //end for
				

				if (listJson.count<=0 && typeof(callbacks.emptyCallback)=='function') {
					try {
						callbacks.emptyCallback();			
					} catch (e) {
						if (Better_InDebug) {
							alert(e.message);
						}
					}
				}
			} //end if			
			if ($.isFunction(callbacks.completeCallback)) {
				callbacks.completeCallback(listJson);
			}
		}catch(e) {
			if (Better_InDebug) {
				Better_Notify_clear();				
				Better_Notify({
					msg: 'name: '+e.name+'\ndescription:'+e.description+'\nnumber:'+e.lineNumber+'\nmessage:'+e.message
				});								
			}
			tbl.empty();
		}		
	} else {
		tbl.empty();
	}	
	Better_Ajax_processing = false;
	if(id=='friendshere'){
		$('#half_left .poiinfo .top-center .typeflag').text(' > 来过的好友');
	}else if(id='friendstobe'){
		$('#half_left .poiinfo .top-center .typeflag').text(' > 想来的好友');
	}
	
}

function Better_parsePoiUserRow(type,data,options){

	var options = typeof(options)=='object' ? options : {};
	var id = typeof(options.id)!='undefined' ? options.id : '';
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;	
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	data.poi = typeof(data.user_poi)!='undefined' ? data.user_poi : (typeof(data.poi)!='undefined' ? data.poi : {});

	var withToDoFuncLinks = typeof(options.withToDoFuncLinks) != 'undefined' ? options.withToDoFuncLinks : true;
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';
	data.priv = 'public';
	var bid_key="";
	var noaction = false;
	if( typeof( data.bid) != 'undefined'){
		bid_key = data.bid.replace('.', '_');
	}else{
		noaction=true;
		data.dateline= data.checkin_time;
	}
	
	var arr = new Array();
	arr.push('<tr id="checkinListRow_'+id+'_'+bid_key+'" class="listRow" uid="'+data.uid+'" priv="'+data.priv+'" tblId="'+id+'" bid="'+data.bid+'" protected="'+data.priv_blog+'">');
	arr.push('<td class="avatar icon"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'"><img src="'+data.avatar_thumb+'" onerror="this.src=Better_AvatarOnError" alt="" width="48" class="pngfix" /></a></td>')
	arr.push('<td class="info">');
	var div4_html = '';
	
		var message = '<span id="checkin_msg_'+bid_key+'"> <a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> ';
		data.city = typeof(data.city)!='undefined' ? data.city : '';
		if(type=="todo"){			
			message += '<span id="message_'+bid_key+'" class="message_row">'+"想去"+' '+data.city+' </span> ';
		}else if(type=="checkin"){
			message += '<span id="message_'+bid_key+'" class="message_row">'+betterLang.global.at+' '+data.city+' </span> ';
		}else{
		}
		message += '<a href="/poi/'+Better_Poi_Detail.poi_id+'" class="place">'+Better_Poi_Detail.name+'</a> ';

		if (LANGUAGE!='en' && data.message=='' && type !="todo") {
			message += betterLang.global.checkin.title+'';
		}
		message += '</span>';
		if (data.message!='') {
			message += ': '+Better_parseMessage(data);
		} else {
			message += ' '+Better_parseMessage(data);
		}
			var source = '<span class="source">'+Better_compareTime(data.dateline);
			if(typeof data.source!='undefined' && data.source){
				source += ' '+betterLang.global.blog.by+Better_parseBlogSource(data.source);	
			}	
			source += '</span>';
			div4_html += source;
	
	arr.push('<div class="status message_row">'+message);
	if((data.attach && data.attach_thumb) || (data.badge_id>0 && typeof(data.badge_detail)!='undefined')){
		arr.push('<div class="info">');
		if(data.badge_id>0 && typeof(data.badge_detail)!='undefined'){
			arr.push('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" class="checkin_big_badge" onclick="return false" bid="'+data.badge_id+'" uid="'+data.uid+'"><img src="'+data.badge_detail.picture+'" alt="" class="badge_attach pngfix" /></a>');
			arr.push('<div style="display:none;">'+Better_Badge_Detail_Row(data, 'checkinListRow_'+id).html()+'</div>');
		}
		
		if(data.attach && data.attach_thumb){
			arr.push('<a href="'+data.attach_url+'" class="attach_href"><img id="attach_'+bid_key+'" class="attach pngfix" onerror="this.src=Better_ImgOnError" src="'+data.attach_tiny+'" alt="" ref="'+data.attach+'" /></a>');
		}
		arr.push('</div>');
	}
	
	arr.push('</div>');
	arr.push('<div class="ext"><span class="action listRowFuncs" id="checkinListRowFuncDiv_'+id+bid_key+'"></span><span class="time">'+div4_html+'</span></div>');
	arr.push('</td>');
	arr.push('</tr>');
	
	var tr = $(arr.join(' '));
	if($.browser.opera){
		tr.find('td div.info a.attach_href').attr('target', '_blank');
	}else{
		tr.find('td div.info a.attach_href').fancybox();
	}
	tr.find('a.badge_users_page').click(function(){
		badgeId = parseInt($(this).attr('bid'));
		direct = $(this).attr('direct');
		pf = $(this).attr('pf');
		uid = $(this).attr('uid');

		nextPage = direct=='next' ? parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())+1 : parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())-1;
		 $('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
		Better_Badge_Users(badgeId, uid, nextPage, pf);
	});	
	
	tr.find('a.checkin_big_badge').click(function(){
		BETTER_BIG_BADGE_ID = parseInt($(this).attr('bid'));
		BETTER_BIG_BADGE_UID = parseInt($(this).attr('uid'));
	}).fancybox({
		autoDimensions: true,
		scrolling: 'no',
		centerOnScroll: true,
		
		'onStart' : function(){
			$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
			$('#fancybox-outer').css('background-color', '#1db8ee');
			
			if ($('#list_badge_users_checkinListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
				Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_checkinListRow_'+id);
			}					
		},
		'onClosed': function(){
			$('#fancybox-outer').css('background-color', '#fff');
		}
	
	})
	
	//tr.mouseenter(function(){
		//thisRow = $(this);
		var thisRow = tr;
	
		if (false && (betterUser.uid>0 && !noaction)) {//不显示操作
			var bid = thisRow.attr('bid');
			var uid = thisRow.attr('uid');
			var bid_key = bid.replace('.', '_');
			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;			

			var funcDiv = thisRow.find('#checkinListRowFuncDiv_'+id+bid_key);
			if (!funcDiv.html()) {
				funcDiv.empty();
				
				
				if (withMyFuncLinks==true) {
					try {
						var a = $(document.createElement('a')).attr('id', 'favoritesFuncA_'+id+'_'+bid_key);
						var afterUnfavoriteCallback = $.isFunction(callbacks.afterUnfavoriteCallback) ? callbacks.afterUnfavoriteCallback : function(){};
					
						a.css('width', '14px');
						a.css('height','14px');
						a.css('margin','3px 5px 0 0');
						if ($.inArray(bid, betterUser.fav_bids)>=0) {
							a.css('background', 'url("/images/action.png") repeat scroll -48px 0px transparent');
							a.attr('title',betterLang.global.favorite.cancel).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid,
								bid_key: bid_key,
								id: 'favoritesFuncA_'+id+'_',
								row_id: 'listRow_'+id+'_'+bid_key,
								tbl_id: id,
								type: 'checkin',
								afterUnfavoriteCallback: afterUnfavoriteCallback
							}, Better_UnFavoriteblog);
						} else {
							a.css('background', 'url("/images/action.png") repeat scroll -32px 0px transparent');
							a.attr('title',betterLang.global.favorite.title).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid,
								id: 'favoritesFuncA_'+id+'_',
								row_id: 'listRow_'+id+'_'+bid_key,
								tbl_id: id,
								type: 'checkin',
								afterUnfavoriteCallback: afterUnfavoriteCallback
							}, Better_Favoriteblog);
						}
	
						funcDiv.append(a).append(' ');
					} catch (eee2) {
						if (Better_InDebug) {
							Better_Notify({
								msg: 'In Favorite:'+eee2.message
							});
						}
					}

				}		
				
				
				if (withHisFuncLinks==true && priv=='public') {
					//	转发
					var a = $(document.createElement('a')).attr('title',betterLang.global.blog.rt).attr('href', 'javascript:void(0);');
					a.css('background', 'url("/images/action.png") repeat scroll -15px 0px transparent');
					a.css('width', '14px');
					a.css('height','14px');
					a.css('margin','3px 5px 0 0');
					a.click(function(){
						var params = {
								msg: $('#checkin_msg_'+bid_key).html(),
								nickname: $('#nickname_'+bid_key).text(),
								username: $('#nickname_'+bid_key).attr('username'),
								uid: uid,
								address: '',
								from: 'checkin',
								attach: $('#attach_'+bid_key).attr('ref'),
								bid_key: bid_key,
								now_bid: bid,
								allow_rt: data.allow_rt	
						};
						Better_Transblog({'data': params});
					});
					funcDiv.append(a).append(' ');		
				}
				
				if(widthCommentLinks){
					a = $(document.createElement('a')).attr('href', 'javascript:void(0)').addClass('commentBtn_'+bid_key).attr('title',' 评论').text(commentText);				
					a.css('height','14px');
					a.css('margin','2px 2px 0 0');
					var comm_data = {
							'bid': bid,
							'pageSize': 10,
							'row': thisRow
					};
					a.click(function() {Better_loadComments(comm_data);});
					funcDiv.append(a);
				}
			}
			funcDiv.show();
		}
	/*}).mouseleave(function(){
		if (betterUser.uid>0) {
			$(this).find('span[id^=checkinListRowFuncDiv_]').hide();
		}
	});*/
	
	return tr;

}



$(function() {
	var tabContainers = $('div.tabs > div');
    tabContainers.hide().filter(':first').show();
    
    $('div.tabs ul.usertabNavigation a').click(function () {
		if(typeof($(this).attr('disabled'))!='undefined' && $(this).attr('disabled')=='true'){
			return false;
		}
		
        tabContainers.hide();
        tabContainers.filter(this.hash).show();
        $('div.tabs ul.usertabNavigation a').removeClass('selected');
        $(this).addClass('selected');
        
        switch(this.hash) {
        	case '#friendshere':
        		$('#tbl_friendshere').empty();
        		Better_Pager_Reset('friendshere');
        		Better_Poi_Friendshere(1);
        		break;
        	case '#friendstobe':       		
        		$('#tbl_friendstobe').empty();
        		Better_Pager_Reset('friendstobe');
        		Better_Poi_Friendstobe(1);
        		break;
        }
        firstLoad = false;
        return false;
    }).ajaxStart(function(e, q, o){
    	$(this).attr('disabled', true);
    }).ajaxComplete(function(e, q, o){
    	$(this).attr('disabled', false);
    });    	

	//	ajax history
    $.history.init(function(tab){
    	 Better_Load_Tab(tab);
    });
    type=$('#viewtype').val();
    if(type == 'friendshere'){
        $('a[href="#friendshere"]').trigger('click');
    }else if(type == 'friendstobe'){
    	 $('a[href="#friendstobe"]').trigger('click');
    }

	Better_InitGMap('Better_Around_initializeGmap');
	Better_GetW3cLL();	
});

