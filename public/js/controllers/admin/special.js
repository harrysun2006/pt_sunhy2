$(function(){	
	d = new Date();			
	nowtm =Math.round(d.setTime(d.getTime()/1000));
	$('#new_special #begintm').val(Better_UnixtoTime(nowtm+25*3600,'YY-MM-DD hh:mm'));
	$('#new_special #endtm').val(Better_UnixtoTime(nowtm+49*3600,'YY-MM-DD hh:mm'));		
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/special';
	});
	$("input[name^='access_']").click(function(){	
	
		var id = $(this).attr('name').replace('access_','');
		var poi_id=  $(this).attr('temppoiid');	
		$.post(BETTER_ADMIN_URL+'/special/checkspecial', {
			'nid': id,
			'doing': 1,
			'poi_id' : poi_id
		}, function(json){
			Better_Notify_clear();
			if (json.result==1) {
				alert('更新成功');			
				$("#check_type_"+id).html('已审核');
				$("#doing_"+id).hide();
						
			} else {
				alert('更新失败');
				return false;
			}
		}, 'json');
		
	});		
	
	$("input[name^='accessall_']").click(function(){	
		
		var id = $(this).attr('name').replace('accessall_','');
		var poi_id=  $(this).attr('temppoiid');	
		$.post(BETTER_ADMIN_URL+'/special/checkallspecial', {
			'groupid':id,
			'nid': '',
			'doing': 1,
			'poi_id' : poi_id
		}, function(json){
			Better_Notify_clear();
			if (json.result==1) {
				alert('更新成功');			
				$("#check_type_"+id).html('已审核');
				$("#doing_"+id).hide();
						
			} else {
				alert('更新失败');
				return false;
			}
		}, 'json');
		
	});	
	
	
	$('#clear_image').click(function(){		
		id=$.trim($('#nid').val());		
		$('#btnUpload').attr('src', '/images/photo.png')
		attach = $.trim($('#attach').val());	
		$('#attach').val('');
		parent.$("#image_"+id).html();
		return false;
	});
	$('#refuse_spceial').click(function(){		
		id=$.trim($('#nid').val());	
		checkinfo=$.trim($('#checkinfo').val());
		poi_id=$.trim($('#poi_id').val());
		updategroup = $('#updategroup').attr('checked') ? '1' : '0';
		groupid = $.trim($('#groupid').val());		
		$.post(BETTER_ADMIN_URL+'/special/checkspecial', {
			'groupid' : groupid,
			'updategroup' : updategroup,
			'nid': id,
			'doing': 2,	
			'poi_id' : poi_id,
			'checkinfo' : checkinfo
		}, function(json){
			Better_Notify_clear();
			if (json.result==1) {
				alert('提交成功');			
				parent.$("#check_type_"+id).html('审核不通过');
				parent.$("#doing_"+id).hide();
				parent.$.fancybox.close();		
			} else {
				alert('提交失败');
				return false;
			}
		}, 'json');
	});
	$('#cancel_spceial').click(function(){		
		id=$.trim($('#nid').val());	
		checkinfo=$.trim($('#checkinfo').val());
		poi_id=$.trim($('#poi_id').val());
		updategroup = $('#updategroup').attr('checked') ? '1' : '0';
		groupid = $.trim($('#groupid').val());	
		$.post(BETTER_ADMIN_URL+'/special/checkspecial', {
			'groupid' : groupid,
			'updategroup' : updategroup,
			'nid': id,
			'doing': 4,	
			'poi_id' : poi_id,
			'checkinfo' : checkinfo
		}, function(json){
			Better_Notify_clear();
			if (json.result==1) {
				alert('提交成功');			
				parent.$("#check_type_"+id).html('用户取消');
				parent.$("#doing_"+id).hide();
				parent.$.fancybox.close();		
			} else {
				alert('提交失败');
				return false;
			}
		}, 'json');
	});

	$("a.xiugai, a.search").fancybox({
		'width'				: '75%',
		'height'			: '97%',
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe',
		'onStart' : function(){			
		}
	});	
	
	$('#special_new').click(function(){			
		content=$.trim($('#content').val());
		begintm = Better_TimetoUnix($('#begintm').val()+':00');
		endtm = Better_TimetoUnix($('#endtm').val()+':59');
		action = $.trim($('#action').val());
		bids = $.trim($('#bids').val());
		if(content.length>500 || content.length<3){
			alert('字数不能超过500个，不能少于3个');
			return false;
		} 
		if(begintm>endtm){
			alert('结束时间不能早于开始时间');
			return false;
		}
		poi_id=$.trim($('#poi_id').val());
		attach = $.trim($('#attach').val());
		imgurl = $.trim($('#btnUpload').attr('src'));		
		$.post(BETTER_ADMIN_URL+'/special/donewspecial', {						
			'poi_id' : poi_id,
			'content' : content,
			'begintm' : begintm,
			'endtm' : endtm,
			'action': action,
			'attach' : attach,
			'bids' : bids
		}, function(json){
			Better_Notify_clear();
			if (json.result==1) {
									 
				parent.$.fancybox.close();		
			} else {
				alert('新增失败');
				return false;
			}
		}, 'json');
		
	});
	
	
	$('#special_update').click(function(){			
		id=$.trim($('#nid').val());	
		content=$.trim($('#content').val());
		begintm = Better_TimetoUnix($('#begintm').val()+':00');
		endtm = Better_TimetoUnix($('#endtm').val()+':59');
		action = $.trim($('#action').val());
		bids = $.trim($('#bids').val());
		if(content.length>500 || content.length<3){
			alert('字数不能超过500个，不能少于3个');
			return false;
		} 
		if(begintm>endtm){
			alert('结束时间不能早于开始时间');
			return false;
		}
		poi_id=$.trim($('#poi_id').val());
		attach = $.trim($('#attach').val());
		updategroup = $('#updategroup').attr('checked') ? '1' : '0';
		groupid = $.trim($('#groupid').val());		
		imgurl = $.trim($('#btnUpload').attr('src'));		
		$.post(BETTER_ADMIN_URL+'/special/doupdatespecial', {
			'nid': id,				
			'poi_id' : poi_id,
			'content' : content,
			'begintm' : begintm,
			'endtm' : endtm,
			'action': action,
			'attach' : attach,
			'bids' : bids,
			'updategroup' :updategroup,
			'groupid' :groupid
		}, function(json){
			Better_Notify_clear();
			
			if (json.result==1) {
				alert('更新成功');				
				parent.$("#content_"+id).html(content);				
				if(imgurl && attach){
					imgurl = imgurl.replace('thumb_','tiny_');
					imghtml = "<img src='"+imgurl+"'>";
					parent.$("#image_"+id).html(imghtml);
				} else {
					try{
						parent.$("#image_"+id).html();
					} catch(e){
					}
				}							 
				parent.$.fancybox.close();		
			} else {
				alert('更新失败');
				return false;
			}
		}, 'json');
		
	});
	var ajaxUploader = new AjaxUpload('btnUpload', {
		action: BASE_URL+'/ajax/attach/upload',
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

			$('#special #btnUpload').attr('disabled', false);
			$('#special #btnPostNew').attr('disabled', false);
			
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
					$('#btnUpload').attr('src', rt.new_file_url).addClass('avatar').css('height', '76px').css('width', '80px').load(function(){
						w = $(this).width();
						h = $(this).height();
						
						if (h>80) {
							$(this).css('height', '76px');
							$(this).css('width', (w*76/h)+'px');
						}
					});
					$('#btnUpload').attr('src', rt.new_file_url).addClass('avatar').css('height', '76px').css('width', '80px').load(function(){
						w = $(this).width();
						h = $(this).height();
						
						if (h>80) {
							$(this).css('height', '76px');
							$(this).css('width', (w*76/h)+'px');
						}
					});
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
				});
			}
		}
	});
});