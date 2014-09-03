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
		$.post(BETTER_ADMIN_URL+'/special/checkspecial', {
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
		$.post(BETTER_ADMIN_URL+'/special/checkspecial', {
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
		if(content.length>1000 || content.length<3){
			alert('字数不能超过1000个，不能少于3个');
			return false;
		} 
		if(begintm>endtm){
			alert('结束时间不能早于开始时间');
			return false;
		}
		$('#new_special').submit();		
	});
	
	$('#special_update').click(function(){			
		content=$.trim($('#content').val());
		begintm = Better_TimetoUnix($('#begintm').val()+':00');
		endtm = Better_TimetoUnix($('#endtm').val()+':59');
		action = $.trim($('#action').val());
		if(content.length>1000 || content.length<3){
			alert('字数不能超过1000个，不能少于3个');
			return false;
		} 
		if(begintm>endtm){
			alert('结束时间不能早于开始时间');
			return false;
		}
		$('#update_special').submit();		
	});
	/*
	
	$('#special_update').click(function(){			
		id=$.trim($('#nid').val());	
		content=$.trim($('#content').val());
		begintm = Better_TimetoUnix($('#begintm').val()+':00');
		endtm = Better_TimetoUnix($('#endtm').val()+':59');
		action = $.trim($('#action').val());
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
		$.post(BETTER_ADMIN_URL+'/special/doupdatespecial', {
			'nid': id,				
			'poi_id' : poi_id,
			'content' : content,
			'begintm' : begintm,
			'endtm' : endtm,
			'action': action,
			'attach' : attach
		}, function(json){
			Better_Notify_clear();
			alert(json.result);
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
	*/
	
});