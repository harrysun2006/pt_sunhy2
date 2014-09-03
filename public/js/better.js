/**
 * Project Better's Main script
 * 
 * @author leip <leip@peptalk.cn>
 * 
 */
var patControllers = 'applycode|notfinished|register|mobile|poi|feedback|windowslive|rss|help|share|admin|messages|debug|mysql|search|ajax|place|index|api|around|base|error|favorites|follower|following|front|home|image|login|people|profile|resetpwd|scode|setting|signup|tools|upload|user';

var isloading=false;
var textsearch=",";
var pcount = 3;

var gmapZomm = 14;
var map;
var marker;

var iplocation = 0;
var ipcity = 0;

var atmp = null;
var Better_Home_AddressHasAlert = 0;
var Better_Home_Ref_lon = 0;
var Better_Home_Ref_lat = 0;
var Better_Home_Ref_range = 0;
var Better_Home_AddressAlert = 0;
var Better_Home_Poi_Search_Range = 5000;
var Better_Home_Map_Inited = false;
var Better_Ajax_processing = false;
var BETTER_HOME_LAST_STATUS_TIPS = false;
var Better_Lbs_Alert_Poped = false;
var Better_This_Script = '';

var wifiAlert = '';
var wifiLon = 0;
var wifiLat = 0;
var wifiRange = 0;
var wifiMsg = '';
var wifiError = false;
var wifiMessage = '';

var ipLon = 0;
var ipLat = 0;    

var Better_Geocode_Got = false;
var Better_CheckinMap_Show = false;
var Better_W3cLL_Gotted = {
		lon: -1,
		lat: -1,
		range: 99999999
}
var Better_Row_Overed = [];

var had_load_id = new Array();

var BETTER_BIG_BADGE_ID = 0;
var BETTER_BIG_BADGE_UID = 0;
var datetimepattern='YY-MM-DD hh:mm:ss'; 

/*Gmap.js*/
var Gmap;
var marker;

/** 变换语言	**/
function change_language(lan){		
	if(lan!='en'){
		lan='zh-cn';
	}
	$.cookie('lan', lan,  { expires: 7, path: '/'});
	window.location.reload();
}

function initialize() {
	lat = betterUser.lat ? betterUser.lat : 39.917;
	lon = betterUser.lon ? betterUser.lon : 116.397;

	if (GBrowserIsCompatible()) {
		var map = new GMap2(document.getElementById("map_canvas"));
		var customUI = map.getDefaultUI();
		map.setMapType(G_DEFAULT_MAP_TYPES[0]);
		map.setCenter(new GLatLng(lat, lon), 14);
		map.setUI(customUI);
		map.enableScrollWheelZoom();
	/*	GEvent.addListener(map, "moveend", function() {
			setCenterIcon();
		} );*/
	}
	Gmap = map;
	setCenterIcon();
	
	//增加驴博士定位
	//wifi = getLLByWifi(); 
}

function setCenterIcon() {
	marker = new GMarker(Gmap.getCenter(), {draggable: true});
	Gmap.clearOverlays();
	Gmap.addOverlay(marker);
	var latlon = marker.getLatLng();
	
	setlonlat(latlon.lng(),latlon.lat());

	GEvent.addListener(marker, "dragend", function() {  
			var latlon = marker.getLatLng();
			document.getElementById("location_range").value = 0;
			setlonlat(latlon.lng(),latlon.lat());
	});
}

function setlonlat(lon,lat) {
	document.getElementById("location_lon").value = lon;
	document.getElementById("location_lat").value = lat;
}
/*Gmap.js End*/

/*lbs.js */
var base64 = '';

function lbs_get_wifiData()
{	
	var lbs = new Object();
	lbs.err = false;
	
	if(navigator.appName != "Microsoft Internet Explorer") {
		lbs.err='5';
		return lbs;
	}
	
	try {  
		bedoLocation = new  ActiveXObject("BedoLocation.Location");  
	} catch(e) {  
		lbs.err='6';
		
		if (Better_Lbs_Promotion) {
			Better_Lbs_Alert();
		}
		
		return lbs;
	}
  
	if (!bedoLocation) {
		lbs.err='7';
		return lbs;
	}	
	
	lbs.bRet = bedoLocation.SetFrequency(60,180);
	lbs.bRet = bedoLocation.SetSensor(1);
	lbs.xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	
	bedoLocation.RequestLocation(function(bstrLocation){
		var locHeader = bstrLocation.substr(0,4);
		if(locHeader == "loc:") {
			lbs.base64 = bstrLocation.substr(4);
		}else if(locHeader == "err:"){
			lbs.err = bstrLocation.substr(4);
		}else if(locHeader == "vac:"){
			lbs.err = betterLang.lbs_wifi_data.empty;
		}else{
			lbs.err = betterLang.lbs_wifi_data.error.toString().replace('{LOCATION}',bstrLocation);
		}
	});

	return lbs;
	
}


function debugError (errCode) {	
	 switch(errCode) {
	 case '1':
	 return betterLang.no_wifidata;
	 break;
	 case '2':
	 return betterLang.no_wifihd;
	 break;
	 case '3':
	 return betterLang.wifi_nosignal;
	 break;
	 case '4':
	 return betterLang.refuse_location;
	 break;
	 case '5':
	 return betterLang.lbs_os;
	 break;
	 case '6':
	 return betterLang.check_setup_lbs;
	 break;
	 case '7':
	 return betterLang.fail_active;
	 break;
	 default:
	 return errCode;
	 break;
	 }
	 return errCode;
	 //return null;
}
/*lbs.js END*/

if(typeof(isAdmin)=='undefined'){
var ajaxUploader = new AjaxUpload('#btnUpload', {
			action: '/ajax/attach/upload',
			name: 'myfile',
			data: {
				attach: $('#attach').val()
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
					$('#btnUpload').attr('disabled',true);
					$('#btnPostNew').attr('disabled',true);
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
	
				$('#btnUpload').attr('disabled', false);
				$('#btnPostNew').attr('disabled', false);
				
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
					
					$('#attach').val(rt.attach);
					if (typeof(rt.new_file_url)!='undefined' && rt.new_file_url!='') {
						$('img#btnUpload').attr('src', rt.new_file_url).addClass('avatar').css('height', '76px').css('width', '80px').load(function(){
							w = $(this).width();
							h = $(this).height();
							
							if (h>80) {
								$(this).css('height', '76px');
								$(this).css('width', (w*76/h)+'px');
							}
						});
//						$('div#btnUpload').css('display', 'none');
						$('div#btnUpload .phototext').html('重选图片');
						$('#tipcontainer').css('height','82px');
						$('#tipcontainer .textinput #tips_status_text').removeClass('tipinput-noimg');
						$('#tipcontainer .textinput #tips_status_text').addClass('tipinput');
						$('#tipcontainer .previewimg img').attr('src', rt.new_file_url).css('margin','3px 2px 2px 2px').css('height', '76px').css('width', '80px').load(function(){
							w = $(this).width();
							h = $(this).height();
							
							if (h>80) {
								$(this).css('height', '76px');
								$(this).css('width', (w*76/h)+'px');
							}
						});
						$('#tipcontainer .previewimg').show();
						
					}
	
					/*del = $(document.createElement('a'));
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
										Better_Notify({
											msg: da.err
										});
									} else {
										Better_Notify({
											msg: betterLang.blog.upload_delete_success
										});
										$('#fileDesc').empty().hide();
										$('#attach').val('');
										$('#btnUpload').attr('src', '/images/photo.png').css('width', '80px').css('height', '80px').removeClass('avatar');
									}
								}, 'json');							
							}
						});
					});*/
				}
			}
});
}

/**
 * Google地图是否初始化过
 */
var Better_GMapInited = false;

/**
 * Geocoder
 */
var Better_Geocoder = null;

/**
 * 是否有ajax请求正在执行
 * 
 * @TODO 同一时刻只能有一个ajax请求在执行
 */
var Better_ajaxInProcess = false;
var Better_ajaxTimer = null;

/**
 * 用户列表配置对象
 */
var Better_UserList_Config = {
		withAvatar: true,
		withFuncLinks: true
}

/**
 * 围脖列表配置对象
 */
var Better_MessageList_Config = {
		withAvatar: true,
		withFuncLinks: true
}

/**
 * 所有分页对象实例
 */
var Better_PagerList = new Array();

/**
 * 显示附件图片出错时的默认图片
 */
var Better_ImgOnError = BASE_URL+'/images/404.png';

/**
 * 显示用户头像出错时的默认头像
 */
var Better_AvatarOnError = BASE_URL+'/images/noavatar.gif';

/**
 * 默认坐标值
 */
var Better_Default_Lat = 39.916977916932;
var Better_Default_Lon = 116.39722818546;

/**
 * 浏览器时区
 */
var Better_Browser_Timezone = (new Date().getTimezoneOffset()/60)*(-1);
var Better_Brwoser_Timezone_Offset = Better_Browser_Timezone*3600;

/**
 * 吼吼是否要带poii
 */
var Better_Shout_Without_Poi = false;

/**
 * 发表吼吼后的回调函数
 * 
 */
var Better_Callbacks_After_Shout = [];

/**
 * 吼吼发布成功以后的强制提示
 */
var Better_Shout_Result_Title = '';

/**
 * Better用户的js对象
 * 
 * @param uid
 * @param username
 * @param realname
 * @param email
 * @return
 */
function BetterUser(uid, username, nickname, realname, email)
{
	this.uid = uid ? uid : 0;
	this.username = username ? username : '';
	this.realname = realname ? realname : '';
	this.nickname = nickname ? nickname : '';
	this.email = email ? email : '';
	this.cell_no = '';	
	this.friends = [];
	this.geo_followings = [];
	this.followings = [];
	this.followers = [];
	this.blocks = [];
	this.blockedby = [];
	this.fav_bids = [];
	this.lbs_report = 0;
	this.address = '';
	this.x = 0;
	this.y = 0;
	this.lon = 0;
	this.lat = 0;
	this.province = '';
	this.city = '';
	this.avatar = '';
	this.avatar_normal = '';
	this.avatar_small = '';
	this.avatar_tiny = '';
	this.location_tips = '';
	this.priv_blog = 0;
	this.live_province = '';
	this.live_city = '';
	this.state = 'enabled';
	this.last_checkin_poi = 0;
	this.poi_favorites = [];
	this.priv = 'public';
	this.gender = '';
	this.last_checkin_poi_name = '';
	this.timezone = 8;
}

function Better_ResetMapCenter()
{
	Gmap.removeOverlay(marker);
	marker = new GMarker(Gmap.getCenter(), {draggable: true});

	Gmap.addOverlay(marker);			
	
	latlon = marker.getLatLng();
	setlonlat(latlon.lng(),latlon.lat());
}


function Better_ResetGmapCenter()
{
}

if (Better_Need_Checkin_Js) {
	Better_ResetGmapCenter.prototype = new GControl();
	Better_ResetGmapCenter.prototype.initialize = function(map)
	{
		var container = document.createElement('div');
		var resetCenterDiv = document.createElement('div');
		
		this.setButtonStyle_(resetCenterDiv);
		container.appendChild(resetCenterDiv);
		resetCenterDiv.appendChild(document.createTextNode(betterLang.global.just_here));
		GEvent.addDomListener(resetCenterDiv, 'click', function(){
			Better_ResetMapCenter();
		});
		
		map.getContainer().appendChild(container);
		
		return container;
	}
	
	Better_ResetGmapCenter.prototype.getDefaultPosition = function()
	{
		return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7,47));
	}
	
	Better_ResetGmapCenter.prototype.setButtonStyle_ = function(button)
	{
	    button.style.textDecoration = "underline";
	    button.style.color = "#0000cc";
	    button.style.backgroundColor = "white";
	    button.style.font = "small Arial";
	    button.style.border = "1px solid black";
	    button.style.padding = "2px";
	    button.style.marginBottom = "3px";
	    button.style.textAlign = "center";
	    button.style.width = "6em";
	    button.style.cursor = "pointer";	
	}
}

/**
 * 弹出lbs插件安装提示
 * 
 * @return
 */
function Better_Lbs_Alert()
{
	ignoreLbsAlert = $.cookie('ignore_lbs_alert');
	
	if (!ignoreLbsAlert) {
		Better_Lbs_Alert_Poped = true;
		
		Better_Notify_clear();
		Better_Confirm({
			msg: betterLang.lbs_promotion,
			height: 180,
			onConfirm: function(){
				window.open('http://lbs.org.cn/api/PepLocation(10051401).exe');
				
				Better_Confirm_clear();
				urlParams = window.location.toString().split('/');
				//用于区分是不是poi详情页面
				if (urlParams[urlParams.length-1] != 'pois') {
					Better_Notify_loading({
						msg_title: betterLang.geoing
					});
					Better_Checkin_GetLLByIp();
				}
			},
			onCancel: function(){
				$.cookie('ignore_lbs_alert', 1);
				
				urlParams = window.location.toString().split('/');
				//用于区分是不是poi详情页面
				if (urlParams[urlParams.length-1] != 'pois') {
					Better_Notify_loading({
						msg_title: betterLang.geoing
					});
					
					Better_Checkin_GetLLByIp();
				}
				Better_Confirm_clear();
			},
			confirmText: betterLang.lbs_confirm,
			cancelText: betterLang.lbs_cancel
		});
	}
}

/**
 * 检测一下返回的json数据
 * 
 * @param json
 * @return
 */
function Better_AjaxCheck(json, exceptionCallback)
{
	success = true;
	
	if (typeof(json.uid)!='undefined' && json.uid<=0) {
		url = BASE_URL+'/login';
		
		top.window.location = url;
		return false;
	}
	
	if (typeof(json.exception)!='undefined' && json.exception.length>0) {
		if (Better_InDebug==true) {
			Better_Notify({
				msg_title: 'Javascript Exception',
				msg: json.exception
			});
		}
		if ($.isFunction(exceptionCallback)) {
			exceptionCallback();
		}
		success = false;
	}
	
	if (success==true && typeof(json.bar)!='undefined') {
		/*
		$('#half_right').html(json.bar);
		$('#half_right img.user_avatar').error(function(){
			$(this).attr('src', Better_AvatarOnError);
		});*/
		
		Better_Callback_After_Ajax();
	}
	
	return success;
}

/**
 * 调试消息
 * 
 * @param debugMsg
 * @return
 */
function Better_DebugMsg(debugMsg)
{
	if (Better_InDebug==true) {
		console.log(debugMsg);
		Better_Notify({
			msg_title: 'Debug Message',
			msg: debugMsg
		});
	}
}

/**
 * 移除数组元素
 * 
 * @param arr 
 * @param val
 * @return 
 */
function Better_Array_Remove(arr, val)
{
	new_array = [];
	
	if ($.isArray(arr)) {
		for(i=0,j=0;i<arr.length;i++) {
			if (arr[i]!=val) {
				new_array[j++] = arr[i];
			}
		}
	} else {
		new_array = arr;
	}

	return new_array;
}

/**
 * 往数组中增加一个元素
 * 
 * @param arr
 * @param val
 * @return
 */
function Better_Array_Push(arr, val)
{
	new_array = [];

	if ($.isArray(arr) && $.inArray(val, arr)<0) {
		arr.push(val);
		new_array = arr;
	}
	
	return new_array;
}

/**
 * 初始化Google地图
 * 
 * @return
 */
function Better_InitGMap(callback, force)
{
	force = force ? force : false;
	callback = callback ? callback : 'initialize';
	if (Better_GMapInited==false || force==true) {
		if ($.isFunction(callback)) {
			callback(force);
		} else if ($.isFunction(eval(callback))) {
			eval(callback+'('+force+')');
		}
		Better_GMapInited = true;
	}
}

/**
 * 卸载Google地图
 * 
 * @return
 */
function Better_UnloadGMap()
{
	if (Better_GMapInited==true) {
		if ($.browser.msie && $.browser.version<7) { 
			
		} else {
			GUnload();
		}
		Better_GMapInited = false;
		Better_CheckinJs_Inited = false;
		Better_Geocode_Got = false
	}
}

/**
 * 如果ajax取回的结果集行数为0，则显示一个提示
 * 
 * @param id
 * @param msg
 * @return
 */
function Better_EmptyResults(id, msg)
{
	html = '<tr><td colspan="8"><div class="notice emptyResult">'+msg+'</div></td></tr>';

	$('#tbl_'+id).html(html);
	$('#pager_'+id).empty().hide();
}

/**
 * 显示一个Confirm对话框
 * 
 * @param options
 * @return
 */
function Better_Confirm(options)
{
	msg_title = typeof(options.msg_title)!='undefined' ? options.msg_title : betterLang.global.action.confirm.title;
	height = typeof(options.height)!='undefined' ? options.height : 140;
	onCancel = $.isFunction(options.onCancel) ? options.onCancel : function(){ $(this).dialog('close');};
	_onConfirm = $.isFunction(options.onConfirm) ? options.onConfirm: function(){};
	onConfirm = function(){
		Better_Notify_clear();
		_onConfirm();
	};
	confirmText = typeof(options.confirmText)!='undefined' ? options.confirmText : betterLang.global.action.confirm.btn_confirm;
	cancelText = typeof(options.cancelText)!='undefined' ? options.cancelText : betterLang.global.action.confirm.btn_cancel;
	
	/*
	if (confirm(options.msg)) {
		onConfirm();
	} else {
		onCancel();
	}
	*/
	
	$bcb = $('#betterConfirmBox');
	
	$bcb.dialog('destroy').attr('title', msg_title).html(options.msg);

	btns = {};
	eval('btns.'+confirmText+'=onConfirm');
	eval('btns.'+cancelText+'=onCancel');
	
	if ($.browser.msie && $.browser.version<7) {
		dialogParams = {
				bgiframee: true,				
				height: height,
				resizable: false,
				zIndex: 4999,
				buttons: btns	
		}
	} else {
		dialogParams = {
				bgiframee: true,
				modal: true,
				height: height,
				resizable: false,
				zIndex: 4999,
				buttons: btns
			}		
	}
	$bcb.dialog(dialogParams);
	
}

/**
 * 清除Notify的dialog
 * 
 * @return
 */
function Better_Notify_clear()
{	
	$('#betterMessageBox').dialog('close').dialog('destroy');
}


/**
 * Pop up the dialog to select one or more friends
 * When we have got the friends' list, we need to record them in a hidden input so that we can use them in selecting all friends.
 */
function Popup_Invite_friends()
{
	Better_Confirm({
		msg:'想要邀请你的好友一起去吗？',
		onConfirm: function(){
			Better_Confirm_clear();
			//取得好友列表第一页(16人)
			Better_Notify_loading();
			$.get('/ajax/user/friends',{nickname:Better_Nickname,page:1,pagesize:pcount*16},function(luJson){
				Better_Notify_clear();
				liHtml = '';
				//初始化页面
				$('#invitation-count').html("0");
				$('#invitation-names').html("");
				$('#currentpage').html("1");
				var alluids = ',';
				$('#alluids').val("");
				for(i in luJson.rows) {
					if(i>15){break;}
					var thisRow = luJson.rows[i];
					liHtml += '<li id="row_'+thisRow.uid+'" class="invitation-item" uid="'+thisRow.uid+'" nickname="'+thisRow.nickname+'" onClick="selectUserHandler('+thisRow.uid+',\''+thisRow.nickname+'\')">'+						
									'<a href="javascript:void(0);" title="'+thisRow.nickname+'">'+
										'<span class="picbox">'+
										'<img class="pic" src="'+thisRow.avatar_small+'"></img>'+
										'</span>'+
										'<h4>'+thisRow.nickname+'</h4>'+
									'</a>'+
									'</li>';				
					alluids = alluids+thisRow.uid+',';
				}
				cacheLiHtml="";
				for(i in  luJson.rows){
					var thisRow = luJson.rows[i];
					cacheLiHtml += '<li id="row_'+thisRow.uid+'" class="invitation-item" uid="'+thisRow.uid+'" nickname="'+thisRow.nickname+'" onClick="selectUserHandler('+thisRow.uid+',\''+thisRow.nickname+'\')">'+						
									'<a href="javascript:void(0);" title="'+thisRow.nickname+'">'+
										'<span class="picbox">'+
										'<img class="pic" src="'+thisRow.avatar_small+'"></img>'+
										'</span>'+
										'<h4>'+thisRow.nickname+'</h4>'+
									'</a>'+
									'</li>';
				}
				var currentpage = 1;
				var pagecount = luJson.pages;
				pagecount = pagecount==0?pagecount+1:pagecount;
				cache = $(document.createElement('div')).addClass('hide').attr('id','cache_page_1_');
				cache.append(cacheLiHtml);
				input = $(document.createElement('input')).attr('id','cache_pagecount_').attr('type','hidden').val(pagecount);
				$('#cachecontainer').append(cache).append(input);
				$('#ulcontainer').empty();
				$('#ulcontainer').append(liHtml);
				$('#fuids').val(',');
				$('#alluids').val(alluids);
				//渲染页码
				
				var fuids = ',';
				if(pagecount==1){
					$('#pager-container').hide();
				}else{
					$('#pager-container').show();
					$('#pagecount').html(pagecount);
					if(currentpage==1){
						$('#lastpage').hide();
						$('#nextpage').show();
					}else if(currentpage==pagecount){
						$('#nextpage').hide();
						$('#lastpage').show();
					}else{
						$('#nextpage').show();
						$('#lastpage').show();
					}
				}
				//添加搜索的支持
				
				$('#keyword-input').focus(function(){
					if($(this).val()=='输入好友姓名快速查找'){
						$(this).val('');
						$(this).css('color', '#333');			
					} 
				}).blur(function(){
					if($(this).val()==''){
						$(this).css('color', '#999');
						$(this).val('输入好友姓名快速查找');			
					} 
				}).keyup(function(e){		
					isloading=false;
					window.setTimeout(function(){						
//						$('#keyword-input').attr('disabled','true');
						searchwords = $.trim($('#keyword-input').val());
						if(searchwords=="输入好友姓名快速查找"){
							searchwords="";
						}
						currentpage=1;
						fuids =  $('#fuids').val();//备份已经选择的用户
						if(!isloading){
							isloading=true;
							if(searchwords!="" && textsearch.indexOf(",1_"+searchwords+",")==-1){
								textsearch = textsearch+"1_"+searchwords+","
								$.get('/ajax/user/friends',{nickname:Better_Nickname,page:1,pagesize:pcount*16,keywords:searchwords},function(reJson){
								liHtml = '';
								pagecount = reJson.pages;
								pagecount = pagecount==0?pagecount+1:pagecount;
								$('#pagecount').html(pagecount);
								alluids = ',';
								$('#alluids').val(alluids);
								for(i in reJson.rows) {
									if(i>15){break;}							
									var thisRow = reJson.rows[i];
									var needler = ','+thisRow.uid+',';//判断当前好友是否已经被选择
									classname="unselected-item";
									if(fuids.indexOf(needler)> -1){
										classname = "selected-item";
									}
									liHtml += '<li id="row_'+thisRow.uid+'" class="invitation-item '+classname+'" uid="'+thisRow.uid+'" nickname="'+thisRow.nickname+'" onClick="selectUserHandler('+thisRow.uid+',\''+thisRow.nickname+'\')">'+						
													'<a href="javascript:void(0);" >'+
														'<span class="picbox">'+
														'<img class="pic" src="'+thisRow.avatar_small+'"></img>'+
														'</span>'+
														'<h4>'+thisRow.nickname+'</h4>'+
													'</a>';	
									alluids = alluids+thisRow.uid+',';
								}
								cacheLiHtml = '';
								for(i in reJson.rows) {					
									var thisRow = reJson.rows[i];
									var needler = ','+thisRow.uid+',';//判断当前好友是否已经被选择
									classname="unselected-item";
									if(fuids.indexOf(needler)> -1){
										classname = "selected-item";
									}
									cacheLiHtml += '<li id="row_'+thisRow.uid+'" class="invitation-item '+classname+'" uid="'+thisRow.uid+'" nickname="'+thisRow.nickname+'" onClick="selectUserHandler('+thisRow.uid+',\''+thisRow.nickname+'\')">'+						
													'<a href="javascript:void(0);" >'+
														'<span class="picbox">'+
														'<img class="pic" src="'+thisRow.avatar_small+'"></img>'+
														'</span>'+
														'<h4>'+thisRow.nickname+'</h4>'+
													'</a>';	
									alluids = alluids+thisRow.uid+',';
								}
								 cache = $(document.createElement('div')).addClass('hide').attr('id','cache_page_1_'+searchwords);
								 input = $(document.createElement('input')).attr('id','cache_pagecount_'+searchwords).attr('type','hidden').val(pagecount);
								 cache.append(cacheLiHtml);
								 
								 $('#cachecontainer').append(cache).append(input);
								 
								$('#ulcontainer').empty();
								$('#ulcontainer').append(liHtml);
								$('#alluids').val(alluids);
	//							$('#keyword-input').removeAttr('disabled');
								if(pagecount==1){
									$('#pager-container').hide();
								}else{
									$('#pager-container').show();
									$('#pagecount').html(pagecount);
									if(currentpage==1){
										$('#lastpage').hide();
										$('#nextpage').show();
									}else if(currentpage==pagecount){
										$('#nextpage').hide();
										$('#lastpage').show();
									}else{
										$('#nextpage').show();
										$('#lastpage').show();
									}
								}
								$('#currentpage').html(currentpage);
							},'json');	
							}else{
								if(searchwords ==""){
								}
								liHtml = '';
								alluids = ',';
								$('#alluids').val(alluids);
								cache_key = '#cache_page_1_'+searchwords;
								start = (currentpage-1)*16+1;
								end = start+16;
								for(i=start;i<end;i++){
									litem=$(cache_key+" li:nth-child("+i+")");
									if(typeof(litem.attr('uid')) == 'undefined'){
										break;
									}
									var needler = ','+litem.attr('uid')+',';//判断当前好友是否已经被选择
									classname="unselected-item";
									if(fuids.indexOf(needler)> -1){
										classname = "selected-item";
									}
									alluids = alluids+litem.attr('uid')+',';
									liHtml +='<li id="row_'+litem.attr('uid')+'" class="invitation-item '+classname+'" uid="'+litem.attr('uid')+'" onClick="selectUserHandler('+litem.attr('uid')+',\''+litem.attr('nickname')+'\')">'+litem.html()+"</li>";
								}
								$('#ulcontainer').empty();
								$('#ulcontainer').append(liHtml);
								$('#alluids').val(alluids);	
//								$('#pager-container').show();
								pagecount = $("#cache_pagecount_"+searchwords).val();
								if(pagecount==1){
									$('#pager-container').hide();
								}else{
									$('#pager-container').show();
									$('#pagecount').html(pagecount);
									if(currentpage==1){
										$('#lastpage').hide();
										$('#nextpage').show();
									}else if(currentpage==pagecount){
										$('#nextpage').hide();
										$('#lastpage').show();
									}else{
										$('#nextpage').show();
										$('#lastpage').show();
									}
								}
								$('#currentpage').html(currentpage);
								
							}
						}						
					},1000);
				});
				
				
				$('#lastpage').click(function(){
					fuids =  $('#fuids').val();//备份已经选择的用户
					searchwords = $.trim($('#keyword-input').val());
					if(searchwords=="输入好友姓名快速查找"){
						searchwords="";
					}
					$('#pager-container').hide();
					currentpage -= 1;
					if(pagecount==1){
						$('#pager-container').hide();
					}else{						
						$('#pagecount').html(pagecount);
						if(currentpage==1 ||currentpage<1){
							$('#lastpage').hide();
							$('#nextpage').show();
							currentpage=1;
						}else if(currentpage==pagecount){
							$('#nextpage').hide();
							$('#lastpage').show();
						}else{
							$('#nextpage').show();
							$('#lastpage').show();
						}
					}
					$('#currentpage').html(currentpage);
//					$.get('/ajax/user/friends',{nickname:Better_Nickname,page:currentpage,pagesize:16,keywords:searchwords},function(luJson){
//						liHtml = '';
//						alluids = ',';
//						$('#alluids').val(alluids);
//						for(i in luJson.rows) {
//							if(i>15){break;}
//							
//							var thisRow = luJson.rows[i];
//							var needler = ','+thisRow.uid+',';//判断当前好友是否已经被选择
//							classname="unselected-item";
//							if(fuids.indexOf(needler)> -1){
//								classname = "selected-item";
//							}
//							liHtml += '<li id="row_'+thisRow.uid+'" class="invitation-item '+classname+'" uid="'+thisRow.uid+'" onClick="selectUserHandler('+thisRow.uid+',\''+thisRow.nickname+'\')">'+						
//											'<a href="javascript:void(0);" >'+
//												'<span class="picbox">'+
//												'<span class="pic" style="background-image:url(\''+thisRow.avatar_small+'\');"></span>'+
//												'</span>'+
//												'<h4>'+thisRow.nickname+'</h4>'+
//											'</a>';			
//							alluids = alluids+thisRow.uid+',';
//						}
//						$('#ulcontainer').empty();
//						$('#ulcontainer').append(liHtml);
//						$('#alluids').val(alluids);
//						$('#pager-container').show();
//					},'json');	
					
					liHtml = '';
					alluids = ',';
					$('#alluids').val(alluids);					
					cpage = Math.ceil(currentpage/pcount);
					start =(currentpage - (cpage-1)*pcount-1)*16+1;
					end = start+16;
					cache_key = '#cache_page_'+cpage+"_"+searchwords;	
					for(i=start;i<end;i++){
						litem=$(cache_key+" li:nth-child("+i+")");
						if(typeof(litem.attr('uid')) == 'undefined'){
							break;
						}
						var needler = ','+litem.attr('uid')+',';//判断当前好友是否已经被选择
						classname="unselected-item";
						if(fuids.indexOf(needler)> -1){
							classname = "selected-item";
						}
						alluids = alluids+litem.attr('uid')+',';
						liHtml +='<li id="row_'+litem.attr('uid')+'" class="invitation-item '+classname+'" uid="'+litem.attr('uid')+'" onClick="selectUserHandler('+litem.attr('uid')+',\''+litem.attr('nickname')+'\')">'+litem.html()+"</li>";
					}
					$('#ulcontainer').empty();
					$('#ulcontainer').append(liHtml);
					$('#alluids').val(alluids);	
					$('#pager-container').show();
				});
				$('#nextpage').click(function(){					
					 fuids =  $('#fuids').val();//备份已经选择的用户
					 searchwords = $.trim($('#keyword-input').val());
					 if(searchwords=="输入好友姓名快速查找"){
							searchwords="";
					}
					$('#pager-container').hide();
					currentpage += 1;
					if(currentpage==1){
						$('#lastpage').hide();
						$('#nextpage').show();
					}else if(currentpage==pagecount || currentpage>pagecount){
						$('#nextpage').hide();
						$('#lastpage').show();
						currentpage=pagecount;
					}else{
						$('#nextpage').show();
						$('#lastpage').show();
					}
					$('#currentpage').html(currentpage);
					cpage = Math.ceil(currentpage/pcount);
					needersearch = ","+cpage+"_"+searchwords+",";
					if( textsearch.indexOf(needersearch)==-1&&((currentpage-1)/pcount) ==  Math.floor(((currentpage-1)/pcount))){	
						textsearch = textsearch+cpage+"_"+searchwords+",";
						Better_Notify_loading();
						$.get('/ajax/user/friends',{nickname:Better_Nickname,page:cpage,pagesize:48,keywords:searchwords},function(luJson){
							Better_Notify_clear();
							liHtml = '';
							alluids = ',';
							$('#alluids').val(alluids);
							for(i in luJson.rows) {
								if(i>15){break;}							
								var thisRow = luJson.rows[i];
								var needler = ','+thisRow.uid+',';//判断当前好友是否已经被选择
								classname="unselected-item";
								if(fuids.indexOf(needler)> -1){
									classname = "selected-item";
								}
								liHtml += '<li id="row_'+thisRow.uid+'" class="invitation-item '+classname+'" uid="'+thisRow.uid+'" nickname="'+thisRow.nickname+'" onClick="selectUserHandler('+thisRow.uid+',\''+thisRow.nickname+'\')">'+						
												'<a href="javascript:void(0);" >'+
													'<span class="picbox">'+
													'<img class="pic" src="'+thisRow.avatar_small+'"></img>'+
													'</span>'+
													'<h4>'+thisRow.nickname+'</h4>'+
												'</a>';	
								alluids = alluids+thisRow.uid+',';
							}
							
							cacheLiHtml="";
							for(i in  luJson.rows){
								var thisRow = luJson.rows[i];
								cacheLiHtml += '<li id="row_'+thisRow.uid+'" class="invitation-item" uid="'+thisRow.uid+'" nickname="'+thisRow.nickname+'" onClick="selectUserHandler('+thisRow.uid+',\''+thisRow.nickname+'\')">'+						
												'<a href="javascript:void(0);" title="'+thisRow.nickname+'">'+
													'<span class="picbox">'+
													'<img class="pic" src="'+thisRow.avatar_small+'"></img>'+
													'</span>'+
													'<h4>'+thisRow.nickname+'</h4>'+
												'</a>'+
												'</li>';
							}
							cache = $(document.createElement('div')).addClass('hide').attr('id','cache_page_'+cpage+"_"+searchwords);
							cache.append(cacheLiHtml);
							//input = $(document.createElement('input')).attr('id','cache_pagecount_').attr('type','hidden').val(pagecount);
							$('#cachecontainer').append(cache);
							$('#ulcontainer').empty();
							$('#ulcontainer').append(liHtml);
							$('#alluids').val(alluids);	
							$('#pager-container').show();
						},'json');
					}else{	
						liHtml = '';
						alluids = ',';
						$('#alluids').val(alluids);		
						start =(currentpage - (cpage-1)*pcount-1)*16+1;
						end = start+16;
						cache_key = '#cache_page_'+cpage+"_"+searchwords;
						for(i=start;i<end;i++){
							litem=$(cache_key+" li:nth-child("+i+")");
							if(typeof(litem.attr('uid')) == 'undefined'){
								break;
							}
							var needler = ','+litem.attr('uid')+',';//判断当前好友是否已经被选择
							classname="unselected-item";
							if(fuids.indexOf(needler)> -1){
								classname = "selected-item";
							}
							alluids = alluids+litem.attr('uid')+',';
							liHtml +='<li id="row_'+litem.attr('uid')+'" class="invitation-item '+classname+'" uid="'+litem.attr('uid')+'" onClick="selectUserHandler('+litem.attr('uid')+',\''+litem.attr('nickname')+'\')">'+litem.html()+"</li>";
						}
						$('#ulcontainer').empty();
						$('#ulcontainer').append(liHtml);
						$('#alluids').val(alluids);	
						$('#pager-container').show();
					}
				});
				//准备好邀请POI的数据
				$('#poiid_invitation_todo').val(Better_Poi_Id);
				$('#poiname_invitation_todo').val(Better_Poi_Detail.name);
				$('#title_poi_name').html(Better_Poi_Detail.name);
				
				$('#invitefriends_btn').trigger('click');					
			},'json');				
		}
	});
}
/**
 * 显示一个基于jQuery的MessageBox
 * 
 * @param msg
 * @return
 */
