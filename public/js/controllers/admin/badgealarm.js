
$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/badgealarm';
	});

	
	$('tr.message_row a.xiugai').click(function(){
		var id=$(this).attr('id');
		$('#update_div').fadeIn();
		$('#xid').val(id);
		$('#id_update').text(id);
		$('#name_update').text($('#'+id+'_name').val());
		$('#begintime_update').val($('#'+id+'_begintime').val());
		$('#endtime_update').val($('#'+id+'_endtime').val());
		$('#interval_update').val($('#'+id+'_interval').val());
		return false;
	}
	);

	$('#btnfangqi').click(function(){
		$('#update_div').fadeOut();
	}
	);


$('#btnUpdate').click(function(){
	var id=$('#xid').val();
	var begintime = $('#begintime_update').val();
	var endtime = $('#endtime_update').val();
	var interval = $('#interval_update').val();
	
	if(begintime.length==0){
		alert("请输入开始时间");
		return false;
	}
	
	if(endtime.length==0){
		alert("请输入结束时间");
		return false;
	}
	
	if(interval.length==0){
		alert("请输入间隔时间");
		return false;
	}
	
	
	Better_Notify_loading({
		title: 'Loading ...',
		msg: '正在操作，请稍候...'
	});
	
	$.post(BETTER_ADMIN_URL+'/badgealarm/update', {
		'xid': id,
		'begintime':begintime,
		'endtime': endtime,
		'interval': interval
	}, function(json){
		Better_Notify_clear();
		if (json.result==1) {
			alert('更新成功');
			$('#reload').val(1);
			$('#search_form').trigger('submit');
			
		} else {
			alert('更新失败');
			return false;
		}
	}, 'json');
}
);
	


	//删除
	$('#btn_del').click(function(){
		var bids = [];
		$('tr.message_row input[type=checkbox]').each(function(){
			if($(this).attr('checked')){
				bids.push($(this).val());
			}
		});
		
		if(bids.length<=0){
			Better_Notify('请选择一条');
			return false;
		}
		
		Better_Confirm({
			'msg': '确认要删除？',
			'onConfirm': function(){
				$.post(BETTER_ADMIN_URL+'/badgealarm/delete', {
					'bids[]': bids
				}, function(json){
					if(json.result==1){
						alert('删除成功');
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					}else{
						alert('删除失败');
						return false;
					}
				}, 'json');
			}
		});
		
	});


	$('#btnaddfangqi').click(function(){
		$('#add_div').hide();
	});
	
	$('a[id$=_watch]').click(function(){
		$('#id_add').text($(this).attr('bid'));
		$('#name_add').text($(this).attr('bname'));
		$('#add_div').show();
	});
	
	$('a[id$=_unwatch]').click(function(){
		var bids = [];
		bids.push($(this).attr('bid'));
		Better_Confirm({
			'msg': '确认要取消？',
			'onConfirm': function(){
				$.post(BETTER_ADMIN_URL+'/badgealarm/delete', {
					'bids[]': bids
				}, function(json){
					if(json.result==1){
						alert('取消成功');
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					}else{
						alert('取消失败');
						return false;
					}
				}, 'json');
			}
		});
	});
	
	
	$('#btnAdd').click(function(){
		var bid = $('#id_add').text();
		var begintime = $.trim($('#begintime_add').val());
		var endtime = $.trim($('#endtime_add').val());
		var interval = $.trim($('#interval_add').val());
		
		if(!bid || !begintime || !endtime || !interval){
			alert('输入数据不完整');
			return false;
		}
		
		$.post(BETTER_ADMIN_URL+'/badgealarm/add', {
			'bid': bid,
			'begin_time':begintime,
			'end_time':endtime,
			'interval':interval
		}, function(json){
			if(json.result==1){
				alert('添加成功');
				$('#reload').val(1);
				$('#search_form').trigger('submit');
			}else{
				alert('添加失败');
				return false;
			}
		}, 'json');
	});
	
});