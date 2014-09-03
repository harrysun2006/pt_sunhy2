$(function(){	
	d = new Date();			
	nowtm =Math.round(d.setTime(d.getTime()/1000));
	$('#new_banner #begintm').val(Better_UnixtoTime(nowtm+25*3600,'YY-MM-DD hh:mm'));
	$('#new_banner #endtm').val(Better_UnixtoTime(nowtm+49*3600,'YY-MM-DD hh:mm'));		
	
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
	
	$('#banner_new').click(function(){			
		
		begintm = Better_TimetoUnix($('#begintm').val()+':00');
		endtm = Better_TimetoUnix($('#endtm').val()+':59');
		if(begintm>endtm){
			alert('结束时间不能早于开始时间');
			return false;
		}
		$('#new_banner').submit();		
	});
	
	$('#banner_update').click(function(){			
	
		begintm = Better_TimetoUnix($('#begintm').val()+':00');
		endtm = Better_TimetoUnix($('#endtm').val()+':59');
		action = $.trim($('#action').val());		
		if(begintm>endtm){
			alert('结束时间不能早于开始时间');
			return false;
		}
		$('#update_banner').submit();		
	});
	
	
});