function Better_Notify(options)
{
	Better_Notify_clear();
	Better_Confirm_clear();
	
	if (typeof(options)=='string') {
		options = {
			msg: options
		};
	}
	msg = options.msg;
	msg_title = typeof(options.msg_title)!='undefined' ? options.msg_title : betterLang.global.action.notify.title;
	//提示消息假如超过50个字，显示加长
	defaultheight =150 + Math.floor(msg.length/50)*40;

	height = typeof(options.height)!='undefined' ? options.height : defaultheight;

	close_timer = typeof(options.close_timer)!='undefined' ? parseFloat(options.close_timer) : 0;
	msg_title = msg_title ? msg_title : betterLang.global.action.notify.title;

	btns = {};
	eval('btns.'+betterLang.global.action.notify.btn_close+'=function(){$(this).dialog("close");}')
	buttons = (typeof(options.btns)!='undefined' && options.btns) ?  options.btns : btns;

	closeCallback = (typeof(options.closeCallback)!='undefined' && $.isFunction(options.closeCallback)) ? options.closeCallback : function(){};

	if ($.browser.msie && $.browser.version<7) {
		dialogParams = {
			height: height,
			closeOnEscape: true,
			resizable: false,
			zIndex: 3999,
			buttons: buttons,
			close: closeCallback	
		}
	} else {
		dialogParams = {
				bgiframe: true,
				modal: true,
				height: height,
				closeOnEscape: true,
				resizable: false,
				zIndex: 3999,
				buttons: buttons,
				close: closeCallback	
			}		
	}
	
	$('#betterMessageBox').dialog('destroy').attr('title', msg_title).html(msg).dialog(dialogParams);
	
	if (close_timer>0) {
		setTimeout(Better_Notify_clear, close_timer*1000);
	}

}

/**
 * 显示一个正在加载的提示窗口
 * 
 * @param options
 * @return
 */
function Better_Notify_loading(options)
{
	options = typeof(options)!='undefined' ? options: {};
	msg_title = typeof(options.msg_title)!='undefined' ? options.msg_title : betterLang.global.please_wait;
	msg = typeof(options.msg)!='undefined' ? options.msg : '<img src="images/loading.gif" alt="" width="180" />';
	closeCallback = $.isFunction(options.closeCallback) ? options.closeCallback : function(){};

	Better_Notify({
		msg: msg,
		msg_title: msg_title,
		btns: null,
		closeCallback: closeCallback
	});
}

/**
 * 清除Confirm的dialog
 * 
 * @return
 */
function Better_Confirm_clear()
{
	$('#betterConfirmBox').dialog('close');
}

/**
 * utf8_encode实现
 * 
 * @param string
 * @return
 */
function Better_utf8_encode(string){
	string=(string+'').replace(/\r\n/g,"\n").replace(/\r/g,"\n");
	var utftext="";var start,end;var stringl=0;start=end=0;stringl=string.length;for(var n=0;n<stringl;n++){var c1=string.charCodeAt(n);var enc=null;if(c1<128){end++;}else if((c1>127)&&(c1<2048)){enc=String.fromCharCode((c1>>6)|192)+String.fromCharCode((c1&63)|128);}else{enc=String.fromCharCode((c1>>12)|224)+String.fromCharCode(((c1>>6)&63)|128)+String.fromCharCode((c1&63)|128);}
if(enc!=null){if(end>start){utftext+=string.substring(start,end);}
utftext+=enc;start=end=n+1;}}
if(end>start){utftext+=string.substring(start,string.length);}
	return utftext;
}

/**
 * md5的js实现
 * 
 * @param str
 * @return
 */
function Better_md5(str)
{
	var xl;

	var rotateLeft = function (lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	};

	var addUnsigned = function (lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
		    return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
		    if (lResult & 0x40000000) {
		        return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
		    } else {
		        return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
		    }
		} else {
		    return (lResult ^ lX8 ^ lY8);
		}
	};

	var _F = function (x,y,z) { return (x & y) | ((~x) & z); };
	var _G = function (x,y,z) { return (x & z) | (y & (~z)); };
	var _H = function (x,y,z) { return (x ^ y ^ z); };
	var _I = function (x,y,z) { return (y ^ (x | (~z))); };

	var _FF = function (a,b,c,d,x,s,ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	};

	var _GG = function (a,b,c,d,x,s,ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	};

	var _HH = function (a,b,c,d,x,s,ac) {
	    a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
	    return addUnsigned(rotateLeft(a, s), b);
	};

	var _II = function (a,b,c,d,x,s,ac) {
		a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
		return addUnsigned(rotateLeft(a, s), b);
	};

	var convertToWordArray = function (str) {
		var lWordCount;
		var lMessageLength = str.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=new Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
		    lWordCount = (lByteCount-(lByteCount % 4))/4;
		    lBytePosition = (lByteCount % 4)*8;
		    lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount)<<lBytePosition));
		    lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
		    };
	
		var wordToHex = function (lValue) {
		var wordToHexValue="",wordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
		    lByte = (lValue>>>(lCount*8)) & 255;
		    wordToHexValue_temp = "0" + lByte.toString(16);
		    wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length-2,2);
		}
		return wordToHexValue;
	};

	var x=[],
	k,AA,BB,CC,DD,a,b,c,d,
	S11=7, S12=12, S13=17, S14=22,
	S21=5, S22=9 , S23=14, S24=20,
	S31=4, S32=11, S33=16, S34=23,
	S41=6, S42=10, S43=15, S44=21;

	str = Better_utf8_encode(str);
	x = convertToWordArray(str);
	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
	    
	xl = x.length;
	for (k=0;k<xl;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=_FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=_FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=_FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=_FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=_FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=_FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=_FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=_FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=_FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=_FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=_FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=_FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=_FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=_FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=_FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=_FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=_GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=_GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=_GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=_GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=_GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=_GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=_GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=_GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=_GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=_GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=_GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=_GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=_GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=_GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=_GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=_GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=_HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=_HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=_HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=_HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=_HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=_HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=_HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=_HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=_HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=_HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=_HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=_HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=_HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=_HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=_HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=_HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=_II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=_II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=_II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=_II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=_II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=_II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=_II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=_II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=_II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=_II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=_II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=_II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=_II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=_II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=_II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=_II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=addUnsigned(a,AA);
		b=addUnsigned(b,BB);
		c=addUnsigned(c,CC);
		d=addUnsigned(d,DD);
	}

	var temp = wordToHex(a)+wordToHex(b)+wordToHex(c)+wordToHex(d);

	return temp.toLowerCase();

}

/**
 * 触发一个按钮click事件
 * 
 * @param e
 * @param btn
 * @return
 */
function Better_TriggerClick(e, btn)
{
	 if (e.which==13) {
		 btn.trigger('click');
		 return false;
	 }
	 return true;	
}

/**
 * 显示一个Checkin的文字信息
 * 
 * @param options
 * @return
 */
function Better_parseCheckinMsg(options)
{
	emptyMsg = typeof(options.emptyMsg)=='undefined' ? '' : options.emptyMsg;

	time = options.time;
	tips = options.tips;
	poi = typeof(options.poi)!='undefined' ? options.poi : {poi_id: 0};
	
	result = betterLang.noping.better.parsecheckin.toString().replace('{TIME}',Better_compareTime(time)).replace('{POI}','<a href="/poi/'+poi.poi_id+' class="place">'+tips+'</a>');	
	return result;
}


/**
 * 显示一个位置提示
 * 
 * @param options
 * @return
 */
function Better_locationTips(options)
{
	result  = '';
	emptyMsg = typeof(options.emptyMsg)=='undefined' ? '' : options.emptyMsg;

	lon = options.lon;
	lat = options.lat;
	time = typeof(options.dateline)!='undefined' ? options.dateline : (typeof(options.time)!='undefined' ? options.time : '');
	isUser = typeof(options.isUser)!='undefined' ? options.isUser : false;

	result = typeof(options.tips)!='undefined' ? options.tips : '';
	tips_poi = typeof(options.poi)!='undefined' ? (typeof(options.poi)!='undefined' ? options.poi : {}) : {};
	
	if (typeof(tips_poi.poi_id)!='undefined' && tips_poi.poi_id!=0 && result!='') {
		result =  betterLang.noping.better.locationtips.toString().replace('{POI}','<a href="/poi/'+tips_poi.poi_id+'" class="place">'+ result +'</a> - ');
	} else if (isUser && typeof(tips_poi.poi_id)!='undefined' && tips_poi.poi_id!=0 && result=='') {
		result =  betterLang.noping.better.locationtips.toString().replace('{POI}','<a href="/poi/'+tips_poi.poi_id+'" class="place">'+tips_poi.city+' '+ tips_poi.name +'</a> - ');
	} else {
		result = betterLang.noping.better.locationtips.toString().replace('@{POI}','');
	}

	if (time>0) {
		result = result.replace('{TIME}',Better_compareTime(time));
	} else {
		result = result.replace('{TIME}','');
	}

	if (typeof(options.key)!='undefined') {
		$('#'+options.key).html(result);
	}

	return result;
}


/**
 * 显示一个位置提示
 * 
 * @param options
 * @return
 */
function Better_locationTodo(options)
{
	result  = '';
//	emptyMsg = typeof(options.emptyMsg)=='undefined' ? '' : options.emptyMsg;
//
//	lon = options.lon;
//	lat = options.lat;
	time = typeof(options.dateline)!='undefined' ? options.dateline : (typeof(options.time)!='undefined' ? options.time : '');
//	isUser = typeof(options.isUser)!='undefined' ? options.isUser : false;
//
//	result = typeof(options.tips)!='undefined' ? options.tips : '';
//	tips_poi = typeof(options.poi)!='undefined' ? (typeof(options.poi)!='undefined' ? options.poi : {}) : {};
//	todo_content =   typeof(options.content)!='undefined' ? options.content : '';
	
//	if (typeof(tips_poi.poi_id)!='undefined' && tips_poi.poi_id!=0 && result!='') {
//		if(todo_content !=''){
//			result =  betterLang.noping.better.locationtips.toString().replace('{POI}','<a href="/poi/'+tips_poi.poi_id+'" class="place">'+ result +'</a>:&nbsp;<span  class="message_row" style="color:#000">'+todo_content+'</span>');
//		}else{
//			result =  betterLang.noping.better.locationtips.toString().replace('{POI}','<a href="/poi/'+tips_poi.poi_id+'" class="place">'+ result +'</a>');
//		}
//	} else if (isUser && typeof(tips_poi.poi_id)!='undefined' && tips_poi.poi_id!=0 && result=='') {
//		if(todo_content !=''){
//			result =  betterLang.noping.better.locationtips.toString().replace('{POI}','<a href="/poi/'+tips_poi.poi_id+'" class="place">'+tips_poi.city+' '+ tips_poi.name +'</a>:&nbsp;<span  class="message_row">'+todo_content+'</span>');
//		}else{
//			result =  betterLang.noping.better.locationtips.toString().replace('{POI}','<a href="/poi/'+tips_poi.poi_id+'" class="place">'+tips_poi.city+' '+ tips_poi.name +'</a>');
//		}
//	} else {
//		result = betterLang.noping.better.locationtips.toString().replace('@{POI}','');
//	}
	result = betterLang.noping.better.locationtips.toString().replace('@{POI}','');
	if (time>0) {
		result = result.replace('{TIME}',"<span style='font-size:12px'>"+Better_compareTime(time)+"</span>");
	} else {
		result = result.replace('{TIME}','');
	}

	if (typeof(options.key)!='undefined') {
		$('#'+options.key).html(result);
	}

	return result;
}


/**
 * 解析成就信息
 * 
 * @param data
 * @return
 */
function Better_parseAchievement(data, addMsg)
{
	str = '';
	addMsg = addMsg ? addMsg : betterLang.global.this_act;
	addMsg = betterLang.sketch.delta.action.toString().replace('{ACTION}',addMsg);
	if (typeof(data)=='object') {
		str = data.achievement;
	} else {
		str = data;
	}
	
	if (!data.checkin_exception && $.trim(str)!='') {
		str = addMsg+''+str;
	}
	
	return str;
}


/**
 * 激活吼吼浮动窗口
 * 
 * @return
 */
function Better_EnableShout()
{
	$("#shouta").unbind('click').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'height' : 250,
		'width' : 500,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').height(247);
				ajaxUploader.enable();
				ajaxUploader._createInput();
				ajaxUploader._rerouteClicks();
			},
		'onClosed': function(){
				$('div#btnUpload').css('height','28px');
				$('div#btnUpload').css('width','84px');
			},
		'onComplete': function(){
				moveCursor(0, 0);
			}
	});
	$('#_shoutb').click(function(){
		Better_Switch_Shout_Form('normal');
		$('#shouta').trigger('click');
		return false;
	});
}

/**
 * 发送私信
 * 
 * @param uid
 * @param content
 * @return
 */
function Better_SendMessage(event)
{	
	if(betterUser.uid!='10000' && $.inArray(event.data.uid, betterUser.friends)==-1 && (((typeof dispUser!='undefined' && dispUser.friend_sent_msg == 1)) || (typeof event.data.friend_sent_msg!='undefined' && event.data.friend_sent_msg==1))){
		Better_Notify('对方只接收好友私信');
		
	}else if ($.inArray(event.data.uid, betterUser.blockedby)>=0) {
		Better_Notify(betterLang.global.direct_message.blocked);
	} else {
		var uid = event.data.uid;
		var nickname = event.data.nickname;
		var text = event.data.text;
		
		$('#betterMessageBox').dialog('destroy');
		
		var $dsm = $('#dlgSendMessage');
		$dsm.dialog('destroy');
		$dsm.attr('title', '<table><tr><td><img src="images/sixin.png" class="pngfix" /> </td><td style="padding-top: 3px;"><span>'+betterLang.noping.global.send_msg_to.toString().replace('{NICKNAME}',nickname)+'</span></td></tr></table>');
		
		$dsm.find('#sendMsgTo').text(nickname);
		$dsm.find('#msg_content').val(text);
		$dsm.find('#msg_receiver').val(nickname);
		if ($.browser.msie && $.browser.version<7) {
			dialogParams = {
					bgiframee: true,				
					autoOpen: true,
					resizable: false,
					zIndex: 4999,
					width: 490,
					buttons: {
				
					},
					open: function(event, ui){
						$('td#messgae_td').html('<textarea name="msg_content" id="msg_content"></textarea>');
					}	
			};
		} else {
			dialogParams = {
					bgiframee: true,	
					modal : true,
					autoOpen: true,
					resizable: false,
					zIndex: 4999,
					width: 490,
					buttons: {
				
					},
					open: function(event, ui){
						$('td#messgae_td').html('<textarea name="msg_content" id="msg_content"></textarea>');
					}	
			};		
		}
	
		$dsm.dialog(dialogParams);
		
		$dsm.find('#btn_send_msg').unbind('click').click(function(){
	
			$(this).attr('disabled', true);
			$('#dlgSendMessage span.error').hide();
	
			nickname = $.trim($('#msg_receiver').val());
			var x = '';
			x = $.trim($('#dlgSendMessage textarea').val());

			if (nickname=='' && !uid) {
				Better_Notify({
					msg: betterLang.global.invalid_receiver
				});
				$(this).attr('disabled', false);
			} else if (x=='') {
				Better_Notify({
					msg: betterLang.global.plz_input_msg_content
				});
				$('#msg_content').focus();
				$(this).attr('disabled', false);
			} else {
				Better_Notify_loading();
				$dsm.dialog('close');

				$.post('/ajax/messages/new', {
					uid: uid,
					nickname: nickname,
					content: x
				}, function(dnJson){
					Better_Notify_clear();
					
					if (Better_AjaxCheck(dnJson)) {
						if (dnJson.error) {
							Better_Notify({
								msg: dnJson.error
							});
						} else {
							
							switch (dnJson.code) {
							case dnJson.codes.INVALID_CONTENT:
								Better_Notify(betterLang.global.direct_message.invalid_content);
								break;
							case dnJson.codes.CANT_SELF:
								Better_Notify(betterLang.global.direct_message.cant_self);
								break;
							case dnJson.codes.BLOCKED_BY_RECEIVER:
								Better_Notify(betterLang.global.direct_message.blocked);
								break;
							case dnJson.codes.SUCCESS:
								Better_Notify(betterLang.global.msg_send_successfully);
								break;
							case dnJson.codes.FAILED:
							case dnJson.codes.INVALID_RECEIVER:
							default:
								Better_Notify(betterLang.global.system.error);
								break;								
							}
							
							
						}
					}
					$('#dlgSendMessage input[type="button"]').attr('disabled', false);
					$.fancybox.close();
					
				}, 'json');
			}
			
			
		});						
	}

	return false;
}


/**
 * 举报某人
 * 
 * @param uid
 * @param content
 * @return
 */
function Better_Denounce(event)
{

	nickname = event.data.nickname;
	text = event.data.text;
	
	var $dn = $('#dlgDenounce');
	
	$dn.dialog('destroy').attr('title', betterLang.noping.global.denounce.toString().replace('{NICKNAME}',nickname));
	
	$dn.find('#denounce_content').val(text);
	$dn.find('#denounce_receiver').val(nickname);

	$('#dlgDenounce').dialog({
		width: 'auto',
		bgiframe: true,
		autoOpen: true,
		modal: true,
		resizable: false,
		buttons: {
			
		}
	});
	
	
	$dn.find('#btn_denounce').click(function(){

		$(this).attr('disabled', true);

		nickname = $.trim($('#denounce_receiver').val());
		var x = '';
		$('#dlgDenounce').find('textarea').each(function(){
			x = $.trim($(this).val());
		});
		var reason = $('#dlgDenounce').find('#denounce_reason').val();

		if (nickname=='') {
			Better_Notify({
				msg: betterLang.denounce.user_empty
			});
			$(this).attr('disabled', false);
		} else if (x=='') {
			Better_Notify({
				msg: betterLang.denounce.content_empty
			});			
			$('#denounce_content').focus();
			$(this).attr('disabled', false);
		} else {
			Better_Notify_loading();
			$dn.dialog('close');
			
			$.post('/ajax/denounce', {
				nickname: nickname,
				content: x,
				reason: reason
			}, function(dnJson){
				if (Better_AjaxCheck(dnJson)) {
					if (dnJson.has_err==0) {
						Better_Notify(betterLang.denounce.success);
					} else {
						Better_Notify(betterLang.denounce.failed);
					}
				}
				$('#dlgDenounce input[type="button"]').attr('disabled', false);
				$.fancybox.close();
				
			}, 'json');
		}
		
	});		
						
	return false;
}

/**
 * 解析消息中的超链接
 * 
 * @param match
 * @return
 */
function Better_replaceHttplink(match)
{
	match = $.trim(match);
	//pat = eval('/'+BASE_DOMAIN+'/;');
	/*if (!pat.test(match)) {
		link = '<a href="'+match+'" target="_blank">'+match+'</a>';
	} else {
		link = match;
	}*/
	
	link = '<a href="'+match+'" target="_blank">'+match+'</a>';
	
	link = ' '+link+' ';

	return link;
}

/**
 * 解析消息中的@
 * 
 * @param match
 * @return
 */
function Better_replaceAt(match)
{
	return match;
}

/**
 * 模拟PHP的nl2br
 * 
 * @param str
 * @return
 */
function Better_nl2br(str)
{
	var breakTag='';
	breakTag='<br />';

	return (str+'').replace(/([^>]?)\n/g,'$1'+breakTag+'\n');
}

/**
 * 解析消息中的“转发”
 * 
 * @param match
 * @return
 */
function Better_replaceRt(match)
{
	name = $.trim(match.replace('RT', ''));
	link = betterLang.blog.zt+' '+Better_replaceAt('@'+name);
	
	return link;
}

/**
 * 解析消息内容，替换成格式化后的文字
 * 
 * @param txt
 * @return txt
 */
