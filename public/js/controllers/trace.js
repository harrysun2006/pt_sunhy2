var days = 180;

/**
 * 踪迹地图
 */
function Better_User_initializeGmap()
{
	count = 0;
	if (GBrowserIsCompatible()) {

		map = new GMap2(document.getElementById("trace_map"));
		var customUI = map.getDefaultUI();
		customUI.maptypes.hybrid = false;
		map.setUI(customUI);
		map.setMapType(G_PHYSICAL_MAP);
		map.enableScrollWheelZoom();

		count = rows.length;
		if(count>0){
			minlon = maxlon = rows[0].poi.lon;
			minlat = maxlat = rows[0].poi.lat;
			for(var i=0; i<count; i++){
				mlon = rows[i].poi.lon;
				mlat = rows[i].poi.lat;
				
				if(mlon < minlon){
					minlon = mlon;
				}
				if(mlon > maxlon){
					maxlon = mlon;
				}
				if(mlat < minlat){
					minlat = mlat;
				}
				if(mlat > maxlat){
					maxlat = mlat;
				}
				
			}
		
			southwest = new GLatLng(minlat, minlon);
			northeast = new GLatLng(maxlat, maxlon);
			bounds = new GLatLngBounds(southwest, northeast);
			
			zoom = map.getBoundsZoomLevel(bounds);
			map.setCenter(bounds.getCenter(), zoom);
			
			window.setTimeout(Better_Trace_addMarkers, 0);
		}else{
			map.setCenter(new GLatLng(34.52466, 102.21679), 4);
		}
		
	}
	
}

/**
 * 时间轴滑块滑动地图改变
 * @return
 */
function Better_Trace_changeMap(){
	if (GBrowserIsCompatible()) {
		$.getJSON('/ajax/user/usertrace', {
			uid: uid,
			days: days
		}, function(json){
			Better_Notify_clear();
			
			rows = json.rows;
			user = json.user;
			clusters = json.clusters;
			Better_User_initializeGmap();
		});
	}
}


function Better_Trace_addMarkers(){
	 var mgr = new MarkerManager(map);
	 for(var i=1; i<=15; i++){
		 mgr.addMarkers(Better_Trace_getMarkers(i), i, i);
	 }
	 
	 mgr.addMarkers(Better_Trace_getMarkers(16), 16);
	 mgr.refresh();
}


