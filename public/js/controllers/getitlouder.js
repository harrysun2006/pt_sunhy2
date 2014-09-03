/**
 * 解析消息中的超链接
 * 
 * @param match
 * @return
 */
function Better_replaceHttplink(match)
{
	link = ' <a href="'+$.trim(match)+'" class="blank">'+$.trim(match)+'</a>';
	return link;
}


function Better_nl2br(str)
{
	var breakTag='';
	breakTag='<br />';

	return (str+'').replace(/([^>]?)\n/g,'$1'+breakTag+'\n');
}

function Better_parseMessage(row)
{
	txt = $.trim(row.message);
	if (txt!='') {
		txt = Better_nl2br(txt+' ');
	} else if (txt=='' && row.attach_thumb) {
		txt = betterLang.blog_with_photo_no_message+ ' ';
	} else if (txt=='' && row.upbid) {
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
	
	/*
	//解析@+昵称
	*//**
	 * @TODO 由于昵称将允许中文字符，所以这个地方的判断需要修改了
	 *//*
	pat = /^@([a-zA-Z0-9\-_]+)([\s\n\r\.\,\，\。 ]+)/ig;

	if (txt.match(pat)) {
		txt = betterLang.blog.reply+' '+txt;
	}
	pat = /@([a-zA-Z0-9\-_]+)([\s\n\r\.\,\，\。]+)/ig;
	txt = txt.replace(pat, Better_replaceAt);
	
	// 解析“转发”字符
	pat = /^RT([a-zA-Z0-9\-_]+)([\s\r\n]+)/ig;
	if (txt.match(pat)) {
		txt = txt.replace(pat, Better_replaceRt);
	}  */
	
	return txt;
}

/**
 * 所有最新
 * 
 * @param page
 * @return
 */
function Better_Index_loadLastest(page, hiddencount, isfirst) 
{
	var page = page ? page : 1;
	var key = 'now_doing_temp';
	var hiddencount = typeof(hiddencount)!='undefined' ? hiddencount : 6;
	var isfirst = typeof(isfirst)!='undefined' ? isfirst : false;
	
	if(isfirst){
		var div = $('#now_doing');
	}else{
		var div = $('#now_doing_temp');
	}
	
	div.empty();
	
	$.get('/maf/getitlouder/blogs', {
		page: page,
		action: 'index'
	}, function(listJson){
			
		if (listJson.rows!=null) {
			$.each(listJson.rows, function(pi, row){
				var rowdiv = Better_Load_IndexBlogRow(row);
				if(rowdiv){
					div.append(rowdiv);
				}else{
					div.empty();
				}
			});
			
			for(var i=0; i<hiddencount; i++){
				if (typeof(listJson.rows[i])!='undefined') {
					var bid=listJson.rows[i].bid;
					var bid_key = bid.replace('.', '_');
					$('#listRow_now_doing_'+bid_key).hide();
				}
			}
		}
		
	}, 'json');

}

function Better_Load_IndexBlogRow(row){
	id = 'now_doing';	
	row_div = $(document.createElement('div')).addClass('blog_row');
	row_div.attr('id', 'listRow_'+id+'_'+row.bid.replace('.', '_'));
	avatar_div = $(document.createElement('div')).addClass('left').addClass('avatar').css('float', 'left');	
	avatar_div.find('img').error(function(){
		$(this).attr('src', 'http://k.ai/images/noavatar.gif');
	});	
	info_div = $(document.createElement('div')).addClass('left').addClass('info').css('margin-left', '10px').css('float', 'left');
	mess_div = $(document.createElement('div')).addClass('message_row').css('margin-top', '10px').css('width','230px').css('overflow','hidden');
	user_span = $(document.createElement('span'));
	user_span.html(row.nickname+' 在<span class="poi_where">'+row.poi.name+'</span><br/>').addClass('message_where');
	mess_span = $(document.createElement('span')).css('float','left').css('width','230px');
	if ($.trim(row.type)=='checkin' && (row.major>0 || (row.badge_id>0 && typeof(row.badge_detail)!='undefined'))) {
		message = '';
		if(row.major>0){
			message = '成为 掌门';
			message = message +'<br/><img src="/images/crown.png">';
		}
		if(row.badge_id>0){
			message = '获得 '+row.badge_detail.name+' 勋章';
			message = message +'<br/><img src="'+row.badge_detail.picture.toString().replace('badges', 'badges/48w/')+'" alt="" width="48">';
		}
		mess_span.html(message);
	} else {
		message = '';
		if(row.badge_id>0 && $.trim(row.attach)==''){			
			message = '<br/><img src="'+row.badge_detail.picture.toString().replace('badges', 'badges/48w/')+'" alt="" width="48">';
		}
		if($.trim(row.type)=='normal' || $.trim(row.type)=='tips'){			
			mess_span.html(Better_parseMessage(row)+message);
		}else if($.trim(row.type)=='checkin'){
			mess_span.html('签到'+message);
		}
	}
	if($.trim(row.attach)!=''){
		attach_span = $(document.createElement('span')).css('float','left').css('width','230px');	
		attach_span.html('<img src="'+row.attach_thumb+'" style="max-width:218px;">');
	}
	rowtime_span =  $(document.createElement('span')).addClass('message_time').css('float','left');
	rowtime_span.html(Better_compareTime(row.dateline)+'<div class="dateline" id='+row.dateline+' style="display:none;">'+row.dateline+'</div>');
	mess_div.append(user_span);
	mess_div.append(mess_span);
	if($.trim(row.attach)!=''){
		mess_div.append(attach_span);
	}
	mess_div.append(rowtime_span);
	ext_div = $(document.createElement('div')).addClass('ext');
	loca_span = $(document.createElement('span'));
	source_span =  $(document.createElement('span')).addClass('source');
	ext_div.append(loca_span);
	ext_div.append(source_span);

	info_div.append(mess_div);
	info_div.append(ext_div);
	row_div.append(avatar_div);
	row_div.append(info_div);
	//row_div.append('<h2 class="index_line" style="margin-top:2px;margin-bottom:2px;!margin-top:1px;!margin-bottom:1px"></h2>');
	row_div.append('<div style="clear:both;"></div>');	
	
	return row_div;
}


function Better_Index_loadPoilastest(page, hiddencount, isfirst) 
{
	var page = page ? page : 1;
	var key = 'poi_doing_temp';
	var hiddencount = typeof(hiddencount)!='undefined' ? hiddencount : 6;
	var isfirst = typeof(isfirst)!='undefined' ? isfirst : false;
	
	if(isfirst){
		var div = $('#poi_doing');
	}else{
		var div = $('#poi_doing_temp');
	}
	
	div.empty();
	
	$.get('/maf/getitlouder/poiblogs', {
		page: page,
		action: 'index'
	}, function(listJson2){
			
		if (listJson2.rows!=null) {
			$.each(listJson2.rows, function(pi, row){
				var rowdiv2 = Better_Load_IndexPoiBlogRow(row);
				if(rowdiv2){
					div.append(rowdiv2);
				}else{
					div.empty();
				}
			});		
			for(var i=0; i<hiddencount; i++){
				if (typeof(listJson2.rows[i])!='undefined') {
					var bid2=listJson2.rows[i].bid;
					var bid_key2 = bid2.replace('.', '_');
					$('#listRow_poi_doing_'+bid_key2).hide();
				}
			}			
		}		
	}, 'json');

}

function Better_Load_IndexPoiBlogRow(row){
	id = 'poi_doing';	
	row_div = $(document.createElement('div')).addClass('poi_row');
	row_div.attr('id', 'listRow_'+id+'_'+row.bid.replace('.', '_'));
	avatar_div = $(document.createElement('div')).addClass('avatar').css('float', 'left');	
	avatar_div.append('<img width="36" height="36" src="'+row.avatar_url+'" style="border:1px solid #eee; padding:1px;"/>');
	avatar_div.find('img').error(function(){
		$(this).attr('src', 'http://k.ai/images/noavatar.gif');
	});	
	info_div = $(document.createElement('div')).addClass('info').css('margin-left', '10px').css('float', 'left');
	mess_div = $(document.createElement('div')).css('overflow','hidden').css('margin-top', '10px');
	user_span = $(document.createElement('span')).addClass('message_where').css('float','left');
	user_span.html(row.nickname+' 在<span class="poi_where">'+row.poi.name+'</span><br/>');
	mess_span = $(document.createElement('span')).css('float','left').css('overflow','hidden').css('width', '500px').css('height','18px').css('text-align', 'left');
	rowtime_mess = Better_compareTime(row.dateline);
	if ($.trim(row.type)=='checkin' && (row.major>0 || (row.badge_id>0 && typeof(row.badge_detail)!='undefined'))) {
		message = '';
		if(row.major>0){
			message = '成为 掌门';
		}
		if(row.badge_id>0){
			message = '获得 '+row.badge_detail.name+' 勋章';
		}
		mess_span.html(message+rowtime_mess);
	} else {
		if($.trim(row.type)=='normal' || $.trim(row.type)=='tips'){
			mess_span.html(Better_parseMessage(row)+rowtime_mess);
		}else if($.trim(row.type)=='checkin'){
			mess_span.html('签到'+rowtime_mess);
		}else if($.trim(row.attach)!=''){
			mess_span.html('上传了一张新图'+rowtime_mess);
		}
	}	
	mess_div.append(user_span);
	mess_div.append(mess_span);
	ext_div = $(document.createElement('div')).addClass('ext');
	loca_span = $(document.createElement('span'));
	source_span =  $(document.createElement('span')).addClass('source');
	ext_div.append(loca_span);
	ext_div.append(source_span);
	info_div.append(mess_div);
	info_div.append(ext_div);
	row_div.append(avatar_div);
	row_div.append(info_div);	
	row_div.append('<div style="clear:both;"></div>');		
	return row_div;
}
function JumpMovie(movieName) {
    if (navigator.appName.indexOf("Microsoft") != -1) {
        return window[movieName]
    }
    else {
    	//alert(document[movieName]);
        return document[movieName]
    }
}
function CallDetail(refid)
{
	JumpMovie('bedo_plugin').getHomeDetail(refid);
	//document['bedo_plugin'].getHomeDetail(refid);
	return false;
}



$(function(){
	Better_Index_loadLastest(1, 3, true);
	Better_Index_loadPoilastest(1, 9, true);

	setInterval(function(){
		
		if($('#now_doing div.blog_row:hidden').length==3){
			Better_Index_loadLastest(1, 0, false);			
			$('#now_doing').ajaxComplete(function(event,request, settings){
				$(this).prepend($('#now_doing_temp').children().hide());
			});
		}		
		$('#now_doing div.blog_row:hidden:last').slideDown(10, function(){			
			$('#now_doing div.blog_row:visible:last').remove();
			tempid = $('#now_doing div.blog_row:visible:first div.dateline').attr("id");
			//alert(tempid);
			
			CallDetail(tempid);
			
		});	
		
	
		
	}, 5000);
	
	setInterval(function(){	
		if($('#poi_doing div.poi_row:hidden').length==9){
			Better_Index_loadPoilastest(1, 0, false);			
			$('#poi_doing').ajaxComplete(function(e2,r2, s2){
				$(this).prepend($('#poi_doing_temp').children().hide());
			});
		}	
		
		$('#poi_doing div.poi_row:first').fadeOut(1000, function(){
			$('#poi_doing div.poi_row:visible:last').fadeOut(2000).remove();
			$('#poi_doing div.poi_row:hidden:last').fadeIn(2000);
		});
	},3000);

});