function Better_parseMessage(row)
{
	txt = $.trim(row.message);
	if (txt!='') {
		txt = Better_nl2br(txt+' ');
	} else if (txt=='' && row.attach_thumb) {
		txt = betterLang.blog_with_photo_no_message+ ' ';
	} else if (row.type == 'normal' && txt=='' && row.upbid) {
		txt = betterLang.global.blog.rt;
	}

	if (row.type!='tips') {
		if(row.priv=='private'){
			txt+='<span style="color: #f09800; font-weight:bold;">('+betterLang.global.priv.screat+')</span>';
		}else if(row.priv=='protected'){
			txt+='<span style="color: #44c8e9; font-weight:bold;">('+betterLang.global.priv.friend+')</span>';
		}
	}

	// 解析超链接
	pat = /((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w]+\.([\.a-zA-Z0-9]+)((\?|\/|:)+[\w\.\/=\?%\-&~`@\':+!#]*)*)/ig;
	txt = txt.replace(pat, Better_replaceHttplink);
	
	return txt;
}

/**
 * 删除一个消息
 * 
 * @param event
 * @return
 */
function Better_Delblog(event)
{
	msg = typeof(event.data.msg)!='undefined' ? event.data.msg : betterLang.blog.confirm_delete;
	type= typeof(event.data.type)!='undefined' ? event.data.type : 'delete';
	status1= typeof(event.data.status)!='undefined' ? event.data.status : "nottodo";
	if(type=="cancel_todo"){
		bid=$('#currentbid').val()?$('#currentbid').val(): typeof(event.data.bid)!='undefined' ? event.data.bid : "0";
		url = '/ajax/blog/opratetodo';
	}else{
		bid = typeof(event.data.bid)!='undefined' ? event.data.bid : "0";
		url = '/ajax/blog/delete';
	}
	Better_Confirm({
		msg: msg,
		onConfirm: function(){
			Better_Confirm_clear();
			Better_Notify_loading();			
			$.post(url, {
				bid: bid,
				status: status1
			}, function(dbjson){
				Better_Notify_clear();				
				if (Better_AjaxCheck(dbjson)) {		
						//Change the UI' elements		
						
					 if (dbjson.result=='1') {
						 $('#checkina').removeClass('checkin-l');
							$('#checkina').addClass('checkin');
							$('#todoa').show();
							$('#action-todo').hide();
							$('#currentbid').val('');
						bid_key = event.data.bid.replace('.', '_');
						
						if ($.isFunction(event.data.afterDeleteCallback)) {
							event.data.afterDeleteCallback();
						} else {
							$('#'+event.data.id).fadeOut();
							temprt = $('#'+event.data.id).next();
							nextisrt = temprt.find('td.rt_info').length;							
							if(nextisrt){	
								temprt.fadeOut();
							}							
						}
						msg = Better_parseAchievement(dbjson).toString().replace('. ','').replace('。','');
						if (msg) {
							Better_Notify({
								msg: msg,
								close_timer: 2
							});
						}						
						$('#checkinListRow_doing_' + bid_key).hide();
						$('#tbl_checkins #'+'checkinListRow_checkins_'+bid_key).hide();			

						$('#tbl_tips #'+'tipsListRow_tips_'+bid_key).hide();
						$('#tbl_messages #'+'listRow_messages__'+bid_key).hide();
						
					}
				}

			}, 'json');		
		}
	});

}

/**
 * 阻止某用户
 * 
 * @param event
 * @return
 */
function Better_Block(event)
{
	Better_Confirm({
		msg: betterLang.global.block.confirm_title.toString().replace('{HE}',event.data.nickname),
		height : ($.cookie('lan')=='zh-cn') ? '180' : '240',
		onConfirm: function(){
			Better_Notify_loading();
		
			uid = event.data.uid;
			$('#betterUserBlockBtn_'+uid).unbind('click', Better_Block);
			from = typeof(event.data.from)!='undefined' ? event.data.from : '';

			$.getJSON('/ajax/user/block', {
				uid: uid,
				from: from
			}, function(bJson){
				Better_Confirm_clear();
				
				if (Better_AjaxCheck(bJson)) {
					if (bJson.result=='1') {
						blocked_uid = bJson.blocked_uid;
						
						$btn = $('#betterUserBlockBtn_'+blocked_uid);
						
						$btn.html(betterLang.global.block.cancel).bind('click', {
							uid: blocked_uid
						}, Better_Unblock);
	
						Better_Notify(betterLang.global.block.success);
						betterUser.blocks.push(blocked_uid);
						if ($.inArray(blocked_uid, betterUser.followings)>=0) {
							betterUser.followings = Better_Array_Remove(betterUser.followings, blocked_uid);
						}
						
						if ($.inArray(blocked_uid, betterUser.friends)>=0) {
							betterUser.friends = Better_Array_Remove(betterUser.friends, blocked_uid);
						}
	
						$('#betterUserBtn_'+blocked_uid).unbind('click', Better_Follow).unbind('click', Better_Unfollow).html(betterLang.global.follow.title).bind('click', {
							uid: blocked_uid,
							nickname: event.data.nickname,
							id: 'betterUserBtn_'
						}, Better_Follow);		
						
						$('#betterUserFriendRequestBtn_'+blocked_uid).unbind('click', Better_Friend_Request).unbind('click', Better_Friend_Remove).html(betterLang.global.friend.request.title).bind('click', {
							uid: blocked_uid,
							nickname: event.data.nickname,
							id: 'betterUserFriendRequestBtn_'
						}, Better_Friend_Request);
						
						if (typeof(event.data.callbacks)!='undefined' && typeof(event.data.callbacks.completeCallback)!='undefined' && $.isFunction(event.data.callbacks.completeCallback)) {
							event.data.callbacks.completeCallback();
						}

					} else if (bJson.result=='-1') {
						Better_Notify({
							msg: betterLang.global.block.cant_block_sys_user
						});
					}
				}
				
			});		
		}
	});

}

/**
 * 取消阻止某用户
 * 
 * @param event
 * @return
 */
function Better_Unblock(event)
{
	Better_Confirm({
		msg: betterLang.global.unblock.confirm_title.toString().replace('{HE}', event.data.nickname),
		onConfirm: function(){
			Better_Notify_loading();
		
			uid = event.data.uid;
			$('#betterUserBlockBtn_'+uid).unbind('click', Better_Unblock);
			
			$.getJSON('/ajax/user/unblock', {
				uid: uid
			}, function(bJson){
				Better_Confirm_clear();
				
				if (Better_AjaxCheck(bJson)) {
					if (bJson.result=='1') {
						unblocked_uid = bJson.unblocked_uid;
						$('#betterUserBlockBtn_'+unblocked_uid).html(betterLang.global.block.title).bind('click', {
							uid: unblocked_uid
						}, Better_Block);
	
						Better_Notify({
							msg: betterLang.global.unblock.success,
							close_timer: 2
						});
						betterUser.blocks = Better_Array_Remove(betterUser.blocks, unblocked_uid);

						if (typeof(Better_Unblock_Callback)!='undefined') {
							Better_Unblock_Callback();
						}
						
					}
				}

			});				
		}
	});

}

/**
 * 取消收藏一个消息
 * 
 * @param event
 * @return
 */
function Better_UnFavoriteblog(event)
{

	Better_Confirm({
		msg: betterLang.global.unfavorite.confirm_title,
		onConfirm: function(){
			Better_Notify_loading();
			
			$.getJSON('/ajax/blog/unfavorite', {
				bid: event.data.bid
			}, function(bfjson){
				Better_Confirm_clear();
				
				if (Better_AjaxCheck(bfjson)) {		
					if (bfjson.data=='success') {
						bid_key = event.data.bid.replace('.', '_');
		
//						$('#'+event.data.id+bid_key).text(betterLang.global.favorite.title).unbind('click', Better_UnFavoriteblog).bind('click', event.data, Better_Favoriteblog);		
						$('#'+event.data.id+bid_key).attr('title',betterLang.global.favorite.title).css('background-position','-32px 0px').unbind('click', Better_UnFavoriteblog).bind('click', event.data, Better_Favoriteblog);
						
						Better_Notify(betterLang.global.unfavorite.success);
						
						betterUser.fav_bids = Better_Array_Remove(betterUser.fav_bids, bfjson.unfavorited_bid);
						$('#my_favorites').text($.makeArray(betterUser.fav_bids).length);
						if (typeof(dispUser)!='undefined' && dispUser.uid==betterUser.uid) {
							$('#disp_favorites').text($.makeArray(betterUser.fav_bids).length);
						}				
						
						if (typeof(event.data.inFavList)!='undefined' && event.data.inFavList==true && typeof(dispUser)!='undefined' && dispUser.uid==betterUser.uid) {
							$('#'+event.data.row_id).fadeOut();
							if ($.makeArray(betterUser.fav_bids).length==0) {
								Better_EmptyResults(event.data.tbl_id, betterUser.nickname+betterLang.global.favorite.havent_favorites);
							}
						}
									
					} else {
						Better_Notify(bfjson.error);
					}
				}
				
				
			});		
		}
	});

}

/**
 * 收藏一个消息
 * 
 * @param event
 * @return
 */
function Better_Favoriteblog(event)
{
	Better_Notify_loading();

	$.getJSON('/ajax/blog/favorite', {
		bid: event.data.bid,
		type: event.data.type
	}, function(bfjson){
		if (Better_AjaxCheck(bfjson)) {		
			if (bfjson.data=='success') {
				bid_key = event.data.bid.replace('.', '_');

//				$('#'+event.data.id+bid_key).text(betterLang.global.favorite.cancel).unbind('click', Better_Favoriteblog).bind('click', event.data, Better_UnFavoriteblog);
				$('#'+event.data.id+bid_key).attr('title',betterLang.global.favorite.cancel).css('background-position','-48px 0px').unbind('click', Better_Favoriteblog).bind('click', event.data, Better_UnFavoriteblog);
				
				Better_Notify(betterLang.global.favorite.success);
				
				betterUser.fav_bids.push(bfjson.favorited_bid);
				$('#my_favorites').text($.makeArray(betterUser.fav_bids).length);
				if (typeof(dispUser)!='undefined' && dispUser.uid==betterUser.uid) {
					$('#disp_favorites').text($.makeArray(betterUser.fav_bids).length);
				}
				
			} else {
				Better_Notify(bfjson.error);
			}
		}
	});
	
}

/**
 * 重置吼吼表单
 * 
 * @return
 */
function Better_ResetPostForm()
{
	if ($('#rt_tips').css('display')!='none' && $('#rt_tips').css('display')!='hidden') {
		$('#fancybox-wrap, #fancybox-outer').css('height', Better_ParseCssHeight({'orginalHeight' : $('#fancybox-outer').css('height'), 'offset': -56}));
	}
	
	$('#rt_tips').hide();
	$('#status_text').val('');
	$('#attach').val('');
	$('#upbid').val(0);
	$('#fileDesc').empty().hide();
	$('#divFileDesc').hide();
	$('#txtCount').html(Better_PostMessageMaxLength);
	$('#btnUpload').attr('src', '/images/photo.png').css('width', '80px').css('height', '80px').removeClass('avatar');
	
	$('#shout_poi_list').show();
	
	$('#priv_sel_shout').attr('priv', 'public').text(betterLang.global.priv_public);
	$('#shout_public').trigger('click');
	Better_Shout_Without_Poi = false;
}


/**
 * 切换吼吼浮动窗
 * 
 * @param formName
 * @return
 */
function Better_Switch_Shout_Form(formName)
{
	switch (formName) {
		case 'tips':
			Better_Switch_Shout_Form_To_Tip();
			break;
		case 'todo':
			Better_Switch_Shout_Form_To_ToDo();
			break;
		case 'normal':
		default:
			Better_Switch_Shout_Form_To_Shout();
			break;
	}
}

function Better_Switch_Shout_Form_To_Shout()
{
	Better_Shout_Type = 'normal';	
	Better_Shout_Without_Poi = false;
	$('#shout_title').css('margin-left','0');
	$('#shout_title').css('width','200px');
	$('#shout_title').text(betterLang.global.shout.title);
	$('#dlg_type').val('shout_dlg');
	$('#post_btn').text(betterLang.global.shout.text);	
	$('#disable_shout_poi').show();
	$('#shoutformicon').show();
	$('#tipsformicon').hide();
	$('#todoformicon').hide();
	$('#div_change_poi').hide();
	$('#btnUpload_img').show();
	$('#btnUpload').show();
	$('#shout_poi_list').show();
	$('#shout_poi_list').css('padding-left', '2px');
	$('#shout_priv, #check_sync').show();
	$('#status_text').css('width','400px');
	
	$('#fancybox-wrap, #fancybox-outer').height($('#fancybox-outer').height()+42);

	if (betterUser.last_checkin_poi) {
		
		$('#ready_to_shout_poi').val(betterUser.last_checkin_poi);
		$('#ready_to_shout_address').text(betterUser.address);
		$('#ready_to_shout_city').text(betterUser.city);
		$('#ready_to_shout_poi_name').text(betterUser.last_checkin_poi_name);		
	}
	
}

function Better_Switch_Shout_Form_To_Tip()
{
	Better_Shout_Type = 'tips';
	Better_Shout_Without_Poi = false;
	
	$('#shout_title').text(betterLang.global.tips.title);
	$('#tipsformicon').show();
	$('#shoutformicon').hide();
	$('#todoformicon').hide();
	$('#post_btn').text(betterLang.global.tips.text);
	$('#disable_shout_poi').hide();
	
	$('#div_change_poi').hide();
	$('#shout_poi_list').css('padding-left', '20px');
	$('#shout_priv, #check_sync').hide();
	
	if (Better_Poi_Id) {
		$('#ready_to_shout_poi').val(Better_Poi_Id);
		$('#ready_to_shout_address').text(Better_Poi_Detail.address);
		$('#ready_to_shout_city').text(Better_Poi_Detail.city);
		$('#ready_to_shout_poi_name').text(Better_Poi_Detail.name);		
	}	
}

/**
 * to-do
 * @return
 */
function Better_Switch_Shout_Form_To_ToDo()
{
	Better_Shout_Type = 'todo';
	Better_Shout_Without_Poi = false;
	$('#shout_title').css('margin-left','-10px');
	$('#shout_title').css('width','280px');
	$('#shout_title').addClass("longtext");
	$('#dlg_type').val('todo_dlg');
	$('#tipsformicon').hide();
	$('#shoutformicon').hide();
	$('#btnUpload_img').hide();
	$('#post_btn').text(betterLang.global.todo.text);
	$('#disable_shout_poi').hide();
	$('#status_text').css('width','490px').css('color','#999').val("说说你要去做啥？").text("说说你要去做啥？");
	$('#fancybox-wrap').width(520);
	
	$('#div_change_poi').hide();
	$('#shout_poi_list').hide();
	$('#shout_priv').show();

	if (Better_Poi_Id) {
		$('#ready_to_shout_poi').val(Better_Poi_Id);
		$('#shout_title').text("我想去"+Better_Poi_Detail.name);
	}
	$('#status_text').click(function (){
		if(this.value=='说说你要去做啥？'){
			this.value="";
			this.style.color="#000000"
		}
	}).blur(function (){
		if(this.value==''){
			this.value="说说你要去做啥？";
			this.style.color="#CBCBCB"
		}
	});
	$("#invitefriends_btn").unbind('click').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'width' : '492px',
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').height(492);
				$('#fancybox-wrap').width(590);
				ajaxUploader.enable();
				ajaxUploader._createInput();
				ajaxUploader._rerouteClicks();
			},
		'onClosed': function(){
			},
		'onComplete': function(){
			}
	});
}

/**
 * 我想去, to-do
 * @param event
 * @return
 */
function Better_ToDo(event)
{
	if (!event || event == 'undefined' || !event.data || event.data == 'undefined') return false;
	var poi = event.data.poi;
	if (!poi || poi == 'undefined' || !poi.poi_id || poi.poi_id == 'undefined' || poi.poi_id == 0) return false;
	Better_Poi_Id = poi.poi_id;
	Better_Poi_Detail = {
			address: poi.address,
			city: poi.city,
			name: poi.name
	};
	Better_Switch_Shout_Form('todo');
	$('#shouta').trigger('click');
}



/**
 * 转换消息的特殊字符
 * 
 * @param event
 * @return
 */
function Better_Transblog(event)
{
	
	if(typeof event.data.allow_rt!='undefined' && event.data.allow_rt==0){
		Better_Notify('该动态不允许被转发');
		return ;
	}
	msg = event.data.msg;
	//msg = msg.replace(/<BR>/ig, '\r');
	//msg = msg.replace(/(\n\r{1,})/ig, '\r');

	///$('#status_text').val('RT'+event.data.nickname+' '+msg).focus();
	//$('#attach').val(event.data.attach);
	//$('#upbid').val('0');
	Better_FilterStatus();	
	if ($('#shouta').html()!='') {
		//msg = msg.replace(/<img(.*?)>/, '');		
		if(typeof event.data.commentpage !=undefined && event.data.commentpage == true&&event.data.from=='todo'){
			html = '<a href="/'+event.data.username+'">'+event.data.nickname+'</a> 想去 <a href="/poi/'+event.data.poiid+'">'+event.data.address+'</a>';
			if(msg != "" && msg != null){
				html += ": "+msg;
			}
		}else{
			html = msg;
		}
		ls = '<a href="/'+event.data.username+'">'+event.data.nickname+'</a> '+event.data.address;
	
		$('#rt_tips').show();
		$('#rt_content').html(html);
		
		if(typeof event.data.with_upbid!='undefined' && event.data.with_upbid==true){
			if(typeof event.data.now_message!='undefined' && typeof event.data.now_nickname!='undefined'){
				$('#status_text').val('//@'+event.data.now_nickname+' :'+event.data.now_message);
			}
		}
		
		if (event.data.from=='checkin' || event.data.from=='todo') {
			$('#rt_location').hide();
		} else {			
			$('#rt_location').html(ls).show();
		}
		
		$('#shouta').trigger('click');
		$('#upbid').val(typeof(event.data.bid)!='undefined' ? event.data.bid : event.data.bid_key.split('_').join('.'));
		$('#real_upbid').val(event.data.now_bid);
		$('#fancybox-wrap, #fancybox-outer').height($('#fancybox-outer').height()+15);
	}
	
	Better_Switch_Shout_Form('normal');
	
	return true;
}

function Better_TransTips(event)
{
	if(typeof event.data.allow_rt!='undefined' && event.data.allow_rt==0){
		Better_Notify('该动态不允许被转发');
		return ;
	}
	
	msg = event.data.msg;
	Better_FilterStatus();
	
	if ($('#shouta').html()!='') {
		html = msg;
		ls = '<a href="/'+event.data.username+'">'+event.data.nickname+'</a> '+event.data.address;
		
		$('#rt_tips').show();
		$('#rt_content').html(html);
		if (event.data.from=='checkin') {
			$('#rt_location').hide();
		} else {
			$('#rt_location').html(ls).show();
		}
		$('#shouta').trigger('click');
		$('#upbid').val(event.data.bid_key.split('_').join('.'));
		$('#fancybox-wrap, #fancybox-outer').css('height', Better_ParseCssHeight({'orginalHeight' : $('#fancybox-outer').css('height'), 'offset': 10}));
	}
	
	Better_Switch_Shout_Form('normal');
	
	return true;
}

/**
 * 收藏POI
 * 
 * @param event
 * @return
 */
function Better_Favorite_Poi(event)
{
	poiId = event.data.poi_id;
	
	if (poiId && $.inArray(poiId, betterUser.poi_favorites)<0) {
		Better_Confirm({
			msg: betterLang.global.favorite.confirm.title,
			onConfirm: function(){
				Better_Notify_loading();
				
				$.getJSON('/ajax/poi/favorites', {
					poi_id: poiId,
					todo: 'create'
				}, function(fapJson){
					Better_Notify_clear();
					Better_Confirm_clear();
					
					result = fapJson.result;
					codes = result.codes;
					
					switch (result.code) {
						case codes.SUCCESS:
							msg = betterLang.global.favorite.success;
							betterUser.poi_favorites.push(poiId);
							
							$('#'+event.currentTarget.id).text(betterLang.global.favorite.cancel).unbind('click', event.data, Better_Favorite_Poi).bind('click', event.data, Better_Unfavorite_Poi);
							break;
						case codes.INVALID_POI:
						case codes.FAILED:
						default:
							msg = betterLang.global.favorite.fail;
							break;
					}

					Better_Notify(msg);
				});				
			}
		});
	}
}

/**
 * 取消收藏POI
 * 
 * @param event
 * @return
 */
function Better_Unfavorite_Poi(event)
{
	poiId = event.data.poi_id;
	
	if (poiId && $.inArray(poiId, betterUser.poi_favorites)>=0) {
		Better_Confirm({
			msg: betterLang.global.unfavorite.confirm_title,
			onConfirm: function(){
				Better_Notify_loading();
				
				$.getJSON('/ajax/poi/favorites', {
					poi_id: poiId,
					todo: 'destroy'
				}, function(ufapJson){
					Better_Notify_clear();
					Better_Confirm_clear();
					
					result = ufapJson.result;
					codes = result.codes;
					
					switch (result.code) {
						case codes.SUCCESS:
							msg = betterLang.global.unfavorite.success;
							betterUser.poi_favorites = Better_Array_Remove(betterUser.poi_favorites, poiId);
							
							$('#'+event.currentTarget.id).text(betterLang.global.favorite.title).unbind('click', event.data, Better_Unfavorite_Poi).bind('click', event.data, Better_Favorite_Poi);
							
							break;
						case codes.INVALID_POI:
						case codes.FAILED:
						default:
							msg = betterLang.global.favorite.fail;
							break;
					}
					
					Better_Notify({
						msg: msg,
						closeCallback: function(){$('a[href="#poi_favorites"]').trigger('click');}
					});
				});				
			}
		});
	}	
}

/**
 * 删除好友
 * 
 * @param event
 * @return
 */
function Better_Friend_Remove(event)
{
	var btnId = event.currentTarget.id;
	
	if ($.inArray(event.data.uid, betterUser.friends)>=0) {
		Better_Confirm({
			msg: betterLang.global.friend.confirm.remove.toString().replace('{NICKNAME}', event.data.nickname),
			onConfirm: function(){
				Better_Notify_loading();
				
				$.getJSON('/ajax/user/remove_friend', {
					uid: event.data.uid
				}, function(ref_json){
					Better_Notify_clear();
					Better_Confirm_clear();
					
					result = ref_json.result;
					
					if (result==1) {
						msg = betterLang.global.friend.remove.success;
						betterUser.friends = Better_Array_Remove(betterUser.friends, event.data.uid);
						
						$('#'+event.currentTarget.id).text(betterLang.global.friend.request.title).unbind('click', event.data, Better_Friend_Remove).bind('click', event.data, Better_Friend_Request);
						
						if (typeof(event.data.closeCallback)!='undefined' && $.isFunction(event.data.closeCallback)) {
							event.data.closeCallback();
						}
					} else {
						msg = betterLang.global.friend.remove.fail;
					}
					
					Better_Notify(msg+''+Better_parseAchievement(ref_json));
				});
			}
		});
	}
	
	return false;
}

/**
 * 添加好友请求
 * 
 * @param event
 * @return
 */
function Better_Friend_Request(event)
{
	if (!Better_Ajax_processing) {
		var btnId = event.currentTarget.id;
		var notify = typeof(event.data.notify)=='string' ? event.data.notify : betterLang.global.friend.request.notify;
		var nickname = typeof(event.data.nickname)=='string' ? event.data.nickname : '';
		var widthConfirm = typeof event.data.widthConfirm!='undefined' ? event.data.widthConfirm : true;
		
		if ($.inArray(event.data.uid, betterUser.blocks)>=0) {
			Better_Notify(betterLang.global.friend.request.blocked);
		} else {
			var onConfirm = function(){
					Better_Notify_loading();
					
					$.getJSON('/ajax/user/friend_request', {
						uid: event.data.uid
					}, function(fr_json){
						Better_Notify_clear();
						Better_Confirm_clear();
						
						result = fr_json.result;
						codes = fr_json.codes;
						noreturn = 0;						
						switch(result) {
							case codes.KARMA_TOO_LOW:
								msg = betterLang.global.friend.request.karma_too_low;
								break;
							case codes.PENDING:
								//msg = betterLang.global.friend.request.pending;
								msg = betterLang.noping.better.friend_request_karma_pending;	
								noreturn = 1;
								$('#'+btnId).text(betterLang.global.friend.request.btn.pending).unbind('click', Better_Friend_Request);
								hasMyFriendRequest = 1;
								break;
							case codes.BLOCKED:
								msg = betterLang.global.friend.request.blocked;
								break;
							case codes.BLOCKEDBY:
								msg = betterLang.global.friend.request.blockedby;
								break;
							case codes.SUCCESS:
								msg = betterLang.global.friend.request.success;
								$('#'+btnId).text(betterLang.global.friend.request.remove).unbind('click', event.data, Better_Friend_Request).bind('click', event.data, Better_Friend_Remove);
								betterUser.friends.push(event.data.uid);
								$('#friend_msg').hide();
								
								if ($.isFunction(event.data.completeCallback)) {
									event.data.completeCallback();
								}
								
								$('#request_'+event.data.msg_id).fadeOut();
								break;
							case codes.REQUESTED:
								msg =  betterLang.global.friend.request.requested;
								//betterLang.noping.better.friend_request_karma_pending;	
								//noreturn = 1;
								//msg = betterLang.global.friend.request.requested;
								break;
							case codes.CANTSELF:
								msg = betterLang.global.friend.request.cantself;
								break;
							case codes.CANTSYS:
								msg = betterLang.global.friend.request.cantsys;
								break;
							case codes.ALREADY:
								msg = betterLang.global.friend.request.already_friends;
								break;
							case codes.FAILED:
							default:
								msg = betterLang.global.freind.request.failed;
								break;
						}
						
						
						karmaMsg = Better_parseAchievement(fr_json);						
						if (karmaMsg!='') {
							karmaMsg =  karmaMsg;							
							if (typeof(fr_json.double_request)!='undefined' && fr_json.double_request!=1) {
								karmaMsg += ', '+betterLang.global.backtoyou;
							}						
						}						
						if(noreturn){
							karmaMsg ='';
						}						
						Better_Notify({msg:msg+karmaMsg, height:190});
					});
			};
			
			if(widthConfirm){
				Better_Confirm({
					msg: notify.toString().replace('{NICKNAME}', nickname),
					onConfirm: onConfirm
			});
			}else{
				onConfirm();
				}
			
		}
	
	}
	
	return false;
}

/**
 * 拒绝好友请求
 * 
 * @param event
 * @return
 */
function Better_Reject_Friend_Request(event)
{
	var uid = event.data.uid;
	var nickname = event.data.nickname;

	Better_Confirm({
		msg: betterLang.user.friend.confirm.reject.toString().replace('{NICKNAME}', nickname),
		onConfirm: function(){
			Better_Notify_loading();

			$.getJSON('/ajax/user/reject_friend', {
				uid: uid
			}, function(rejf_json){
				Better_Confirm_clear();
				
				if (rejf_json.result==1) {
					msg = betterLang.user.friend.reject.success.toString().replace('{NICKNAME}', nickname);
					$('#friend_msg').hide();
					hasFriendRequest = '0';			
					
					if ($.isFunction(event.data.completeCallback)) {
						event.data.completeCallback();
					}
				} else {
					msg = betterLang.user.friend.reject.fail;
				}
				
				Better_Notify(msg);
			});
		}
	});
		
	return false;
}

/**
 * 关注一个用户
 * 
 * @param event
 * @return
 */
function Better_Follow(event)
{
	/*if ($.inArray(event.data.uid, betterUser.blocks)>=0) {
		Better_Notify(betterLang.global.follow.blocked);
	} else {
	
		Better_Confirm({
			msg: betterLang.user.confirm_follow,
			onConfirm: function(){
				Better_Notify_loading();

				$.getJSON('/ajax/user/follow', {
					uid: event.data.uid
				}, function(follow_json){
					Better_Confirm_clear();
					
					if (Better_AjaxCheck(follow_json)) {			
						followed_uid = follow_json.followed_uid;
						codes = follow_json.codes;
						
						switch(follow_json.result) {
							case codes.INSUFFICIENT_KARMA:
								msg = betterLang.global.follow.karma_too_low;
								break;
							case codes.PENDING:
								$('#'+event.currentTarget.id).html(betterLang.global.follow.plz_wait_for_confirm).unbind('click', Better_Follow);
								msg = betterLang.global.follow.plz_wait_for_confirm_long.toString().replace('{NICKNAME}', event.data.nickname);
								break;
							case codes.DUPLICATED_REQUEST:
								$('#'+event.currentTarget.id).html(betterLang.global.follow.plz_wait_for_confirm).unbind('click', Better_Follow);
								msg = betterLang.global.follow.request.duplicated;	
								break;
							case codes.ALREADY:
								msg = betterLang.global.follow.request.already.toString().replace('{NICKNAME}', event.data.nickname);
								break;
							case codes.ALREADYGEO:
								msg = betterLang.global.follow.request.already_geo.toString().replace('{NICKNAME}', event.data.nickname);
								break;
							case codes.CANTSELF:
								msg = betterLang.global.follow.request.cant_self;
								break;
							case codes.BLOCKED:
								msg = betterLang.global.follow.request.blocked.toString().replace('{NICKNAME}', event.data.nickname);
								break;
							case codes.BLOCKEDBY:
								msg = betterLang.global.follow.request.blockedby.toString().replace('{NICKNAME}', event.data.nickname);
								break;
							case codes.INVALIDUSER:
								msg = betterLang.global.follow.request.invalid_user;
								break;
							case codes.SUCCESS:
								msg = betterLang.global.follow.success;
								$('#'+event.currentTarget.id).html(betterLang.global.follow.cancel).unbind('click', Better_Follow).bind('click', event.data, Better_Unfollow);
								
								betterUser.followings.push(followed_uid);
								
								if (typeof(dispUser)!='undefined' && dispUser.uid==betterUser.uid) {
									$('#disp_followings').text($.makeArray(betterUser.followings).length);
								}		
								break;
							case codes.FAILED:
							default:
								msg = betterLang.global.system.error;
								break;
						}
						
						Better_Notify(msg);
					}

				});				
			}
		});
	}*/
	return false;
}

/**
 * 取消关注一个用户
 * 
 * @param event
 * @return
 */
function Better_Unfollow(event)
{
	/*Better_Confirm({
		msg: betterLang.user.confirm_unfollow,
		onConfirm: function(){
			Better_Notify_loading();
			
			$.getJSON('/ajax/user/unfollow', {
				uid: event.data.uid
			}, function(unfollow_json){
				Better_Confirm_clear();
				
				if (Better_AjaxCheck(unfollow_json)) {			
					if (unfollow_json.result==1) {
						$('#'+event.currentTarget.id).html(betterLang.global.follow.title).unbind('click', Better_Unfollow).bind('click', event.data, Better_Follow);
						Better_Notify(betterLang.global.unfollow.success);
						betterUser.followings = Better_Array_Remove(betterUser.followings, unfollow_json.unfollowed_uid);
						
						if (typeof(dispUser)!='undefined' && dispUser.uid==betterUser.uid) {
							$('#disp_followings').text($.makeArray(betterUser.followings).length);
							if (typeof(event.data.afterUnfollowCallback)!='undefined' && $.isFunction(event.data.afterUnfollowCallback)) {
								event.data.afterUnfollowCallback();
							}
						}					
					}else if(unfollow_json.result==-2){
						Better_Notify(betterLang.global.unfollow.sys_account);
					}
				}

			});			
		}
	});*/
	return false;
}

/**
 * 通过关注请求
 * 
 * @param event
 * @return
 */
function Better_Allow_Follow(event)
{
	/*if (!Better_Ajax_processing) {
		var nickname = typeof(event.data.nickname)!='undefined' ? ' '+event.data.nickname+' ' : betterLang.global.follow.request.he;
		var requestUid = event.data.uid;
		
		Better_Confirm({
			msg: betterLang.noping.global.follow.request.pass.info.toString().replace('{NICKNAME}',nickname),
			onConfirm: function(){
				Better_Notify_loading();
			
				$.getJSON('/ajax/user/confirmfollow', {
					request_uid: requestUid
				}, function(cfJson){
					Better_Confirm_clear();
					
					if (Better_AjaxCheck(cfJson)) {
						if (cfJson.result.code>0) {
							betterUser.followers.push(cfJson.request_uid);
							
							var closeCallback = (typeof(event.data.closeCallback)!='undefined' && $.isFunction(event.data.closeCallback)) ? event.data.closeCallback : function(){};
							var completeCallback = (typeof(event.data.completeCallback)!='undefined' && $.isFunction(event.data.completeCallback)) ? event.data.completeCallback : function(){};
							Better_Notify({
								msg: betterLang.noping.global.follow.request.sucess.info.toString().replace('{NICKNAME}',nickname)+''+Better_parseAchievement(cfJson),
								closeCallback: function(){
									if (typeof(event.data.tab)!='undefined') {
										event.data.tab.trigger('click');
									}
									closeCallback();
								}
							});
							
							if ($.isFunction(completeCallback)) {
								completeCallback();
							}
	
						} else if (cfJson.result.code==-3) {
							Better_Notify(betterLang.noping.global.follow.request.fail.info.toString().replace('{NICKNAME}',nickname));
						} else {
							Better_Notify(betterLang.noping.global.system.error.toString().replace('{WHATHAPPEND}',cfJson.result.code));
						}
					}
				});			
			}
		});
	}*/
		
	return false;
}
	
/**
 * 拒绝关注请求
 * 
 * @param event
 * @return
 */
function Better_Reject_Follow(event)
{
	Better_Confirm({
		msg: betterLang.noping.global.follow.request.reject.sure.toString().replace('{NICKNAME}',event.data.nickname),
		onConfirm: function(){
			Better_Notify_loading();
				
			$.getJSON('/ajax/user/rejectfollow', {
				request_uid: event.data.uid
			}, function(rfJson){
				Better_Confirm_clear();
					
				if (Better_AjaxCheck(rfJson)) {
					if (rfJson.result.code!=0) {
						var closeCallback = (typeof(event.data.closeCallback)!='undefined' && $.isFunction(event.data.closeCallback)) ? event.data.closeCallback : function(){};
							
						Better_Notify({
							msg: betterLang.noping.global.follow.request.reject.info.toString().replace('{NICKNAME}',event.data.nickname),
							closeCallback: function(){
								if (typeof(event.data.tab)!='undefined') {
									event.data.tab.trigger('click');
								}
								closeCallback();
							}
						});
					} else {
						Better_Notify(betterLang.noping.global.system.error.toString().replace('{WHATHAPPEND}',rfJson.result.code));						
					}
				}		
	
			});			
		}
	});
	
	return false;
}

/**
 * 分页已到最后一页
 * 
 * @param pagerKey
 * @return
 */
function Better_Pager_Reach_Last(pagerKey)
{
	$('#pager_href_'+pagerKey).text(betterLang.global.pager.arrive_last_page);
	eval('Better_PagerList.'+pagerKey+'=2;');
}

/**
 * 分页运算
 * 
 * @return
 */
function Better_Pager(ops)
{
	var pagerKey = ops.key;

	try {

		if (typeof(eval('Better_PagerList.'+pagerKey))=='undefined' || eval('Better_PagerList.'+pagerKey)==2) {
			if($.browser.msie==true && parseInt($.browser.version)<7){
				var pager = $(document.createElement('div')).attr('id', 'pager_href_'+pagerKey).addClass('ie6page');
			} else {
				var pager = $(document.createElement('a')).attr('id', 'pager_href_'+pagerKey);
			}
			
			nextStr = typeof(ops.next)=='undefined' ? betterLang.pager.next : ops.next;
			pager.attr('href', 'javascript:void(0)').html(nextStr+' <img src="images/more.gif"/>').click(function(){
				
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

function Better_PagerIe6(ops)
{
	var pagerKey = ops.key;

	try {

		if (typeof(eval('Better_PagerList.'+pagerKey))=='undefined' || eval('Better_PagerList.'+pagerKey)==2) {
			pager = $(document.createElement('div')).attr('id', 'pager_href_'+pagerKey).addClass('ie6page');
			
			nextStr = typeof(ops.next)=='undefined' ? betterLang.pager.next : ops.next;
			pager.attr('href', 'javascript:void(0)').html(nextStr+' <img src="images/more.gif"/>').click(function(){
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
/**
 * 重置分页对象
 * 
 * @param key
 * @return
 */
function Better_Pager_Reset(key)
{
	$('#pager_pages_'+key).val(1);
	$('#pager_page_'+key).val(1);	
	$('#pager_href_'+key).html(betterLang.global.pager.next_page+' <img src="/images/more.gif" />');
}

/**
 * 设置分页的页数
 * 
 * @param key
 * @param pages
 * @return
 */
function Better_Pager_setPages(key, pages)
{
	if (pages==1) {
		$('#pager_'+key).html('<span class="pager_none">'+betterLang.global.pager.only_one_page+'</span>');
	} else if (pages==0) {
		$('#pager_href_'+key).html('<span class="pager_none">'+betterLang.global.pager.empty_page+'</span>');
	}
	
	$('#pager_pages_'+key).val(parseInt(pages));
}

/**
 * 判断字符串是不是Email
 * 
 * @param email
 * @return
 */
function Better_isEmail(email_str) 
{
	pat = /(\S)+[@]{1}(\S)+[.]{1}(\w)+/;
	return pat.test(email_str);
}

/**
 * 格式化距离
 * 
 * @param meter
 * @return
 */
function Better_Format_Meter(meter)
{
	formated = '';
	if (typeof(meter)!='undefined') {
		if (meter<1000 && meter>0) {
			formated = meter+betterLang.global.meter;
		} else if (meter>=1000) {
			formated = (meter/1000).toFixed(3)  + betterLang.global.km;
		}
	}
	
	return formated;
}

/**
 * 比较时间
 * 
 * @param time1
 * @param time2
 * @return
 */
/*function Better_compareTime(time1, time2)
{

	time2 = time2 ? time2 : Math.round(new Date().getTime()/1000) ;//+ Better_Brwoser_Timezone_Offset;
	oneMinute = 60;
	oneHour = oneMinute*60;
	oneDay = oneHour*24;
	oneWeek = oneDay*7;
	offset = time2-time1;
	
	if (offset<oneMinute) {
		str = betterLang.date.justnow;
	} else if (offset<oneHour) {
		str = parseInt(offset/oneMinute)+' '+betterLang.date.minute+betterLang.date.before;
	} else if (offset<oneDay) {
		str = parseInt(offset/oneHour)+' '+betterLang.date.hour+betterLang.date.before;
	} else if (offset<oneWeek) {
		str = parseInt(offset/oneDay)+' '+betterLang.date.day+betterLang.date.before;
	} else {
		d = new Date(time1*1000);
		str = betterLang.noping.global.ddyymm.toString().replace('{YEAR}',d.getFullYear()).replace('{MONTH}',d.getMonth()+1).replace('{DAY}',d.getDate());
	}

	return str;
}*/
function Better_compareTime(time, relate, format){
	var ttime = time*1000;
	var str = '';
	relate = typeof relate !='undefined' ? relate: 'relative';
	format = typeof format !='undefined' ? format: 'date';
	
	if(relate=='relative'){
		var now = (Date.parse(new Date()))/1000;
		var delta = now - time;
		var d1 = new Date(ttime).getDate();
		var d2 = new Date().getDate();
		if ((delta <= 0)||(Math.floor(delta/60)<=0))
			str = "刚才";
         else if (delta < 3600)
        	 str =  Math.floor(delta/60) + "分钟前";
         else if (d2-d1 == 0 && delta < 86400)
        	 str = Math.floor(delta/3600) + "小时前";
         else if ((new Date(ttime).getFullYear() == new Date().getFullYear()) && (new Date(ttime).getMonth()==new Date().getMonth()) && (d2-d1 == 1))
        	 str = '昨天 ' + new Date(ttime).getHours()+':'+(new Date(ttime).getMinutes()<10 ? '0'+(new Date(ttime).getMinutes()) : new Date(ttime).getMinutes());
         else if (new Date(ttime).getFullYear() == new Date().getFullYear()){
        	 var m = new Date(ttime).getMonth()+1;
        	 var d = new Date(ttime).getDate();
        	 str = m+'月'+d+'日'+' '+new Date(ttime).getHours()+':'+(new Date(ttime).getMinutes()<10 ? '0'+(new Date(ttime).getMinutes()) : new Date(ttime).getMinutes());
         } else{
        	 var m = new Date(ttime).getMonth()+1;
        	 var d = new Date(ttime).getDate();
        	 str = new Date(ttime).getFullYear()+'-'+m+'-'+d;
         }
	}
	
	return str;
	
}



/*
 * 将'2010-11-30 12:23:59'转换成unix时间戳
 */
function Better_TimetoUnix(time){
	var str = time;	
	var new_str = str.replace(/:/g,'-');
	var new_str = new_str.replace(/ /g,'-');
	var arr = new_str.split("-");
	var datum = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
	unixtime = datum.getTime()/1000
	return unixtime;	
}

function Better_UnixtoTime(str,datetimepattern){	
	var unixTimestamp = new Date(str*1000+8*3600000);	
	var year = unixTimestamp.getUTCFullYear();
	var month = unixTimestamp.getUTCMonth()+1;
	var day = unixTimestamp.getUTCDate();
	var hours = unixTimestamp.getUTCHours();
	var mm = unixTimestamp.getUTCMinutes();
	var ss = unixTimestamp.getUTCSeconds();
	month = month<10 ? '0'+month:month;
	day = day<10 ? '0'+day:day;
	hours = hours<10 ? '0'+hours:hours;
	mm = mm<10 ? '0'+mm:mm;
	ss = ss<10 ? '0'+ss:ss;	
	datetimepattern = datetimepattern.replace('YY', year).replace('MM', month).replace('DD', day).replace('hh', hours).replace('mm', mm).replace('ss', ss);	
	return datetimepattern;
}

/**
 * 执行ajax请求之前，用该函数将要填充的html区域设置一个loading效果
 * 
 * @param id 标识符，有tbody的和pager的
 * @return
 */
function Better_Table_Loading(id)
{
	$('#pager_'+id).hide();
	
	tr = '<tr class="tbl_tr_ajax_loading"><td colspan="8" class="tbl_ajax_loading"><img src="images/ajax_loading.gif" alt="" /></tr>';
	$('#tbl_'+id).append(tr);
}

/**
 * 清除ajax的loading效果
 * 
 * @param id
 * @return
 */
function Better_Clear_Table_Loading(id)
{
	$('#pager_'+id).show();
	$('#tbl_'+id+' tr.tbl_tr_ajax_loading').replaceWith('');
}

/**
 * 贴士顶的alt提示
 * 
 * @param polled
 * @param ups
 * @return
 */
function Better_Tips_Up_Alt(polled, ups)
{
	split = '\n';
	if ($.browser.mozilla || $.browser.opera) {
		split = ', ';
	}
	
	ups = betterLang.up_total.toString().replace('{TOTAL}', ups);
	upTips = polled ? betterLang.you_have_polled : betterLang.you_havent_polled;	
	upTips = upTips +split+ups;
	
	return upTips;
}

/**
 * 贴士砸的alt提示
 * 
 * @param polled
 * @param ups
 * @return
 */
function Better_Tips_Down_Alt(polled, downs)
{
	split = '\n';
	if ($.browser.mozilla || $.browser.opera) {
		split = ', ';
	}
	
	downs = betterLang.down_total.toString().replace('{TOTAL}', downs);
	downTips = polled ? betterLang.you_have_polled : betterLang.you_havent_polled;	
	downTips = downTips +split+downs;
	
	return downTips;
}

/**
 * 贴士“砸”
 * 
 * @param event
 * @return
 */
function Better_Tips_Down(event)
{
	bid = event.data.bid;
	var akey = event.data.akey;
	var trKey = event.data.trKey;

	Better_Notify_loading();
	
	$.getJSON('/ajax/poi/poll', {
		todo: 'create',
		option: 'down',
		status_id: bid
	}, function(tdJson){
		msg = betterLang.poi.tips.failed;
		
		if (Better_AjaxCheck(tdJson)) {
			
			if (tdJson.result>0) {
				
				ups = parseInt($('#'+trKey).attr('up'));
				downs = parseInt($('#'+trKey).attr('down'))+1;
				
				$('#'+trKey).attr('down', downs);
				
				$('#pollFuncA_up_'+akey).unbind('click').attr('alt', Better_Tips_Up_Alt(1, ups)).attr('title', Better_Tips_Up_Alt(1, ups));
				$('#pollFuncA_down_'+akey).unbind('click').attr('alt', Better_Tips_Down_Alt(1, downs)).attr('title', Better_Tips_Down_Alt(1, downs));
				$('#pollFuncA_up_'+akey+' img').attr('title', Better_Tips_Up_Alt(1, ups));
				$('#pollFuncA_down_'+akey+' img').attr('title', Better_Tips_Down_Alt(1, downs))
				
				msg = betterLang.poi.tips.success;
			}
		}
		
		Better_Notify({
			msg: msg
		});
	});	
}

/**
 * 贴士“顶”
 * 
 * @param event
 * @return
 */
function Better_Tips_Up(event)
{
	bid = event.data.bid;
	var akey = event.data.akey;
	var trKey = event.data.trKey;
	
	Better_Notify_loading();
	
	$.getJSON('/ajax/poi/poll', {
		todo: 'create',
		option: 'up',
		status_id: bid
	}, function(tuJson){
		msg = betterLang.poi.tips.failed;

		if (Better_AjaxCheck(tuJson)) {
			
			if (tuJson.result>0) {
				ups = parseInt($('#'+trKey).attr('up'))+1;
				downs = parseInt($('#'+trKey).attr('down'));
				
				$('#'+trKey).attr('up', ups);
				
				$('#pollFuncA_up_'+akey).unbind('click');
				$('#pollFuncA_down_'+akey).unbind('click');
				$('#pollFuncA_up_'+akey+' img').attr('title', Better_Tips_Up_Alt(1, ups));
				$('#pollFuncA_down_'+akey+' img').attr('title', Better_Tips_Down_Alt(1, downs))
				
				msg = betterLang.poi.tips.success;
			}
		}
		
		Better_Notify({
			msg: msg
		});
	});
}

/**
 * 加载一个用户列表
 * 
 * @param id 标识符
 * @param url 请求的url
 * @param post post的数据
 * @param callback 回调函数
 * @param renew 是否强制刷新ajax结果（由于jquery的ajax有个缓存）
 * @return
 */
function Better_loadUsers(options)
{
	id = options.id;
	url = options.url;
	callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};
	posts = typeof(options.posts)=='object' ? options.posts : {};
	var btns = typeof(options.btns)!='undefined' ? options.btns : ['dmessage', 'follow', 'block', 'friend'];
	var with_laststatus = typeof(options.with_laststatus)!='undefined' ? options.with_laststatus : false;

	Better_Table_Loading(id);

	var withRealname = false;
	var isFollowRequest = false;
	
	typeof(posts.withRealname)!='undefined' ? withRealname = posts.withRealname : withRealname = false;
	typeof(posts.isFollowRequest)!='undefined' ? isFollowRequest = posts.isFollowRequest : isFollowRequest = false;

	$.get(url, posts, function(luJson){
		if ($.isFunction(callbacks.beforeCallback)) {
			callbacks.beforeCallback(luJson);
		}
		
		var page = typeof(posts.page)!='undefined' ? posts.page : 0;

		tbl = $('#tbl_'+id);

		//	是否有异常回调函数
		exceptionCallback = $.isFunction(callbacks.exceptionCallback) ? callbacks.exceptionCallback : function(){};
		
		if (Better_AjaxCheck(luJson, exceptionCallback)) {

			//	从取回的ajax结果设置分页
			Better_Pager_setPages(id, luJson.pages);
			//	清除Table的loading
			Better_Clear_Table_Loading(id);

			var pages = typeof(luJson.pages)!='undefined' ? luJson.pages : 0;
			
			if (page==pages) {
				Better_Pager_Reach_Last(id);
			}
			
			if (luJson.count>0) {
	
				//	渲染结果行
				for(i in luJson.rows) {
					var thisRow = luJson.rows[i];

					trHtml = new Array();
					trHtml.push('<tr id="better_user_row_'+thisRow.uid+'_'+id+'" nickname="'+thisRow.nickname+'" uid="'+thisRow.uid+'" id_key="'+id+'" gender="'+thisRow.gender+'" class="betterUserRow">');
					trHtml.push('<td width="56">');
					trHtml.push('<a href="/'+thisRow.username+'"><img class="avatar pngfix avatar_small" src="'+thisRow.avatar_small+'" alt="'+thisRow.nickname+'" onerror="this.src=Better_AvatarOnError" /></a>');
					trHtml.push('</td>');
					trHtml.push('<td class="info" style="vertical-align: middle;">');
					trHtml.push('<div class="status message_row">');
					trHtml.push('<span class="left"><a href="/'+thisRow.username+'" class="user">'+thisRow.nickname+'</a></span>');
					if(typeof betterUser.uid!='undefined' && betterUser.uid>0){
						trHtml.push('<span id="betterUserFuncDiv_'+thisRow.uid+'_'+id+'" class="action" style="font-size: 12px;">');
						if(id=='friends' && (typeof dispUser!='undefined' && dispUser.uid==betterUser.uid)){
							var checked = (typeof thisRow.home_show!='undefined' && thisRow.home_show==1)? 'checked' :'';
							trHtml.push('<span class="left" style="margin-right: 15px;"><input type="checkbox" '+checked+' id="betterHomeshow_'+thisRow.uid+'" uid="'+thisRow.uid+'" />首页显示TA的动态</span>');
						}
						
						if ($.inArray(thisRow.uid, betterUser.friends)<0) {
							trHtml.push('<a id="betterUserFriendBtn_'+thisRow.uid+'" href="javascript:void(0);" class="button right" style="color:#fff;">加为好友</a>');
						}else{
							if(id=='friends' && (typeof dispUser!='undefined' && dispUser.uid==betterUser.uid)){
								trHtml.push('<a id="betterUserFriendBtn_'+thisRow.uid+'" href="javascript:void(0);" class="button right" style="color:#fff;">解除好友</a>');
							}else{
								trHtml.push('<span class="left" style="margin-right: 30px;">已是好友</span>');
							}
						}
						
						if(id!='friends'){
							var blockBtn = '阻止此人';
							if ($.inArray(thisRow.uid, betterUser.blocks)>=0) {
								blockBtn = '取消阻止';
							}
							trHtml.push('<a id="betterUserBlockBtn_'+thisRow.uid+'" href="javascript:void(0);" class="button right" style="color:#fff;">'+blockBtn+'</a>');
						}
						trHtml.push('<a id="betterMsgBtn_'+thisRow.uid+'" href="javascript:void(0);" class="button right" style="color:#fff;">发私信</a>');
						trHtml.push('</span>');
					}
					trHtml.push('<div class="clearfix"></div>');
					trHtml.push('</div>');
					
					/*trHtml.push('<div class="ext">');
					if(with_laststatus){
						div2Html = Better_locationTips({
							lon: thisRow.lon,
							lat: thisRow.lat,
							dateline: thisRow.lbs_report,
							isUser: true,
							poi: thisRow.poi
						});		
					}else{
						div2Html = '';
					}
					
					trHtml.push('<span class="time">'+$.trim(div2Html)+'</span>');
					trHtml.push('<span id="betterUserFuncDiv_'+thisRow.uid+'_'+id+'" class="action userAction betterUserFuncDiv" style="visibility:hidden"></span>');
					trHtml.push('</div>');*/
					trHtml.push('</td>');
					trHtml.push('</tr>'); 

					tbl.append(trHtml.join(''));
					
					//发私信按钮
					$('#betterMsgBtn_'+thisRow.uid).bind('click', {
						'uid': thisRow.uid,
						'nickname': thisRow.nickname,
						'text': '',
						'friend_sent_msg': thisRow.friend_sent_msg
					}, Better_SendMessage);
					
					//加/解除好友按钮
					var btn = $('#betterUserFriendBtn_'+thisRow.uid);
					if(btn.text()=='解除好友'){
						btn.bind('click', {
							'uid': thisRow.uid,
							'nickname': thisRow.nickname
						}, Better_Friend_Remove);
					}else{
						btn.bind('click', {
							'uid': thisRow.uid,
							'nickname': thisRow.nickname
						}, Better_Friend_Request);
					}
					
					//在首页显示动态
					$('#betterHomeshow_'+thisRow.uid).click(function(){
						if($(this).attr('checked')){
							var show = true;
						}else{
							var show = false;
						}
						Better_Set_Homeshow({
							'show': show,
							'fuid': $(this).attr('uid')
						});
					});
					//阻止/取消阻止按钮
					var blockBtn = $('#betterUserBlockBtn_'+thisRow.uid);
					if(blockBtn.text()=='取消阻止'){
						blockBtn.bind('click', {
							'uid': thisRow.uid,
							'nickname': thisRow.nickname
						}, Better_Unblock);
					}else{
						blockBtn.bind('click', {
							'uid': thisRow.uid,
							'nickname': thisRow.nickname
						}, Better_Block);
					}
					
				}
				
				/*trmouseenter = $.browser.opera ? $('tr') : $('#tbl_'+id+' tr.betterUserRow');			
				// 设置用户结果行鼠标上移效果
				trmouseenter.mouseenter(function(e){
					trRow = $.browser.opera ? $(this).find('ul') : $(this);

					if (betterUser.uid>0) {
						uid = trRow.attr('uid');
						gender = trRow.attr('gender');
						idKey = trRow.attr('id_key');
						nickname = trRow.attr('nickname');

						if (trRow.attr('uid')!=betterUser.uid) {
							Better_Row_Overed.push('betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key'));
							$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).empty();

							if (isFollowRequest) {
								
								aAllow = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterAllowFollowRequest_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).html(betterLang.global.follow.request.pass.title).bind('click', {
									uid: trRow.attr('uid'),
									nickname: nickname,
									gender: gender,
									tab: $('div.tabs ul.tabNavigation a[href="#'+id+'"]')
								}, Better_Allow_Follow);
								$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).append(aAllow);
								
								aReject = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterRejectFollowRequest_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).html(betterLang.global.follow.request.reject.title).bind('click', {
									uid: trRow.attr('uid'),
									nickname: nickname,
									gender: gender,
									tab: $('div.tabs ul.tabNavigation a[href="#'+id+'"]')
								}, Better_Reject_Follow);
								$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).append(aReject);
								
							} else {

								if ($.inArray('dmessage', btns)>=0 && $.inArray(trRow.attr('uid'), betterUser.blockedby)<0) {
									//	发送私信的链接
									aMessage = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterMsgBtn_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).html(betterLang.global.send_msg).bind('click', {
										uid: trRow.attr('uid'),
										nickname: nickname,
										gender: gender,
										id: 'betterMsgBtn_',
										text: ''
									}, Better_SendMessage);
									$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).append(aMessage);
								}
	
								if ($.inArray('follow', btns)>=0 && $.inArray(trRow.attr('uid'), betterUser.blockedby)<0) {
									//	关注此人的链接
									if ($.inArray(trRow.attr('uid'), betterUser.blockedby)<0) {
										aFollow = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterUserBtn_'+trRow.attr('uid'));
										afterUnfollowCallback = (typeof(callbacks.afterUnfollowCallback)!='undefined' && $.isFunction(callbacks.afterUnfollowCallback)) ? callbacks.afterUnfollowCallback : function(){};

										if ($.inArray(trRow.attr('uid'), betterUser.followings)>=0) {
											aFollow.html(betterLang.global.follow.cancel).bind('click', {
												uid:trRow.attr('uid'), 
												id:'betterUserBtn_',
												gender: gender,
												nickname: nickname,
												afterUnfollowCallback: afterUnfollowCallback
											}, Better_Unfollow);
										} else {
											aFollow.html(betterLang.global.follow.title).bind('click', {
												uid:trRow.attr('uid'), 
												id:'betterUserBtn_',
												nickname: nickname,
												gender: gender,
												afterUnfollowCallback: afterUnfollowCallback
											}, Better_Follow);					
										}
										$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).append(aFollow);
									}
								}
	
								if ($.inArray('block', btns)>=0) {
									//	阻止此人的链接
									aBlock = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterUserBlockBtn_'+trRow.attr('uid'));
									
									if ($.inArray(trRow.attr('uid'), betterUser.blocks)>=0) {
										aBlock.html(betterLang.global.block.cancel).bind('click', {
											uid: trRow.attr('uid'),
											id: 'betterUserBlockBtn_',
											nickname: nickname,
											gender: gender
										}, Better_Unblock);
									} else {
										aBlock.html(betterLang.global.block.title).bind('click', {
											uid: trRow.attr('uid'),
											gender: gender,
											nickname: nickname,
											id: 'betterUserBlockBtn_'
										}, Better_Block);
									}
									$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).append(aBlock);
								}

								if ($.inArray('friend', btns)>=0 && $.inArray(trRow.attr('uid'), betterUser.blockedby)<0) {
									//	加为好友的链接
									aFriend = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterUserFriendBtn_'+trRow.attr('uid'));

									if ($.inArray(trRow.attr('uid'), betterUser.friends)>=0) {
										aFriend.html(betterLang.global.friend.request.remove).bind('click', {
											uid: trRow.attr('uid'),
											gender: gender,
											nickname: nickname,
											id: 'btnUserFriendBtn_',
											closeCallback: function(){
												$('#better_user_row_'+trRow.attr('uid')+'_'+id).fadeOut();
											}
										}, Better_Friend_Remove);
									} else {
										aFriend.html(betterLang.global.friend.request.title).bind('click', {
											uid: trRow.attr('uid'),
											gender: gender,
											nickname: nickname,
											id: 'btnUserFriendBtn_'
										}, Better_Friend_Request);									
									}
									$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+trRow.attr('id_key')).append(aFriend);
								}
								
								if ($.inArray('friend_request', btns)>=0 && $.inArray(trRow.attr('uid'), betterUser.blockedby)<0) {
									//	对方申请加好友的链接
									aFriendRequest = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterUserFriendRequestBtn_'+trRow.attr('uid')+'_'+trRow.attr('id_key'));
									if ($.inArray(trRow.attr('uid'), betterUser.friends)<0) {
										aFriendRequest.html(betterLang.global.friend.confirm.agree).bind('click', {
											uid: trRow.attr('uid'),
											gender: gender,
											nickname: nickname,
											notify: betterLang.global.friend.confirm.agree_notify
										}, Better_Friend_Request);
										//$('#betterUserFuncDiv_'+uid).append(aFriendRequest);
									}
								}
							}
						}
						
						$('#betterUserFuncDiv_'+trRow.attr('uid')+'_'+idKey).css('visibility', 'visible');

					}

				}).mouseleave(function(){
					trRow = $.browser.opera ? $(this).find('ul') : $(this);
					if (betterUser.uid>0) {
						uid = trRow.attr('uid');
						gender = trRow.attr('gender');
						idKey = trRow.attr('id_key');
						nickname = trRow.attr('nickname');
						
						$('#betterUserFuncDiv_'+uid+'_'+idKey).css('visibility', 'hidden');
					}
				});	*/	
			} 
			
			//	如果有结果集为空的回调函数
			if (luJson.count==0 && $.isFunction(callbacks.emptyCallback)) {
				code = typeof(luJson.code)!='undefined' ? luJson.code : '';
				callbacks.emptyCallback(code);
			}

			//	如果有完成事件的回调函数
			if ($.isFunction(callbacks.completeCallback)) {
				callbacks.completeCallback(luJson);
			}
			
		} else { // if (Better_AjaxCheck
			$('#tbl_'+id).empty();
			if ($.isFunction(callbacks.errorCallback)) {
				code = typeof(luJson.code)!='undefined' ? luJson.code : '';
				callbacks.errorCallback(code);
			}			
		}			
	}, 'json');

}

/**
 * 加载一个消息列表
 * 
 * @param options 参数
 * @return
 */
function Better_loadBlogs(options)
{
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
	
	if(Better_WaitForLastAjax()){
		return false;
	}
	
	 withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;;
	 withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	 withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
	 withPhoto = typeof(options.withPhoto)!='defined' ? options.withPhoto : true;	
	 withFavLinks = typeof options.withFavLinks!='undefined' ? options.withFavLinks: false;
	if (page<=1) {
		Better_Pager_Reset(id);	
	}
	
	/**
	 * ff和chrome把所有ajax都看成不可缓存的，
	 * 而ie则会试图去缓存ajax
	 * 那么，ie下就有可能造成stack overflow
	 * 然后，试图让ie不缓存ajax?
	 */
	//posts.noCache = new Date().getTime();
	if (posts.page == 1 && (id == 'followings' || id == 'doing') && needRef != true ) {
		Better_Ajax_processing = false;
		_callback1(_page_1);
		needRef = true;
	} else {
		Better_Ajax_processing = true;
		$.getJSON(url, posts, _callback1);		
	}
}


/**
 * 回调函数 
 */
function _callback1(listJson){
	var tr;
	var array_tr = new Array();
	
	if (id == 'doing') {
		tbl.empty();
		had_load_id = new Array();
	}
	
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

			// 渲染结果行
			var showInList=true;
			if (rows!=null) {
				for(i=0; i<rows.length; i++){	
					if(typeof rows[i].comment!='undefined' && rows[i].comment==true){
						var content = rows[i].content;
						tbl.append('<tr><td style="width:100%;" colspan="2">'+content+'</td></tr>');
						continue;
					}
					if(id=='tips' && i>2 &&rows.length>3 &&nowPage==1){//做一个截断						
						$('#pager_tips').hide();
						$('#showalltips').show();
						showInList=false;
					}
					tbid = $.trim(rows[i].bid);
					if($.inArray(tbid,had_load_id)>=0){
						continue;
					} else {
						had_load_id.push(tbid);
					}
					
					switch ($.trim(rows[i].type)) {
						case 'checkin':
							tr = Better_parseCheckinRow(rows[i], {
								id: id,
								withAvatar: withAvatar,
								withMyFuncLinks: withMyFuncLinks,
								withHisFuncLinks: withHisFuncLinks,
								withPhoto: withPhoto,
								inFavList: inFavList
							});
							break;
						case 'tips':
							tr = Better_parseTipsRow(rows[i], {
								id: id,
								withAvatar: withAvatar,
								withMyFuncLinks: withMyFuncLinks,
								withHisFuncLinks: withHisFuncLinks,
								withPhoto: withPhoto,
								inFavList: inFavList,
								showInList:showInList,
								withFavLinks: withFavLinks
							});
							break;
						case 'todo':
							tr = Better_parseTodoRow(rows[i], {
								id: id,
								withAvatar: withAvatar,
								withMyFuncLinks: withMyFuncLinks,
								withHisFuncLinks: withHisFuncLinks,
								withPhoto: withPhoto,
								inFavList: inFavList,
								withFavLinks: withFavLinks
							});
							break;
						case 'normal':
						default:
							tr = Better_parseBlogRow(rows[i], {
								id: id,
								withAvatar: withAvatar,
								withMyFuncLinks: withMyFuncLinks,
								withHisFuncLinks: withHisFuncLinks,
								withPhoto: withPhoto,
								inFavList: inFavList
							}, (typeof listJson.rts!='undefined' && typeof listJson.rts[rows[i].upbid]!='undefined' && listJson.rts[rows[i].upbid]) ? listJson.rts[rows[i].upbid] : '');
							break;								
					}
					
					tbl.append(tr);			

					if (parseRt==true && typeof(rows[i])!='undefined' && rows[i].type=='normal' && rows[i].upbid!='0' && typeof(listJson.rts)!='undefined') {
						uprows = typeof(listJson.rts[rows[i].upbid])!='undefined' ? listJson.rts[rows[i].upbid] : {bid: ''};
						if (uprows.bid!='') {
							switch (uprows.type) {
								case 'tips':
									tr = Better_parseRtTipsRow(uprows, {
										withAvatar: withAvatar,
										withMyFuncLinks: withMyFuncLinks,
										withHisFuncLinks: (uprows.uid==betterUser.uid ? false : true),
										isRt: true
									});										
									break;
								case 'checkin':
									tr = Better_parseRtCheckinRow(uprows, {
										withAvatar: withAvatar,
										withMyFuncLinks: withMyFuncLinks,
										withHisFuncLinks: (uprows.uid==betterUser.uid ? false : true),
										isRt: true
									});									
									break;
								case 'todo':
									tr = Better_parseRtTodoRow(uprows, {
										withAvatar: withAvatar,
										withMyFuncLinks: withMyFuncLinks,
										withHisFuncLinks: (uprows.uid==betterUser.uid ? false : true),
										isRt: true
									});
									break;
								case 'normal':
								default:
									tr = Better_parseRtBlogRow(uprows, {
										withAvatar: withAvatar,
										withMyFuncLinks: withMyFuncLinks,
										withHisFuncLinks: (uprows.uid==betterUser.uid ? false : true),
										isRt: true
									});									
									break;
								}

							tbl.append(tr);
						}
					}
					
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
}



/**
 * 解析围脖来源
 * 
 * @param source
 * @return
 */
function Better_parseBlogSource(source)
{
	downurl = '';
	switch($.trim(source)) {
		case 'PPC':
		case 'ppc':
		case 'WM':	
		case 'win':
			downurl = 'wmdownload';
			result = betterLang.global.blog.source.win;
			break;
		case 'S60':
			downurl = 's60download';
			result = betterLang.global.blog.source.s60;
			break;
		case 'UIQ':
			result = betterLang.global.blog.source.uiq;
			break;
		case 'SPN':
			result = betterLang.global.blog.source.spn;
			break;
		case 'BRW':
			result = betterLang.global.blog.source.brw;
			break;
		case 'PLM':
			result = betterLang.global.blog.source.plm;
			break;
		case 'J2M':
		case 'j2m':
			result = betterLang.global.blog.source.j2m;
			break;
		case 'BlackBerry':
			downurl = 'blackberrydownload';
			result = betterLang.global.blog.source.blackberry;
			break;
		case 'IFN':
		case 'iPhone':
			downurl = 'iphonedownload';
			result = betterLang.global.blog.source.ifn;
			break;
		case 'AND':
			downurl = 'androiddownload';
			result = betterLang.global.blog.source.and;
			break;
		case 'msn':
			result = betterLang.global.blog.source.msn;
			break;
		case 'mobile':
			result = betterLang.global.blog.source.cell;
			break;
		case 'sms':
			result = betterLang.global.blog.source.sms;
			break;
		case 'mms':
			result = betterLang.global.blog.source.mms;
			break;
		case 'java':
			result = betterLang.global.blog.source.java;
			break;
		case 'Better':
		case betterLang.global.blog.source.kai:
		case 'kai':
		case 'web':
			result = betterLang.global.blog.source.web;
			break;
		case 'html5':
			result = betterLang.global.blog.source.html5;
			break;
		case 'api':
			result = betterLang.global.blog.source.api;
			break;			
		default:
			result = source;
			break;
	}
	if(downurl.length>0){
		result = "<a href='/tools/"+downurl+"' class='place'>"+result+"</a>";
	}
	return result;
}

/**
 * 解析一个吼吼的row
 * 
 * @param data
 * @param options
 * @return
 */
function Better_parseBlogRow(data, options, uprow)
{
	var options = typeof(options)=='object' ? options : {};
	var id = typeof(options.id)!='undefined' ? options.id : '';
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
	var withPhoto = typeof(options.withPhoto)!='undefined' ? options.withPhoto : true;
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	var inFavList = typeof(options.inFavList)!='undefined' ? options.inFavList : false;
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';
	uprow = typeof uprow!='undefined'? uprow: '';
	
	if(typeof data=='undefined' || !data || typeof data.bid=='undefined' || !data.bid){
		return ;
	}
	
	var bid = data.bid;
	var bid_key = bid.replace('.', '_');
	
	var arr = new Array();
	arr.push('<tr class="listRow" id="listRow_'+id+'_'+bid_key+'" uid="'+data.uid+'" priv="'+data.priv+'" tblId="'+id+'" bid="'+bid+'" protected="'+data.priv_blog+'">');
	if (withAvatar==true) {
		arr.push('<td class="avatar icon"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'"><img onerror="this.src=Better_AvatarOnError" class="avatar pngfix" src="'+data.avatar_thumb+'" alt="" width="48" /></a></td>')
	}
	arr.push('<td class="info">');
	arr.push('<div class="text"></div>');
	arr.push('<div class="status message_row"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> '+'<span id="message_'+bid_key+'" class="message_row">'+Better_parseMessage(data)+'</span>');
	if ((withPhoto==true && data.attach && data.attach_thumb) || (typeof(data.badge_detail)!='undefined' && data.badge_id>0)) {
		arr.push('<div class="info">');
		if(typeof(data.badge_detail)!='undefined' && data.badge_id>0){
			arr.push('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" title="" class="badge_fcbox" uid="'+data.uid+'" bid="'+data.badge_id+'"><img class="badge_attach pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>');
			arr.push('<div style="display:none;">'+Better_Badge_Detail_Row(data, 'listRow_'+id).html()+'</div>');
		}
		if(withPhoto==true && data.attach && data.attach_thumb){
			arr.push('<a href="'+data.attach_url+'" class="attach_href"><img id="attach_'+bid_key+'" class="attach pngfix" onerror="this.src=Better_ImgOnError" src="'+data.attach_tiny+'" alt="" ref="'+data.attach+'" /></a>');
		}
		arr.push('</div>');
	}
	arr.push('</div>');
	
	div4_html = Better_locationTips({
		lon: data.lon,
		lat: data.lat,
		tips: data.user_poi==0 ? '' : data.location_tips,
		dateline: data.dateline,
		poi: typeof(data.user_poi)!='undefined' ? data.user_poi : data.poi
	});
	div4_html += '  ';
	
	if(typeof data.source!='undefined' && data.source){
		source = '<span class="source">'+betterLang.global.blog.by;
		source += Better_parseBlogSource(data.source);
		source += '</span>';
		div4_html += source;	
	}
	
	arr.push('<div class="ext"><span class="action listRowFuncs" id="blogListRowFuncDiv_'+id+'_'+bid_key+'"></span><span class="time" id="listRowAddress_'+bid_key+'">'+div4_html+'</span></div>');
	
	arr.push('</td>');
	
	/*arr.push('<td style="width:50px;">');
	
	if (typeof(data.badge_detail)!='undefined' && data.badge_id>0) {
		arr.push('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" title="" class="badge_fcbox" uid="'+data.uid+'" bid="'+data.badge_id+'"><img class="pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>')
		arr.push('<div style="display:none;">'+Better_Badge_Detail_Row(data, 'listRow_'+id).html()+'</div>');
	} 
	
	arr.push('</td>');*/
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
	
	tr.find('a.badge_fcbox').click(function(){
		BETTER_BIG_BADGE_ID = parseInt($(this).attr('bid'));
		BETTER_BIG_BADGE_UID = parseInt($(this).attr('uid'));
	}).fancybox({
		autoDimensions: true,
		scrolling: 'no',
		centerOnScroll: true,
		'onStart' : function(){
			$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
			$('#fancybox-outer').css('background-color', '#1db8ee');
			if ($('#list_badge_users_listRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
				Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_listRow_'+id);
			}			
		},
		'onClosed': function(){
			$('#fancybox-outer').css('background-color', '#fff');
		}
	});	

	// 设置消息行鼠标上移效果
	//tr.mouseenter(function(){
		//thisRow = $(this);
		var thisRow = tr;
		
		if (betterUser.uid>0) {

			var tblId = thisRow.attr('tblId');
			var bid = thisRow.attr('bid');
			var bid_key = bid.replace('.', '_');
			var uid = thisRow.attr('uid');

			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;
		
			var funcDiv = thisRow.find('#blogListRowFuncDiv_'+tblId+'_'+bid_key);
			
			if (funcDiv.html()=='') {
				funcDiv.empty();
				
				//删除
				if (withMyFuncLinks==true && uid==betterUser.uid) {	
					try {
						//afterDeleteCallback = $.isFunction(callbacks.afterDeleteCallback) ? callbacks.afterDeleteCallback : function(){};//这边导致上面判断出错
						afterDeleteCallback = callbacks.afterDeleteCallback;
						var a = $(document.createElement('a')).attr('title',betterLang.global.blog.delete_it).attr('href', 'javascript:void(0)').bind('click', {
									bid: bid, 
									id:'listRow_'+tblId+'_'+bid_key,
									afterDeleteCallback: afterDeleteCallback
								}, Better_Delblog);
						a.css('background', 'url("/images/action.png") repeat scroll 0px 0px transparent');
						a.css('width', '14px');
						a.css('height','14px');
						a.css('margin','3px 5px 0 0');
						funcDiv.append(a);
					} catch (eee3) {
						if (Better_InDebug) {
							Better_Notify({
								msg: 'In Delete:'+eee3.message
							});
						}						
					}
				}

				if (withHisFuncLinks==true && priv!='private') {

					try {
						//	转发
						var a = $(document.createElement('a')).attr('href', 'javascript:void(0);').attr('title',betterLang.global.blog.rt);
						a.css('background', 'url("/images/action.png") repeat scroll -15px 0px transparent');
						a.css('width', '14px');
						a.css('height','14px');
						a.css('margin','3px 5px 0 0');
						if(typeof data.upbid!='undefined' && data.upbid!=0 && uprow.priv=='public'){
							a.click(function(){
								var upbid_key = data.upbid.replace('.', '_');
								var from = uprow ? (uprow.type ? uprow.type : 'normal') : 'normal';
								msg="";
								if(from == 'checkin'){
									msg = $('#checkin_msg_'+upbid_key).html();
								}else if(from == 'todo'){
									msg = $('#todo_msg_'+upbid_key).html();
								}else{
									msg = $('#message_'+upbid_key).html()
								}
									
								var params = {
									msg: msg,
									nickname: $('#nickname_'+upbid_key).text(),
									username: $('#nickname_'+upbid_key).attr('username'),
									address: $('#listRowAddress_'+upbid_key).html(),
									attach: $('#attach_'+upbid_key).attr('ref'),
									from: from,
									bid_key: upbid_key,
									bid: data.upbid,
									with_upbid: true,
									now_message:$('#message_'+bid_key).text(),
									now_nickname: $('#nickname_'+bid_key).text(),
									now_bid: bid,
									allow_rt: data.allow_rt
								};
								
								Better_Transblog({'data': params});
							});
						}else{
							a.click(function(){
								var params = {
										msg: $('#message_'+bid_key).html(),
										nickname: $('#nickname_'+bid_key).text(),
										username: $('#nickname_'+bid_key).attr('username'),
										uid: uid,
										address: $('#listRowAddress_'+bid_key).html(),
										attach: $('#attach_'+bid_key).attr('ref'),
										from: 'normal',
										bid_key: bid_key,
										bid: bid,
										now_bid: bid,
										allow_rt: data.allow_rt
									};
								
								Better_Transblog({'data': params});
							});
						}
	
						funcDiv.append(a).append(' ');	
					} catch (eee) {
						
					}
					
				}
				
				//	收藏
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
								id: 'favoritesFuncA_'+tblId+'_',
								row_id: 'listRow_'+tblId+'_'+bid_key,
								inFavList: inFavList,
								tbl_id: id,
								type: 'normal',
								afterUnfavoriteCallback: afterUnfavoriteCallback
							}, Better_UnFavoriteblog);
						} else {
							a.css('background', 'url("/images/action.png") repeat scroll -32px 0px transparent');
							a.attr('title',betterLang.global.favorite.title).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid,
								id: 'favoritesFuncA_'+tblId+'_',
								row_id: 'listRow_'+tblId+'_'+bid_key,
								inFavList: inFavList,
								tbl_id: id,
								type: 'normal',
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
	
	return tr;
}



/**
 * 解析一个我想去的row
 * 
 * @param data
 * @param options
 * @param uprow
 * @return
 */
function Better_parseTodoRow(data, options, uprow)
{
	var options = typeof(options)=='object' ? options : {};
	var id = typeof(options.id)!='undefined' ? options.id : '';
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
	var withPhoto = typeof(options.withPhoto)!='undefined' ? options.withPhoto : true;
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	var inFavList = typeof(options.inFavList)!='undefined' ? options.inFavList : false;
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';
	uprow = typeof uprow!='undefined'? uprow: '';
	var inList = (typeof(data.inlist)!='undefined' && data.inlist==true )? true:false;
	if(typeof data=='undefined' || !data || typeof data.bid=='undefined' || !data.bid){
		return ;
	}
	
	var bid = data.bid;
	var bid_key = bid.replace('.', '_');
	
	var arr = new Array();
	arr.push('<tr class="listRow" id="listRow_'+id+'_'+bid_key+'" uid="'+data.uid+'" priv="'+data.priv+'" tblId="'+id+'" bid="'+bid+'" protected="'+data.priv_blog+'">');
	if (withAvatar==true) {
		arr.push('<td class="avatar icon"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'"><img onerror="this.src=Better_AvatarOnError" class="avatar pngfix" src="'+data.avatar_thumb+'" alt="" width="48" /></a></td>')
	}
	arr.push('<td class="info">');
	todo_content = Better_parseMessage(data);
	if(data.message ==""){
		todo_content = "&nbsp;"+todo_content;
	}else{
		todo_content = ":&nbsp;"+todo_content;
	}
	div4_html = Better_locationTodo({
		lon: data.lon,
		lat: data.lat,
		tips: data.user_poi==0 ? '' : data.location_tips,
		dateline: data.dateline,
		poi: typeof(data.user_poi)!='undefined' ? data.user_poi : data.poi,
		content:todo_content
	});
	div4_html += '  ';
	
	if(typeof data.source!='undefined' && data.source){
		source = '<span class="source">'+betterLang.global.blog.by;
		source += Better_parseBlogSource(data.source);
		source += '</span>';
		div4_html += source;	
	}	
	todopoi =  typeof(data.user_poi)!='undefined' ? data.user_poi : data.poi;
	poi_html = '<a href="/poi/'+todopoi.poi_id+'" class="place">'+ data.location_tips +'</a><span  style="color:#000">'+todo_content+'</span>';
	arr.push('<div class="status message_row"><a href="/'+data.username+'#'+
				Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+
				data.username+'">'+data.nickname+'</a> '+'想去&nbsp;'+poi_html);
	arr.push('</div>');
	arr.push('<div class="ext"><span class="action listRowFuncs" id="blogListRowFuncDiv_'+id+'_'+bid_key+'"></span><span class="time" id="listRowAddress_'+bid_key+'">'+div4_html+'</span></div>');
//	arr.push('<div class="status message_row"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> '+'<span id="message_'+bid_key+'" class="message_row">'+Better_parseMessage(data)+'</span>');
	if ((withPhoto==true && data.attach && data.attach_thumb) || (typeof(data.badge_detail)!='undefined' && data.badge_id>0)) {
		arr.push('<div class="info">');
		if(typeof(data.badge_detail)!='undefined' && data.badge_id>0){
			arr.push('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" title="" class="badge_fcbox" uid="'+data.uid+'" bid="'+data.badge_id+'"><img class="badge_attach pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>');
			arr.push('<div style="display:none;">'+Better_Badge_Detail_Row(data, 'listRow_'+id).html()+'</div>');
		}
		if(withPhoto==true && data.attach && data.attach_thumb){
			arr.push('<a href="'+data.attach_url+'" class="attach_href"><img id="attach_'+bid_key+'" class="attach pngfix" onerror="this.src=Better_ImgOnError" src="'+data.attach_tiny+'" alt="" ref="'+data.attach+'" /></a>');
		}
		arr.push('</div>');
	}
	
	

	arr.push('</td>');
	
	/*arr.push('<td style="width:50px;">');
	
	if (typeof(data.badge_detail)!='undefined' && data.badge_id>0) {
		arr.push('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" title="" class="badge_fcbox" uid="'+data.uid+'" bid="'+data.badge_id+'"><img class="pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>')
		arr.push('<div style="display:none;">'+Better_Badge_Detail_Row(data, 'listRow_'+id).html()+'</div>');
	} 
	
	arr.push('</td>');*/
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
	
	tr.find('a.badge_fcbox').click(function(){
		BETTER_BIG_BADGE_ID = parseInt($(this).attr('bid'));
		BETTER_BIG_BADGE_UID = parseInt($(this).attr('uid'));
	}).fancybox({
		autoDimensions: true,
		scrolling: 'no',
		centerOnScroll: true,
		'onStart' : function(){
			$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
			$('#fancybox-outer').css('background-color', '#1db8ee');
			if ($('#list_badge_users_listRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
				Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_listRow_'+id);
			}			
		},
		'onClosed': function(){
			$('#fancybox-outer').css('background-color', '#fff');
		}
	});	

	// 设置消息行鼠标上移效果
	//tr.mouseenter(function(){
		//thisRow = $(this);
		var thisRow = tr;
		
		if (betterUser.uid>0) {

			var tblId = thisRow.attr('tblId');
			var bid = thisRow.attr('bid');
			var bid_key = bid.replace('.', '_');
			var uid = thisRow.attr('uid');

			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;
		
			var funcDiv = thisRow.find('#blogListRowFuncDiv_'+tblId+'_'+bid_key);
			
			if (funcDiv.html()=='') {
				funcDiv.empty();

				
				if (withMyFuncLinks==true && uid==betterUser.uid) {	
					try {
						//afterDeleteCallback = $.isFunction(callbacks.afterDeleteCallback) ? callbacks.afterDeleteCallback : function(){};//这边导致上面判断出错
						afterDeleteCallback = callbacks.afterDeleteCallback;					
						if(inList == true){
							var a = $(document.createElement('a')).text('不想去了').attr('title',betterLang.global.blog.delete_it).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid, 
								id:'listRow_'+tblId+'_'+bid_key,
								afterDeleteCallback: afterDeleteCallback,
								msg: '确认不去这一地方了吗？',
								status : "nottodo",
								type: 'cancel_todo'
							}, Better_Delblog);
							var a1 = $(document.createElement('a')).text('已去过了').attr('title',betterLang.global.blog.delete_it).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid, 
								id:'listRow_'+tblId+'_'+bid_key,
								afterDeleteCallback: afterDeleteCallback,
								msg: '确认已经去过这一地方了？',
								status : "beenhere",
								type: 'cancel_todo'
							}, Better_Delblog);
							funcDiv.append(a1).append(a);
						}else{
							var a = $(document.createElement('a')).attr('title',betterLang.global.blog.delete_it).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid, 
								id:'listRow_'+tblId+'_'+bid_key,
								afterDeleteCallback: afterDeleteCallback,
								msg: '确认要删除吗？'
							}, Better_Delblog);
							a.css('background', 'url("/images/action.png") repeat scroll 0px 0px transparent');
							a.css('width', '14px');
							a.css('height','14px');
							a.css('margin','3px 5px 0 0');
							funcDiv.append(a);
						}			
					} catch (eee3) {
						if (Better_InDebug) {
							Better_Notify({
								msg: 'In Delete:'+eee3.message
							});
						}						
					}
				}
				

				
				if (withHisFuncLinks==true && priv=='public' ) {
					//	转发
					var a = $(document.createElement('a')).attr('title',betterLang.global.blog.rt).attr('href', 'javascript:void(0);');
					a.css('background', 'url("/images/action.png") repeat scroll -15px 0px transparent');
					a.css('width', '14px');
					a.css('height','14px');
					a.css('margin','3px 5px 0 0');
					a.click(function(){
						var params = {
								msg: $('#listRow_followings_'+bid_key+" .message_row").html(),
								nickname: $('#nickname_'+bid_key).text(),
								username: $('#nickname_'+bid_key).attr('username'),
								uid: uid,
								from: 'todo',
								bid_key: bid_key,
								now_bid: bid,
								allow_rt: data.allow_rt	
						};
						Better_Transblog({'data': params});
					});
					
					funcDiv.append(a).append(' ');		
				}
				
				
				//	收藏
				if (withMyFuncLinks==true && inList==false) {
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
								id: 'favoritesFuncA_'+tblId+'_',
								row_id: 'listRow_'+tblId+'_'+bid_key,
								inFavList: inFavList,
								tbl_id: id,
								type: 'normal',
								afterUnfavoriteCallback: afterUnfavoriteCallback
							}, Better_UnFavoriteblog);
						} else {
							a.css('background', 'url("/images/action.png") repeat scroll -32px 0px transparent');
							a.attr('title',betterLang.global.favorite.title).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid,
								id: 'favoritesFuncA_'+tblId+'_',
								row_id: 'listRow_'+tblId+'_'+bid_key,
								inFavList: inFavList,
								tbl_id: id,
								type: 'normal',
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
				
				if(widthCommentLinks && inList==false){
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

	
	return tr;
}

/**
 * 解析一个转发To-do的row
 * 
 * @param data
 * @param options
 * @return
 */
function Better_parseRtTodoRow(data, options)
{
	
	var options = typeof(options)=='object' ? options : {};
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	data.poi = typeof(data.poi)!='undefined' ? data.poi : {};
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';

	var bid = data.bid;
	var bid_key = bid.replace('.', '_');

	var tr = $('<tr id="rtBlogListRow_'+id+'_'+bid_key+'" priv="'+data.priv+'" protected="'+data.priv_blog+'" class="listRow"></tr>');

	if (withAvatar==true) {
		tr.append('<td style="width:48px;"></td>');
	}
	todo_content = Better_parseMessage(data);
	if(data.message==""){
//		todo_content = "";
	}else{
		todo_content = ":&nbsp;"+todo_content;
	}

	var td1 = $('<td class="rt_info"><div class="rt_avatar"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'"><img class="avatar zoompng" onerror="this.src=Better_AvatarOnError" src="'+data.avatar_url+'" alt="" /></a></div></td>');

	var div1 = $('<div class="rt_text"></div>');
	
	div4_html = Better_locationTodo({
		dateline: data.dateline
	});
	div4_html += '  ';
	
	if(typeof data.source!='undefined' && data.source){
		source = '<span class="source">'+betterLang.global.blog.by;
		source += Better_parseBlogSource(data.source);
		source += '</span>';
		div4_html += source;	
	}	

//	var funcDiv = $(document.createElement('span')).addClass('action').attr('id', 'rtBlogListRowFuncDiv_'+id+bid_key).addClass('listRowFuncs').empty();
	todopoi =  typeof(data.user_poi)!='undefined' ? data.user_poi : data.poi;
	poi_html = '<a href="/poi/'+todopoi.poi_id+'" class="place">'+ data.location_tips +'</a><span  style="color:#000">'+todo_content+'</span>';
	var div2 = $('<div class="rt_status rt_message_row" id="todo_msg_'+bid_key+'"><a href="/'+data.username+'#'+
			Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+
			data.username+'">'+data.nickname+'</a> '+'想去&nbsp;'+poi_html+"</div>");
	div2.append('<div class="ext"><span class="action listRowFuncs" id="rtBlogListRowFuncDiv_'+id+bid_key+'"></span><span class="time" id="listRowAddress_'+bid_key+'">'+div4_html+'</span></div>');

	td1.append(div2);

	tr.append(td1);
	tr.find('a.badge_users_page').click(function(){
		badgeId = parseInt($(this).attr('bid'));
		direct = $(this).attr('direct');
		pf = $(this).attr('pf');
		uid = $(this).attr('uid');
		
		nextPage = direct=='next' ? parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())+1 : parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())-1;
		 $('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
		Better_Badge_Users(badgeId, uid, nextPage, pf);
	});
	
	// 设置消息行鼠标上移效果
	//tr.mouseenter(function(){
		if (betterUser.uid>0) {
			//thisRow = $(this);
			var thisRow = tr;
			var tmp = thisRow.attr('id').split('_');
			var bid = tmp[2]+'.'+tmp[3];
			var uid = tmp[2];
			var bid_key = bid.replace('.', '_');
			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;

			var funcDiv = thisRow.find('#rtBlogListRowFuncDiv_'+id+bid_key);//rtBlogListRowFuncDiv_followings_175623_2983

			if (funcDiv.html()=='') {
				funcDiv.empty();				
			
				if (priv!='private') {
					//	转发
//					var a = $(document.createElement('a')).html(betterLang.global.blog.rt).attr('href', 'javascript:void(0);');
					var a = $(document.createElement('a')).attr('href', 'javascript:void(0);').attr('title',betterLang.global.blog.rt);
					a.css('background', 'url("/images/action.png") repeat scroll -15px 0px transparent');
					a.css('width', '14px');
					a.css('height','14px');
					a.css('margin','3px 5px 0 0');
					a.click(function(){
						var params = {
								msg: $('#rtBlogListRow_followings_'+bid_key+" .rt_message_row").html(),
								nickname: $('#nickname_'+bid_key).text(),
								username: $('#nickname_'+bid_key).attr('username'),
								uid: uid,
								from: 'todo',
								bid_key: bid_key,
								bid: bid,
								now_bid: bid,
								allow_rt: data.allow_rt	
							};
						Better_Transblog({'data': params});
					});
					funcDiv.append(a).append(' ');	
				}

				//收藏
				if (withMyFuncLinks==true && betterUser.uid) {
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
							inFavList: false,
							tbl_id: id,
							type: 'normal',
							afterUnfavoriteCallback: afterUnfavoriteCallback
						}, Better_UnFavoriteblog);
					} else {
						a.css('background', 'url("/images/action.png") repeat scroll -32px 0px transparent');
						a.attr('title',betterLang.global.favorite.title).attr('href', 'javascript:void(0)').bind('click', {
							bid: bid,
							id: 'favoritesFuncA_'+id+'_',
							row_id: 'listRow_'+id+'_'+bid_key,
							inFavList: false,
							tbl_id: id,
							type: 'normal',
							afterUnfavoriteCallback: afterUnfavoriteCallback
						}, Better_Favoriteblog);
					}

					funcDiv.append(a);

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
		}
	return tr;
}


/**
 * 解析一个转发吼吼的row
 * 
 * @param data
 * @param options
 * @return
 */
function Better_parseRtBlogRow(data, options)
{
	var options = typeof(options)=='object' ? options : {};
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	data.poi = typeof(data.poi)!='undefined' ? data.poi : {};
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';

	var bid = data.bid;
	var bid_key = bid.replace('.', '_');

	var tr = $('<tr id="rtBlogListRow_'+id+'_'+bid_key+'" priv="'+data.priv+'" protected="'+data.priv_blog+'" class="listRow"></tr>');

	if (withAvatar==true) {
		tr.append('<td style="width:48px;"></td>');
	}

	message = '<a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> '+'<span id="message_'+bid_key+'" class="message_row">'+Better_parseMessage(data)+'</span>';

	var td1 = $('<td class="rt_info"><div class="rt_avatar"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'"><img class="avatar zoompng" onerror="this.src=Better_AvatarOnError" src="'+data.avatar_url+'" alt="" /></a></div></td>');

	var div1 = $('<div class="rt_text"></div>');
	var div2 = $('<div class="rt_status rt_message_row">'+message+'</div>');
	
	if((data.attach && data.attach_thumb) || (typeof(data.badge_detail)!='undefined' && data.badge_id>0)){
		var div3 = $(document.createElement('div')).addClass('info');
		
		if(typeof(data.badge_detail)!='undefined' && data.badge_id>0){
			var badge = $('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" bid="'+data.badge_id+'" class="checkin_big_badge" onclick="return false" uid="'+data.uid+'"><img class="badge_attach pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>');
			badge.click(function(){
				BETTER_BIG_BADGE_ID = $(this).attr('bid');
				BETTER_BIG_BADGE_UID = $(this).attr('uid');
			}).fancybox({
				autoDimensions: true,
				scrolling: 'no',
				centerOnScroll: true,
				'onStart' : function(){
					$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
					$('#fancybox-outer').css('background-color', '#1db8ee');
					if ($('#list_badge_users_rtBlogListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
						Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_rtBlogListRow_'+id);
					}						
				},
				'onClosed': function(){
					$('#fancybox-outer').css('background-color', '#fff');
				}
			
			});	
			
			var badge_detail = Better_Badge_Detail_Row(data, 'rtBlogListRow_'+id);
			
			div3.append(badge).append(badge_detail);
		}

		if (data.attach && data.attach_thumb) {
			div3.append('<a href="'+data.attach_url+'" class="attach_href"><img id="attach_'+bid_key+'" class="attach pngfix" src="'+data.attach_tiny+'" alt="" ref="'+data.attach+'" /></a>');
			if ($.browser.opera) {
				div3.find('a').attr('target', '_blank');
			} else {
				div3.find('a').fancybox();
			}
			
			div3.find('img').error(function(){
				$(this).attr('src', Better_ImgOnError);
				$(this).parent().attr('href', Better_ImgOnError);
			});
		}	
		
		div2.append(div3);
	}
		
	
	var div4_html = Better_locationTips({
		lon: data.lon,
		lat: data.lat,
		tips: data.location_tips,
		dateline: data.dateline,
		poi: typeof(data.user_poi)!='undefined' ? data.user_poi : data.poi
	});
	div4_html += '  ';
	
	var source = '<span class="source">'+betterLang.global.blog.by;
	source += Better_parseBlogSource(data.source);
	source += '</span>';
	div4_html += source;

	var funcDiv = $(document.createElement('span')).addClass('action').attr('id', 'rtBlogListRowFuncDiv_'+id+bid_key).addClass('listRowFuncs').empty();
	var div4 = $(document.createElement('div')).addClass('ext').append(funcDiv).append('<span class="time" id="listRowAddress_'+bid_key+'">'+$.trim(div4_html)+'</span>');

	td1.append(div2).append(div4);

	tr.append(td1);
	//td2 = $(document.createElement('td'));
	/*if (data.badge_id>0 && typeof(data.badge_detail)!='undefined') {
		badge = $('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" bid="'+data.badge_id+'" class="checkin_big_badge" onclick="return false" uid="'+data.uid+'"><img src="'+data.badge_detail.picture.toString().replace('badges', 'badges/24/')+'" alt="" width="24" /></a>');
		badge.click(function(){
			BETTER_BIG_BADGE_ID = $(this).attr('bid');
			BETTER_BIG_BADGE_UID = $(this).attr('uid');
		}).fancybox({
			autoDimensions: true,
			scrolling: 'no',
			centerOnScroll: true,
			'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
				$('#fancybox-outer').css('background-color', '#1db8ee');
				if ($('#list_badge_users_rtBlogListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
					Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_rtBlogListRow_'+id);
				}						
			},
			'onClosed': function(){
				$('#fancybox-outer').css('background-color', '#fff');
			}
		
		});	
		
		badge_detail = Better_Badge_Detail_Row(data, 'rtBlogListRow_'+id);
		
		td2.append(badge);
		td2.append(badge_detail);
	} else {
		td2.append(' ');
	}*/
	
	tr.find('a.badge_users_page').click(function(){
		badgeId = parseInt($(this).attr('bid'));
		direct = $(this).attr('direct');
		pf = $(this).attr('pf');
		uid = $(this).attr('uid');
		
		nextPage = direct=='next' ? parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())+1 : parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())-1;
		 $('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
		Better_Badge_Users(badgeId, uid, nextPage, pf);
	});
	
	// 设置消息行鼠标上移效果
	//tr.mouseenter(function(){
		if (betterUser.uid>0) {
			//thisRow = $(this);
			var thisRow = tr;
			var tmp = thisRow.attr('id').split('_');
			var bid = tmp[2]+'.'+tmp[3];
			var uid = tmp[2];
			var bid_key = bid.replace('.', '_');
			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;

			var funcDiv = thisRow.find('[id="rtBlogListRowFuncDiv_'+id+bid_key+'"]');

			if (funcDiv.html()=='') {
				funcDiv.empty();				
			
				if (priv!='private') {
					//	转发
//					var a = $(document.createElement('a')).html(betterLang.global.blog.rt).attr('href', 'javascript:void(0);');
					var a = $(document.createElement('a')).attr('href', 'javascript:void(0);').attr('title',betterLang.global.blog.rt);
					a.css('background', 'url("/images/action.png") repeat scroll -15px 0px transparent');
					a.css('width', '14px');
					a.css('height','14px');
					a.css('margin','3px 5px 0 0');
					a.click(function(){
						var params = {
								msg: $('#message_'+bid_key).html(),
								nickname: $('#nickname_'+bid_key).text(),
								username: $('#nickname_'+bid_key).attr('username'),
								uid: uid,
								address: $('#listRowAddress_'+bid_key).html(),
								attach: $('#attach_'+bid_key).attr('ref'),
								from: 'normal',
								bid_key: bid_key,
								bid: bid,
								now_bid: bid,
								allow_rt: data.allow_rt	
							};
						Better_Transblog({'data': params});
					});
					funcDiv.append(a).append(' ');	
				}

				//收藏
				if (withMyFuncLinks==true && betterUser.uid) {
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
							inFavList: false,
							tbl_id: id,
							type: 'normal',
							afterUnfavoriteCallback: afterUnfavoriteCallback
						}, Better_UnFavoriteblog);
					} else {
						a.css('background', 'url("/images/action.png") repeat scroll -32px 0px transparent');
						a.attr('title',betterLang.global.favorite.title).attr('href', 'javascript:void(0)').bind('click', {
							bid: bid,
							id: 'favoritesFuncA_'+id+'_',
							row_id: 'listRow_'+id+'_'+bid_key,
							inFavList: false,
							tbl_id: id,
							type: 'normal',
							afterUnfavoriteCallback: afterUnfavoriteCallback
						}, Better_Favoriteblog);
					}

					funcDiv.append(a);

				}
				
				//评论
//				if(widthCommentLinks){
//					var a = $(document.createElement('a')).html(commentText).attr('href', 'javascript:void(0)').addClass('commentBtn_'+bid_key);
//					var comm_data = {
//							'bid': bid,
//							'pageSize': 10,
//							'row': thisRow
//					};
//					a.toggle(function(){Better_loadComments(comm_data);}, function(){Better_removeCommetnsList(thisRow);});
//					funcDiv.append(a);
//				}
				
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
			$(this).find('span[id^=rtBlogListRowFuncDiv_]').hide();
		}
	});	*/
	
	return tr;
}

/**
 * 解析一个贴士的row
 * 
 * @param data
 * @param options
 * @return
 */
function Better_parseTipsRow(tipsRow, options)
{
	var options = typeof(options)=='object' ? options : {};
	var id = typeof(options.id)!='undefined' ? options.id : '';
	var withAvatar = true;
	var withMyFuncLinks = true;//typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = true;//typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
	var withPhoto = typeof(options.withPhoto)!='undefined' ? options.withPhoto : true;
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	var inFavList = typeof(options.inFavList)!='undefined' ? options.inFavList: false;
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+tipsRow.comments+')';
	var withFavLinks = typeof options.withFavLinks!='undefined'? options.withFavLinks: false;
	var showInList = typeof options.showInList!='undefined'? options.showInList: true;
	
	var bid = tipsRow.bid;
	var bid_key = bid.replace('.', '_');

	var tr = $(document.createElement('tr')).attr('id', 'tipsListRow_'+id+'_'+bid_key).attr('ref', bid).addClass('listRow').attr('uid', tipsRow.uid).attr('tblId', id).attr('bid', bid).attr('up', tipsRow.up).attr('down', tipsRow.down);
	if(!showInList){
		tr.addClass('hide');
	}
	var td0 = $(document.createElement('td')).addClass('avatar').addClass('icon').html('<a href="/'+tipsRow.username+'#'+Better_GetAnchorByType(tipsRow.type)+'"><img src="'+tipsRow.avatar_thumb+'" class="pngfix zoompng" alt="" width="48" /></a>');
	td0.find('img').addClass('avatar').error(function(){
		$(this).attr('src', Better_AvatarOnError);
	});
	tr.append(td0);

	var td1 = $(document.createElement('td')).addClass('info');

	var div1 = $(document.createElement('div')).addClass('text');
	var div2 = $(document.createElement('div')).addClass('status').addClass('message_row');
	
	var poi =  typeof(tipsRow.user_poi)!='undefined' ? tipsRow.user_poi : tipsRow.poi;
	var result = typeof(tipsRow.location_tips)!='undefined' ? tipsRow.location_tips : '';
	if(typeof(poi.poi_id)!='undefined' && poi.poi_id!=0 && result!=''){
		var poi_detail = '<a href="/poi/'+poi.poi_id+'" class="place">'+ result +'</a> ';
	}
	
	if(withFavLinks){
		var message = '<a href="/'+tipsRow.username+'#'+Better_GetAnchorByType(tipsRow.type)+'" class="user" id="nickname_'+bid_key+'" username="'+tipsRow.username+'">'+tipsRow.nickname+'</a> '+
		'<span id="message_'+bid_key+'" class="message_row">'+Better_parseMessage(tipsRow)+'</span>';
	}else{
		var message = '<a href="/'+tipsRow.username+'#'+Better_GetAnchorByType(tipsRow.type)+'" class="user" id="nickname_'+bid_key+'" username="'+tipsRow.username+'">'+tipsRow.nickname+'</a> '+
		'在 '+poi_detail+' 发表贴士 ：'+
		'<span id="message_'+bid_key+'" class="message_row">'+Better_parseMessage(tipsRow)+'</span>';
	}

	div2.append(message);			
	div2.find('a.blank').attr('target', '_blank');
	
	if((typeof(tipsRow.badge_detail)!='undefined' && tipsRow.badge_id>0) || (withPhoto && tipsRow.attach && tipsRow.attach_thumb)){
		var div3 = $(document.createElement('div')).addClass('info');
		if(typeof(tipsRow.badge_detail)!='undefined' && tipsRow.badge_id>0){
			var badge_id = tipsRow.badge_id;
			var uid = tipsRow.uid;		
			
			try {
				var data = tipsRow.badge_detail;
			}catch(EEE) {
				
			}


			var badge = $('<a href="#bigbadge_row_'+badge_id+'_'+uid+'" title="" onclick="return false;" bid="'+badge_id+'" uid="'+uid+'"><img class="badge_attach pngfix zoompng" width="50" src="'+tipsRow.badge_detail.picture+'" alt="" /></a>');
			badge.click(function(){
				BETTER_BIG_BADGE_ID = $(this).attr('bid');
				BETTER_BIG_BADGE_UID = $(this).attr('uid');
			}).fancybox({
				autoDimensions: true,
				scrolling: 'no',
				centerOnScroll: true,			
				'onStart' : function(){
					$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
					$('#fancybox-outer').css('background-color', '#1db8ee');
					
					if ($('#list_badge_users_tipsListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
						Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_tipsListRow_'+id);
					}						
				},
				'onClosed': function(){
					$('#fancybox-outer').css('background-color', '#fff');
				}
			
			});	
			
			var badge_detail = Better_Badge_Detail_Row(tipsRow, 'tipsListRow_'+id);
			
			div3.append(badge).append(badge_detail);
		}
		
		if(withPhoto && tipsRow.attach && tipsRow.attach_thumb){
			div3.append('<a href="'+tipsRow.attach_url+'" class="attach_href"><img id="attach_'+bid_key+'" class="attach  pngfix zoompng"  max-width="80" src="'+tipsRow.attach_tiny+'" alt="" ref="'+tipsRow.attach+'" /></a>');
			if ($.browser.opera) {
				div3.find('a.attach_href').attr('target', '_blank');
			} else {
				div3.find('a.attach_href').fancybox();
			}
			
			div3.find('img').error(function(){
				$(this).attr('src', Better_ImgOnError);
				$(this).parent().attr('href', Better_ImgOnError);
			});
		}
		div2.append(div3);
	}
	
	var div4 = $(document.createElement('div')).addClass('ext');
	
	if(withFavLinks){
		var div4_html = Better_locationTips({
			lon: tipsRow.lon,
			lat: tipsRow.lat,
			tips: tipsRow.location_tips,
			dateline: tipsRow.dateline,
			poi: typeof(tipsRow.user_poi)!='undefined' ? tipsRow.user_poi : tipsRow.poi
		});
	}else{
		if(tipsRow.dateline>0){
			var div4_html = Better_compareTime(tipsRow.dateline);
		}else{
			var div4_html = '';
		}
	}
	
	

	div4_html += '  ';

	var source = '<span class="source">'+betterLang.global.blog.by;
	source += Better_parseBlogSource(tipsRow.source);
	source += '</span>';
	div4_html += source;

	var funcDiv = $(document.createElement('span')).addClass('action').attr('id', 'tipsListRowFuncDiv_'+id+bid_key).addClass('listRowFuncs').empty();
	div4.append(funcDiv).append('<span class="time" id="listRowAddress_'+bid_key+'">'+$.trim(div4_html)+'</span>');

	td1.append(div2).append(div4);
	tr.append(td1);
	
	//td2 = $(document.createElement('td')).css('width', '50px');

/*	if (tipsRow.badge_id>0 && typeof(tipsRow.badge_detail)!='undefined' && tipsRow.badge_detail!=null) {
		badge_id = tipsRow.badge_id;
		uid = tipsRow.uid;		
		
		try {
			data = tipsRow.badge_detail;
		}catch(EEE) {
			
		}

		badge = $('<a href="#bigbadge_row_'+badge_id+'_'+uid+'" title="" onclick="return false;" bid="'+badge_id+'" uid="'+uid+'"><img class="pngfix" src="'+tipsRow.badge_detail.picture+'" alt="" /></a>');
		badge.click(function(){
			BETTER_BIG_BADGE_ID = $(this).attr('bid');
			BETTER_BIG_BADGE_UID = $(this).attr('uid');
		}).fancybox({
			autoDimensions: true,
			scrolling: 'no',
			centerOnScroll: true,
			
			'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
				$('#fancybox-outer').css('background-color', '#1db8ee');
				
				if ($('#list_badge_users_tipsListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
					Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_tipsListRow_'+id);
				}						
			},
			'onClosed': function(){
				$('#fancybox-outer').css('background-color', '#fff');
			}
		
		});	
		
		badge_detail = Better_Badge_Detail_Row(tipsRow, 'tipsListRow_'+id);
		
		td2.append(badge);
		td2.append(badge_detail);
	} else {
		td2.append(' ');
	}
	tr.append(td2);	*/
	
	tr.find('a.badge_users_page').click(function(){
		var badgeId = parseInt($(this).attr('bid'));
		var direct = $(this).attr('direct');
		var pf = $(this).attr('pf');
		var uid = $(this).attr('uid');

		var nextPage = direct=='next' ? parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())+1 : parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())-1;
		 $('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
		Better_Badge_Users(badgeId, uid, nextPage, pf);
	});
	
	// 设置消息行鼠标上移效果
	//tr.mouseenter(function(){
		//thisRow = $(this);
		var thisRow = tr;
		if (betterUser.uid>0) {
			var tblId = thisRow.attr('tblId');
			var bid = thisRow.attr('bid');
			var uid = thisRow.attr('uid');
			var bid_key = bid.replace('.', '_');
			
			var funcDiv = thisRow.find('#tipsListRowFuncDiv_'+id+bid_key);

			if (funcDiv.html()=='') {
				funcDiv.empty();

				//顶/砸
				if(withFavLinks){
					try {
						var ups = parseInt(thisRow.attr('up'));
						var upTips = Better_Tips_Up_Alt(tipsRow.polled, ups);
						var au = $(document.createElement('a')).attr('id', 'pollFuncA_up_'+id+'_'+bid_key).attr('key', id+'_'+bid_key).attr('href', 'javascript:void(0)').attr('title', upTips);
						au.css('background', 'url("/images/icons.png") repeat scroll -100px -285px transparent');
						//au.html('<img style="vertical-align:text-bottom;" src="/images/poll_good.gif" alt="" title="'+upTips+'" class="pngfix" />').attr('href', 'javascript:void(0)')
						au.css('width', '14px');
						au.css('height','14px');
						au.css('margin','3px 5px 0 0');
//						var downs = parseInt(thisRow.attr('down'));
//						var downTips = Better_Tips_Down_Alt(tipsRow.polled, downs);
//						var ad = $(document.createElement('a')).attr('id', 'pollFuncA_down_'+id+'_'+bid_key).attr('key', id+'_'+bid_key).attr('href', 'javascript:void(0)').attr('title', downTips);
//						ad.css('background', 'url(/images/poll_bad.gif) no-repeat 0 bottom');
//						//ad.html('<img src="/images/poll_bad.gif" alt="" title="'+downTips+'" class="pngfix" />');
//						ad.css('width', '14px');
						
						
						if (!tipsRow.polled && uid!=betterUser.uid) {
							au.bind('click', {
								bid: bid,
								akey: id+'_'+bid_key,
								trKey: 'tipsListRow_'+id+'_'+bid_key
							}, Better_Tips_Up);
							
//							ad.bind('click', {
//								bid: bid,
//								akey: id+'_'+bid_key,
//								trKey: 'tipsListRow_'+id+'_'+bid_key							
//							}, Better_Tips_Down);
						}
						
//						funcDiv.append(au).append(ad);
						funcDiv.append(au);
					} catch(eee3) {
					}
				}
				
				
				//删除
				if (withMyFuncLinks==true && uid==betterUser.uid) {	
					try {
						//afterDeleteCallback = $.isFunction(callbacks.afterDeleteCallback) ? callbacks.afterDeleteCallback : function(){};//这边导致上面判断出错
						afterDeleteCallback = callbacks.afterDeleteCallback;
						var a = $(document.createElement('a')).attr('title',betterLang.global.blog.delete_it).attr('href', 'javascript:void(0)').bind('click', {
									bid: bid, 
									id:'listRow_'+tblId+'_'+bid_key,
									afterDeleteCallback: afterDeleteCallback,
									msg: betterLang.global.delete_tips.title
								}, Better_Delblog);
						a.css('background', 'url("/images/action.png") repeat scroll 0px 0px transparent');
						a.css('width', '14px');
						a.css('height','14px');
						a.css('margin','3px 5px 0 0');
						funcDiv.append(a);
					} catch (eee4) {
						if (Better_InDebug) {
							Better_Notify({
								msg: 'In Delete:'+eee4.message
							});
						}						
					}
				}
				
				if (withHisFuncLinks==true) {
					//	转发
					try {
						a = $(document.createElement('a')).attr('href', 'javascript:void(0);').attr('title',betterLang.global.blog.rt);
						a.css('background', 'url("/images/action.png") repeat scroll -15px 0px transparent');
						a.css('width', '14px');
						a.css('height','14px');
						a.css('margin','3px 5px 0 0');
						a.click(function(){
							var params = {
									msg: $('#message_'+bid_key).html(),
									nickname: $('#nickname_'+bid_key).text(),
									username: $('#nickname_'+bid_key).attr('username'),
									uid: uid,
									address: $('#listRowAddress_'+bid_key).html(),
									attach: $('#attach_'+bid_key).attr('ref'),
									from: 'tips',
									bid_key: bid_key,
									now_bid: bid,
									allow_rt: tipsRow.allow_rt	
								};
							Better_TransTips({'data': params});
						});

						funcDiv.append(a);
					} catch (eee2) {
					}
				}
				
				//收藏
				if (withMyFuncLinks==true) {
					try {
						var a = $(document.createElement('a')).attr('id', 'favoritesFuncA_'+id+'_'+bid_key);
						var afterUnfavoriteCallback = $.isFunction(callbacks.afterUnfavoriteCallback) ? callbacks.afterUnfavoriteCallback : function(){};
						
						a.css('width', '14px');
						a.css('height','14px');
						a.css('margin','3px 5px 0 0');
						if ($.inArray(bid, betterUser.fav_bids)>=0) {
							a.css('background', 'url("/images/action.png") repeat scroll -48px 0px transparent');
							a.attr('title', betterLang.global.favorite.cancel);
							a.attr('href', 'javascript:void(0)').bind('click', {
								bid: bid,
								bid_key: bid_key,
								id: 'favoritesFuncA_'+id+'_',
								row_id: 'tipsListRow_'+id+'_'+bid_key,
								inFavList: inFavList,
								tbl_id: id,
								type: 'tips',
								afterUnfavoriteCallback: afterUnfavoriteCallback
							}, Better_UnFavoriteblog);
						} else {
							a.css('background', 'url("/images/action.png") repeat scroll -32px 0px transparent');
							a.attr('title', betterLang.global.favorite.title);
							a.attr('href', 'javascript:void(0)').bind('click', {
								bid: bid,
								id: 'favoritesFuncA_'+id+'_',
								row_id: 'tipsListRow_'+id+'_'+bid_key,
								inFavList: inFavList,
								tbl_id: id,
								type: 'tips',
								afterUnfavoriteCallback: afterUnfavoriteCallback
							}, Better_Favoriteblog);
						}
	
						funcDiv.append(a);
					}catch (eee) {
						
					}
				}
				
				
				//评论
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
	
	return tr;
}

/**
 * 解析一个转发的贴士的row
 * 
 * @param data
 * @param options
 * @return
 */
function Better_parseRtTipsRow(data, options)
{
	var options = typeof(options)=='object' ? options : {};
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	// to-do
	var withToDoFuncLinks = typeof(options.withToDoFuncLinks) != 'undefined' ? options.withToDoFuncLinks : true;
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';

	var bid = data.bid;
	var bid_key = bid.replace('.', '_');
	
	var tr = $(document.createElement('tr')).attr('id', 'rtTipsListRow_'+id+'_'+bid_key).attr('priv', data.priv).attr('protected', data.priv_blog).attr('up', data.up).attr('down', data.down);
	tr.addClass('listRow');

	if (withAvatar==true) {
		var td0 = $(document.createElement('td')).css('width', '48px');
		tr.append(td0);
	}

	var td1 = $(document.createElement('td')).addClass('rt_info');
	var divA = $(document.createElement('div')).addClass('rt_avatar').html('<a href="/'+data.username+'"><img src="'+data.avatar_url+'" alt="" width="24" height="24"  class="zoompng"/></a>');
	divA.find('img').addClass('avatar').error(function(){
		$(this).attr('src', Better_AvatarOnError);
	});
	td1.append(divA);

	var div1 = $(document.createElement('div')).addClass('rt_text');
	var div2 = $(document.createElement('div')).addClass('rt_status').addClass('rt_message_row');

	var message = '<a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> '+'<span id="message_'+bid_key+'" class="message_row">'+Better_parseMessage(data)+'</span>';
	
	div2.append(message);			
	div2.find('a.blank').attr('target', '_blank');
	if((data.attach && data.attach_thumb) || (data.badge_id>0 && typeof(data.badge_detail)!='undefined')){
		var div3 = $(document.createElement('div')).addClass('info');
		if(data.badge_id>0 && typeof(data.badge_detail)!='undefined'){
			//div3.append('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" title="" class="badge_fcbox" uid="'+data.uid+'" bid="'+data.badge_id+'"><img class="badge_attach pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>');
			//div3.append('<div style="display:none;">'+Better_Badge_Detail_Row(data, 'listRow_'+id).html()+'</div>');
			
			var badge = $('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" bid="'+data.badge_id+'" class="checkin_big_badge" onclick="return false" uid="'+data.uid+'"><img class="badge_attach pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>');
			badge.click(function(){
				BETTER_BIG_BADGE_ID = $(this).attr('bid');
				BETTER_BIG_BADGE_UID = $(this).attr('uid');
			}).fancybox({
				autoDimensions: true,
				scrolling: 'no',
				centerOnScroll: true,
				'onStart' : function(){
					$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
					$('#fancybox-outer').css('background-color', '#1db8ee');
					if ($('#list_badge_users_rtTipsListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
						Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_rtTipsListRow_'+id);
					}								
				},
				'onClosed': function(){
					$('#fancybox-outer').css('background-color', '#fff');
				}
			
			});	
			
			var badge_detail = Better_Badge_Detail_Row(data, 'rtTipsListRow_'+id);
			
			div3.append(badge).append(badge_detail);
			
		}
		
		if (data.attach && data.attach_thumb) {
			div3.append('<a href="'+data.attach_url+'" class="attach_href"><img id="attach_'+bid_key+'" class="pngfix attach" src="'+data.attach_tiny+'" alt="" ref="'+data.attach+'" /></a>');
			if ($.browser.opera) {
				div3.find('a.attach_href').attr('target', '_blank');
			} else {
				div3.find('a.attach_href').fancybox();
			}
			
			div3.find('img').error(function(){
				$(this).attr('src', Better_ImgOnError);
				$(this).parent().attr('href', Better_ImgOnError);
			});
		}	
		div2.append(div3);
	}
	
	
	var div4 = $(document.createElement('div')).addClass('ext');

	var div4_html = Better_locationTips({
		lon: data.lon,
		lat: data.lat,
		tips: data.location_tips,
		dateline: data.dateline,
		poi: typeof(data.user_poi)!='undefined' ? data.user_poi : data.poi
	});
	div4_html += '  '
	
	var source = '<span class="source">'+betterLang.global.blog.by;
	source += Better_parseBlogSource(data.source);
	source += '</span>';
	div4_html += source;

	var funcDiv = $(document.createElement('span')).addClass('action').attr('id', 'rtTipsListRowFuncDiv_'+id+bid_key).addClass('listRowFuncs').empty();
	div4.append(funcDiv).append('<span class="time" id="listRowAddress_'+bid_key+'">'+$.trim(div4_html)+'</span>');

	td1.append(div2).append(div4);
	tr.append(td1);
	/*td2 = $(document.createElement('td'));
	if (data.badge_id>0 && typeof(data.badge_detail)!='undefined') {
		badge = $('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" bid="'+data.badge_id+'" class="checkin_big_badge" onclick="return false" uid="'+data.uid+'"><img src="'+data.badge_detail.picture.toString().replace('badges', 'badges/24/')+'" alt="" width="24" /></a>');
		badge.click(function(){
			BETTER_BIG_BADGE_ID = $(this).attr('bid');
			BETTER_BIG_BADGE_UID = $(this).attr('uid');
		}).fancybox({
			autoDimensions: true,
			scrolling: 'no',
			centerOnScroll: true,
			'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
				$('#fancybox-outer').css('background-color', '#1db8ee');
				if ($('#list_badge_users_rtTipsListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
					Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_rtTipsListRow_'+id);
				}								
			},
			'onClosed': function(){
				$('#fancybox-outer').css('background-color', '#fff');
			}
		
		});	
		
		badge_detail = Better_Badge_Detail_Row(data, 'rtTipsListRow_'+id);
		
		td2.append(badge);
		td2.append(badge_detail);
	} else {
		td2.append(' ');
	}
	
	tr.append(td2);	*/
	tr.find('a.badge_users_page').click(function(){
		var badgeId = parseInt($(this).attr('bid'));
		var direct = $(this).attr('direct');
		var pf = $(this).attr('pf');
		var uid = $(this).attr('uid');

		var nextPage = direct=='next' ? parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())+1 : parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())-1;
		 $('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
		Better_Badge_Users(badgeId, uid, nextPage, pf);
	});	
	
	// 设置消息行鼠标上移效果
	//tr.mouseenter(function(){
		if (betterUser.uid>0) {
			//thisRow = $(this);
			var thisRow = tr;
		
			var tmp = thisRow.attr('id').split('_');
			var bid = tmp[2]+'.'+tmp[3];
			var uid = tmp[2];
			var bid_key = bid.replace('.', '_');
			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;
			
			var funcDiv = thisRow.find('#rtTipsListRowFuncDiv_'+id+bid_key);

			if (funcDiv.html()=='') {
				funcDiv.empty();				
				

				if (true) {
					//	转发
					var a = $(document.createElement('a')).attr('title',betterLang.global.blog.rt).attr('href', 'javascript:void(0);');
					a.css('background', 'url("/images/action.png") repeat scroll -15px 0px transparent');
					a.css('width', '14px');
					a.css('height','14px');
					a.css('margin','3px 5px 0 0');
					a.click(function(){
						var params = {
								msg: $('#message_'+bid_key).html(),
								nickname: $('#nickname_'+bid_key).text(),
								username: $('#nickname_'+bid_key).attr('username'),
								uid: uid,
								address: $('#listRowAddress_'+bid_key).html(),
								attach: $('#attach_'+bid_key).attr('ref'),
								from: 'normal',
								bid_key: bid_key,
								now_bid: bid,
								allow_rt: data.allow_rt	
						};
						Better_Transblog({'data': params});
					});

					funcDiv.append(a).append(' ');	
				}
				
				//收藏
				if (true) {
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
							inFavList: false,
							tbl_id: id,
							afterUnfavoriteCallback: afterUnfavoriteCallback
						}, Better_UnFavoriteblog);
					} else {
						a.css('background', 'url("/images/action.png") repeat scroll -32px 0px transparent');
						a.attr('title',betterLang.global.favorite.title).attr('href', 'javascript:void(0)').bind('click', {
							bid: bid,
							id: 'favoritesFuncA_'+id+'_',
							row_id: 'listRow_'+id+'_'+bid_key,
							inFavList: false,
							tbl_id: id,
							afterUnfavoriteCallback: afterUnfavoriteCallback
						}, Better_Favoriteblog);
					}
					
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
	
	return tr;
}

/**
 * 解析一个签到的数据行
 * 
 * @param data
 * @param options
 * @return
 */
function Better_parseCheckinRow(data, options)
{
	var options = typeof(options)=='object' ? options : {};
	var id = typeof(options.id)!='undefined' ? options.id : '';
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;	
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	data.poi = typeof(data.user_poi)!='undefined' ? data.user_poi : (typeof(data.poi)!='undefined' ? data.poi : {});
	// to-do
	var withToDoFuncLinks = typeof(options.withToDoFuncLinks) != 'undefined' ? options.withToDoFuncLinks : true;
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';
	
	var bid_key = data.bid.replace('.', '_');
	
	var arr = new Array();
	arr.push('<tr id="checkinListRow_'+id+'_'+bid_key+'" class="listRow" uid="'+data.uid+'" priv="'+data.priv+'" tblId="'+id+'" bid="'+data.bid+'" protected="'+data.priv_blog+'">');
	arr.push('<td class="avatar icon"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'"><img src="'+data.avatar_thumb+'" onerror="this.src=Better_AvatarOnError" alt="" width="48" class="pngfix" /></a></td>')
	arr.push('<td class="info">');
	var div4_html = '';
	if (data.major>0) {
		var message = '<span id="checkin_msg_'+bid_key+'"><a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> ';
		if (data.major>0) {
			message += betterLang.global.got_major.toString().replace('{POI}', '<a href="/poi/'+data.poi.poi_id+'" class="place">'+data.poi.name+'</a>');
			if (data.message!='') {
				message += ': '+Better_parseMessage(data);
			} else {
				message += ' '+Better_parseMessage(data);
			}
			div4_html += Better_compareTime(data.dateline)+'  ';
		} 
		message += '</span>';
			
		var source = '<span class="source">'+betterLang.global.blog.by;
		source += Better_parseBlogSource(data.source);	
		source += '</span>';
		div4_html += source;
	} else {
		var message = '<span id="checkin_msg_'+bid_key+'"> <a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> ';
		message += '<span id="message_'+bid_key+'" class="message_row">'+betterLang.global.at+' '+data.city+' </span> ';
		message += '<a href="/poi/'+data.poi.poi_id+'" class="place">'+data.poi.name+'</a> ';

		if (LANGUAGE!='en' && data.message=='') {
			message += betterLang.global.checkin.title+'';
		}
		message += '</span>';
		if (data.message!='') {
			message += ': '+Better_parseMessage(data);
		} else {
			message += ' '+Better_parseMessage(data);
		}
				
		var source = '<span class="source">'+Better_compareTime(data.dateline) + ' '+betterLang.global.blog.by;
		source += Better_parseBlogSource(data.source);	
		source += '</span>';
		div4_html += source;
	}
	
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
	/*arr.push('<td style="width:50px;">');
	if (data.major>0) {
		arr.push('<img src="/images/crown.png" alt="" class="pngfix" />');
	} else if (data.badge_id>0 && typeof(data.badge_detail)!='undefined') {
		arr.push('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" class="checkin_big_badge" onclick="return false" bid="'+data.badge_id+'" uid="'+data.uid+'"><img src="'+data.badge_detail.picture+'" alt="" class="pngfix" /></a>');
		arr.push('<div style="display:none;">'+Better_Badge_Detail_Row(data, 'checkinListRow_'+id).html()+'</div>');
	} else {
	}
	arr.push('</td>');*/
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
	
		if (betterUser.uid>0) {
			var bid = thisRow.attr('bid');
			var uid = thisRow.attr('uid');
			var bid_key = bid.replace('.', '_');
			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;			

			var funcDiv = thisRow.find('#checkinListRowFuncDiv_'+id+bid_key);
			if (!funcDiv.html()) {
				funcDiv.empty();
				
				//删除
				if (withMyFuncLinks==true && uid==betterUser.uid) {	
					//afterDeleteCallback = $.isFunction(callbacks.afterDeleteCallback) ? callbacks.afterDeleteCallback : function(){};//这边导致上面判断出错
					var afterDeleteCallback = callbacks.afterDeleteCallback;
					var a = $(document.createElement('a')).attr('title',betterLang.global.blog.delete_it).attr('href', 'javascript:void(0)').bind('click', {
								bid: bid, 
								id:'listRow_'+id+'_'+bid_key,
								afterDeleteCallback: afterDeleteCallback,
								msg: betterLang.global.delete_checkin.title
							}, Better_Delblog);
					a.css('background', 'url("/images/action.png") repeat scroll 0px 0px transparent');
					a.css('width', '14px');
					a.css('height','14px');
					a.css('margin','3px 5px 0 0');
					funcDiv.append(a);
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
				
				
				//收藏
				if (withMyFuncLinks==true) {
					try {
						var a = $(document.createElement('a')).attr('id', 'favoritesFuncA_'+id+'_'+bid_key);
						var afterUnfavoriteCallback = $.isFunction(callbacks.afterUnfavoriteCallback) ? callbacks.afterUnfavoriteCallback : function(){};
					
						a.css('width', '14px');
						a.css('height','14px');
						a.css('margin','3px 5px 0 0');
						if ($.inArray(bid, betterUser.fav_bids)>=0) {
							a.css('background', 'url("/images/action.png") repeat scroll -48x 0px transparent');
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


/**
 * 解析一个转发签到的数据行
 * 
 * @param data
 * @param options
 * @return
 */
function Better_parseRtCheckinRow(data, options)
{
	var options = typeof(options)=='object' ? options : {};
	var withAvatar = typeof(options.withAvatar)!='undefined' ? options.withAvatar : true;
	var withAvatar = true;
	var withMyFuncLinks = typeof(options.withMyFuncLinks)!='undefined' ? options.withMyFuncLinks : true;
	var withHisFuncLinks = typeof(options.withHisFuncLinks)!='undefined' ? options.withHisFuncLinks : true;	
	var isRt = typeof(options.isRt)!='undefined' ? options.isRt : false;
	// to-do
	var withToDoFuncLinks = typeof(options.withToDoFuncLinks) != 'undefined' ? options.withToDoFuncLinks : true;
	var widthCommentLinks = typeof(options.widthCommentLinks)!='undefined' ? options.widthCommentLinks : true;
	var commentText = typeof(options.commentText)!='undefined' ? options.commentText : '评论('+data.comments+')';

	var bid_key = data.bid.replace('.', '_');
	var tr = $(document.createElement('tr')).attr('id', 'rtCheckinListRow_'+id+'_'+bid_key).addClass('listRow').attr('priv', data.priv).attr('protected', data.priv_blog).attr('uid', data.uid).attr('bid', data.bid);

	var td0 = $(document.createElement('td')).css('width', '48px');
	tr.append(td0);

	var td1 = $(document.createElement('td')).addClass('rt_info');
	
	var divA = $(document.createElement('div')).addClass('rt_avatar').html('<a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'"><img src="'+data.avatar_url+'" alt="" width="24"  class="zoompng"/></a>');
	divA.find('img').addClass('avatar').error(function(){
		$(this).attr('src', Better_AvatarOnError);
	});
	td1.append(divA);
	
	var div1 = $(document.createElement('div')).addClass('rt_text');
	var div2 = $(document.createElement('div')).addClass('rt_status').addClass('rt_message_row').attr('id', 'checkin_msg_'+bid_key);
	
	var div4 = $(document.createElement('div')).addClass('ext');
	var div4_html = '';	

	if (data.major>0) {
		var message = '<a href="/'+data.username+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> '
		if (data.major>0) {
			message += betterLang.global.got_major.toString().replace('{POI}', '<a href="/poi/'+data.poi.poi_id+'" class="place">'+data.poi.name+'</a>');
			if (data.message!='') {
				message += ': '+Better_parseMessage(data);
			} else {
				message += ' '+Better_parseMessage(data);
			}
			div4_html += Better_compareTime(data.dateline)+'  ';
		} /*else if (data.badge_id>0 && typeof(data.badge_detail)!='undefined') {
			message += betterLang.global.got_badge.toString().replace('{BADGE}', data.badge_detail.name);
			if (data.message!='') {
				message += ': '+Better_parseMessage(data);
			} else {
				message += ' '+Better_parseMessage(data);
			}			
			div4_html += Better_locationTips({
				lon: data.lon,
				lat: data.lat,
				tips: data.user_poi==0 ? '' : data.location_tips,
				dateline: data.dateline,
				poi: typeof(data.user_poi)!='undefined' ? data.user_poi : data.poi
			});
			div4_html += '  ';			
		}*/
		var source = '<span class="source">'+betterLang.noping.global.blog.howtovia.toString().replace('{SOURCE}',Better_parseBlogSource(data.source));		
		source += '</span>';
		div4_html += source;
	
		var funcDiv = $(document.createElement('span')).addClass('action').attr('id', 'rtCheckinListRowFuncDiv_'+id+bid_key).addClass('listRowFuncs').empty();
		div4.append(funcDiv).append('<span class="time">'+$.trim(div4_html)+'</span>');		
	} else {
		
		if (data.message!='') {
			var message = betterLang.noping.better.parsertcheckin_nothing2.toString().replace('{NICKNAME}','<a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> ').replace('{CITY}','<span id="message_'+bid_key+'" class="message_row">'+data.city+' </span> ').replace('{POI}','<a href="/poi/'+data.poi.poi_id+'" class="place">'+data.poi.name+'</a> ');
			message += ': '+Better_parseMessage(data);
		} else {
			message = betterLang.noping.better.parsertcheckin_nothing.toString().replace('{NICKNAME}','<a href="/'+data.username+'#'+Better_GetAnchorByType(data.type)+'" class="user" id="nickname_'+bid_key+'" username="'+data.username+'">'+data.nickname+'</a> ').replace('{CITY}','<span id="message_'+bid_key+'" class="message_row">'+data.city+' </span> ').replace('{POI}','<a href="/poi/'+data.poi.poi_id+'" class="place">'+data.poi.name+'</a> ');
			message += ' '+Better_parseMessage(data);
		}
			
		var source = '<span class="source">'+Better_compareTime(data.dateline) + ' '+betterLang.noping.global.blog.howtovia.toString().replace('{SOURCE}',Better_parseBlogSource(data.source));		
		source += '</span>';		
		div4_html += source;

		var funcDiv = $(document.createElement('span')).addClass('action').attr('id', 'rtCheckinListRowFuncDiv_'+id+bid_key).addClass('listRowFuncs').empty();
		div4.append(funcDiv).append('<span class="time">'+$.trim(div4_html)+'</span>');
	}	

	div2.append(message);	
	if((data.attach && data.attach_thumb) || (data.badge_id>0 && typeof(data.badge_detail)!='undefined')){
		var div3 = $(document.createElement('div')).addClass('info');
		
		if(data.badge_id>0 && typeof(data.badge_detail)!='undefined'){
			var badge = $('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" bid="'+data.badge_id+'" class="checkin_big_badge" onclick="return false" uid="'+data.uid+'"><img class="badge_attach pngfix" src="'+data.badge_detail.picture+'" alt="" /></a>');
			badge.click(function(){
				BETTER_BIG_BADGE_ID = $(this).attr('bid');
				BETTER_BIG_BADGE_UID = $(this).attr('uid');
			}).fancybox({
				autoDimensions: true,
				scrolling: 'no',
				centerOnScroll: true,
				'onStart' : function(){
					$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
					$('#fancybox-outer').css('background-color', '#1db8ee');
					if ($('#list_badge_users_rtCheckinListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
						Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_rtCheckinListRow_'+id);
					}								
				},
				'onClosed': function(){
					$('#fancybox-outer').css('background-color', '#fff');
				}
			
			});
		
			var badge_detail = Better_Badge_Detail_Row(data, 'rtCheckinListRow_'+id);
			div3.append(badge).append(badge_detail);
		}
		
		if (data.attach && data.attach_thumb) {
			div3.append('<a href="'+data.attach_url+'" class="attach_href"><img id="attach_'+bid_key+'" class="attach pngfix" onerror="this.src=Better_ImgOnError" src="'+data.attach_tiny+'" alt="" ref="'+data.attach+'" /></a>');
		}	
		
		
		div2.append(div3);
	}
		
	td1.append(div2).append(div4);
	
	tr.append(td1);
	if($.browser.opera){
		tr.find('td div.info a.attach_href').attr('target', '_blank');
	}else{
		tr.find('td div.info a.attach_href').fancybox();
	}
	
	/*td2 = $(document.createElement('td'));
	if (data.badge_id>0 && typeof(data.badge_detail)!='undefined') {
		badge = $('<a href="#bigbadge_row_'+data.badge_id+'_'+data.uid+'" bid="'+data.badge_id+'" class="checkin_big_badge" onclick="return false" uid="'+data.uid+'"><img src="'+data.badge_detail.picture.toString().replace('badges', 'badges/24/')+'" alt="" width="24" /></a>');
		badge.click(function(){
			BETTER_BIG_BADGE_ID = $(this).attr('bid');
			BETTER_BIG_BADGE_UID = $(this).attr('uid');
		}).fancybox({
			autoDimensions: true,
			scrolling: 'no',
			centerOnScroll: true,
			'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').css('height', '454px');
				$('#fancybox-outer').css('background-color', '#1db8ee');
				if ($('#list_badge_users_rtCheckinListRow_'+id+BETTER_BIG_BADGE_ID+'_'+BETTER_BIG_BADGE_UID).html()=='') {
					Better_Badge_Users(BETTER_BIG_BADGE_ID, BETTER_BIG_BADGE_UID, 1, 'list_badge_users_rtCheckinListRow_'+id);
				}								
			},
			'onClosed': function(){
				$('#fancybox-outer').css('background-color', '#fff');
			}
		
		});	
		
		badge_detail = Better_Badge_Detail_Row(data, 'rtCheckinListRow_'+id);
		
		td2.append(badge);
		td2.append(badge_detail);
	} else {
		td2.append(' ');
	}
	
	tr.append(td2);	*/
	tr.find('a.badge_users_page').click(function(){
		var badgeId = parseInt($(this).attr('bid'));
		var direct = $(this).attr('direct');
		var pf = $(this).attr('pf');
		var uid = $(this).attr('uid');
		
		var nextPage = direct=='next' ? parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())+1 : parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())-1;
		 $('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
		Better_Badge_Users(badgeId, uid, nextPage, pf);
	});
	
	// 设置消息行鼠标上移效果
	//tr.mouseenter(function(){
		if (betterUser.uid>0) {
			//thisRow = $(this);
			var thisRow = tr;
			
			var bid = thisRow.attr('bid');
			var uid = thisRow.attr('uid');
			var bid_key = bid.replace('.', '_');
			var priv = thisRow.attr('priv');
			var userProtected = thisRow.attr('protected')=='1' ? true : false;	
			
			var funcDiv = thisRow.find('#rtCheckinListRowFuncDiv_'+id+bid_key);

			if (funcDiv.html()=='') {
				funcDiv.empty();

				

				if (priv=='public') {
					//	转发
					a = $(document.createElement('a')).attr('title',betterLang.global.blog.rt).attr('href', 'javascript:void(0);');
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
				
				//收藏
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
	//});

	// 设置消息行鼠标移出效果
	/*tr.mouseleave(function(){
		if (betterUser.uid>0) {
			$(this).find('span[id^=rtCheckinListRowFuncDiv_]').hide();
		}
	});*/		
	
	return tr;
}

function Better_doNothing()
{
	
}

function Better_WaitForLastAjax()
{
	return false;
	return Better_ajaxInProcess;
}


/**
 * 读取消息框中文字字数
 * 
 * @return
 */
function Better_GetPostLength(key)
{
	key = typeof key=='undefined' ? '' : key;
	
	txt = $('#'+key+'status_text').val();
	txt = txt.split('\r').join("");
	txt = txt.replace(/[\r\n]/g, '');
	txt = $.trim(txt);
	if(key=="tips_" && txt=="写个贴士分享给大家吧"){
		len=0;
	}else{
		len = parseInt(txt.length);
	}
	
	return len;
}

/**
 * 更新字数提示颜色
 * 
 * @return
 */
function Better_FilterStatus(key)
{
	key = typeof key == 'object'? '' : key;
	
	len = Better_GetPostLength(key);
	slen = Better_PostMessageMaxLength - len;
	if (len>slen) {
		$('#'+key+'txtCount').removeClass('green').addClass('red');
	} else if (len<Better_PostMessageMinLength && len>0) {
		$('#'+key+'txtCount').removeClass('green').addClass('red');
	} else {
		$('#'+key+'txtCount').removeClass('red').addClass('green');
	}
	
	$('#'+key+'txtCount').html(slen);
}

/**
 * 解析得到的高度
 */
function Better_ParseCssHeight(options){
	orginalHeight=options.orginalHeight ? options.orginalHeight : '';
	offset=options.offset ? options.offset : 0;
	returnstr=(parseInt(orginalHeight.replace('px', ''))+offset)+'px';
	return returnstr;
}

if (Better_InDebug==false) {
	window.onerror = function()
	{
		return true;
	}
}

/**
 * 获得发吼吼的隐私设置
 * 
 * @return
 */
function Better_getPostBlogPriv()
{
	priv = 'public';
	
	/*if ($('#privacy_public').attr('checked')) {
		priv = 'public';
	} else if ($('#privacy_protected').attr('checked')) {
		priv = 'protected';
	} else if ($('#privacy_private').attr('checked')) {
		priv = 'private';
	}*/
	
	priv = $('#priv_sel_shout').attr('priv');
	
	return priv;
}

/**
 * 获得签到的隐私设置
 * 
 * @return
 */
function Better_getPostCheckinPriv()
{
	priv = 'public';
	
	/*if ($('#checkin_privacy_public').attr('checked')) {
		priv = 'public';
	} else if ($('#checkin_privacy_protected').attr('checked')) {
		priv = 'protected';
	} else if ($('#checkin_privacy_private').attr('checked')) {
		priv = 'private';
	}*/
	
	priv = $('#priv_sel').attr('priv');
	
	return priv;
}

/**
 * 获得性别称谓
 * 
 * @param gender
 * @return
 */
function Better_getGenderCaller(gender)
{
	switch (gender) {
		case 'male':
			caller = betterLang.global.gender.he;
			break;
		case 'female':
			caller = betterLang.global.gender.she;
			break;
		default:
			caller = betterLang.global.gender.unknown;
			break;
	}
	
	return caller;
}

/**
 * 忽略请求
 * 
 * @param id
 * @return
 */
function Better_DiscardRequest(event)
{
	if (!Better_Ajax_processing) {
		msg_id = event.data.msg_id;
		request_type = event.data.request_type;
		
		$('#request_'+msg_id).fadeOut();
		
		$.getJSON('/ajax/user/discard_request', {
			msg_id: msg_id,
			request_type: request_type
		}, function(drJson){
			remain = parseInt($('#home_request_count').text())-1;
			remain = remain>0 ? remain : 0;			
			$('#home_request_count').text(remain);
		});
	}
}

function Better_Checkin_Search_Poi_List(options)
{
	url = options.url;
	page = options.page;
	key = options.key;
	
	if (page<=1) {
		$('#tbl_'+key).empty();
		Better_Pager_Reset(key);
	}

	Better_Table_Loading(key);
	
	keyword = $.trim($('#poi_keyword').val());
	if (keyword==betterLang.global.checkin.poi_search.tips) {
		keyword = '';
	}
	
	if(Better_Home_Map_Inited){
		t = marker.getLatLng();
		sLon = t.lng();
		sLat = t.lat();
	}else{
		sLon = wifiLon ? wifiLon : (ipLon ? ipLon : (pageLon ? pageLon : 116.397));
		sLat = wifiLat ? wifiLat : (ipLat ? ipLat : (pageLat ? pageLat : 39.917));
	}

	$.get(url, {
		lon: sLon,
		lat: sLat,
		range: $('#checkin_poi_search_range').val(),
		wifi_range: wifiRange,
		count: 20,
		keyword: keyword,
		order: 'distance',
		page: page
	}, function(psJson){

		if (Better_AjaxCheck(psJson)) {

			cTbl = $('#tbl_'+key);
			
			Better_Pager_setPages(key, psJson.pages);	
			Better_Clear_Table_Loading(key);
			
			if (psJson.total>0) {
				
				for(i=0;i<psJson.rows.length;i++) {
					abid = typeof(psJson.rows[i].bizid)!='undefined' ? psJson.rows[i].bizid : '';
					city = typeof(psJson.rows[i].city)!='undefined' ? psJson.rows[i].city : '';
					city = city==null ? '' : city;
					
					tr = $(document.createElement('tr')).addClass('poi_list_row').attr('id', 'poi_list_row1_'+psJson.rows[i].poi_id).attr('abid', abid);
					tr.attr('city', city).attr('poi_name', psJson.rows[i].name).attr('address', psJson.rows[i].address);
					tr.attr('lon', psJson.rows[i].lon).attr('lat', psJson.rows[i].lat).attr('title', betterLang.global.checkin.just_here);
					
					td = $(document.createElement('td'));
					td.attr('width', '26').css('width', '26px').css('padding-top', '2px').append('<img src="'+psJson.rows[i].logo_url+'?ss=333" width="24" style="width:24px; height:24px;" class="ie6poiicon" />');
					tr.append(td);
					
					td = $(document.createElement('td'));
					td.append('<div style="font-size:14px;">&nbsp;'+city+' '+psJson.rows[i].name+'</div>');
					td.append('<div class="poi_address_row">'+psJson.rows[i].address+' '+Better_Format_Meter(psJson.rows[i].distance)+'</div>')

					tr.append(td).mouseenter(function(){
						tmp = $(this).attr('id').split('_');
						poi_id = tmp[3];
						$(this).css('background', '#dee5ee').css('cursor', 'pointer');
						$('#poi_list_row2_'+poi_id).css('background', '#dee5ee');
					}).mouseleave(function(){
						tmp = $(this).attr('id').split('_');
						poi_id = tmp[3];	
						$(this).css('background', '');
						$('#poi_list_row2_'+poi_id).css('background', '');
					}).click(function(){
						tmp = $(this).attr('id').split('_');
						poi_id = tmp[3];
						
						abId = $(this).attr('abid');
						lon = parseFloat($(this).attr('lon'));
						lat = parseFloat($(this).attr('lat'));

						$('#ready_to_checkin_poi').val(poi_id);
						$('#ready_to_checkin_city').text($(this).attr('city'));
						$('#ready_to_checkin_address').text($(this).attr('address'));
						$('#ready_to_checkin_poi_name').text($(this).attr('poi_name'));
						$('#ready_to_checkin_ab_poi').val(abId);
						
						$('#tbl_checkined').show();
						$('#tbl_not_checkined').hide();							
						
						if(Better_Home_Map_Inited){
							map.removeOverlay(marker);
							map.setCenter(new GLatLng(lat, lon), gmapZomm);
					        marker = new GMarker(map.getCenter(), {draggable: true});
	
					        GEvent.addListener(marker, "dragend", Better_Home_MapCenter_Move);
					        map.addOverlay(marker);	
						}
						
						$('#change_place_checkin').trigger('click');
					});

					cTbl.append(tr);

				}
			}
			
		}
		
	}, 'json');	
}

function Better_Checkin_Search_Poi(page) 
{
	page = page ? page : 1;
	var key = 'checkin_poi_search';
	if($.browser.msie==true && parseInt($.browser.version)<7){
		Better_PagerIe6({
			key: key,
			next: betterLang.global.checkin.more_poi,
			last: betterLang.global.checkin.no_more_poi,
			callback: Better_Checkin_Search_Poi,
			css: 'simple'
		});
	}else{
		Better_Pager({
			key: key,
			next: betterLang.global.checkin.more_poi,
			last: betterLang.global.checkin.no_more_poi,
			callback: Better_Checkin_Search_Poi,
			css: 'simple'
		});
	}

	Better_Checkin_Search_Poi_List({
		key: key,
		url: '/ajax/poi/search',
		page: page,
		wifi_range: wifiRange
	});

	if (page==1) {

		add_tbl = $(document.createElement('table')).attr('cellpadding', '0').attr('cellspacing', '0').attr('border', '0').addClass('left').addClass('add_table');
		add_tbl.css('line-height', '20px').css('margin-top', '2px').attr('width', '100%');

		ntr = $(document.createElement('tr')).addClass('poi_list_row');
		ntd = $('<td colspan="2"></td>');
		ntd.css('text-align', 'center').css('height', '50px');
		ntd.append('<span style="font-size:14px;">'+betterLang.global.checkin.poi_not_in_list+'</span>');
		ntd.append('&nbsp;').append(betterLang.global.checkin.add_poi);
		
		ntr.append(ntd);
		add_tbl.append(ntr);
		
		trh = $(document.createElement('tr'));
		tdh = $(document.createElement('td')).attr('colspan', '2');
		tdh.attr('height', '1').css('height', '1px').css('background', '#a4afbf');
		trh.append(tdh);
		
		ftr1 = $(document.createElement('tr')).addClass('add_poi_tr_row').addClass('poi_list_row');
		ftd1_l = $(document.createElement('td')).css('text-align', 'center').attr('width', '60').text(betterLang.home.add_poi.name);
		ftd1_r = $(document.createElement('td')).css('text-align', 'left');
		var ip1 = $(document.createElement('input')).attr('id', 'add_poi_name').addClass('left').addClass('ap_input').css('width', '246px').attr('maxlength', '25');
		ftd1_r.append(ip1);
		ftr1.append(ftd1_l).append(ftd1_r);
		add_tbl.append(ftr1);

		ftr2 = $(document.createElement('tr')).addClass('add_poi_tr_row').addClass('poi_list_row');
		ftd2_l = $(document.createElement('td')).css('text-align', 'center').attr('width', '60').text(betterLang.home.add_poi.address);
		ftd2_r = $(document.createElement('td')).css('text-align', 'left');
		var ip2 = $(document.createElement('input')).attr('id', 'add_poi_address').addClass('ap_input').css('width', '246px').attr('maxlength', '30');
		ftd2_r.append(ip2);
		ftr2.append(ftd2_l).append(ftd2_r);
		add_tbl.append(ftr2);
		
		ftr3 = $(document.createElement('tr')).addClass('add_poi_tr_row').addClass('poi_list_row');
		ftd3_l = $(document.createElement('td')).css('text-align', 'center').attr('width', '60').text(betterLang.home.add_poi.phone);
		ftd3_r = $(document.createElement('td')).css('text-align', 'left');
		var ip3 = $(document.createElement('input')).attr('id', 'add_poi_phone').addClass('ap_input').css('width', '246px').attr('maxlength', '30');
		ftd3_r.append(ip3);
		ftr3.append(ftd3_l).append(ftd3_r);	
		add_tbl.append(ftr3);

		ftr5 = $(document.createElement('tr')).addClass('add_poi_tr_row').addClass('poi_list_row');
		ftd5_l = $(document.createElement('td')).css('text-align', 'center').attr('width', '60').text(betterLang.home.add_poi.category);
		ftd5_r = $(document.createElement('td')).css('text-align', 'left');
		var ip5 = $(document.createElement('select')).attr('id', 'add_poi_category');
		
		for (i=1;i<=10;i++) {
			op = $(document.createElement('option')).text(eval('betterLang.home.add_poi.category'+i)).val(i);
			ip5.append(op);
		}
		ftd5_r.append(ip5);
		ftr5.append(ftd5_l).append(ftd5_r);	
		add_tbl.append(ftr5);				
		
		ftr4 = $(document.createElement('tr')).addClass('add_poi_tr_row').addClass('poi_list_row');
		ftd4_l = $(document.createElement('td')).css('text-align', 'center').attr('width', '60').text(betterLang.home.add_poi.city);
		ftd4_r = $(document.createElement('td')).css('text-align', 'left');
		var ip4 = $(document.createElement('input')).attr('id', 'add_poi_city').addClass('ap_input').css('width', '60px').addClass('ip_center').attr('maxlength', '10');
		label_province = betterLang.home.add_poi.province;
		var ip4_1 = $(document.createElement('input')).attr('id', 'add_poi_province').addClass('ap_input').css('width', '60px').addClass('ip_center').attr('maxlength', '10');
		label_country = betterLang.home.add_poi.country;
		var ip4_2 = $(document.createElement('input')).attr('id', 'add_poi_country').addClass('ap_input').css('width', '60px').addClass('ip_center').attr('maxlength', '10');
		ftd4_r.append(ip4).append('&nbsp;').append(label_province).append('&nbsp;').append(ip4_1).append('&nbsp;').append(label_country).append('&nbsp;').append(ip4_2);
		ftr4.append(ftd4_l).append(ftd4_r);	
		add_tbl.append(ftr4);				

		ftr6 = $(document.createElement('tr')).addClass('add_poi_tr_row').addClass('poi_list_row');
		if ($.browser.msie) {
			ftd6 = $(document.createElement('td'));
			ftr6.append($(document.createElement('td')));
			ftd6.attr('text-align', 'left').css('padding-left', '120px').attr('align', 'left');
		} else {					
			ftd6 = $(document.createElement('td')).attr('colspan', '2').css('padding-left', '160px').css('text-align', 'center').attr('align', 'center');
			ftr6.colSpan = 2;					
		}
		
		apBtn = $(document.createElement('input')).attr('type', 'button').val(betterLang.home.add_poi.btn).addClass('blue_btn');
		
		apBtn.bind('click', {
			poi_name: $('#add_poi_name').val(),
			address: $('#add_poi_address').val(),
			category: $('#add_poi_category').val(),
			phone: $('#add_poi_phone').val(),
			city: $('#add_poi_city').val(),
			province: $('#add_poi_province').val(),
			country: $('#add_poi_country').val()
		}, Better_Home_AddPoi);	
		ftd6.append(apBtn);

		ftr6.append(ftd6);
		add_tbl.append(ftr6);

		$('#checkin_poi_list_add_poi').empty().append(add_tbl).show();		
	}
}

/**
 * 加载地点
 * 
 * @param options
 * @return
 */
function Better_loadPois(options)
{
	url = options.url;
	page = options.page;
	key = options.key;
	uid = typeof(options.uid)!='undefined' ? options.uid : betterUser.uid;
	keyword = typeof(options.keyword)!='undefined' ? options.keyword : '';
	callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};
	withoutMine = typeof(options.without_mine)!='undefined' ? options.without_mine : false;
	count = typeof(options.count)!='undefined' ? options.count : 20;
	
	if (page<=1) {
		Better_Pager_Reset(key);
	}

	Better_Table_Loading(key);
	
	$.get(url, {
		page: page,
		uid: uid,
		keyword: keyword,
		without_mine: withoutMine,
		count: count
	}, function(lpJson){
		
		Better_Clear_Table_Loading(key);
		
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
					tr.push("<tr class='poi_row' poi_id='"+poiId+"'>");
					
					tr.push("<td style='width:48px;'>");
					tr.push("<img src='"+logoUrl+"' class='ie6poi_logo' />");
					tr.push("</td>");
					
					tr.push("<td class='poi_intro'>");
					
					tr.push('<div class="poi_row_name"><a href="/poi/'+poiId+'">'+lpJson.rows[i].name+'</a>');
					tr.push(' <span class="poi_row_address">'+lpJson.rows[i].city+' '+address+'</span>');
					tr.push('</div>');
					
					tr.push('<div class="poi_row_info">');
					tr.push('<span class="left"></span>');
					tr.push('<span class="action hide listRowFuncs right" id="poi_list_row_'+poiId+'"></span>');
					tr.push('</div>');
					tr.push("</td>");
					
					tr.push("<td style='width:48px;'>");
					if (parseFloat(lpJson.rows[i].major)>0 && typeof(lpJson.rows[i].major_detail)!='undefined' && lpJson.rows[i].major_detail!=null && typeof(lpJson.rows[i].major_detail.username)!='undefined') {
						tr.push('<a href="/'+lpJson.rows[i].major_detail.username+'"><img src="'+lpJson.rows[i].major_detail.avatar_small+'" class="avatar pngfix" width="48" /></a>');
					}
					tr.push("</td>");
					
					tr.push("</tr>");
					
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
				
				if ($.isFunction(callbacks.completeCallback)) {
					callbacks.completeCallback(lpJson);
				}				
			} else if ($.isFunction(callbacks.emptyCallback)) {
				callbacks.emptyCallback();
			}
		}
		
	}, 'json');
}

/**
 * 加载请求
 * 
 * @param options
 * @return
 */
function Better_loadRequest(options)
{
	url = options.url;
	page = options.page;
	key = options.key;
	callbacks = typeof(options.callbacks)!='undefined' ? options.callbacks : {};

	if (page<=1) {
		Better_Pager_Reset(key);
	}
	
	Better_Table_Loading(key);
	if (key == 'friend_request' && page == 1 && needRef_friend_request != true) {
		_callbackFsq(_friend_request_page1);
		needRef_friend_request = true;
		//window.location = Better_This_Script+ '#x' + key;	
	} else if(key == 'follow_request' && page == 1 && needRef_follow_request != true) {
		_callbackFsq(_follow_request_page1);
		needRef_follow_request = true;
		//window.location = Better_This_Script + '#x' + key;		
	} else {
		$.get(url, {page: page}, _callbackFsq, 'json');
	}
}

function _callbackFsq(frqJson) {
	if (Better_AjaxCheck(frqJson)) {
		if($.isFunction(callbacks.beforeCallback)){
			callbacks.beforeCallback();
		}
		
		Better_Pager_setPages(key, frqJson.pages);	
		
		$('#'+key+'_count').text(frqJson.count);
		
		if (frqJson.count==0) {
			if ($.isFunction(callbacks.emptyCallback)) {
				callbacks.emptyCallback();
			} else {
				Better_EmptyResults(key, betterLang.messages.no_friend_request);
			}
			
		} else {
			
			Better_Clear_Table_Loading(key);
			nowPage = typeof(frqJson.page)!='undefined' ? frqJson.page : page;
			
			for(i=0;i<frqJson.rows.length;i++) {
				tr = $(document.createElement('tr'));
				tr.attr('id', frqJson.rows[i].msg_id);
				td0 = $(document.createElement('td')).css('width', '48px');
				td0.append('<a href="/'+frqJson.rows[i].userInfo.username+'"><img src="'+frqJson.rows[i].userInfo.avatar_small+'" width="36" class="avatar zoompng" /></a>');
				tr.append(td0);
				
				td1 = $(document.createElement('td'));
				td1.append('<a href="/'+frqJson.rows[i].userInfo.username+'">'+frqJson.rows[i].userInfo.nickname+'</a> ');
				td1.append(frqJson.rows[i].text.toString().replace(frqJson.rows[i].userInfo.nickname, ''));
				td1.append(' <span class="request_time" style="color:#ddd;font-size: 12px;">'+Better_compareTime(frqJson.rows[i].dateline)+'</span>');
				tr.append(td1);
				
				td2 = $(document.createElement('td')).css('text-align', 'right');
				a1 = $(document.createElement('a')).addClass('request_agree_link').text(betterLang.messages.agree).attr('href', 'javascript:void(0)');
				a1.attr('uid', frqJson.rows[i].userInfo.uid).attr('nickname', frqJson.rows[i].userInfo.nickname).attr('request_type', frqJson.rows[i].type).attr('msg_id', frqJson.rows[i].msg_id);
				a2 = $(document.createElement('a')).addClass('request_reject_link').text(betterLang.messages.reject).attr('href', 'javascript:void(0)');
				a2.attr('uid', frqJson.rows[i].userInfo.uid).attr('nickname', frqJson.rows[i].userInfo.nickname).attr('request_type', frqJson.rows[i].type).attr('msg_id', frqJson.rows[i].msg_id);
				
				td2.append(a1).append(' ').append(a2);
				
				if ($.inArray(frqJson.rows[i].userInfo.uid, betterUser.blockedby)<0) {
					//	阻止此人的链接
					aBlock = $(document.createElement('a')).attr('href', 'javascript:void(0)').attr('id', 'betterUserBlockBtn_request_'+frqJson.rows[i].userInfo.uid);

					if ($.inArray(frqJson.rows[i].userInfo.uid, betterUser.blocks)>=0) {
						aBlock.html(betterLang.global.block.cancel).bind('click', {
							uid: frqJson.rows[i].userInfo.uid,
							id: 'betterUserBlockBtn_request_',
							nickname: frqJson.rows[i].userInfo.nickname,
							gender: frqJson.rows[i].userInfo.gender
						}, Better_Unblock);
					} else {
						aBlock.html(betterLang.global.block.title).bind('click', {
							callbacks: {
								completeCallback: $.isFunction(callbacks.blockCallback) ? callbacks.blockCallback : function(){}
							},								
							uid: frqJson.rows[i].userInfo.uid,
							gender: frqJson.rows[i].userInfo.gender,
							nickname: frqJson.rows[i].userInfo.nickname,
							id: 'betterUserBlockBtn_request_'
						}, Better_Block);
					}
					
					td2.append(' ').append(aBlock);
				}					
				
				tr.append(td2);
				$('#tbl_'+key).append(tr);
			}			
			
			$('#tbl_'+key+' a.request_agree_link').unbind('click').click(function(){
				request_type = $(this).attr('request_type');
				msg_id = $(this).attr('msg_id');
				nickname = $(this).attr('nickname');
				
				switch (request_type) {
					case 'friend_request':
						Better_Friend_Request({
							data: {
								uid: $(this).attr('uid'),
								nickname: $(this).attr('nickname'),
								notify: betterLang.noping.global.follow.request.pass.info.toString().replace('{NICKNAME}',nickname),
								completeCallback: function() {
									//$('a[href="#friend_request"]').trigger('click');
									if(typeof(msg_id)!='undefined'){
										$('#tbl_friend_request #'+msg_id).hide();
									}
								}
							},
							currentTarget: {
								id: 'xxxx'
							}
						});							
						break;
					case 'follow_request':
						$(this).bind('click', {
							uid: $(this).attr('uid'),
							nickname: $(this).attr('nickname'),
							closeCallback: function(){
								//$('a[href="#follow_request"]').trigger('click');
								if(typeof(msg_id)!='undefined'){
									$('#tbl_follow_request #'+msg_id).hide();
								}
							}
						}, Better_Allow_Follow);
						break;
				}
			});
			
			$('#tbl_'+key+' a.request_reject_link').unbind('click').click(function(){
				request_type = $(this).attr('request_type');
				
				switch (request_type) {
					case 'friend_request':
						Better_Reject_Friend_Request({
							data: {
								uid: $(this).attr('uid'),
								nickname: $(this).attr('nickname'),
								completeCallback: function() {
									//$('a[href="#friend_request"]').trigger('click');
									if(typeof(msg_id)!='undefined'){
										$('#tbl_friend_request #'+msg_id).hide();
									}
								}
							},
							currentTarget: {
								id: 'xxxx'
							}
						});							
						break;
					case 'follow_request':
						$(this).bind('click', {
							uid: $(this).attr('uid'),
							nickname: $(this).attr('nickname'),
							closeCallback: function(){
								//$('a[href="#follow_request"]').trigger('click');
								if(typeof(msg_id)!='undefined'){
									$('#tbl_follow_request #'+msg_id).hide();
								}
							}
						}, Better_Reject_Follow);
						break;
				}
			});				
		}
	}
	
	$('#msg_pager_friend_request').show();	
}



/**
 * 激活吼吼的poi选择
 * 
 * @return
 */
function Better_Enable_Shout_Poi_Choice()
{
	$('#ready_to_shout_not_checkined').hide();
	
	$('#ready_to_shout_at').show();
	$('#ready_to_shout_poi_name').show();
	$('#ready_to_shout_city').show();
}

function getCaret(txb)
{
	var pos = 0;//设置初始位置
	txb.focus();//输入框获得焦点,这句也不能少,不然后面会出错,血的教训啦.
	var s = txb.scrollTop;//获得滚动条的位置
	var r = document.selection.createRange();//创建文档选择对象
	var t = txb.createTextRange();//创建输入框文本对象
	t.collapse(true);//将光标移到头
	t.select();//显示光标,这个不能少,不然的话,光标没有移到头.当时我不知道,搞了十几分钟
	var j = document.selection.createRange();//为新的光标位置创建文档选择对象
	r.setEndPoint("StartToStart",j);//在以前的文档选择对象和新的对象之间创建对象,妈的,不好解释,我表达能力不算太好.有兴趣自己去看msdn的资料
	var str = r.text;//获得对象的文本
	var re = new RegExp("[\\n]","g");//过滤掉换行符,不然你的文字会有问题,会比你的文字实际长度要长一些.搞死我了.我说我得到的数字怎么总比我的实际长度要长.
	str = str.replace(re,"");//过滤
	pos = str.length;//获得长度.也就是光标的位置
	
	r.collapse(false);
	r.select();//把光标恢复到以前的位置
	txb.scrollTop = s;//把滚动条恢复到以前的位置
	
	return pos;
}


/**
 * 文本框中移动光标位置
 * @return
 */
function moveCursor(start, end){
    var oTextarea = document.getElementById("status_text");
    
    if($.browser.msie){
      var oTextRange = oTextarea.createTextRange();
      var LStart = start;
      var LEnd = end;
      var start = 0;
      var end = 0;
      var value = oTextarea.value;
      for(var i=0; i<value.length && i<LStart; i++){
        var c = value.charAt(i);
        if(c!='\n'){
          start++;
        }
      }
      for(var i=value.length-1; i>=LEnd && i>=0; i--){
        var c = value.charAt(i);
        if(c!='\n'){
          end++;
        }
      }
      oTextRange.moveStart('character', start);
      oTextRange.moveEnd('character', -end);
      //oTextRange.collapse(true);
      oTextRange.select();
      oTextarea.focus();
    }else{
      oTextarea.select();
      oTextarea.selectionStart=start;
      oTextarea.selectionEnd=end;
    }
 } 


/**
 * 新增poi
 * 
 * @param event
 * @return
 */
function Better_Home_AddPoi(event)
{
	poi_name = event.data.poi_name ? $.trim(event.data.poi_name) : $.trim($('#add_poi_name').val());
	address = event.data.address ? $.trim(event.data.address) : $.trim($('#add_poi_address').val());
	city = event.data.city ? $.trim(event.data.city) : $.trim($('#add_poi_city').val());
	province = event.data.province ? $.trim(event.data.province) : $.trim($('#add_poi_province').val());
	country = event.data.country ? $.trim(event.data.country) : $.trim($('#add_poi_country').val());
	phone = event.data.phone ? $.trim(event.data.phone) : $.trim($('#add_poi_phone').val());
	category = event.data.category ? $.trim(event.data.category) : $.trim($('#add_poi_category').val());
	lon = 0;
	lat = 0;
	
	if (poi_name=='') {
		Better_Notify({
			msg: betterLang.home.add_poi.plz_input_name
		});
		$('#add_poi_name').focus();
	} else if (address=='') {
		Better_Notify({
			msg: betterLang.home.add_poi.plz_input_address
		});
		$('#add_poi_address').focus();
	} else if (city=='') {
		Better_Notify({
			msg: betterLang.home.add_poi.plz_input_city
		});
		$('#add_poi_city').focus();
	} else {
		
		Better_Notify_loading();
		
		if (address!='' && Better_Geocoder) {
			Better_Geocoder.getLatLng(address, function(gotPoint){
				if (gotPoint) {
					lon = gotPoint.lng();
					lat = gotPoint.lat();
					/*
					Gmap.removeOverlay(marker);
					marker = new GMarker(Gmap.getCenter(), {draggable: true});
	
					Gmap.addOverlay(marker);		*/
				}else{
					
					Better_Notify({
						msg: betterLang.add_poi.error,
						closeCallback: function(){
							$('#add_poi_address')[0].focus();
						}
					});
					
					
					return false;
				}
				
				$.post('/ajax/poi/create', {
					name: poi_name,
					lon: lon,
					lat:lat, 
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
							
							$('#ready_to_checkin_city').text(city);
							$('#ready_to_checkin_poi').val(pcJson.result.poi_id);
							$('#ready_to_checkin_poi_name').text(poi_name);
							$('#ready_to_checkin_address').text(address);
							$('#ready_to_checkin_ab_poi').val(0);
							
							$('#change_place_checkin').trigger('click');
							
							map.removeOverlay(marker);
							map.setCenter(new GLatLng(lat, lon), gmapZomm);
					        marker = new GMarker(map.getCenter(), {draggable: true});

					        GEvent.addListener(marker, "dragend", Better_Home_MapCenter_Move);
					        map.addOverlay(marker);											
							
							$('#tbl_checkined').show();
							$('#tbl_not_checkined').hide();
							break;
						case codes.FAILED:
						default:
							Better_Notify({
								msg: betterLang.home.add_poi.failed
							});
							break;
					}
				}, 'json');
			});
		} else {
			$.post('/ajax/poi/create', {
				name: poi_name,
				lon: lon,
				lat:lat, 
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
						
						$('#ready_to_checkin_city').text(city);
						$('#ready_to_checkin_poi').text(pcJson.result.poi_id).val(pcJson.result.poi_id);
						$('#ready_to_checkin_poi_name').text(poi_name);
						$('#ready_to_checkin_address').text(address);
						$('#ready_to_checkin_ab_poi').val(0);
						
						$('#change_place_checkin').trigger('click');
						
						map.removeOverlay(marker);
						map.setCenter(new GLatLng(lat, lon), gmapZomm);
				        marker = new GMarker(map.getCenter(), {draggable: true});

				        GEvent.addListener(marker, "dragend", Better_Home_MapCenter_Move);
				        map.addOverlay(marker);				
				        
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
}


/**
 * 初始化check in窗口地图
 */
function Better_Checkin_InitGMap(force)
{	
	if(Script_has_loaded){
		if ($.browser.msie && $.browser.version<7) {
			Better_Checkin_InitGMapIe6(force);
		} else {
		
			force = force ? force : true;
			if (typeof(Better_Poi_Detail)!='undefined') {
				sLon = Better_Poi_Detail.lon;
				sLat = Better_Poi_Detail.lat;
			} else {
				sLon = wifiLon;// ? wifiLon : (ipLon ? ipLon : (pageLon ? pageLon : 116.397));
				sLat = wifiLat;// ? wifiLat : (ipLat ? ipLat : (pageLat ? pageLat : 39.917));
			}
	
			if (Better_Home_Map_Inited==false || force==true) {
				if (GBrowserIsCompatible()) {
					map = new GMap2(document.getElementById("checkin_map"), {size:new GSize(500, 150)});
					map.setCenter(new GLatLng(sLat, sLon), gmapZomm);
					map.addControl(new GSmallMapControl());
					map.enableScrollWheelZoom();

			        marker = new GMarker(map.getCenter(), {draggable: false});
			
			       /* GEvent.addListener(map, 'dragend', function(){
						
						marker.hide();
						marker.disableDragging();
				        marker = new GMarker(map.getCenter(), {draggable: true});		
				        map.addOverlay(marker);
				        GEvent.addListener(marker, "dragend", Better_Home_MapCenter_Move);
			        });
			        GEvent.addListener(marker, "dragend", Better_Home_MapCenter_Move);*/
			        map.addOverlay(marker);
				}	
				Better_Home_Map_Inited = true;
			}
			
		}
	}else{
		$('#checkin_map').html('<img src="images/map_unload.png?v" width="500" height="150" />');
	}
	
}

function Better_Checkin_InitGMapIe6(force)
{	
	force = force ? force : false;
	if (typeof(Better_Poi_Detail)!='undefined') {
		sLon = Better_Poi_Detail.lon;
		sLat = Better_Poi_Detail.lat;
	} else {
		sLon = wifiLon ? wifiLon : (ipLon ? ipLon : (pageLon ? pageLon : 116.397));
		sLat = wifiLat ? wifiLat : (ipLat ? ipLat : (pageLat ? pageLat : 39.917));
	}

	if (Better_Home_Map_Inited==false || force==true) {
		if (GBrowserIsCompatible()) {
			map = new GMap2(document.getElementById("checkin_map"), {size:new GSize(500, 150)});
			map.setCenter(new GLatLng(sLat, sLon), gmapZomm);
			map.addControl(new GSmallMapControl());
			map.enableScrollWheelZoom();
			
	        marker = new GMarker(map.getCenter(), {draggable: true});
	        GEvent.addListener(map, 'dragend', function(){
				
				marker.hide();
				marker.disableDragging();
		        marker = new GMarker(map.getCenter(), {draggable: true});		
		        map.addOverlay(marker);
		        GEvent.addListener(marker, "dragend", Better_Home_MapCenter_Move);
	        });	
	        GEvent.addListener(marker, "dragend", Better_Home_MapCenter_Move);
	        map.addOverlay(marker);
	
		}	
		Better_Home_Map_Inited = true;
	}
	
}
/**
 * 地图中心移动的事件
 * 
 * @return
 */
function Better_Home_MapCenter_Move()
{
	t = marker.getLatLng();
	//wifiLon = t.lng();
	//wifiLat = t.lat();
	
	map.setCenter(new GLatLng(t.lat(), t.lng()), map.getZoom());
	
	checkinDiv = $('#checkin_poi_list');
	if ($.trim(checkinDiv.html())!='') {
		$('#search_poi').trigger('click');
	}       
}

function showMap(position)
{
	wifiLon = position.coords.longitude;
	wifiLat = position.coords.latitude;
	wifiRange = position.coords.accuracy;
	
}

function showError(error) 
{
	Better_Home_Ref_lon = Better_Default_Lon;
	Better_Home_Ref_lat = Better_Default_Lat;
}

function displayMsg(lon, lat,range)
{
	if (lon && lat) {
		Better_Home_Ref_lon = lon;
		Better_Home_Ref_lat = lat;
		Better_Home_Ref_range = range;
		Better_Home_AddressHasAlert = 1;	
		Better_Home_AddressAlert = betterLang.home.got_your_new_place;	
	} else {
		atmp = http_request.responseText.split(',');
		Better_Home_AddressHasAlert = atmp[0];
		Better_Home_Ref_lon = atmp[1];
		Better_Home_Ref_lat = atmp[2];
		Better_Home_Ref_range = atmp[3];
		Better_Home_AddressAlert = atmp[4];
	}
	document.getElementById("location_range").value = Better_Home_Ref_range;

	if (Better_Home_AddressHasAlert=='1') {
		if (betterUser.lbs_report!=0) {
			$('#addressAlertText').html(Better_Home_AddressAlert);
			
			$('#aPassbyAddressAlert').click(function(){
				$('#addressAlert').fadeOut();
				
				return false;
			});
		}
		
		if (Better_Home_Ref_lon && Better_Home_Ref_lat) {

			$('#refPlace').click(function(){

				if (Better_GMapInited==true) {
					Gmap.setCenter(new GLatLng(betterUser.lat, betterUser.lon), Gmap.getZoom());
					
					Gmap.removeOverlay(marker);
					marker = new GMarker(Gmap.getCenter(), {draggable: true});
					GEvent.addListener(marker, "dragend", function(){
						var latlon = marker.getLatLng();
						setlonlat(latlon.lng(),latlon.lat());
					});
					Gmap.addOverlay(marker);					
					
					$('#spanRefPlace').fadeOut();
					
					setlonlat(betterUser.lon, betterUser.lat);

				} else {
				
					if (Better_GMapInited==false) {
						Better_Home_InitGMap();
						$(this).text(betterLang.home.im_not_here);
					}
					
					Gmap.setCenter(new GLatLng(Better_Home_Ref_lat, Better_Home_Ref_lon), Gmap.getZoom());
						
					Gmap.removeOverlay(marker);
					marker = new GMarker(Gmap.getCenter(), {draggable: true});
					GEvent.addListener(marker, "dragend", function(){
						var latlon = marker.getLatLng();
						setlonlat(latlon.lng(),latlon.lat());
					});
					Gmap.addOverlay(marker);
					
					setlonlat(Better_Home_Ref_lon, Better_Home_Ref_lat);
					
				}

				return false;
			});
			$('#spanRefPlace').show();
		}
	}	

}

/**
 * 执行完ajax请求后的回调
 * 
 * @return
 */
function Better_Callback_After_Ajax()
{
	$('a.badge_icon').fancybox({
		onShow: function(){
			$('#fancybox-title-wrap').css('width', '200px');
		}
	});
	$('a.badge_icons').click(function(){
		return false;
	});
	
	//将有class=blank的a链接设置为在新窗口打开，兼容XHTML标准
	$('a.blank').attr('target','_blank');

	//	清理无效的头像
	$('img.user_avatar').error(function(){
		$(this).attr('src', Better_AvatarOnError);
	});	

	$('img.avatar').error(function(){
		$(this).attr('src', Better_AvatarOnError);
	});		
}


//勋章详情框
function Better_Badge_Detail_Row(data, pf){
	
	pf = pf ? pf : '';
	var badgedetail = $('<div style="display:none"></div>');
	var divX = $(document.createElement('div')).attr('id', 'bigbadge_row_'+data.badge_id+'_'+data.uid).css('width','690px').css('height','419px').css('padding', '15px 20px 0 20px').css('background-color', '#fff');
	var div1 = $('<div style="height:330px;"></div>');
	ul = $('<ul></ul>');
	var li1 = $('<li></li>').addClass('left').addClass('badge_big_icon').append('<img src="'+data.badge_detail.big_picture+'" class="pngfix" />');
	var li2 = $('<li></li>').addClass('left').addClass('badge_info');
	li2.append('<div style="font-size: 25px; color: #333; border-bottom: 2px solid #1db8ee; padding-bottom: 10px; font-weight: bold; line-height: 30px;">'+data.badge_detail.name+'</div>');
	got_tip =data.badge_detail.got_tip;
	if(got_tip.length>132){
		got_tip =got_tip.substring(0,132)+'...';
	}
	li2.append('<div style="font-size: 14px; color: #7589AE; margin-top: 5px; font-weight: bold; line-height:24px; word-wrap: break-word;"><span>'+got_tip+'</span></div>');
	var div2 = $(document.createElement('div')).css('margin-top', '30px');
	div2.append('<div class="left" style="margin-right: 12px;"><img height="52" width="52" style="padding: 2px; border: 1px solid #dde1e0;" class="zoompng" src="'+data.avatar_url+'" /></div>');
	var div3 = $(document.createElement('div')).addClass('left').css('font-size', '14px').css('font-weight', 'bold').css('padding', '0px').css('width', '250px');
	div3.append('<span style="color: #0F7CC5; font-family: arial; font-weight: bold;">'+data.nickname+'</span> ');
	
	var unixTimestamp = new Date(data.dateline*1000+8*3600000);	
	var year = unixTimestamp.getUTCFullYear();
	var month = unixTimestamp.getUTCMonth()+1;
	var day = unixTimestamp.getUTCDate();
	var hours = unixTimestamp.getUTCHours();
	var mm = unixTimestamp.getUTCMinutes();
	var ss = unixTimestamp.getUTCSeconds();
	month = month<10 ? '0'+month:month;
	day = day<10 ? '0'+day:day;
	hours = hours<10 ? '0'+hours:hours;
	mm = mm<10 ? '0'+mm:mm;
	ss = ss<10 ? '0'+ss:ss;
	var thistime = year+'-'+month+'-'+day+' '+hours+':'+mm+':'+ss;
	try{
		
		if(data.poi.poi_id>0){	
			div3.append('<span style="color: #7589AE;">@'+data.poi.city+' '+data.poi.name+' </span>');
		}
			
	} catch (en) {
		
	}
	div3.append('<span style="font-size: 12px;word-break: break-word;">'+thistime+' '+betterLang.global.got_badge.toString().replace('{BADGE}', '')+'</span>');
	div2.append(div3);
	div2.append('<div class="clearfix"></div>');
	li2.append(div2);

	if (data.uid==betterUser.uid && data.badge_detail.total) {
		bMsg = data.exchanged ? betterLang.global.badge_exchanged : betterLang.global.badge_not_exchanged;
		divB = '<div style="padding-top:10px;color:#aaa;">'+bMsg+'</div>';
		li2.append(divB);
	}
	ul.append(li1).append(li2);
	div1.append(ul);
	divX.append(div1);
	
	dArr = new Array();
	dArr.push('<div class="badge_users" id="div_list_badge_users_'+pf+data.badge_id+'_'+data.uid+'">');
	dArr.push('<div class="badge_users_title">'+betterLang.badge_users+'</div>');
	dArr.push('<table width="100%" cellspacing="0">');
	dArr.push('<tr><td width="20" id="list_badge_users_'+pf+'prev_'+data.badge_id+'_'+data.uid+'"><a href="javascript:void(0);" class="badge_users_page" bid="'+data.badge_id+'" pf="list_badge_users_'+pf+'" direct="prev" uid="'+data.uid+'"><img src="images/badge_left.jpg" /></a></td>');
	dArr.push('<td><table width="100%"><tbody>');
	dArr.push('<tr id="list_badge_users_'+pf+data.badge_id+'_'+data.uid+'"></tr>');
	dArr.push('</tbody></table></td>');
	dArr.push('<td width="20" id="list_badge_users_'+pf+'next_'+data.badge_id+'_'+data.uid+'"><a href="javascript:void(0);" class="badge_users_page" bid="'+data.badge_id+'" pf="list_badge_users_'+pf+'" direct="next" uid="'+data.uid+'"><img src="images/badge_right.jpg" /></a></td>');
	dArr.push('</tr>');
	dArr.push('</table>');
	dArr.push('<span id="list_badge_users_'+pf+'page_'+data.badge_id+'_'+data.uid+'" style="display:none">1</span>');
	dArr.push('</div>');
	
	divX.append(dArr.join(' '));
	badgedetail.append(divX);

	return badgedetail;
	
}

function Better_showCheckinMap()
{
	var h;
	h = $('#height_fb').val();
	if (h) {
	
	} else {
		h = 245;
	}
	$("#hideCheckina").fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){
			//Better_Checkin_InitGMap(true);
			$('#fancybox-wrap, #fancybox-outer').height(h);
			$('#fancybox-wrap').width(520);
			Better_Notify_clear();
		},	
		'onClosed': function(){
		}
	}).trigger('click');
	
}

/**
 * 根据IP取位置
 * 
 * @return
 */
function Better_Checkin_GetLLByIp()
{
	$.getJSON('/ajax/lbs/ip', {}, function(ipJson){
		wifiLon = ipJson.lon;
		wifiLat = ipJson.lat;
		wifiRange = ipJson.range;
		
		ipLon = ipJson.lon;
		ipLat = ipJson.lat;
		
		Better_Notify_clear();
		Better_showCheckinMap();
	});	

}

/**
 * FF、Chrome定位
 * 
 * @return
 */
function Better_GetW3cLL()
{
	if (typeof(navigator.geolocation)!='undefined') {
		try {
			w3cGeo = navigator.geolocation.getCurrentPosition(function(position){
				try {
					Better_W3cLL_Gotted.range = position.coords.accuracy;	
					
					$.getJSON('/ajax/location/mix', {
						lon: position.coords.longitude,
						lat: position.coords.latitude
					}, function(mixJson) {
						Better_W3cLL_Gotted.lon = mixJson.lon;
						Better_W3cLL_Gotted.lat = mixJson.lat;				
						
						d = new Date();
						d.setTime(d.getTime() + 1800000);
						expires = d.toUTCString();
						$.cookie('web_lon', mixJson.lon, { expires: expires, path: '/'});
						$.cookie('web_lat', mixJson.lat, { expires: expires, path: '/'});
					});					
				} catch (en) {
					
				}
				
			}, function(){
								
			});
		} catch (ef) {
		}
	}
}

/**
 * 获取签到所需要的用户经纬度
 * 
 * @return
 */
function Better_CheckinGotLL()
{
	if (Better_Need_Checkin_Js) {

		if (Better_Geocode_Got==false) {
			
			Better_Notify_loading({
				msg_title: betterLang.geoing
			});

			try {
				if (typeof(navigator.geolocation)=='undefined') {
					var wifiData = lbs_get_wifiData();

					if(!wifiData.err && typeof(wifiData.base64).toString()!='undefined' && wifiData.base64!='undefined'){
						$.post('/ajax/lbs', {
							lbs: wifiData.base64
						}, function(wifiJson){
							wifiError = wifiJson.error;
							
							if (wifiError=='true') {
								Better_Checkin_GetLLByIp();
							} else {
								if (parseFloat(wifiJson.lat)<200 && parseFloat(wifiJson.lon)<200) {
									wifiLon = wifiJson.lon;
									wifiLat = wifiJson.lat;
									wifiRange = wifiJson.range;
									
									d = new Date();
									d.setTime(d.getTime() + 1800000);
									expires = d.toUTCString();
									$.cookie('web_lon', wifiJson.lon, { expires: expires, path: '/'});
									$.cookie('web_lat', wifiJson.lat, { expires: expires, path: '/'});
								} else {
									Better_Checkin_GetLLByIp();
								}
							}
							
							Better_showCheckinMap();
						}, 'json');
					}else{
						if (!Better_Lbs_Alert_Poped && (!Better_Lbs_Promotion || $.cookie('ignore_lbs_alert') || wifiData.err)) {
							Better_Checkin_GetLLByIp();
						}
					}
	
				} else {
					if (Better_W3cLL_Gotted.lon!=-1 && Better_W3cLL_Gotted.lat!=-1) {
						wifiLon = Better_W3cLL_Gotted.lon;
						wifiLat = Better_W3cLL_Gotted.lat;
						wifiRange = Better_W3cLL_Gotted.range;
						
						Better_showCheckinMap();
					} else {
						Better_Checkin_GetLLByIp();
					}
				}
			} catch (e) {
				Better_Checkin_GetLLByIp();
			}	
		
			Better_Geocode_Got = true;
		} else {
			Better_showCheckinMap();
		}
	
		Better_CheckinJs_Inited = true;
	}
}

/**
 * 解析消息类型图标前缀
 * 
 * @param type
 * @return
 */
function Better_GetMessageTypeIcon(type, major){
	major = (typeof major!='undefined' && major>0) ? true : false;
	if(type=='checkin'){
		if(major){
			return 'crown16.png';
		}else{
			return 'checkin16.png';
		}
	}else if(type=='tips'){
		return 'tips16.png';
	}else{
		return 'shout16.png';
	}
}

/**
 * 获取用户连接后的Anchor
 * 
 * @param type
 * @return
 */
/*
	
	

*/

/**
 * 取勋章的用户
 * 
 * @param badgeId
 * @param page
 * @return
 */
function Better_Badge_Users(badgeId, uid, page, prefix)
{
	prefix = prefix ? prefix : 'badge_users_';
	var dom = $('#'+prefix+badgeId+'_'+uid);
	orgHtml = dom.html();
	dom.html('<td width="528" align="center"><img src="images/badge_loading.gif" /></td>');

	$.getJSON('/ajax/badge', {
		id: badgeId,
		page: page
	}, function(bdJson){
		if (Better_AjaxCheck(bdJson)) {
			if (page==1) {
				$('#'+prefix+'prev_'+badgeId+'_'+uid).hide();
			} else {
				$('#'+prefix+'prev_'+badgeId+'_'+uid).show();
			}
			
			if (page==bdJson.pages) {
				$('#'+prefix+'next_'+badgeId+'_'+uid).hide();
			} else {
				$('#'+prefix+'next_'+badgeId+'_'+uid).show();
			}
			
			bs = new Array();
			for(i=0;i<bdJson.rows.length;i++) {
				bs.push('<td width="48">');
				bs.push('<a href="/'+bdJson.rows[i].username+'" target="_blank" title="'+bdJson.rows[i].nickname+'"><img src="'+bdJson.rows[i].avatar_small+'" class="avatar" width="48" height="48" /></a>');
				bs.push('</td>');
			}
			
			for(i=0;i<11-bdJson.rows.length;i++) {
				bs.push('<td width="48">&nbsp;</td>');
			}
			
			html = bs.join(' ');
			dom.html(html);

			$(dom).find('img').error(function(){
				$(this).attr('src', Better_AvatarOnError);
			});
		}
	});	
}



function Better_GetAnchorByType(type){
	/*
	if(type=='checkin'){
		return 'checkins';
	}else if(type=='tips'){
		return 'tips';
	}else{
		return 'messages';
	}
	*/
	return '';
}


/**
 * 加载页面中某个tab的ajax内容
 * 
 * @param tab
 * @return
 */
function Better_Load_Tab(tab)
{
    tab = tab ? tab : '';
    
    if (!Better_Ajax_processing) {
	    if (tab=='') {
		    tmp = window.location.toString().split('#');
			if (tmp.length>1) {
				selectedTab = tmp[1];
				Better_Auto_Scroll = true;
				
				if (selectedTab.indexOf('x')==0) {
					selectedTab = selectedTab.substr(1, selectedTab.length);
				}
				
				if (selectedTab!='') {
					$('div.tabs ul.tabNavigation a[href="#'+selectedTab+'"] a:first').trigger('click');
				} else {
					$('div.tabs ul.tabNavigation a').filter(':first').trigger('click')
				}
			} else{
				$('div.tabs ul.tabNavigation a').filter(':first').trigger('click')
			}	
	    } else {
			if (tab.indexOf('x')==0) {
				tab = tab.substr(1, tab.length);
			}
			
			if (tab!='') {
				if($('div.tabs ul.tabNavigation a[href="#'+tab+'"]:first').length==0){
					$('div.tabs ul.tabNavigation a').filter(':first').trigger('click');
				}else{
					$('div.tabs ul.tabNavigation a[href="#'+tab+'"]:first').trigger('click');
				}
			} else {
				$('div.tabs ul.tabNavigation a').filter(':first').trigger('click')
			}
	    }    	
    }
}



$(function(){
	if(typeof(isAdmin)=='undefined'){
	$.ajaxSetup({
		cache: false 
	});
	
	Better_Callback_After_Ajax();

	if ($.browser.msie && $.browser.version>=7) {
		$('#half_left').css('min-height', $('#half_right').outerHeight()+20+'px');
	} else if ($.browser.msie && $.browser.version<7) {
		$('#half_left').css('height', $('#half_right').outerHeight()+20+'px');
	} else {
		$('#half_left').css('min-height', $('#half_right').outerHeight()+20+'px');
	}

	Better_Switch_Shout_Form('normal');
	
	//	顶部搜索
	$('#top_search').submit(function(){
		search_text = $.trim($('#search_text').val());
		
		if (search_text=='') {
			Better_Notify({
				msg: betterLang.global.search.plz_input_keyword
			});
			$('#search_text').focus();
		} else {
			$(this).find('input[type!="text"]').attr('disabled', true);
			window.location = encodeURI('/search?search_text='+search_text+'&search_range='+$('#global_search_range').val());
		}
		
		return false;
	});

	
	
	//shout dialog
	Better_EnableShout();

	//cancel shout
	$('#cancel_shout, #close_shout').click(function(){
		Better_ResetPostForm();
		
		$('#status div.poi_div').hide();
		ajaxUploader.disable();
		ajaxUploader.destroy();
		$.fancybox.close();
		return false;
	});	
	
	$('#disable_shout_poi').click(function(){
		$('#shout_poi_list').hide();
		$('#fancybox-wrap, #fancybox-outer').css('height',Better_ParseCssHeight({'orginalHeight' : $('#fancybox-outer').css('height'), 'offset': -42}));
		
		Better_Shout_Without_Poi = true;
	});	

	//	提交按钮
	if (Better_Need_Post_Js) {
		$('#post_btn').click(function(){
			status_text = $('#status_text').val();
			attach = $('#attach').val();
			upbid = $('#upbid').val();
			need_sync = $('#need_sync').attr('checked') ? 1 : 0;
			var dlg_type = $('#dlg_type').val();						
			var len = Better_GetPostLength();
			if(dlg_type == 'todo_dlg'){
				posturl = '/ajax/blog/posttodo';
			}else{
				posturl = '/ajax/blog/post';
			}
			if(dlg_type == 'todo_dlg' && len<Better_PostMessageMinLength){			
				len = parseInt(Better_PostMessageMinLength);
			}
			if (len>Better_PostMessageMaxLength) {
				Better_Notify({
					msg: betterLang.blog.post_size_to_large.replace('%s', Better_PostMessageMaxLength)
				});
			} else if (!attach && len<Better_PostMessageMinLength && upbid == 0) {
				Better_Notify({
					msg: betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength)
				});
			} else {
				var real_upbid = $('#real_upbid').val();
				Better_Notify_loading();
	
				ready_to_shout_poi = Better_Shout_Without_Poi ? 0 : parseInt($('#ready_to_shout_poi').val());
		    	lon = wifiLon;
		    	lat = wifiLat;
		    	range = wifiRange;
		    	
		    	if(dlg_type == 'todo_dlg'){
		    		status_text = $('#status_text').val();
		    		if(status_text=="说说你要去做啥？") {
		    			status_text="";
		    		}
				}
				$.post(posturl, {
					message: status_text, 
					upbid: upbid,
					real_upbid: real_upbid,
					attach: attach,
					priv: Better_getPostBlogPriv(),
					lon: wifiLon,
					lat: wifiLat,
					range: wifiRange,
					poi_id: ready_to_shout_poi,
					type: Better_Shout_Type,
					need_sync: need_sync
					}, function(json){
						
						if (Better_AjaxCheck(json)) {							
							if (json.code=='success') {
	
								$('#cancel_shout').trigger('click');//关闭dialog
								
								if (Better_Shout_Result_Title=='') {
									if (Better_Shout_Type=='normal') {
										
										if (BETTER_HOME_LAST_STATUS_TIPS) {
											$('#home_last_status_tips').text(status_text);
										}
										
										if (upbid!=0) {
											success_notify = betterLang.global.rt.success;
										} else {
											success_notify = betterLang.global.shout.success;
										}
										
										if(upbid==0){
											if(typeof(personpage)=='undefined'){
												$('a[href="#followings"]').attr('disabled', false);
												window.scrollTo(0, 0);
												$('a[href="#followings"]').trigger('click');
											}
										}
										
									} else if (Better_Shout_Type=='tips') {
										success_notify = betterLang.global.tips.success;
										
										$('a[href="#tips"]').attr('disabled', false);
										$('a[href="#tips"]').trigger('click');
									} else {
										success_notify = betterLang.global.post.success;
									}
								} else {
									success_notify = Better_Shout_Result_Title;
								}
								if(Better_Shout_Type=='todo'){
									Better_Notify_clear();
									//Change the UI's elements
									$("#checkina").removeClass("checkin");
									$("#checkina").addClass("checkin-l");
									$("#todoa").hide();
									$("#action-todo").show();
									$('#currentbid').val(json.nbid);
									Popup_Invite_friends();
									
								}else{
									Better_Notify({
										msg: success_notify+' '+Better_parseAchievement(json, Better_Shout_Type=='normal' ? betterLang.global.this_shout : betterLang.global.this_tips),
										close_timer: 2
									});
								}
	
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
							} else if(json.code=='not_allow_rt'){
								Better_Notify({
									msg: '原文消息已不允许被转发'
								});	
							}else {
								Better_Notify({
									msg: 'Failed'
								});										
							}							
							
							$('#privacy_public').attr('checked', true);
						}
				}, 'json');
			}
		});
		
		
		$('#havebeenhere').bind('click', {
			bid: $('#currentbid').val(),
			msg: '确认去过了吗？',
			type: 'cancel_todo',
			status:"beenhere"
		}, Better_Delblog);
		$('#nottodo').bind('click', {
			bid: $('#currentbid').val(),
			msg: '确认不去这一地方了吗？',
			type: 'cancel_todo',
			status:"nottodo"
		}, Better_Delblog);
		
		$('#invite-email').focus(function(){
			if($(this).val()=='通过Email邀请好友一起去。(填写多个Email时，请用空格、分号、回车分割)'){
				$(this).val('');
				$(this).css('color', '#333');			
			} 
		}).blur(function(){
			if($(this).val()==''){
				$(this).css('color', '#999');
				$(this).val('通过Email邀请好友一起去。(填写多个Email时，请用空格、分号、回车分割)');			
			} 
		})
		$('#msg_content').focus(function(){
			if($(this).val()=='我想去这儿看看，哪天有时间一起去吧？'){
				$(this).val('');
				$(this).css('color', '#333');			
			} 
		}).blur(function(){
			if($(this).val()==''){
				$(this).css('color', '#999');
				$(this).val('我想去这儿看看，哪天有时间一起去吧？');
			} 
		})
		
		$('#chooseAll').click(function(){
			var temp = $('#alluids').val();
			temp = temp.substr(1,temp.length-2);
			if(temp !=""){
				temparr = temp.split(',');
				for(i in temparr){
					if(temparr[i]!=""){
						thisObj = $('#row_'+temparr[i]);
						if(thisObj.hasClass('selected-item')){
						}else{
							thisObj.trigger('click');
						}
					}
				}
			}
		});
		
		$('#resetAll').click(function(){
			$('#invitation-names').html(" ");
			$('#fuids').val(",");
			$('#invitation-count').html('0');
			//remove the selected style 
			for(i=1;i<17;i++){
				$('#ulcontainer li:nth-child('+i+')').removeClass('selected-item');
			}
		});
		
		$('#invitation_btn').click(function(){
			//1.验证表单
			//验证表单的顺序：1：邮件地址填写是否有误;2:是否选择了好友；3：私信内容是否为空
			
			var mailadds = $('#invite-email').val();
			/* 邮件地址之间的分隔符有3种，逗号，分号和回车键需要把地址转换成add1,add2,add3的形式*/
			if(mailadds=="通过Email邀请好友一起去。(填写多个Email时，请用空格、分号、回车分割)"){
				mailadds="";
			}
			mailadds = escape(mailadds);
			mailadds = mailadds.replace(/%3B/g,"%2C");
			mailadds = mailadds.replace(/%0A/g,"%2C");
			mailadds = mailadds.replace(/%20/g,"%2C");
			mailadds = unescape(mailadds);
			mailadds = mailadds.replace(/,+/g,',');
			var mailaddsArr = mailadds.split(",");
			var validateAdd=true;
			for(i in mailaddsArr){
				emailadd = mailaddsArr[i];
				if(emailadd!=""){
					pat = /(\S)+[@]{1}(\S)+[.]{1}(\w)+/;
					if(pat.test(emailadd)){
					}else{
						validateAdd=false;
						break;
					}
				}
			}
			if(validateAdd){
				var fuids = $('#fuids').val();
				var msg_content = $('#msg_content').val();
				var poi_id =  $('#poiid_invitation_todo').val();
				var poi_name =  $('#poiname_invitation_todo').val();//冗余字段，这样就不需要去到数据库中去查询
				fuids = fuids.substr(1,fuids.length-2);
				if((fuids!=null && fuids!="") || (mailadds!=null &&mailadds!="")){
					if(msg_content== null || msg_content == ""){
						Better_Notify('最少说点什么吧！');
					}else{
						Better_Notify_loading();
						$.post('/ajax/messages/sendgroup', {
							fuids: fuids,
							content: msg_content,
							poiid:poi_id,
							poiname:poi_name,
							mailadds:mailadds
						}, function(dnJson){
							Better_Notify_clear();
							if (Better_AjaxCheck(dnJson)) {
								if (dnJson.error) {
									Better_Notify({
										msg: dnJson.error
									});
								} else {
									var success = 0;
									var failure = 0;
									for (i in dnJson.item){	
										var item = dnJson.item[i];
										switch (item.code) {
										case item.codes.INVALID_CONTENT:
											failure++;
											break;
										case item.codes.CANT_SELF:
											failure++;
											break;
										case item.codes.BLOCKED_BY_RECEIVER:
											failure++;
											break;
										case item.codes.SUCCESS:
											success++;
											break;
										case item.codes.FAILED:failure++;
										case item.codes.INVALID_RECEIVER:failure++;
										default:
											failure++;
											break;								
										}								
									}
									var str = "成功邀请了"+(success+failure)+"个开开好友和"+dnJson.mailcounts+"个Email好友。";
									if(failure>0){
										str = "总共邀请"+(success+failure+dnJson.mailcounts)+"个好友\n其中"+(failure)+"个没有邀请成功";
									}
									Better_Notify(str);							
								}
								$.fancybox.close();
							}
						},'json');
					}
				}else{
					Better_Notify('还没有选择好友！');
				}
			}else{
					Better_Notify('邮件地址有误！');
			}
			//2.提交邀请好友的请求到后台
		});
	
	
		$('#status_text').keydown(function(e){
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
		}).keyup(Better_FilterStatus).mousedown(Better_FilterStatus).keypress(function(e){
			//	快捷键提交
			if ($.browser.msie && ((e.ctrlKey && e.which==10) || (e.altKey && e.which==10) || (e.shiftKey && e.which==10))) {
				$('#post_btn').trigger('click');
				return false;
			} else if ($.browser.mozilla && ((e.ctrlKey && e.which==13 || e.which==10) || (e.altKey && e.which==13))) {
				$('#post_btn').trigger('click');
				return false;
			} else {
				return true;
			}
		});
	
		$('#txtCount').html( Better_PostMessageMaxLength );
	}
	

	$('#shout_search_poi').click(function(){
		keyword = $.trim($('#shout_poi_keyword').val());
		if (keyword==betterLang.global.checkin.poi_search.tips) {
			keyword = '';
		}
		
		tbl = '<table width="100%" cellspacing="0"><tr><td align="center"><img src="images/ajax_loading.gif" alt="" /></td></tr></table>';
		$('#shout_poi_list').html(tbl);

		$.get('/ajax/poi/search', {
			lon: pageLon,
			lat: pageLat,
			range: $('#shout_poi_search_range').val(),
			count: 10,
			keyword: keyword,
			order: 'distance'
		}, function(stJson){
			
			if (Better_AjaxCheck(stJson)) {
				tbl = $(document.createElement('table')).attr('cellpadding', '0').attr('cellspacing', '0').attr('border', '0').addClass('left');
				tbl.css('line-height', '20px').css('margin-top', '2px').attr('width', '100%');
				
				if (stJson.total>0) {
					for(i=0;i<stJson.rows.length;i++) {
						tr = $(document.createElement('tr')).addClass('shout_poi_list_row').attr('id', 'shout_poi_list_row1_'+stJson.rows[i].poi_id);
						tr.attr('city', stJson.rows[i].city).attr('poi_name', stJson.rows[i].name).attr('address', stJson.rows[i].address);
						tr.attr('lon', stJson.rows[i].lon).attr('lat', stJson.rows[i].lat).attr('title', betterLang.global.checkin.just_here);
						
						td = $(document.createElement('td'));
						td.append('<span style="font-size:14px;">&nbsp;'+stJson.rows[i].city+' '+stJson.rows[i].name+'</span>');
						tr.append(td);
						tr.mouseenter(function(){
							tmp = $(this).attr('id').split('_');
							poi_id = tmp[4];
							$(this).css('background', '#dee5ee').css('cursor', 'pointer');
							$('#shout_poi_list_row2_'+poi_id).css('background', '#dee5ee');
						}).mouseleave(function(){
							tmp = $(this).attr('id').split('_');
							poi_id = tmp[4];	
							$(this).css('background', '');
							$('#shout_poi_list_row2_'+poi_id).css('background', '');
						}).click(function(){
							tmp = $(this).attr('id').split('_');
							poi_id = tmp[4];
							
							lon = parseFloat($(this).attr('lon'));
							lat = parseFloat($(this).attr('lat'));

							$('#ready_to_shout_poi').val(poi_id);
							$('#ready_to_shout_city').text($(this).attr('city'));
							$('#ready_to_shout_address').text($(this).attr('address'));
							$('#ready_to_shout_poi_name').text($(this).attr('poi_name'));

							$('#change_place_shout').trigger('click');
						});
						
						tbl.append(tr);
						
						tr2 = $(document.createElement('tr')).addClass('shout_poi_list_row').attr('id', 'shout_poi_list_row2_'+stJson.rows[i].poi_id);
						td2 = $(document.createElement('td'));
						td2.append('<span style="color:#a4afbf;">&nbsp;'+stJson.rows[i].address+'</span>');
						tr2.append(td2);
						tbl.append(tr2);
						
						tr3 = $(document.createElement('tr'));
						td3 = $(document.createElement('td'));
						td3.attr('height', '1').css('height', '1px').css('background', '#a4afbf');
						tr3.append(td3);
						tbl.append(tr3);
					}
				}
				
				ntr = $(document.createElement('tr'));
				ntd = $(document.createElement('td')).css('text-align', 'center');
				ntd.append('<span style="font-size:14px;">'+betterLang.global.checkin.poi_not_in_list+'</span>');
				
				na = $(document.createElement('a'));
				na.text(betterLang.global.checkin.add_poi);
				
				ntd.append(na);
				ntr.append(ntd);
				tbl.append(ntr);
					
				$('#shout_poi_list').empty().append(tbl);
			}
			
		}, 'json');		
		
	});	
	
	if (betterUser.last_checkin_poi>0 && betterUser.lbs_report>0) {
		Better_Enable_Shout_Poi_Choice();
	}
	
	//shout dialog
	$('#change_place_shout').click(function(){
		if($('#status div.poi_div').css('display')=='none'){
			$('#status div.poi_div').show();
			$('#fancybox-wrap, #fancybox-outer').css('height', Better_ParseCssHeight({'orginalHeight' : $('#fancybox-outer').css('height'), 'offset': 420}));
			
			if ($.trim($('#shout_poi_list').html())=='') {
				$('#shout_search_poi').trigger('click');
			}
		}else{
			$('#status div.poi_div').hide();
			$('#fancybox-wrap, #fancybox-outer').css('height',Better_ParseCssHeight({'orginalHeight' : $('#fancybox-outer').css('height'), 'offset': -420}));
		}
	});

	//check in
	$('#search_poi').click(function(){
		Better_Checkin_Search_Poi(1);
	});
	
	//check in dialog
	$('#change_place_checkin').click(function(){
		//8-25-2010 注释掉
		
		/*if($('#checkindlg div.poi_div').css('display')=='none'){
			$('#fancybox-wrap').css('top', '0');
			
			$('#checkindlg div.poi_div').show();
			$('#fancybox-wrap, #fancybox-outer').css('height', Better_ParseCssHeight({'orginalHeight' : $('#fancybox-outer').css('height'), 'offset': 420}));
			
			if ($.trim($('#tbl_checkin_poi_search').html())=='') {
				$('#search_poi').trigger('click');
			}
			
		}else{
			$('#fancybox-wrap').css('top', '90px');
			
			$('#checkindlg div.poi_div').hide();
			$('#fancybox-wrap, #fancybox-outer').css('height',Better_ParseCssHeight({'orginalHeight' : $('#fancybox-outer').css('height'), 'offset': -420}));
		}*/
	});
	
	
	$('#badge_box a.badge_icons').click(function(){
		BETTER_BIG_BADGE_ID = parseInt($(this).attr('href').replace('#bigbadge_', ''));
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
	document.cookie = "offset_time="+Better_Brwoser_Timezone_Offset ;
	
	}
	
	
	$('#priv_sel').click(function(){
		$('#drop_priv').toggle();
	});
	
	$('#priv_sel_shout').click(function(){
		$('#drop_priv_shout').toggle();
	});
	
	$('#drop_priv span').click(function(){
		$('#drop_priv').hide();
		$('#priv_sel').text($(this).text());
		$('#priv_sel').attr('priv', $(this).attr('priv'));
		
		if($(this).attr('priv')=='public'){
			$('#checkin_nopublic').hide();
			$('#checkin_checkbox').show();
		}else{
			$('#checkin_checkbox').hide();
			$('#checkin_nopublic').show();
		}
		return false;
	});
	
	$('#drop_priv_shout span').click(function(){
		$('#drop_priv_shout').hide();
		$('#priv_sel_shout').text($(this).text());
		$('#priv_sel_shout').attr('priv', $(this).attr('priv'));
		
		if($(this).attr('priv')=='public'){
			$('form #shout_nopublic').hide();
			$('form #shout_checkbox').show();
		}else{
			$('form #shout_checkbox').hide();
			$('form #shout_nopublic').show();
		}
		return false;
	});
	
	
	//ie6 fix css子选择符 >
	if($.browser.msie==true && parseInt($.browser.version)<7){
		$('div.tabs > div').addClass('tabs_div');
	}
	
	
	//返回顶部
	if($(window).scrollTop()!=0){
		$('#backTop').show();
	}
	$(window).scroll(function(){
		if($(this).scrollTop()!=0){
			$('#backTop').show();
			if($.browser.msie==true && parseInt($.browser.version)<7){
				//$('#backTop').css('top', 'expression(eval(document.documentElement.scrollTop)+eval($(window).height()-80-68))');
			}else{
				if($(this).scrollTop() > ($(document).height()-$(window).height()-48)){
					$('#backTop').css('bottom', '48px');
				}else{
					$('#backTop').css('bottom', '0');
				}
			}
		}else{
			$('#backTop').hide();
		}
	});
	
	
	//登录框
	$('#login_id').focus(function(){
		if($(this).val()=='Email/手机号/贝多号'){
			$(this).val('');
			$(this).css('color', '#333');	
		} 
	}).blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).val('Email/手机号/贝多号');			
		}
	});
	
	
	//搜索框
	
	
	$('#top_login_fromthird, #top_login_third_guide_block').mouseover(function(){		
		$('#top_login_third_guide_block').show();
	}).mouseout(function(){		
		$('#top_login_third_guide_block').hide();
	});
	
	$('#search_text').focus(function(){
		if($(this).val()=='输入您要找的地名/人名'){
			$(this).val('');
			$(this).css('color', '#333');			
		} 
	}).blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).val('输入您要找的地名/人名');			
		} else {
			//alert(search_text+'blur');
			search_text = $.trim($('#search_text').val());		
			if(search_text.length>4){
				search_text = search_text.substring(0,4)+'...';
			}
			if (search_text!=''){
				$('#search_choose').show();
				$('#m_keyword_poi').text(search_text);
				$('#m_keyword_people').text(search_text);
			} else {
				$('#search_choose').hide();
				
			}
		}
	}).keydown(function(){	
		search_text = $.trim($('#search_text').val());		
		if(search_text.length>4){
			search_text = search_text.substring(0,4)+'...';
		}
		if (search_text!=''){
			$('#search_choose').show();
			$('#m_keyword_poi').text(search_text);
			$('#m_keyword_people').text(search_text);
		} else {
			$('#search_choose').hide();
		}
	}).keyup(function(){
		search_text = $.trim($('#search_text').val());	
		if(search_text.length>4){
			search_text = search_text.substring(0,4)+'...';
		}
		if (search_text!=''){
			$('#search_choose').show();
			$('#m_keyword_poi').text(search_text);
			$('#m_keyword_people').text(search_text);
		} else {
			$('#search_choose').hide();
		}
	});
		
	$('#search_type_people').mouseover(function(){		
		$(this).addClass('search_mouse_on');	
		$('#search_type_poi').removeClass('search_mouse_on');
	}).mouseout(function(){		
		$(this).removeClass('search_mouse_on');	
		$('#search_type_poi').addClass('search_mouse_on');
	});	

	$("#search_choose li").click(function(){
		var id = $(this).attr('id');		
		if(id!='search_type_poi'){
			document.getElementById("global_search_range").value = 'user';				
		} else {
			document.getElementById("global_search_range").value = 'poi';	
		}
		$("#search_button").trigger('click');
		return false;
	});
	$("body").click(function(){		
		$('#search_choose').hide();
	});
	
});

function _nextBadgeUser(badgeId, uid)
{
	var pf = 'badge_users_';
	var nextPage = parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())+1;
	
	$('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
	Better_Badge_Users(badgeId, uid, nextPage, pf);
	
	return false;	
}

function _prevBadgeUser(badgeId, uid)
{
	var pf = 'badge_users_';
	var nextPage = parseInt($('#'+pf+'page_'+badgeId+'_'+uid).text())-1;
	
	$('#'+pf+'page_'+badgeId+'_'+uid).text(nextPage);
	Better_Badge_Users(badgeId, uid, nextPage, pf);
	
	return false;	
}

commentLoading = false;
/**
 * 加载评论
 */
function Better_loadComments(data){
	if (commentLoading) {return;}
	divComment = data.row.find('td.info div.comment, td.rt_info div.comment');
	if (divComment.html() != null) {
		Better_removeCommetnsList(data.row);
		return;
	}
	commentLoading = true;
	var bid = data.bid;
	var pageSize = data.pageSize || 10;
	var row = data.row;
	
	row.find('td.info, td.rt_info').append('<div style="text-align: center;" class="loading"><img src="images/ajax_loading.gif" /></div>');
	$.getJSON('/ajax/blog/replies', {
		'bid': bid,
		'pageSize': pageSize
	}, function(json){
		Better_Notify_clear();
		row.find('td.info div.loading, td.rt_info div.loading').replaceWith(json.rows);
		commentLoading = false;
	});
}

/**
 * 删除评论列表
 */
function Better_removeCommetnsList(row){
	row.find('td.info div.comment, td.rt_info div.comment').remove();
}


/**
 * 发表回复
 */
function Better_postComment(button, bid, successCallback, small_avatar){
	button =$(button);
	small_avatar = typeof small_avatar=='undefined'? 1: small_avatar;
	
	var comment = $.trim(button.parent('div').prev('textarea').val());
	var to_shout = button.prev('span').find('input.to_shout').attr('checked')? 1 : 0;
	var sync = button.prev('span').find('input.sync').attr('checked')? 1 : 0;
	
	var len = comment.length;
	if (len>Better_PostMessageMaxLength) {
		Better_Notify({
			msg: betterLang.blog.post_size_to_large.replace('%s', Better_PostMessageMaxLength)
		});
	} else if (len<Better_PostMessageMinLength) {
		Better_Notify({
			msg: betterLang.blog.post_size_to_short.replace('%s', Better_PostMessageMinLength)
		});
	} else{
		Better_Notify_loading();
		$.getJSON('/ajax/blog/postreply', {
			'bid': bid,
			'message': comment,
			'to_shout': to_shout,
			'need_sync': sync,
			'upbid': bid,
			'small_avatar': small_avatar
		}, function(json){
			//Better_Notify_clear();
			if (json.code=='success') {
				if(typeof successCallback=='function'){
					successCallback(json);
				}else if(typeof successCallback=='undefined'){
					button.closest('div.post_comment').next('div.comments_list').prepend(json.msg);
					button.parent('div').prev('textarea').val('');
					//发表成功后数量+1
					var _bid = bid.toString().replace('.', '_');
					var str = $('a.commentBtn_'+_bid).eq(0).text();
					var tmp = str.match(/([0-9]+)/i);
					if(tmp && tmp.length>0){
						var tx = str.replace(/([0-9]+)/i, parseInt(tmp[0])+1);
						$('a.commentBtn_'+_bid).text(tx);
					}
				}
				
				
				Better_Notify({
					msg: '评论成功 '+Better_parseAchievement(json, ''),
					close_timer: 2
				});
				
				
			} else if (json.code=='need_check') {
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
				Better_Notify({
					msg: betterLang.post.forbidden
				});								
			} else if (json.code=='words_r_banned') {
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
		});
	}
}


/**
 * 读取评论消息框中文字字数
 * 
 * @return
 */
function Better_Comment_getPostLength(txt)
{
	txt = txt.split('\r').join("");
	reg = /(https?:\/\/[-_\w./?%(&amp;)=\d]+)(\s|\/\/|$)/ig;
	txt = txt.replace(reg, "http://bedo.cn");
	txt = txt.replace(/[\r\n]/g, '');
	txt = $.trim(txt);
	
	len = parseInt(txt.length);
	
	return len;
}

/**
 * 评论框剩余字数
 * @return
 */
function Better_Commment_setRemainCounts(textarea){
	var txt = textarea.val();
	var len = Better_Comment_getPostLength(txt);
	var slen = Better_PostMessageMaxLength - len;
	
	textarea.prev('div').find('span.remain_count').html(slen);
}

/**
 * 评论列表里的回复
 */
function Better_Comment_reply(reply){
	var nickname = reply.prevAll('p').children('span.username').children('a').text();
	var textarea = reply.closest('div.comments_list').prev('div.post_comment').children('textarea');
	
	textarea.val('回复@'+nickname+' :');
	$('#comment_content').val('回复@'+nickname+' :');
	
	if(textarea.offset()){
		var left = textarea.offset().left;
		var top = textarea.offset().top;
		if($(document).scrollTop()>top){
			window.scrollTo(0, top-100);
		}
	}
	
	if($('#comment_content').offset()){
		var left = $('#comment_content').offset().left;
		var top = $('#comment_content').offset().top;
		if($(document).scrollTop()>top){
			window.scrollTo(0, top-100);
		}
	}
}


/**
 * 评论列表里的删除
 */
function Better_Comment_delete(id, bid, btn, inrtme){
	inrtme = typeof inrtme!='undefined' ? true: false;
	
	Better_Confirm({
		msg:'确认要删除该评论？',
		onConfirm: function(){
			if(id && bid){
				Better_Notify_loading();
				$.getJSON('/ajax/blog/delreply', {
					'id':id,
					'bid': bid
				}, function(json){
					Better_Notify_clear();
					if(json.code==1){
						if(!inrtme){
							btn.closest('div.row').remove();
							if($('#comments_count').length==0){
								//删除成功后数量-1， 列表页
								var _bid = bid.replace('.', '_');
								var str = $('a.commentBtn_'+_bid).eq(0).text();
								var tmp = str.match(/([0-9]+)/i);
								var tx = str.replace(/([0-9]+)/i, parseInt(tmp[0])-1);
								$('a.commentBtn_'+_bid).text(tx);
							}else{
								//评论详情页
								$('#comments_count').text(parseInt($('#comments_count').text())-1);
							}
						}else{
							btn.closest('tr').remove();
						}
					}else if(json.code==-1){
						Better_Notify({
							msg: '你没有权限删除该评论'
						});
					}else{
						Better_Notify({
							msg: '删除失败'
						});
					}
				});
			}
		}
	});
	
}


/**
 * 提到我的列表的回复
 */
function Better_Rtlist_Reply(btn){
	if(btn.text()=='回复'){
		var tmp = [];
		tmp.push("<div class=\'comment\'>");
		tmp.push("<div class=\'incomment\'>");
		tmp.push("<div class=\'post_comment\'>");
		tmp.push("<div style=\'font-size: 12px;\'>");
		tmp.push("<span class=\'left\'>还可以输入<span class=\'remain_count\'>140<\/span>个字<\/span>");
		tmp.push("<div class=\'clearfix\'><\/div>");
		tmp.push("<\/div>");
		tmp.push("<textarea style=\'width: 553px; height: 35px; overflow-x: hidden; margin:8px 0;\' onkeyup=\'Better_Commment_setRemainCounts($(this));\' onmousedown=\'Better_Commment_setRemainCounts($(this));\'>");
		tmp.push("回复@"+btn.attr('nick')+" :");
		tmp.push("<\/textarea>");
		tmp.push("<div style=\'font-size: 12px;\'>");
		tmp.push("<span class=\'left\'>");
		if(btn.attr('allow_rt')){
			tmp.push("<input type=\'checkbox\' class=\'to_shout\' onclick=\'switch_sync($(this));\'>同时发布到吼吼<br>");
		}else{
			tmp.push('原文不允许被转发');
		}
		if(window.hasSync==true){
			tmp.push("<span class='hide'><input type='checkbox' class='sync'>分享到我已绑定的社交网络</span>");
		}
		tmp.push("<\/span>");
		tmp.push("<a class=\'comment_btn button right\' href=\'javascript: void(0);\' onclick=\"Better_postComment(this, \'"+btn.attr('bid')+"\');\">发表评论<\/a>");
		tmp.push("<div class=\'clearfix\'><\/div>");
		tmp.push("<\/div>");
		tmp.push("<\/div>");
		tmp.push("<\/div>");
		tmp.push("<\/div>");
		
		var result = tmp.join('');
		btn.closest('div.msg').append(result);
		btn.text('收起回复');
	}else{
		btn.parent().next('div').remove();
		btn.text('回复');
	}
}


function switch_sync(checkbox){
	var nextspan = checkbox.nextAll('span');
	if(checkbox.attr('checked')){
		nextspan.show();
	}else{
		nextspan.hide();
		nextspan.find('input.sync').attr('checked', false);
	}
}