function Better_Trace_getMarkers(zoomlevel){
	var batch = [];
	if(zoomlevel>=1 && zoomlevel<=15){
		/*var radius = Better_Trace_getRadius(zoomlevel);
		var activeClusterId = 0;
		var cluster = [];
		
		for(var n=0; n<count; n++){
			rows[n].cluster_flag = 0;
		}
		
		for(var i=0; i<count; i++){
			if(rows[i].cluster_flag==0){
				cluster[activeClusterId] = {};
				cluster[activeClusterId].numCheckins = parseInt(rows[i].checkin_count);
				cluster[activeClusterId].LL = new GLatLng(rows[i].poi.lat, rows[i].poi.lon);
				cluster[activeClusterId].poi = rows[i].poi;
				cluster[activeClusterId].checkin_time = rows[i].checkin_time;
				
				for(var m=i+1; m<count; m++){
					if(rows[m].cluster_flag==0){
						latlon = new GLatLng(rows[m].poi.lat, rows[m].poi.lon);
						if(latlon.distanceFrom(cluster[activeClusterId].LL) <= radius){
							cluster[activeClusterId].numCheckins +=parseInt(rows[m].checkin_count);
							cluster[activeClusterId].checkin_time = cluster[activeClusterId].checkin_time >= rows[m].checkin_time? cluster[activeClusterId].checkin_time : rows[m].checkin_time;
							rows[m].cluster_flag = 1;
						}
					}
				}
				activeClusterId++;
			}	
		}*/
		var cluster = clusters[zoomlevel];
		for(var c in cluster){
			cluster[c].LL = new GLatLng(cluster[c].poi.lat, cluster[c].poi.lon);
		}
		
		maxNum = Better_Trace_getMaxNumcheckins(cluster);
		
		/*levelMinNum = [];
		levelMinNum[0] = Math.ceil(maxNum*(1/5));
		levelMinNum[1] = Math.ceil(maxNum*(2/5));
		levelMinNum[2] = Math.ceil(maxNum*(3/5));
		levelMinNum[3] = Math.ceil(maxNum*(4/5));
		levelMinNum[4] = Math.ceil(maxNum*(5/5));*/

		for(var j in cluster){
			var size = Better_Trace_getCircleSize(cluster[j].numCheckins, maxNum, 5);
			
			/*//计算每级的最小次数
			switch(size)
			{
				case 1:
					levelMinNum[0] = cluster[j].numCheckins<levelMinNum[0]? cluster[j].numCheckins : levelMinNum[0];
					break;
				case 2:
					levelMinNum[1] = cluster[j].numCheckins<levelMinNum[1]? cluster[j].numCheckins : levelMinNum[1];
					break;
				case 3:
					levelMinNum[2] = cluster[j].numCheckins<levelMinNum[2]? cluster[j].numCheckins : levelMinNum[2];
					break;
				case 4:
					levelMinNum[3] = cluster[j].numCheckins<levelMinNum[3]? cluster[j].numCheckins : levelMinNum[3];
					break;
				case 5:
					levelMinNum[4] = cluster[j].numCheckins<levelMinNum[4]? cluster[j].numCheckins : levelMinNum[4];
					break;
				default:
					break;
			}*/
			
			/*var newIcon = MapIconMaker.createFlatIcon({
				width: size, 
				height: size, 
				primaryColor: "#ec4800",
				label: cluster[j].numCheckins.toString(),
				labelColor: "#ffffff"
			});
			
			var marker = new GMarker(cluster[j].LL, {icon: newIcon});*/
			
			var icon_ = Better_Trace_getIconPro(size);
			var icon_img = icon_.img;
			var width = icon_.width;
			var style= icon_.style;
			var offset = icon_.offset;
			
			var newIcon = new GIcon();
			newIcon.image = icon_img;
			newIcon.iconSize = new GSize(width, width);
			newIcon.iconAnchor = new GPoint(width/2, width/2);
			newIcon.infoWindowAnchor = new GPoint(width/2, width/3);
			
			var opts = { "icon": newIcon,
					  	 "clickable": true,
					   	 "labelText": cluster[j].numCheckins.toString(),
					  	 "labelOffset": offset,
					  	 "labelClass": style
			};

			
			var marker = new LabeledMarker(cluster[j].LL, opts);
			
			marker.bindInfoWindowHtml(Better_Trace_createInfoWindow({
				poi: cluster[j].poi,
				poi_id: cluster[j].poi.poi_id,
				checkin_count: cluster[j].numCheckins,
				checkin_time: cluster[j].checkin_time
			}, true));
			
			batch.push(marker);
		}
	}else if(zoomlevel>=16 && zoomlevel<=19){
		var maxpoicheckins = 0;
		/*for(var h=0; h<count; h++){
			if(parseInt(rows[h].checkin_count) > maxpoicheckins){
				maxpoicheckins = parseInt(rows[h].checkin_count);
			}
		}*/
		
		for(var k=0; k<count; k++){
			var size = Better_Trace_getCircleSize(parseInt(rows[k].checkin_count), maxpoicheckins, 5);
			/*var newIcon = MapIconMaker.createFlatIcon({
				width: size, 
				height: size, 
				primaryColor: "#ec4800",
				label: rows[k].checkin_count,
				labelColor: "#ffffff"
			});*/
			
			var icon_ = Better_Trace_getIconPro(size);
			var icon_img = icon_.img;
			var width = icon_.width;
			var style= icon_.style;
			var offset = icon_.offset;
			
			var newIcon = new GIcon();
			newIcon.image = icon_img;
			newIcon.iconSize = new GSize(width, width);
			newIcon.iconAnchor = new GPoint(width/2, width/2);
			newIcon.infoWindowAnchor = new GPoint(width/2, width/3);
			
			var opts = { "icon": newIcon,
					  	 "clickable": true,
					   	 "labelText": rows[k].checkin_count,
					  	 "labelOffset": offset,
					  	 "labelClass": style
			};
			
			var LL = new GLatLng(rows[k].poi.lat, rows[k].poi.lon);
			
			var marker = new LabeledMarker(LL, opts);
			//var marker = new GMarker(LL);
			marker.bindInfoWindowHtml(Better_Trace_createInfoWindow(rows[k], false));
			
			batch.push(marker);
		}
	}
	
	return batch;
}


