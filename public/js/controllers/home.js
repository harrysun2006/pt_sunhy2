BETTER_HOME_LAST_STATUS_TIPS = true;

Better_This_Script = '/home';

/**
 * 我的周围
 * 
 * @param page
 * @return
 */

function Better_Home_ArroundMessages(page)
{
	page = page ? page : 1;
	var key = 'aroundme';
	
	window.location = Better_This_Script+'#x'+key;
	
	Better_Pager({
		key: key,
		next: betterLang.home.around.more_messages,
		last: betterLang.home.around.no_more_messages,
		callback: Better_Home_ArroundMessages
	});
	
	Better_loadBlogs({
		id: key, 
		url: '/ajax/blog/aroundme',
		posts: {
			page: page,
			lon: betterUser.lon,
			lat: betterUser.lat
		},
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults('aroundme', betterLang.home.around.no_message);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').trigger('click');
			}			
		}
	});

}

/**
 * 更新页面上方的地址文字
 * 
 * @param city
 * @param address
 * @param time
 * @return
 */
function Better_Home_UpdateAddressString(city, address, time)
{
	city = city ? city : '';
	city_str = city ? ' '+city+' ' : '';
	str = betterLang.noping.home.your_location.toString().replace('{TIME}',Better_compareTime(time)).replace('{CITY}',city_str).replace('{ADDRESS}',address);
	//str = betterLang.home.you+' '+Better_compareTime(time)+' '+betterLang.home.appear+' '+city_str+address;

	return str;
}


/**
 * 我关注的
 * 
 * @param page
 * @param renew
 * @return
 */
function Better_Home_loadMine(page) 
{
	page = page ? page : 1;
	var key = 'followings';
	if (firstLoad == false) {
		window.location = Better_This_Script+'#x'+key;
	}
	Better_Pager({
		key: key,
		next: betterLang.home.mine.more_messages,
		last: betterLang.home.mine.no_more_messages,
		callback: Better_Home_loadMine
	});

	Better_loadBlogs({
		id: key,
		url: '/ajax/blog/list?bar=1',
		posts: {
			page: page
		},
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.home.mine.no_message);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').attr('disabled', false);
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').trigger('click');
				
			}
		}
	});

}

/**
 * 所有最新
 * 
 * @param page
 * @return
 */
function Better_Home_loadLastest(page) 
{
	page = page ? page : 1;
	key = 'all';
	
	window.location = Better_This_Script+'#x'+key;

	Better_Pager({
		key: key,
		next: betterLang.home.lastest.more_messages,
		last: betterLang.home.lastest.no_more_messages,
		callback: Better_Home_loadLastest
	});
	
	Better_loadBlogs({
		id: key,
		url: '/ajax/blog/listall',
		posts: {
			page: page
		},
		callbacks: {
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').trigger('click');
			}			
		}
	});

}


/**
 * 切换到默认地图位置
 * 
 * @param event
 * @return
 */
function Better_Home_switchToDefaultMap(event)
{
	link = event.data.link;
	text = event.data.text;
	
	if (Better_GMapInited==false) {
		Better_Home_InitGMap();
	}
	
	link.text(text);
	
	Gmap.setCenter(new GLatLng(betterUser.lat, betterUser.lon), Gmap.getZoom());
	
	Gmap.removeOverlay(marker);
	marker = new GMarker(Gmap.getCenter(), {draggable: true});
	GEvent.addListener(marker, "dragend", function(){
		var latlon = marker.getLatLng();
		setlonlat(latlon.lng(),latlon.lat());
	});
	Gmap.addOverlay(marker);		
	
	link.unbind('click', Better_Home_switchToDefaultMap).bind('click', {
		link: link,
		text: betterLang.home.im_not_here
	}, Better_Home_switchToRefMap);

	if ($('#setloc').css('display')=='none') {
		$('#setloc').slideDown();
	}
	
	return false;
}

/**
 * 切换到参考地图位置
 * 
 * @param event
 * @return
 */
