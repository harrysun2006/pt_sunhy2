function Better_Venue_Reg(){
	
	err = false;
	if($("#havelotspoi").attr("checked")==true){
		 $('#hadlotspoi').val(1);
	}		
	company = $.trim($('#company').val());
	ownername = $.trim($('#ownername').val());
	phone = $.trim($('#phone').val());
	email = $.trim($('#email').val());
	ownertype = $('#ownertype').attr('checked') ? '1' : '0';
	lotsshop = $.trim($('#hadlotspoi').val());
	poi_id = $.trim($('#poi_id').val());	
	checkinfo = 0;		
	if(company=='' || $.trim(company)=='' || company==betterLang.venue.require.company){
		$('#err_company').html(betterLang.venue.require.company_empty).show();
		checkinfo =1;
	} 
	if(ownername=='' || $.trim(ownername)=='' || ownername==betterLang.venue.require.name){			
		$('#err_ownername').html(betterLang.venue.require.ownername_empty).show();
		checkinfo =1;
	} 
	if(phone=='' || $.trim(phone)==''  || phone==betterLang.venue.require.phone){			
		$('#err_phone').html(betterLang.venue.require.phone_empty).show();
		checkinfo =1;
	} 
	if(email==betterLang.venue.require.email){
		email = '';
	}
	/*
	if(email=='' || $.trim(email)==''){			
		$('#err_email').html(betterLang.venue.require.email_empty).show();
		checkinfo =1;
	} 
	*/	
	poiname = $('#venue-poiname').html();	
	if(poi_id=='' || $.trim(poi_id)=='' || poiname.length==0){			
		$('#err_poi').html(betterLang.venue.require.poiid_empty).show();
		checkinfo =1;
	}
	if(checkinfo){
		
	} else {		
		$('#err_company').empty().hide();
		$('#err_ownername').empty().hide();
		$('#err_phone').empty().hide();
		$('#err_email').empty().hide();
		$('#err_poi').empty().hide();		
		Better_Notify_loading();
		$.post('/venue/postrequire', {
			poi_id: poi_id,
			company: company,
			ownername:ownername, 
			phone: phone,
			email: email,			
			hadlotspoi: lotsshop,
			ownertype: ownertype			
		}, function(requirejson){		
			Better_Confirm_clear();
			if(requirejson.has_err==0){				
				window.location.href = '/venue/step2?id='+requirejson.poi_id+'&r_id='+requirejson.r_id;
			} else if(requirejson.has_err==2){
				Better_Notify({
					msg: betterLang.venue.poi_hadowner,
					close_timer: 2
				});
			}
		}, 'json');		
	}
	return false;
}