//info window
function Better_Trace_createInfoWindow(row, need_nearby){
	
	avatar = '<div class="left"><img src="'+user.avatar_normal+'" width="50" height="50" /></div>';
	info = '<div class="left" style="margin-left: 10px;"><span style="font-size: 14px;">'+row.poi.city+' <a href="/poi/'+row.poi_id+'" style="color: #7589AE;">'+row.poi.name+'</a> '+ eval("need_nearby==true? betterLang.global.nearby: ''")+'</span><br>'+
			'<span style="color: #B7BDC4; white-space: nowrap;">'+betterLang.trace.appear_times.replace("{TIMES}",row.checkin_count)+'&nbsp;&nbsp;&nbsp;'+betterLang.trace.appear_lasttime+Better_compareTime(row.checkin_time)+'</span></div>';
	
	table='<table><tr><td>';
	table += avatar;
	table +='</td><td>';
	table += info;
	table += '</td></tr></table>'
	
	return table;
}

function Better_Trace_getIconPro(size){
	size = size || 1;
	var icon_ = {};
	var img = '';
	var width = 0;
	var style= '';
	var offset = new Object();
	switch(size)
	{
		case 1:
			img = '/images/point/blue.png';
			width = 30;
			style = 'LabelMarker1';
			offset = new GSize(-15, -15);
			break;
		case 2:
			img = '/images/point/green.png';
			width = 48;
			style = 'LabelMarker2';
			offset = new GSize(-24, -24);
			break;
		case 3:
			img = '/images/point/yellow.png';
			width = 52;
			style = 'LabelMarker3';
			offset = new GSize(-26, -26);
			break;
		case 4:
			img = '/images/point/orange.png';
			width = 54;
			style = 'LabelMarker4';
			offset = new GSize(-27, -27);
			break;
		case 5:
			img = '/images/point/red.png';
			width = 58;
			style = 'LabelMarker5';
			offset = new GSize(-29, -29);
			break;
		default:
				break;
	}
	
	icon_.img = img;
	icon_.width = width;
	icon_.style = style;
	icon_.offset = offset;
	return icon_;
}


function Better_Trace_getCircleSize(num, maxNum, scale){
	/*scale = typeof(scale)=="undefined" ? 5 : scale;
	
	result = Math.ceil(((num-1)/maxNum)*scale)+1;
	result = result>scale? scale : result;
	
	return result;*/
	
	var result = 0;
	if(num==1){
		result = 1;
	}else if(num>1 && num<=5){
		result = 2;
	}else if(num>5 && num<=10){
		result = 3;
	}else if(num>10 && num<=30){
		result = 4;
	}else if(num>30){
		result = 5;
	}
	
	return result;
	
}

function Better_Trace_getMaxNumcheckins(cluster){
	var max = 0;
	/*for(var i in cluster){
		if(cluster[i].numCheckins > max){
			max = cluster[i].numCheckins;
		}
	}*/
	return max;
}


/*function Better_Trace_getRadius(zoomlevel){
	var radius = 0;
	switch(zoomlevel)
	{
		case 1:
			radius = 5000000;
			break;
		case 2:
			radius = 2000000;
			break;
		case 3:
			radius = 2000000;
			break;
		case 4:
			radius = 1000000;
			break;
		case 5:
			radius = 500000;
			break;
		case 6:
			radius = 200000;
			break;
		case 7:
			radius = 100000;
			break;
		case 8:
			radius = 50000;
			break;
		case 9:
			radius = 20000;
			break;
		case 10:
			radius = 10000;
			break;
		case 11:
			radius = 5000;
			break;
		case 12:
			radius = 2000;
			break;
		case 13:
			radius = 2000;
			break;
		case 14:
			radius = 1000;
			break;
		case 15:
			radius = 500;
			break;
		default:
			break;
	}
	radius = radius/2;
	
	return radius;
}*/


