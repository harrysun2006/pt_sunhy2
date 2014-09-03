var pageLon = betterUser.lon;
var pageLat = betterUser.lat;
var gmapZomm = 14;
var map;
var marker;

/**
 * 附近的消息
 * 
 * @param page
 * @param renew
 * @return
 */
function Better_Around_Messages(page)
{
	page = page ? page : 1;

	Better_Pager({
		key: 'messages',
		next: betterLang.around.more_messages,
		last: betterLang.around.no_more_messages,
		callback: Better_Around_Messages
	});	
	
	Better_loadBlogs({
		id: 'messages', 
		url: '/ajax/blog/searchqbs',
		posts: {
			page: page,
			lon: pageLon,
			lat: pageLat
		},
		withHisFuncLinks: false,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults('messages', betterLang.around.no_messages);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#messages"]').trigger('click');
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
function Better_Around_Users(page)
{
	page = page ? page : 1;
	var key = 'users';
	
	lon = pageLon;
	lat = pageLat;
	
	Better_Pager({
		key: 'users',
		next: betterLang.around.more_people,
		last: betterLang.around.no_more_people,
		callback: Better_Around_Users
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
				Better_EmptyResults('users', betterLang.around.no_people);
			}
		}
	});

}

/**
 * 根据中心点移动地图
 * 
 * @param newMarker
 * @return
 */
function Better_Around_moveCenter(newMarker)
{
	newMarker = newMarker ? true : false;
	map.setCenter(new GLatLng(pageLat, pageLon), map.getZoom());
	
	if (Better_GMapInited==true) {

		if (newMarker==true) {
			map.removeOverlay(marker);
			marker = new GMarker(Gmap.getCenter(), {draggable: true});
        	
			GEvent.addListener(marker, "dragend", function() {
	        	t = marker.getLatLng();
	        	pageLon = t.lng();
	        	pageLat = t.lat();
	        	
		        Better_Around_moveCenter();
		    });
			map.addOverlay(marker);
		}
		
		$('div.tabs ul.tabNavigation a').each(function(){
			if ($(this).hasClass('selected')) {
				tab = $(this).attr('href');
				switch(tab) {
    		    	case '#messages':
    		    		$('#tbl_messages').empty();
    		    		Better_Around_Messages();
    		    		break;
    		    	case '#users':
    		    		$('#tbl_users').empty();
    		    		Better_Around_Users();
    		    		break;        				
				}
			}
		})
	}	
}

/**
 * 初始化“雷达”的Google地图
 * 
 * @return
 */
function Better_Around_initializeGmap()
{
	lat = pageLat ? pageLat : 39.917;
	lon = pageLon ? pageLon : 116.397;

	if (GBrowserIsCompatible()) {
		
		map = new GMap2(document.getElementById("map_canvas"));
		var customUI = map.getDefaultUI();
		customUI.maptypes.hybrid = false;

//		map.addControl(new GSmallMapControl());
		map.setUI(customUI);
		map.addControl(new GOverviewMapControl());
		map.setMapType(G_DEFAULT_MAP_TYPES[0]);
		map.setCenter(new GLatLng(lat, lon), gmapZomm);
		map.enableScrollWheelZoom();
		
		Gmap = map;
        marker = new GMarker(Gmap.getCenter(), {draggable: true});

        GEvent.addListener(marker, "dragend", function() {
        	t = marker.getLatLng();
        	pageLon = t.lng();
        	pageLat = t.lat();
        	Better_Around_moveCenter();
        });

        map.addOverlay(marker);

	}	
}

/**
 * 更新页面上方的地址文字
 * 
 * @param city
 * @param address
 * @param time
 * @return
 */
function Better_Around_UpdateAddressString(city, address, time)
{
	city = city ? city : '';
	city_str = city ? ' '+city+' ' : '';
	str = betterLang.noping.around.appear.info.toString().replace('{time}',Better_compareTime(time)).replace('{CITY}',city_str).replace('{ADDRESS}',address);
	//str = +' '+betterLang.around.appear+' '+city_str+address;

	return str;
}

$(function() {

	Better_InitGMap('Better_Around_initializeGmap');
	
	if (betterUser.lbs_report>0) {
		$('#aroundLocationTips').html(Better_Around_UpdateAddressString(betterUser.city, betterUser.address, betterUser.lbs_report));
	}

	$('#showCity').click(function(){
		div = $(document.createElement('div'));
		
		for(i=0;i<qbsCities.length;i++) {
			a = $(document.createElement('a')).css('color', '#000').attr('href', '#'+qbsCities[i][0]).attr('ref', qbsCities[i][1]+','+qbsCities[i][2]).html(qbsCities[i][0]).click(function(){
				ref = $(this).attr('ref').toString().split(',');
				
				pageLon = ref[1];
				pageLat = ref[0];
				Better_Around_moveCenter(true);			
				
				$('#dlgCities').dialog('close');
				return false;
			});

			div.append(a).append(' ');
			
			if (i>0 && i%8==0) {
				div.append('<br />');
			}
			
		}

		$('#dlgCities').empty().append(div).dialog({
			bgiframe: true,
			autoOpen: true,
			modal: true,
			resizable: false,
			title: betterLang.around.choose_city,
			buttons: {
			
			}			
		}).dialog('open');

		return false;
	});

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
	    		Better_Around_Messages();
	    		break;
	    	case '#users':
	    		$('#tbl_users').empty();
	    		Better_Around_Users();
	    		break;
    	}
    	
    		return false;
    }).filter(':first').click();
        
});