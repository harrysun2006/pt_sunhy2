
/**
 * 附近的消息
 * 
 * @param page
 * @param renew
 * @return
 */


function Better_PoiOwner_Notification(){
	
	attach = $('#tempattach').val();
	
	content = $('#notification_content').val();		
	
	begintm = Better_TimetoUnix($('#begintm').val()+':00');
	
	endtm = Better_TimetoUnix($('#endtm').val()+':59');

	endnid = $('#tempnid').val();
	
	len = Better_GetPostLengthById('#notification_content');

	checkinbeg = !(/^[0-9]*[1-9][0-9]*$/.test(begintm));

	checkinend = !(/^[0-9]*[1-9][0-9]*$/.test(endtm));	

	if((begintm-nowtm)<24*3600 || checkinbeg){
		Better_Notify({
			msg: betterLang.special.begintm
		});
	} else if((endtm-begintm)>10000*3600 || (endtm<=begintm) || checkinend){
		Better_Notify({
			msg: betterLang.special.endtm
		});
	} else if(len>Better_PostSpecialMaxLength){
		Better_Notify({
			msg: betterLang.blog.post_size_to_large.replace('%s', Better_PostSpecialMaxLength)
		});
	} else {
		
		Better_Notify_loading();
		$.post('/ajax/poi/newspecial', {
			message: content, 			
			attach: attach,	
			begintm : begintm,
			endtm : endtm,
			nid: endnid,
			poi_id: $('#poi_id').val()
			}, function(json){				
				if (Better_AjaxCheck(json)) {					
					if (json.code=='success') {
						try{													
							$('#require_form').hide();
							$('#notification_content').val('');
							$('#tempattach').val('');
							$('#tempuploadurl').val('');
							$('#tempnid').val('');
							$('#tempbtnUpload').attr('src','');
							$('#tempuploadimg').attr('src','');
							$('#tempuploadimg').hide();
							$('#newspecial').show();
						} catch (e) {
							
						}
						Better_Notify({
							msg: betterLang.global.special.needcheck,
							close_timer: 2
						});
						window.location = BASE_URL+'/poi/owner?id='+$('#poi_id').val();
					} 
				}
		}, 'json');
	}	
	return false;
}

function Better_Freview_Special(){	
	content = $('#notification_content').val();
	imgurl = $('#tempuploadimg').attr("src");
    if(imgurl){    
    	content = content + "<img src='"+imgurl+"' style='width:137px' />";
    }
	$('#view_special_txt').html(content);
}
function Better_Business_Checkin_Hours(){	
	
	userdiv = $('#owern_checkin_hour');	
	userdiv.empty();
	try{
		$.get('/ajax/business/getcheckinhours', {
			poi_id: $('#poi_id').val(),
			begtm: Better_TimetoUnix($('#checkbegtm').val()+" 00:00:00"),
			endtm: Better_TimetoUnix($('#checkendtm').val()+" 23:59:59")
		}, function(hourscheckin){		
			if (hourscheckin.result!=null) {
				 rowdiv = "";
				 userdiv.append(rowdiv);
				 abc = $.parseJSON(hourscheckin.result);
				$.each(abc, function(pi, row){				
					rowdiv = "<li><div class='hourscheckin_tm '>"+row.time_interval+"</div><div class='hourscheckin_per'><b>"+row.per+"</b>%</div><div class='hourscheckin_times'><b>"+row.times+"</b>人次</div></li>";
					if(rowdiv){
						userdiv.append(rowdiv);
					}else{
						userdiv.empty();
					}
				});			
			}
		}, 'json');
	} catch(e){
	}
	return false;
}





function Better_Business_Checkin_Gender(){	
	
	
	try{
		$.get('/ajax/business/getcheckingender', {
			poi_id: $('#poi_id').val(),
			begtm: Better_TimetoUnix($('#checkbegtm').val()+" 00:00:00"),
			endtm: Better_TimetoUnix($('#checkendtm').val()+" 23:59:59")
		}, function(ce){	
			Better_Notify_clear();
			if(ce.result){		
				$('#checkin_genderdate').html(ce.result);
				$('#updatetm').trigger('click');
			}
		}, 'json');
	} catch(e){
	}
	
	return false;
}




