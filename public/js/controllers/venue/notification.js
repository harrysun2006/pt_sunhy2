/**
 * 优惠图片上传
 * 
 */

function Better_GetPostLengthById(textid)
{
	txt = $(textid).val() ;
	txt = txt.split('\r').join("");
	reg = /(https?:\/\/[-_\w./?%(&amp;)=\d]+)(\s|\/\/|$)/ig;
	txt = txt.replace(reg, "http://bedo.cn");
	txt = txt.replace(/[\r\n]/g, '');
	txt = $.trim(txt);
	
	len = parseInt(txt.length);
	
	return len;
}

function Better_Market_Notification(){
	//alert('xxa');
	var begtm = $.trim($('#begintm').val());
	var begintm = Better_TimetoUnix(begtm+':00');
	//alert(begintm);
	var end_tm = $.trim($('#endtm').val());
	var endtm = Better_TimetoUnix(end_tm+':59');
	var attach = $.trim($('#tempattach').val());	
	var poi_id = $.trim($('#poi_id').val());
	var r_id = $.trim($('#r_id').val());
	var content = $.trim($('#notification_content').val());
	
	//len = parseInt(content);
	len = Better_GetPostLengthById('#notification_content');
	checkinbeg = !(/^[0-9]*[1-9][0-9]*$/.test(begintm));
	checkinend = !(/^[0-9]*[1-9][0-9]*$/.test(endtm));	
	if((begintm-nowtm)<24*3600 || begintm=='' || checkinbeg){
		Better_Notify({
			msg: betterLang.special.begintm
		});

	} else if((endtm-begintm)>10000*3600 || (endtm<=begintm) || endtm=='' || checkinend){

		Better_Notify({
			msg: betterLang.special.endtm
		});
	} else if(len==0){
		Better_Notify({
			msg: betterLang.special.contentempty
		});
	} else if(len>1000){
		Better_Notify({
			msg: betterLang.blog.post_size_to_large.replace('%s', 1000)
		});
	}else {
		
		Better_Notify_loading();
		$.post('/venue/postnotification', {
			message: content, 			
			attach: attach,	
			begintm : begintm,
			endtm : endtm,		
			poi_id: poi_id,
			r_id: r_id
		}, function(pcJson){
			Better_Confirm_clear();
			if(pcJson.has_err==0){				
				window.location.href = '/venue/step3?id='+pcJson.poi_id;
			}else {
				Better_Notify({
					msg: '请勿提交不存在的申请优惠'
				});
			}
		},'json');
	}
	
}

function Better_Freview_Special(){	
	
	var content = $.trim($('#notification_content').val());
	
	imgurl = $('#end_btnUpload').attr("src");
    if(imgurl){    
    	content = content + "<img src='"+imgurl+"' style='width:137px' />";
    }
	$('#view_special_txt').html(content);
}

$(function(){

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
				//$('#tempbtnPostNew').attr('disabled',true);
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
			//$('#tempbtnPostNew').attr('disabled', false);
			
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
					$('#end_btnUpload').attr('src', rt.new_file_url).addClass('avatar').css('height', '76px').css('width', '80px').load(function(){
						w = $(this).width();
						h = $(this).height();							
						if (h>80) {
							$(this).css('height', '76px');
							$(this).css('width', (w*76/h)+'px');
						}
					});
					} catch(ee){
						
					}
				}

				del = $(document.createElement('a'));
				del.attr('href', 'javascript:void(0)');
				del.text(betterLang.blog.deleteit);
				del.click(function(){
					Better_Confirm({
						msg: betterLang.blog.upload_delete_confirm,
						onConfirm: function(){
							Better_Notify_loading();
							
							$.post('/ajax/attach/delete', {
								attach: $('#tempattach').val()
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
									$('#tempfileDesc').empty().hide();
									$('#tempattach').val('');
									$('#tempbtnUpload').attr('src', '/images/photo.png').css('width', '80px').css('height', '80px').removeClass('avatar');
								}
							}, 'json');							
						}
					});
				});
			}
		}
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
		Better_Market_Notification();
	});
	
	d = new Date();			
	nowtm =Math.round(d.setTime(d.getTime()/1000));	
	$('#begintm').val(Better_UnixtoTime(nowtm+25*3600,'YY-MM-DD hh:mm'));
	$('#endtm').val(Better_UnixtoTime(nowtm+90*24*3600,'YY-MM-DD hh:mm'));	
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
	$('#_freview_special').click(function(){
		Better_Freview_Special();
	});
	$('#venue_change_tm').click(function(){		
		showtm = $('#show_tm').val();		
		if($('#show_tm').val()!=0){
			$('#special_tm').hide();
			$('#show_tm').val(0);
		} else {
			$('#special_tm').show();
			$('#show_tm').val(1);
		}
	});
});