/*function Better_Trace_getStaticLabel(label){
	var level = Better_Trace_getCircleSize(parseInt(label), maxNum, 5);
	//level = (level-15)/5;
	var result = '';
	
	switch(level)
	{
		case 1:
			result = levelMinNum[0]+'%2B';
			break;
		case 2:
			result = levelMinNum[1]+'%2B';
			break;
		case 3:
			result = levelMinNum[2]+'%2B';
			break;
		case 4:
			result = levelMinNum[3]+'%2B';
			break;
		case 5:
			result = levelMinNum[4]+'%2B';
			break;
		default:
			break;
	}
	
	return result;
}*/


function Better_Trace_post(){
	var status_text = $('#trace_status_text').val();
	if(inOther_trace){
		status_text = betterLang.trace.their_tips.replace('NAME', user.nickname)+status_text;
	}
	
	var need_sync = $('#trace_need_sync').attr('checked') ? 1 : 0;
	
	Better_Notify_loading();

	$.post('/ajax/blog/post', {
		message: status_text, 
		upbid: 0,
		attach: '',
		priv: 'public',
		lon: wifiLon,
		lat: wifiLat,
		range: wifiRange,
		poi_id: 0,
		type: Better_Shout_Type,
		need_sync: need_sync,
		map_url: static_map
		}, function(json){
			
			if (Better_AjaxCheck(json)) {							
				if (json.code=='success') {
					
					if (Better_Shout_Result_Title=='') {
						if (Better_Shout_Type=='normal') {
							
							success_notify = betterLang.global.shout.success;
							
						} else {
							success_notify = betterLang.global.post.success;
						}
					} else {
						success_notify = Better_Shout_Result_Title;
					}

					Better_Notify({
						msg: success_notify+' '+Better_parseAchievement(json, Better_Shout_Type=='normal' ? betterLang.global.this_shout : betterLang.global.this_tips),
						close_timer: 2
					});

				} else if (json.code=='need_check') {
					Better_ResetPostForm();
					
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
					Better_ResetPostForm();
					
					Better_Notify({
						msg: betterLang.post.forbidden
					});								
				} else if (json.code=='words_r_banned') {
					Better_ResetPostForm();
					
					Better_Notify({
						msg: betterLang.post.ban_words
					});
				} else if (json.code=='too_short') {
					Better_Notify({
						msg: betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength)
					});
				} else if(json.code=='link_wrong'){
					Better_Notify({
						msg: betterLang.post.wrong_link
					});
				}else {
					Better_Notify({
						msg: 'Failed'
					});										
				}							
				
			}
	}, 'json');
}