function Better_Poi_Owner_Checkin_gender(){
	var checkingender = '';
	try {		
		checkingender = $.parseJSON($('#checkin_genderdate').html());		
	} catch(e) {
		
	}	
	chart = new Highcharts.Chart({
		chart: {
			renderTo: 'checkin_gender',
			margin: [50, 100, 60, 70]
		},
		title: {
			text: ''
		},
		plotArea: {
			shadow: null,
			borderWidth: null,
			backgroundColor: null
		},
		tooltip: {
			formatter: function() {
				return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
			}
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				dataLabels: {
					enabled: true,
					formatter: function() {
						if (this.y > 5) return this.point.name;
					},
					color: 'white',
					style: {
						font: '13px Trebuchet MS, Verdana, sans-serif'
					}
				}
			}
		},
		legend: {
			layout: 'vertical',
			style: {
				left: 'auto',
				bottom: 'auto',
				right: '50px',
				top: '100px'
			}
		},
	        series: [{
			type: 'pie',
			name: 'Browser share',
			data: checkingender
			//data: [3.40, 1.05, 2.90, 1.65, 1.35, 2.59, 1.39, 3.07, 2.82]
		}]
	});
}

function Better_Business_Checkin_Days(){	
	
	try {
		$.get('/ajax/business/getcheckindays', {
			poi_id: $('#poi_id').val(),
			begtm: Better_TimetoUnix($('#checkbegtm').val()+" 00:00:00"),
			endtm: Better_TimetoUnix($('#checkendtm').val()+" 23:59:59")
		}, function(checkindays){		
			if(checkindays.days){				
				$('#checkindate').html(checkindays.days);
				$('#checkinnum').html(checkindays.checkin);
				$('#updatetm').trigger('click');
			}
		}, 'json');
	} catch(e){
	}

	return false;
}

function Better_Poi_Owner_Checkin_thirdydays(){
	
	var checkindate = '';	
	var checkinnum = ''; 
	try {
		checkindate = $.parseJSON($('#checkindate').html());
		checkinnum = $.parseJSON($('#checkinnum').html());
	} catch(bd){
	}
	chart = new Highcharts.Chart({
		chart: {
			renderTo: 'checkinthird',
			defaultSeriesType: 'column',
			margin: [ 50, 50, 100, 80]
		},
		title: {
			text: ''
		},
		xAxis: {
			categories:checkindate,
			labels: {				
				align: 'center',
				style: {
					 font: 'normal 12px Verdana, sans-serif'
				}
			}
		},
		yAxis: {
			min: 0,
			title: {
				text: betterLang.owner.checkin_times_y
			}		
		},
		legend: {
			enabled: false
		},
		tooltip: {
			formatter: function() {
				return betterLang.owner.checkin_times_title.replace('{TIMES}',Highcharts.numberFormat(this.y, 1));				
			}
		},
	        series: [{
			name: 'Population',
			data: checkinnum,
			dataLabels: {
				enabled: true,
				rotation: -90,
				color: '#FFFFFF',
				align: 'right',
				x: -3,
				y: 10,
				formatter: function() {
					return this.y;
				},
				style: {
					font: 'normal 13px Verdana, sans-serif'
				}
			}			
		}]
	});
	
		
}

function Better_Business_Poisync(){	
	
	try{
		$.get('/ajax/business/getpoisync', {
			poi_id: $('#poi_id').val(),
			begtm: Better_TimetoUnix($('#checkbegtm').val()+" 00:00:00"),
			endtm: Better_TimetoUnix($('#checkendtm').val()+" 23:59:59")
		}, function(synclist){		
			if(synclist.sync_nums){				
				$('#synclist_nums').html(synclist.sync_nums);
				$('#synclist_site').html(synclist.sync_site);
				$('#updatetm').trigger('click');
			}
		}, 'json');
	} catch(e){
	}

	return false;
}

