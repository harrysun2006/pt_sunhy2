var wifiLon = 39.917;
var wifiLat = 116.397;
var majorsGot = false;

var add_lon = 0;
var add_lat = 0;

var tmpLon = 0;
var tmpLat = 0;

Better_This_Script = '/pois';

/**
 * 初始化Google地图
 * 
 * @return
 */
function Better_Pois_initializeGmap()
{
	lon = wifiLon ? wifiLon : 116.397;
	lat = wifiLat ? wifiLat : 39.917;
	
	if (GBrowserIsCompatible()) {
		var amap = new GMap2(document.getElementById("map_canvas"));
		var customUI = amap.getDefaultUI();
		customUI.maptypes.hybrid = false;

//		map.addControl(new GSmallMapControl());
		amap.setUI(customUI);
		amap.addControl(new GOverviewMapControl());
		amap.setMapType(G_DEFAULT_MAP_TYPES[0]);
		amap.setCenter(new GLatLng(lat, lon), 14);
		amap.enableScrollWheelZoom();
		
        amarker = new GMarker(amap.getCenter(), {draggable: true});
        
        GEvent.addListener(amap, 'dragend', function(){
        	amarker.hide();
        	amarker.disableDragging();
        	amarker = new GMarker(amap.getCenter(), {draggable: true});	
        	
        	Better_Pois_Move();
        	
	        amap.addOverlay(amarker);
	        GEvent.addListener(amarker, "dragend", function(ll){
	        	amap.setCenter(ll);
	        	Better_Pois_Move();
	        });
        });
        
        GEvent.addListener(amarker, "dragend", function(ll){
        	amap.setCenter(ll);
        	Better_Pois_Move();
        });
        amap.addOverlay(amarker);
        
        Better_Pois_Init();
	}	
}

/**
 * 地图点移动事件
 * @return
 */
function Better_Pois_Move(){
	alatlon = amarker.getLatLng();
	wifiLon = alatlon.lng();
	wifiLat = alatlon.lat();
		
	$('div.tabs ul.tabNavigation a[href="#pois"]').trigger('click');

	return false;
}

/**
 * 初始化窗口地图
 */
function Better_Create_InitGMap(force)
{			
		force = force ? force : true;
		
		sLon = wifiLon ? wifiLon : 39.917;
		sLat = wifiLat ? wifiLat : 116.397;
	
		if (Better_Home_Map_Inited==false || force==true) {
			if (GBrowserIsCompatible()) {
				map = new GMap2(document.getElementById("create_map"), {size:new GSize(500, 150)});
				map.setCenter(new GLatLng(sLat, sLon), 14);
				map.addControl(new GSmallMapControl());
				map.enableScrollWheelZoom();

		        marker = new GMarker(map.getCenter(), {draggable: true});
		
		        Better_Pois_GetAddressByLL();
		        
		       GEvent.addListener(map, 'dragend', function(){
					marker.hide();
					marker.disableDragging();
			        marker = new GMarker(map.getCenter(), {draggable: true});		
			        map.addOverlay(marker);
			        
			        Better_Pois_GetAddressByLL();
			        
		        	GEvent.addListener(marker, "dragend", function(ll){
		        		map.setCenter(ll);
		        		Better_Pois_GetAddressByLL();
		        	});
		        });
		       
		        GEvent.addListener(marker, "dragend", function(ll){
		        	map.setCenter(ll);
		        	Better_Pois_GetAddressByLL();
		        });
		        map.addOverlay(marker);
			}	
			Better_Create_Map_Inited = true;
		}

	
}


function Better_Pois_GetAddressByLL(){
	latlon = marker.getLatLng();
	add_lon = latlon.lng();
	add_lat = latlon.lat();
	
	$.getJSON('/ajax/location/getaddressbyll', {
		'lon': add_lon,
		'lat': add_lat
	}, function(json){
		$('#add_poi_address').val(json.address);
		$('#add_poi_city').val(json.cityname);
	});
}


function Better_Pois_GetLLByIp()
{
	$.getJSON('/ajax/lbs/ip', {}, function(ipJson){
		wifiLon = ipJson.lon;
		wifiLat = ipJson.lat;
		wifiRange = ipJson.range;
		
		ipLon = ipJson.lon;
		ipLat = ipJson.lat;
		
		Better_Notify_clear();
		Better_Pois_initializeGmap();
	});	
}