$(function() {
	
	Better_User_initializeGmap();
	
	var reg_date = $('#reg_date').text();
	var tmp = reg_date.split('.');
	var y = tmp[0];
	var m = tmp[1]-1;
	var d = tmp[2];
	var reg_date = new Date(y, m, d, 0, 0, 0);
	
	//滑动条
	$("#slider").slider({
		range: "min",
		value: total_days,
		min: 0,
		max: total_days,
		slide: function(event, ui) {
			var left = parseFloat($('#slider a.ui-slider-handle').css('left'))/100*625-$('#show_date').width()/2;
			if(left<0){
				left = 0;
			}
			if(left>625-$('#show_date').width()){
				left = 625-$('#show_date').width();
			}
			left +='px';
			$("#show_date").css('left', left);
			
			var now_date = new Date(y, m, d, 0, 0, 0);
			now_date.setDate(reg_date.getDate()+ui.value);
			$('#show_date').text(formatDate(now_date));
		},
		stop: function(event, ui){
			var left = parseFloat($('#slider a.ui-slider-handle').css('left'))/100*625-$('#show_date').width()/2;
			if(left<0){
				left = 0;
			}
			if(left>625-$('#show_date').width()){
				left = 625-$('#show_date').width();
			}
			left +='px';
			$("#show_date").css('left', left);
			days = ui.value;
			
			Better_Notify_loading();
			Better_Trace_changeMap();
			
		}
	});

	if(inOther_trace){ 
		$('#status_text').val('');
	}
	
	//trace页提交
	if (Better_Need_Post_Js) {
		$('#post_btn_trace').click(function(){
			
			/*if($('#need_zj_picture').attr('checked')){
				//生成静态地图
				if(map.getCurrentMapType().getName()=='地图'){
					var maptype = 'roadmap';
				}else if(map.getCurrentMapType().getName()=='卫星'){
					var maptype = 'satellite';
				}else{
					var maptype = 'terrain';
				}
				
				static_map = 'http://maps.google.com/maps/api/staticmap?center='+map.getCenter().lat().toFixed(2)+','+map.getCenter().lng().toFixed(2)+'&zoom='+map.getZoom()+
				'&size=640x380&sensor=false&language=zh-CN&maptype='+maptype;
				
				var map_bounds = map.getBounds();
				var mks = Better_Trace_getMarkers(map.getZoom());
				var icon_arr = {};
				var img_arr = {};
				var img_arr_ = [];
				
				for(var m in mks){
					if(map_bounds.containsLatLng(mks[m].getLatLng())){
						var marker_icon = mks[m].getIcon();
						var icon_img = BASE_URL+marker_icon.image;
						
						if(typeof(icon_arr[icon_img])=='undefined' || icon_arr[icon_img].length==0){
							icon_arr[icon_img] = [];
							icon_arr[icon_img].push(mks[m].getLatLng().lat().toFixed(4)+','+mks[m].getLatLng().lng().toFixed(4));
							//img_arr.push(icon_img);
							img_arr[icon_img] = icon_img.replace('.png','_.png');//parse _
							img_arr_.push(icon_img);
							
							for(var n=parseInt(m)+1; n<mks.length; n++){
								if(map_bounds.containsLatLng(mks[n].getLatLng())){
									var marker_icon2 = mks[n].getIcon();
									var icon_img2 = BASE_URL+marker_icon2.image;
									if(icon_img==icon_img2){
										icon_arr[icon_img].push(mks[n].getLatLng().lat().toFixed(4)+','+mks[n].getLatLng().lng().toFixed(4));
									}
								}
							}
						}
					}
				}
				
				if(img_arr_.length>0){
					
					attach = $('#attach').val();
					var len = Better_GetPostLength();
					if (!attach && len>Better_PostMessageMaxLength) {
						alert(betterLang.blog.post_size_to_large.replace('%s', Better_PostMessageMaxLength));
					} else if (!attach && len<Better_PostMessageMinLength) {
						alert(betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength));
					} else {
						Better_Notify_loading();
						$.getJSON('/trace/shorturl', {
							urls: img_arr
						}, function(shortUrls){
							
							for(var key in icon_arr){
								static_map +='&markers=icon:'+shortUrls[key];
								for(var p in icon_arr[key]){
									static_map +='|'+icon_arr[key][p];
								}
							}
							
							static_map = static_map.substring(0, 1900);//google url最大只支持 2048个字符
							Better_Trace_post();
						});
						//暂时去除short url
						for(var key in img_arr){
							static_map +='&markers=icon:'+img_arr[key];
							for(var p in icon_arr[key]){
								static_map +='|'+icon_arr[key][p];
							}
						}
						
						static_map = static_map.substring(0, 1900);//google url最大只支持 2048个字符
						Better_Trace_post();
					}
					
				}else{
					Better_Notify({
						msg: betterLang.trace.no_tracks
					});	
				}
				
			}else{*/
				static_map = '';
				var len = Better_GetPostLength('trace_');
				if (len>Better_PostMessageMaxLength) {
					alert(betterLang.blog.post_size_to_large.replace('%s', Better_PostMessageMaxLength));
				} else if (len<Better_PostMessageMinLength) {
					alert(betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength));
				} else {
					Better_Trace_post();
				}
			//}
		});
	}
	
});


function formatDate(date){
	var y = date.getFullYear();
	var m = (date.getMonth()+1) < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1;
	var d = date.getDate() < 10 ? '0'+date.getDate() : date.getDate();
	return y+'.'+m+'.'+d;
}