function Better_Poi_Owner_Sync_tips(){
	
	var sync_nums = '';	
	var sync_site = '';	
	try{
		sync_nums = $.parseJSON($('#synclist_nums').html());	
		sync_site = $.parseJSON($('#synclist_site').html());	
	} catch(be){
	}
	chart = new Highcharts.Chart({
		chart: {
			renderTo: 'poi_sync',
			defaultSeriesType: 'column',
			margin: [ 50, 50, 100, 80]
		},
		title: {
			text: ''
		},
		xAxis: {
			categories:sync_site,
			labels: {				
				align: 'center',
				style: {
					 font: 'normal 12px Verdana, sans-serif'
				}
			}
		},
		yAxis: {
			min: 0,
			title: {
				text:  betterLang.owner.sync_times_y
			}
		},
		legend: {
			enabled: false
		},
		tooltip: {
			formatter: function() {
				return betterLang.owner.sync_times_title.replace('{TIMES}',Highcharts.numberFormat(this.y, 1));		
			}
		},
	        series: [{
			name: 'Population',
			data: sync_nums,
			dataLabels: {
				enabled: true,
				rotation: -90,
				color: '#FFFFFF',
				align: 'right',
				x: -3,
				y: 10,
				formatter: function() {
					return this.y;
				},
				style: {
					font: 'normal 14px Verdana, sans-serif'
				}
			}			
		}]
	});
		
}


function Better_Switch_Shout_Form_To_Special()
{
	Better_Shout_Type = 'special';
	Better_Shout_Without_Poi = false;	
	$('#tempshout_title').text(betterLang.global.special.title);
	$('#temptipsformicon').show();
	$('#tempshoutformicon').hide();	
	$('#view_btn').text(betterLang.global.special.text);
	$('#tempdisable_shout_poi').hide();
	$('#tempdiv_change_poi').hide();
	$('#tempshout_poi_list').css('padding-left', '20px');
	$('#tempshout_priv, #check_sync').hide();
	$('#temptxtCount').html(Better_PostSpecialMaxLength);
	if (Better_Poi_Id) {
		$('#tempready_to_shout_poi').val(Better_Poi_Id);
		$('#tempready_to_shout_address').text(Better_Poi_Detail.address);
		$('#tempready_to_shout_city').text(Better_Poi_Detail.city);
		$('#tempready_to_shout_poi_name').text(Better_Poi_Detail.name);	
		$('#end_ready_to_shout_poi').val(Better_Poi_Id);
		$('#end_ready_to_shout_address').text(Better_Poi_Detail.address);
		$('#end_ready_to_shout_city').text(Better_Poi_Detail.city);
		$('#end_ready_to_shout_poi_name').text(Better_Poi_Detail.name);	
	}	
}


function Better_Switch_Shout_Form_View_Special()
{
	Better_Shout_Type = 'special';
	Better_Shout_Without_Poi = false;	
	$('#end_shout_title').text(betterLang.global.special.viewtitle);
	$('#end_tipsformicon').show();
	$('#end_shoutformicon').hide();	
	$('#post_special_btn').text(betterLang.global.special.submit);
	$('#back_special_btn').text(betterLang.global.special.back);
	$('#end_disable_shout_poi').hide();
	$('#end_div_change_poi').hide();
	$('#end_shout_poi_list').css('padding-left', '20px');
	$('#end_shout_priv, #end_check_sync').hide();
	
}

function Better_FilterStatusById(textid)
{
	len = Better_GetPostLengthById(textid);
	slen = Better_PostSpecialMaxLength - len;
	if (len>slen) {
		$('#temptxtCount').removeClass('green').addClass('red');
	} else if (len<Better_PostSpecialMinLength && len>0) {
		$('#temptxtCount').removeClass('green').addClass('red');
	} else {
		$('#temptxtCount').removeClass('red').addClass('green');
	}
	
	$('#temptxtCount').html(slen);
}

function Better_GetPostLengthById(textid)
{
	txt = $('#notification_content').val();
	txt = txt.split('\r').join("");
	reg = /(https?:\/\/[-_\w./?%(&amp;)=\d]+)(\s|\/\/|$)/ig;
	txt = txt.replace(reg, "http://bedo.cn");
	txt = txt.replace(/[\r\n]/g, '');
	txt = $.trim(txt);
	
	len = parseInt(txt.length);
	
	return len;
}