function Better_Home_switchToRefMap(event)
{

	link = event.data.link;
	text = event.data.text;
	
	if (Better_GMapInited==false) {
		Better_Home_InitGMap();
	}
	
	link.text(text);
	
	if (Better_Home_Ref_lat==0 && Better_Home_Ref_lon==0) {
		Better_Home_Ref_lat = Better_Default_Lat;
		Better_Home_Ref_lon = Better_Default_Lon;
	}

	Gmap.setCenter(new GLatLng(Better_Home_Ref_lat, Better_Home_Ref_lon), Gmap.getZoom());
	Gmap.removeOverlay(marker);
	marker = new GMarker(Gmap.getCenter(), {draggable: true});
	GEvent.addListener(marker, "dragend", function(){
		var latlon = marker.getLatLng();
		setlonlat(latlon.lng(),latlon.lat());
	});
	Gmap.addOverlay(marker);
	
	if (Better_Home_Ref_lat==Better_Default_Lat && Better_Home_Ref_lon==Better_Default_Lon) {
		link.hide();
	} else {
		link.unbind('click', Better_Home_switchToRefMap).bind('click', {
			link: link,
			text: betterLang.home.reference_place
		}, Better_Home_switchToDefaultMap);
	}
	
	if ($('#setloc').css('display')=='none') {
		$('#setloc').slideDown();
	}
	
	return false;
}

/*
 * 取得我被转发的内容
 */
function Better_Home_loadRtMine(page) 
{
	page = page ? page : 1;
	var key = 'rtmine';
	
	window.location = Better_This_Script+'#x'+key;

	Better_Pager({
		key: key,
		next: betterLang.home.mine.more_messages,
		last: betterLang.home.mine.no_more_messages,
		callback: Better_Home_loadRtMine
	});

	Better_loadBlogs({
		id: key,
		url: '/ajax/blog/listrtmine',
		posts: {
			page: page
		},
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults(key, betterLang.home.lastest.no_message);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').attr('disabled', false);
				$('div.tabs ul.tabNavigation a[href="#'+key+'"]').trigger('click');
				
			}
		}
	});
}


/**
 * 提到我的列表里的回复
 */
function Better_Mentionme_reply(){
	
}

var firstLoad = true;
$(function(){
	
	var tabContainers = $('div.tabs > div');
    tabContainers.hide().filter(':first').show();
    
    $('div.tabs ul.tabNavigation a').click(function () {
    		
		if(typeof($(this).attr('disabled'))!='undefined' && $(this).attr('disabled')=='true'){
			return false;
		}
		
        tabContainers.hide();
        tabContainers.filter(this.hash).show();
        $('div.tabs ul.tabNavigation a').removeClass('selected');
        $(this).addClass('selected');
        
        switch(this.hash) {
        	case '#followings':
        		$('#tbl_followings').empty();
        		Better_Pager_Reset('followings');
        		Better_Home_loadMine(1);
        		break;
        	case '#aroundme':
        		$('#tbl_aroundme').empty();
        		Better_Pager_Reset('aroundme');
        		Better_Home_ArroundMessages(1);
        		break;
        	case '#all':
        		$('#tbl_all').empty();
        		Better_Pager_Reset('all');
        		Better_Home_loadLastest(1);
        		break;
        	case '#rtmine':
        		$(this).text('提到我的');
        		
        		$('#tbl_rtmine').empty();
        		$('#tbl_rtmine').append('<tr style="visibility:collapse;"><td width="56" style="border:0;padding:0;"></td><td width="582" style="border:0;padding:0"></td></tr>');
        		Better_Pager_Reset('rtmine');
        		Better_Home_loadRtMine(1);
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

    
    $('#checkin_img').mouseover(function(){
    	$(this).attr('src','images/checkin-blue.png?v=3333');
    }).mouseout(function(){
    	$(this).attr('src','images/check-in.png?v=3333');
    });
    $('#shout_img').mouseover(function(){
    	$(this).attr('src','images/shout-blue.png?v=3333');
    }).mouseout(function(){
    	$(this).attr('src','images/shout.png?v=3333');
    });
    $('#down_img').mouseover(function(){
    	$(this).attr('src','images/downtocell_.png?v=3333');
    }).mouseout(function(){
    	$(this).attr('src','images/downtocell.png?v=3333');
    });
    
    //提示框
    if(!$.cookie('kai_tip_'+betterUser.uid)){
    	 $('#tip_box').fadeIn();
    	 
    	 $('#close_tips').click(function(){
    		 $('#tip_box').fadeOut();
    		 $.cookie('kai_tip_'+betterUser.uid, '1', {expires: 1, path: '/'});
    	 });
    }else{
    	$('#tip_box').remove();
    }
});