function Better_Pois_GotLL()
{
	Better_Notify_loading({
		msg_title: betterLang.geoing
	});
	
	try {
		cookieLon = $.cookie('web_lon');
		cookieLat = $.cookie('web_lat');
		
		if (cookieLon && cookieLat) {
			wifiLon = cookieLon;
			wifiLat = cookieLat;
			
			Better_Pois_initializeGmap();
		} else {
			if (typeof(navigator.geolocation)=='undefined') {
				var wifiData = lbs_get_wifiData();
	
				if(!wifiData.err && typeof(wifiData.base64).toString()!='undefined' && wifiData.base64!='undefined'){
					$.post('/ajax/lbs', {
						lbs: wifiData.base64
					}, function(wifiJson){
						wifiError = wifiJson.error;
						if (wifiError=='true') {
							Better_Pois_GetLLByIp();
						} else {
							if (parseFloat(wifiJson.lat)<200 && parseFloat(wifiJson.lon)<200) {
								wifiLon = wifiJson.lon;
								wifiLat = wifiJson.lat;
								wifiRange = wifiJson.range;
							} else {
								Better_Pois_GetLLByIp();
							}
						}
						
						Better_Pois_initializeGmap();
					}, 'json');
				}else{
					Better_Pois_GetLLByIp();
				}
	
			} else {
				if (Better_W3cLL_Gotted.lon!=-1 && Better_W3cLL_Gotted.lat!=-1) {
					wifiLon = Better_W3cLL_Gotted.lon;
					wifiLat = Better_W3cLL_Gotted.lat;
					wifiRange = Better_W3cLL_Gotted.range;
					
					Better_Pois_initializeGmap();
				} else {
					Better_Pois_GetLLByIp();
				}
			}
		}
	} catch (e) {
		Better_Pois_GetLLByIp();
	}		
}

function Better_Pois_Users(page)
{
	page = page ? page : 1;
	var key = 'users';
	
	window.location = Better_This_Script+'#x'+key;

	Better_Pager({
		key: key,
		next:betterLang.pois.users.more_users,
		last:betterLang.pois.users.no_more_users,
		callback: Better_Pois_Users
	});
	
	Better_loadUsers({
		id: key,
		url: '/ajax/pois/users',
		posts: {
			page: page,
			lon: wifiLon,
			lat: wifiLat
		}
	});		
}

function Better_Pois_Tips(page)
{
	page = page ? page : 1;
	
	window.location = Better_This_Script+'#xtips';

	Better_Pager({
		key: 'tips',
		next: betterLang.pois.tips.more_tips,
		last: betterLang.pois.tips.no_more_tips,
		callback: Better_Pois_Tips
	});	
	
	Better_loadBlogs({
		id: 'tips', 
		url: '/ajax/pois/tips',
		posts: {
			page: page,
			lon: wifiLon,
			lat: wifiLat
		},
		withHisFuncLinks: true,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults('tips', betterLang.pois.tips.no_tips);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#tips"]').trigger('click');
			}	
		}
	});		
}

function Better_Pois_Pois(page, withMajors)
{
	page = page ? page : 1;
	var withMajors = withMajors ? 1 : 0;
	
	Better_Pager({
		key: 'pois',
		next: betterLang.pois.pois.more_tips,
		last: betterLang.pois.pois.no_more_tips,
		callback: Better_Pois_Pois
	});	
	
	Better_loadPois({
		id: 'pois', 
		key: 'pois',
		url: '/ajax/pois/pois?order=distance&lon='+wifiLon+'&lat='+wifiLat+'&range='+50000+'&with_major='+withMajors,
		page: page,
		count: 30,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults('pois', betterLang.pois.pois.no_pois);
			},
			afterDeleteCallback: function(){
				$('div.tabs ul.tabNavigation a[href="#pois"]').trigger('click');
			},
			completeCallback: function(json){
				if (!majorsGot) {
					arr = new Array();
					for(i in json.majors) {
						arr.push('<li><a href="/'+json.majors[i].username+'" title="'+json.majors[i].nickname+'"><img class="avatar" src="'+json.majors[i].avatar_small+'" style="width:30px height:30px;" onerror="" /></a></li>');
					}
					
					if (arr.length>0) {
						html = arr.join(' ');
						$('#nearby_majors').html(html);
						$('#nearby_majors img').error(function(){
							$(this).attr('src', Better_AvatarOnError);
						});
					}
					
					majorsGot = true;
				}
			}
		}
	});			
}