function dothis(){
	$("input[name=choose_poi]:radio").each(function(){
		  if(this.checked){		
			  $('#poi_id').val($(this).val());
			  $('#venue-poiname').html($(this).attr('poiname'));
			  $('#err_poi').empty().hide();	
		  }
		}); 
	
//	$('#search_pois_result').hide();
//	$('#search_pois').hide();	
//	$('#choose_poi').hide();
}
function Better_Pois_Search_Poi(page) 
{
	page = page ? page : 1;
	var key = 'search_pois';
	
	Better_venuePager({
		key: key,
		next: betterLang.global.checkin.more_poi,
		last: betterLang.global.checkin.no_more_poi,
		callback: Better_Pois_Search_Poi
	});
	
	keyword = $.trim($('#poi_keyword').val());
	if (keyword==betterLang.global.checkin.poi_search.tips) {
		keyword = '';
	}

	Better_loadvenuePois({
		key: key,
		url: '/ajax/pois/pois?order=distance&lon='+wifiLon+'&lat='+wifiLat+'&range='+$('#checkin_poi_search_range').val()+'&with_major=0',
		page: page,
		keyword: keyword,
		count: 6,
		callbacks: {
			emptyCallback: function(){
				
				Better_venueEmptyResultse('search_pois', betterLang.search.no_result+"<a href='#createdlg' id='create_poi' >创建一个</a>");
				Better_GetW3cLL();
				Better_Pois_GotLL();
				$('#create_poi').unbind("click");	
				$('#create_poi').click(function(){
					
				}).fancybox({
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
			},			
			completeCallback: function(json){
				
			}
		}
	});
}

function Better_venueEmptyResultse(id, msg)
{
	html = '<div class="notice emptyResult">'+msg+'</div>';
	$('#tbl_'+id).html(html);
	$('#pager_'+id).empty().hide();
}


function Better_venuePager(ops)
{
	var pagerKey = ops.key;

	try {

		if (typeof(eval('Better_PagerList.'+pagerKey))=='undefined' || eval('Better_PagerList.'+pagerKey)==2) {
			
			if($.browser.msie==true && parseInt($.browser.version)<7){
				var pager = $(document.createElement('div')).attr('id', 'pager_href_'+pagerKey).addClass('ie6page');
			} else {
				var pager = $(document.createElement('a')).attr('id', 'pager_href_'+pagerKey);
			}
			nextStr = typeof(ops.next)=='undefined' ? betterLang.venue.page_next : ops.next;
			pager.attr('href', 'javascript:void(0)').html(nextStr).click(function(){
				
				toPage = parseInt($('#pager_page_'+pagerKey).val())+1;
				pages = parseInt($('#pager_pages_'+pagerKey).val());
				
				
				
				if (toPage<=pages) {
					$('#pager_page_'+pagerKey).val(toPage);
					if (typeof(ops.callback)=='function') {
						ops.callback(toPage);
					}
						
					if(typeof ops.need_last!='undefined' && ops.need_last){
						if (toPage==pages) {
							lastStr = typeof(ops.last)=='undefined' ? betterLang.global.pager.arrive_last_page : ops.last;
							$(this).html(lastStr);
							
							eval('Better_PagerList.'+pagerKey+'=2;');
						}
					}
				}
				
			});
			
			$('#pager_'+pagerKey).empty().append("<input type='hidden' id='pager_pages_"+pagerKey+"' value='1' /><input type='hidden'  id='pager_page_"+pagerKey+"' value='1' />").append(pager);
			
			eval('Better_PagerList.'+pagerKey+'=1;');
			
		}
	} catch(e) {
		alert(e.message);
	}

}

function Better_Tablevenue_Loading(id)
{
	$('#pager_'+id).hide();
	
	tr = '<div class="tbl_tr_ajax_loading" style="margin-top:10px;"><img src="images/ajax_loading.gif" alt="" /></div>';
	$('#tbl_'+id).append(tr);
}

function Better_Clear_Tablevenue_Loading(id)
{
	$('#pager_'+id).show();
	$('#tbl_'+id+' div.tbl_tr_ajax_loading').replaceWith('');	
	$('#search_pois_result').show();
}

function Better_Pagervenue_Reset(key)
{
	$('#pager_pages_'+key).val(1);
	$('#pager_page_'+key).val(1);	
	$('#pager_href_'+key).html("<span style='font-weight:bold;font-size:16px;line-height:22px;'>"+betterLang.venue.page_next+"</span>");
}

function Better_loadvenuePois(options)
{
	url = options.url;
	page = options.page;
	key = options.key;
	uid = typeof(options.uid)!='undefined' ? options.uid : betterUser.uid;
	keyword = typeof(options.keyword)!='undefined' ? options.keyword : '';
	callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};
	withoutMine = typeof(options.without_mine)!='undefined' ? options.without_mine : false;
	count = typeof(options.count)!='undefined' ? options.count : 6;
	
	if (page<=1) {
		Better_Pagervenue_Reset(key);
	}

	Better_Tablevenue_Loading(key);
	
	$.get(url, {
		page: page,
		uid: uid,
		keyword: keyword,
		without_mine: withoutMine,
		count: count
	}, function(lpJson){		
		Better_Clear_Tablevenue_Loading(key);
		
		if (Better_AjaxCheck(lpJson)) {
			Better_Pager_setPages(key, lpJson.pages);
			nowPage = typeof(lpJson.page)!='undefined' ? lpJson.page : page;
			
			if (lpJson.count>0) {		
				
				for(i=0;i<lpJson.rows.length;i++) {
					poiId = (typeof(lpJson.rows[i].poi_id)!='undefined' && lpJson.rows[i].poi_id) ? lpJson.rows[i].poi_id : (typeof(lpJson.rows[i].aibang_id)!='undefined' ? lpJson.rows[i].aibang_id : 0);
					address = typeof(lpJson.rows[i].addr)!='undefined' ? lpJson.rows[i].addr : lpJson.rows[i].address;
					check_ins = typeof(lpJson.rows[i].checkins)!='undefined' ? lpJson.rows[i].checkins : 0;
					visitors = typeof(lpJson.rows[i].users)!='undefined' ? lpJson.rows[i].users : 0;
					tipss = typeof(lpJson.rows[i].tips)!='undefined' ? lpJson.rows[i].tips : 0;
					logoUrl = typeof(lpJson.rows[i].logo_url)!='undefined' ? lpJson.rows[i].logo_url : 'images/poi/category/48/default.png';
					logoUrl = logoUrl.replace('101', '48');
					
					tr = new Array();
					
					tr.push("<div class='venue_poi_row'>");
					tr.push("<div style='font-weight:bold;height:25px;width:287px;overflow: hidden;'><input type='radio' name='choose_poi' value='"+poiId+"' poiname='"+lpJson.rows[i].name+"' onclick='dothis()'/>"+lpJson.rows[i].name+"</div>");
					tr.push("<div style='color:#999;padding-left:20px;height:14px;line-height:14px;width:287px;overflow: hidden;'>"+lpJson.rows[i].city+' '+address+"</div>");
					
					tr = tr.join(' ');
					
					jTr = $(tr);
					
					jTr.mouseenter(function(){
						poiId = $(this).attr('poi_id');
						funcDiv = $('#poi_list_row_'+poiId);
						
						if (funcDiv.html()=='') {
							a = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'poi_favorite_link_'+poiId);
							a.css('font-weight', 'normal');								
							if ($.inArray(poiId, betterUser.poi_favorites)>=0) {
								a.bind('click', {
									poi_id: poiId
								}, Better_Unfavorite_Poi).text(betterLang.global.favorite.cancel);
							} else {
								a.bind('click', {
									poi_id: poiId
								}, Better_Favorite_Poi).text(betterLang.global.favorite.title);
							}

							funcDiv.append(a);
						}
						
						funcDiv.show();
					});
					
					jTr.mouseleave(function(){
						$('#poi_list_row_'+$(this).attr('poi_id')).hide();
					});
					
					$('#tbl_'+key).append(jTr);
				}
			//	$('#choose_poi').show();
				$('#check_lotspoi').show();
				if ($.isFunction(callbacks.completeCallback)) {
					callbacks.completeCallback(lpJson);
				}				
			} else if ($.isFunction(callbacks.emptyCallback)) {
				callbacks.emptyCallback();
			}
		}
		
	}, 'json');
}

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
					 $('#poi_id').val(pcJson.result.poi_id);
					 $('#venue-poiname').html(poi_name);
					 $.fancybox.close();
					 $('#search_pois_result').hide();
					 $('div#search_pois').show();
					 $('#tbl_search_pois').empty();
					//window.location.href = '/poi/'+pcJson.result.poi_id;
			        
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