$(function() {
		
	var ajaxUploader = new AjaxUpload('#tempbtnUpload', {
		action: '/ajax/attach/upload',
		name: 'myfile',
		data: {
			attach: $('#tempattach').val()
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
				$('#tempbtnUpload').attr('disabled',true);
				$('#tempbtnPostNew').attr('disabled',true);
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

			$('#tempbtnUpload').attr('disabled', false);
			$('#tempbtnPostNew').attr('disabled', false);
			
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
				
				$('#tempattach').val(rt.attach);
				$('#avatar_path').val(rt.attach);
				$('#tempuploadurl').val(rt.new_file_url);
				$('#tempuploadimg').show();
				
				if (typeof(rt.new_file_url)!='undefined' && rt.new_file_url!='') {
					$('#tempbtnUpload').load(function(){
						w = $(this).width();
						h = $(this).height();
						
						if (h>80) {
							$(this).css('height', '76px');
							$(this).css('width', (w*76/h)+'px');
						}
					});
					try{							
					$('#tempuploadimg').attr('src', rt.new_file_url).css('width', '80px').load(function(){
						
					});
					} catch(ee){
						
					}
				}

				
			}
		}
});
	d = new Date();			
	nowtm =Math.round(d.setTime(d.getTime()/1000));	

	Better_Business_Checkin_Gender();	
	Better_Business_Checkin_Days();
	Better_Business_Poisync();
	Better_Business_Checkin_Hours();
	
	
	$('#updatetm').click(function(){
		Better_Poi_Owner_Checkin_gender();
		Better_Poi_Owner_Checkin_thirdydays();	
		Better_Poi_Owner_Sync_tips();
		$('#owner_report_title').empty();
		testowner_report_title = betterLang.owner.owner_report_title.toString().replace('{BEGTM}',$('#checkbegtm').val()).replace('{ENDTM}',$('#checkendtm').val());
		$('#owner_report_title').html(testowner_report_title);
	});
	
	$('#newspecial').click(function(){
		$('#begintm').val(Better_UnixtoTime(nowtm+25*3600,'YY-MM-DD hh:mm'));
		$('#endtm').val(Better_UnixtoTime(nowtm+90*24*3600,'YY-MM-DD hh:mm'));	
		$('#newspecial').hide();
		$('#tempattach').val();
		$('#tempuploadurl').val();
		$('#tempnid').val();
		$('#require_form').show();	
	});
	
	
	$('#_freview_special').fancybox({
		'autoDimensions': false,
		'scrolling': 'no',
		'centerOnScroll': false,
		'titleShow': false,
		'height' : 490,
		'width' : 710,
		'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').css('height', '490px').css('width','710px');
				$('#fancybox-outer').css('background-color', '#1db8ee');
				ajaxUploader.enable();
				ajaxUploader._createInput();
				ajaxUploader._rerouteClicks();
			},
		'onClosed': function(){
				$('#fancybox-outer').css('background-color', '#fff');
			}
	});	
	
	
	$('#_viewspecial').fancybox({
		'modal' : true,
		'autoDimensions' : true,
		'height' : '280px',
		'width' : '624px',
		'scrolling' : 'no',
		'centerOnScroll' : false,
		'onStart' : function(){
			$('#fancybox-wrap, #fancybox-outer').css('height', '385px').css('width','624px');
			$('#fancybox-outer').css('background-color', '#1db8ee');
									
		},
		'onClosed': function(){
			$('#fancybox-outer').css('background-color', '#fff');
		}
	});	
	$('.cancelspecial').click(function(){		
		var nid =$(this).attr('xxnid');
		var poi_id =$(this).attr('ref');	
		$.post('/ajax/poi/cancelspecial', {
			nid: nid, 
			poi_id: poi_id
			}, function(json){				
				if (Better_AjaxCheck(json)) {	
					
					if (json.code=='success') {
						try{
							$('#special'+nid).hide();
							$()
						} catch (e) {
							
						}
						Better_Notify({
							msg: betterLang.global.special.ownercancel,
							close_timer: 2
						});

					} 
				}
		}, 'json');
		return false;
	});
	
	

	$('.editspecial').unbind('click').click(function(){			
		var nid =$(this).attr('xxnid');
		var poi_id =$(this).attr('ref');		
		tempstatus_text = $('#special_text_'+nid).html();		
		attach = $('#special_img_id_'+nid).html();
		end_img = $('#special_img_url_'+nid).html();
		temp_begintm = $('#special_begintm_'+nid).html();
		temp_endtm = $('#special_endtm_'+nid).html();
		$('#newspecial').hide();
		$('#require_form').show();
		$('#notification_content').html(tempstatus_text);
		$('#notification_content').val(tempstatus_text);		
		if(attach){
			$('#tempattach').val(attach);
			$('#tempbtnUpload').attr('src',end_img );
			$('#tempuploadimg').show();
			$('#tempuploadimg').attr('src',end_img);		
			$('#tempuploadurl').val(end_img);
		} else {
			$('#tempattach').val('');
			$('#tempbtnUpload').attr('src','' );
			$('#tempuploadimg').hide();
			$('#tempuploadimg').attr('src','');		
			$('#tempuploadurl').val('');
		}
		$('#begintm').val(temp_begintm);
		$('#endtm').val(temp_endtm);
		$('#tempnid').val(nid);			
		return false;		
	});	
	
	
	$('#tempclose_shout, #end_close_shout').click(function(){
		$('#tempstatus_text').val('');		
		$('#tempattach').val('');
		$('#tempupbid').val(0);
		$('#tempnid').val(0);
		$('#tempfileDesc').empty().hide();
		$('#tempdivFileDesc').hide();
		$('#temptxtCount').html(Better_PostSpecialMaxLength);
		$('#tempbtnUpload').attr('src', '/images/photo.png').css('width', '80px').css('height', '80px').removeClass('avatar');		
		ajaxUploader.disable();
		ajaxUploader.destroy();
		$.fancybox.close();		
		return false;
	});
	try {
		days = betterLang.calendar.days.toString().split(',');
		months = betterLang.calendar.months.toString().split(',');
	}catch (e) {
	}
	$('#checkbegtm').datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-d'	,
		minDate : new Date(1900,1-1,1)	,
		yearRange : '2011:2011' , 
		dayNames : days,
		dayNamesMin :  days	,
		dayNamesShort :  days,
		monthNames : months,
		monthNamesShort : months,		
		defaultDate :  ''
	});	
	
	$('#checkendtm').datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-d'	,
		minDate : new Date(1900,1-1,1)	,
		yearRange : '2011:2011' , 
		dayNames : days,
		dayNamesMin :  days	,
		dayNamesShort :  days,
		monthNames : months,
		monthNamesShort : months,		
		defaultDate :  ''
	});	
	$('#changetime').click(function(){
		Better_Business_Checkin_Gender();	
		Better_Business_Checkin_Days();
		Better_Business_Poisync();
		Better_Business_Checkin_Hours();
	});
	$('#notification_content').blur(function(){
		if($(this).val()==''){
			$(this).css('color', '#999');
			$(this).text(betterLang.venue.specialtext.mark);
		}
	}).focus(function(){
		if($(this).val()==betterLang.venue.specialtext.mark){
			$(this).val('');
			$(this).css('color', '#333');
		}
	});		
	$('#avatar_path').val(betterLang.venue.attach.mark);
	$('#venue_notification_submit').click(function(){	
		
		Better_PoiOwner_Notification();
	});
	$('#_freview_special').click(function(){
		Better_Freview_Special();
	});
	$('a.freview_link').click(function(){
		
		var xxtxt =$(this).attr('xxtxt');
		var xximg =$(this).attr('xximg');		
		content = xxtxt;
		imgurl = xximg;
	    if(imgurl){    
	    	content = content + "<img src='"+imgurl+"' style='width:137px' />";
	    }
		$('#freview_form_txt').html(content);
	});
	$('a.freview_link').fancybox({
		'autoDimensions': false,
		'scrolling': 'no',
		'centerOnScroll': false,
		'titleShow': false,
		'height' : 490,
		'width' : 710,
		'onStart' : function(){
				$('#fancybox-wrap, #fancybox-outer').css('height', '490px').css('width','710px');
				$('#fancybox-outer').css('background-color', '#1db8ee');
				ajaxUploader.enable();
				ajaxUploader._createInput();
				ajaxUploader._rerouteClicks();
			},
		'onClosed': function(){
				$('#fancybox-outer').css('background-color', '#fff');
			}
	});	
	
	//Better_Poi_Specialist();
});