function Better_Pois_Init()
{
	var tabContainers = $('div.tabs > div').not('#search_poi_div');
	tabContainers.hide().filter(':first').show();
        
	Better_Notify_clear();
	
    $('div.tabs ul.tabNavigation a').click(function () {
    	if(typeof($(this).attr('disabled'))!='undefined' && $(this).attr('disabled')=='true'){
			return false;
		}
    	
    	tabContainers.hide();
    	tabContainers.filter(this.hash).show();
    	
    	$('#search_poi_div').hide();
    	
    	$('div.tabs ul.tabNavigation a').removeClass('selected');

        $(this).addClass('selected');
    	switch(this.hash) {
	    	case '#users':
	    		$('#tbl_users').empty();
	    		Better_Pois_Users(1);
	    		break;
	    	case '#pois':
	    		$('#tbl_pois').empty();
	    		$('#search_poi_div').show();
	    		Better_Pois_Pois(1, true);
	    		break;
	    	case '#tips':
	    		$('#tbl_tips').empty();
	    		Better_Pois_Tips(1);
	    		break;
    	}
    	
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

}


function Better_Pois_Search_Poi(page) 
{
	page = page ? page : 1;
	var key = 'search_pois';
	
	Better_Pager({
		key: key,
		next: betterLang.global.checkin.more_poi,
		last: betterLang.global.checkin.no_more_poi,
		callback: Better_Pois_Search_Poi
	});
	
	keyword = $.trim($('#poi_keyword').val());
	if (keyword==betterLang.global.checkin.poi_search.tips) {
		keyword = '';
	}

	Better_loadPois({
		key: key,
		url: '/ajax/pois/pois?order=distance&lon='+wifiLon+'&lat='+wifiLat+'&range='+$('#checkin_poi_search_range').val()+'&with_major=0',
		page: page,
		keyword: keyword,
		count: 30,
		callbacks: {
			emptyCallback: function(){
				Better_EmptyResults('search_pois', betterLang.search.no_result);
			},
			
			completeCallback: function(json){
				
			}
		}
	});
}


/**
 * 新增poi
 * 
 * @param event
 * @return
 */
function Better_Pois_AddPoi(event)
{
	poi_name = event.data.poi_name ? $.trim(event.data.poi_name) : $.trim($('#add_poi_name').val());
	address = event.data.address ? $.trim(event.data.address) : $.trim($('#add_poi_address').val());
	city = event.data.city ? $.trim(event.data.city) : $.trim($('#add_poi_city').val());
	province = event.data.province ? $.trim(event.data.province) : $.trim($('#add_poi_province').val());
	country = event.data.country ? $.trim(event.data.country) : $.trim($('#add_poi_country').val());
	phone = event.data.phone ? $.trim(event.data.phone) : $.trim($('#add_poi_phone').val());
	category = event.data.category ? $.trim(event.data.category) : $.trim($('#add_poi_category_hide').val());

	if (poi_name=='') {
		Better_Notify({
			msg: betterLang.home.add_poi.plz_input_name
		});
		//$('#add_poi_name').focus();
	} else if (address=='') {
		Better_Notify({
			msg: betterLang.home.add_poi.plz_input_address
		});
		//$('#add_poi_address').focus();
	} else if (city=='') {
		Better_Notify({
			msg: betterLang.home.add_poi.plz_input_city
		});
		//$('#add_poi_city').focus();
	} else {
		
		Better_Notify_loading();
		
		$.post('/ajax/poi/create', {
			name: poi_name,
			lon: add_lon,
			lat:add_lat, 
			address: address,
			phone: phone,
			category: category,
			city: city,
			province: province,
			country: country
		}, function(pcJson){
			
			Better_Confirm_clear();
			codes = pcJson.result.codes;

			switch (pcJson.result.code) {
				case codes.EMPTY_NAME:
					Better_Notify({
						msg: betterLang.home.add_poi.plz_input_name
					});
					break;
				case codes.BAN_POINAME:
					Better_Notify({
						msg: betterLang.home.add_poi.ban_poiname
					});
					break;
				case codes.SUCCESS:
					Better_Notify({
						msg: betterLang.home.add_poi.success.toString().replace('{POI}', poi_name)
					});
					
					window.location.href = '/poi/'+pcJson.result.poi_id;
			        
					break;
				case codes.TOO_MORE:
					Better_Notify({
						msg: betterLang.home.add_poi.too_more
					});
					
					
			        
					break;
				case codes.TOO_QUICK:
					Better_Notify({
						msg: betterLang.home.add_poi.too_quick
					});
					
					
			        
					break;
				case codes.FAILED:
				default:
					Better_Notify({
						msg: betterLang.home.add_poi.failed
					});
					break;
			}
		}, 'json');			

	}
}


$(function(){
	Better_GetW3cLL();
	Better_Pois_GotLL();
	
	$('#search_poi_btn').click(function(){
		var tabContainers = $('div.tabs > div').not('#search_poi_div');
		tabContainers.hide();
		$('div#search_pois').show();
		$('#tbl_search_pois').empty();
		
		Better_PagerList.search_pois = 2; //分页
		Better_Pois_Search_Poi(1);
	});
	
	$('#create_poi').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){
			$('#fancybox-wrap, #fancybox-outer').css('height', '560px');
			Better_Create_InitGMap(true);
			
			if ($('#poi_keyword').val()!=betterLang.global.checkin.poi_search.tips) {
				$('#add_poi_name').val($('#poi_keyword').val());
			}
		},	
		'onClosed': function(){
			Better_UnloadGMap();
		}
	});
	
	
	$('#close_create').click(function(){
		$('#add_poi_city').val('');
		$('#add_poi_address').val('');
		$.fancybox.close();
		return false;
	});
	
	$('#add_poi_category').change(function(){
		$('#add_poi_category').find('option').each(function(){
			if($(this).attr('selected')){
				$('#add_poi_category_hide').val($(this).val());
			}
		});
	});
	
	$('#create_poi_btn').bind('click',{}, Better_Pois_AddPoi);

	
	//键盘事件
	$(document).keypress(function(event){
		if(event.keyCode==13){
			$('#search_poi_btn').trigger('click');
		}
	});
	
	
	$('#poi_keyword').focus(function(){
		if($(this).val()==betterLang.global.checkin.poi_search.tips){
			$(this).val('');
			$(this).css('color', '#333');
		}
	}).blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).val(betterLang.global.checkin.poi_search.tips);
		}
	});

});