$(function() {
	$('#check_lotspoi').hide();
	$('#choose_poi').hide();
	$('#search_pois_result').hide();
	
	/*
	 * onclick="dodo(this.value)"
	$('#choose_poi').click(function(){
		$("input[name=choose_poi]:radio").each(function(){
			  if(this.checked){		
				  $('#poi_id').val($(this).val());
				  $('#venue-poiname').html($(this).attr('poiname'));
			  }
			}); 
		if($("#havelotspoi").attr("checked")==true){
			 $('#hadlotspoi').val(1);
		}		
		$('#search_pois_result').hide();
		$('#search_pois').hide();	
		$('#choose_poi').hide();
	});
	*/
	
	
	
	
	if(typeof(Better_Poi_Id)!='undefined' && Better_Poi_Id>0){
		
		$('#poi_id').val(Better_Poi_Id);
		$('#venue-poiname').html(Better_Poi_Detail.name);
	}
	$('#venue_reg_submit').click(function(){
		$('#err_company').empty().hide();
		$('#err_ownername').empty().hide();
		$('#err_phone').empty().hide();
		$('#err_email').empty().hide();
		$('#err_poi').empty().hide();
		Better_Venue_Reg();		
	});
	$('#searchpoi_button').click(function(){
		//var tabContainers = $('div.tabs > div').not('#search_poi_div');
		//tabContainers.hide();
		$('#search_pois_result').hide();
		$('#choose_poi').hide();
		$('#check_lotspoi').hide();
		$('div#search_pois').show();
		$('#tbl_search_pois').empty();		
		Better_PagerList.search_pois = 2; //分页
		Better_Pois_Search_Poi(1);	
		
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
	
	$('#company').focus(function(){
		if($(this).val()==betterLang.venue.require.company){
			$(this).val('');
			$(this).css('color', '#333');
		}
	}).blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).val(betterLang.venue.require.company);
		}
	});	
	$('#ownername').focus(function(){
		if($(this).val()==betterLang.venue.require.name){
			$(this).val('');
			$(this).css('color', '#333');
		}
	}).blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).val(betterLang.venue.require.name);
		}
	});	
	
	$('#phone').focus(function(){
		if($(this).val()==betterLang.venue.require.phone){
			$(this).val('');
			$(this).css('color', '#333');
		}
	}).blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).val(betterLang.venue.require.phone);
		}
	});
	$('#email').focus(function(){
		if($(this).val()==betterLang.venue.require.email){
			$(this).val('');
			$(this).css('color', '#333');
		}
	}).blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).val(betterLang.venue.require.email);
		}
	});
	
	
	
	
	
	
	$('#create_poi_btn').bind('click',{}, Better_Pois_AddPoi);
	$('#close_create').click(function(){
		$('#add_poi_city').val('');
		$('#add_poi_address').val('');
		$.fancybox.close();
		return false;
	});
	
});