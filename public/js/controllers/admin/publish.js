
$(function(){
	
	try {
		days = betterLang.calendar.days.toString().split(',');
		months = betterLang.calendar.months.toString().split(',');
	}catch (e) {
		alert(e.message);
	}
	
	$('#post_date, #post_date_update').datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-d'	,
		minDate : new Date(2009,1-1,1)	,
		yearRange : '2009:2011' , 
		dayNames : days,
		dayNamesMin :  days	,
		dayNamesShort :  days,
		monthNames : months,
		monthNamesShort : months,		
		defaultDate :  '-20y'
	});	
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/publish/phone';
	});
	
	
	$('#btnDel').click(function(){
	
		if (confirm('确定吗?')) {
			bids = new Array();
			var bi = 0;
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					bids[bi++] = $(this).val();
				}
			});
			
			if (bids.length<=0) {
				alert('请选择要删除的行');
			} else {
				Better_Notify_loading({
					title: 'Loading ...',
					msg: '正在删除，请稍候...'
				});
				$.post(BETTER_ADMIN_URL+'/publish/del', {
					'pids[]': bids
				}, function(json){
					Better_Notify_clear();
					if (json.result==1) {
						alert('删除成功');
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					} else {
						alert('删除失败');
					}
				}, 'json');
			}
		}
		
	});
	
	$('#btnAdd').click(function(){
		name=$.trim($('#name').val());
		desc=$.trim($('#desc').val());
		oid=$.trim($('#oid').val());
		version=$.trim($('#version').val());
		filename=$.trim($('#filename').val());
		post_date = $.trim($('#post_date').val());
		
		if(name.length==0){
			alert("请输入名称");
			return false;
		}
		
		if(desc.length==0){
			alert("请输入描述");
			return false;
		}
		
		if(version.length==0){
			alert("请输入版本");
			return false;
		}
		
		if(filename.length==0){
			alert("请输入文件名");
			return false;
		}
		
		Better_Notify_loading({
			title: 'Loading ...',
			msg: '正在操作，请稍候...'
		});
		
		$.post(BETTER_ADMIN_URL+'/publish/add', {
			'name': name,
			'desc': desc,
			'oid': oid,
			'version': version,
			'filename': filename,
			'post_date': post_date
		}, function(json){
			Better_Notify_clear();
			if (json.result==1) {
				alert('添加成功');
				$('#reload').val(1);
				$('#search_form').trigger('submit');
			}
		}, 'json');
	}
	);
	
	
	$('tr.message_row a').click(function(){
			pid=$(this).attr('id');
			$('#update_div').fadeIn();
			$('#pid').val(pid);
			$('#name_update').val($('#'+pid+'_name').text());
			$('#desc_update').val($('#'+pid+'_desc').text());
			$('#version_update').val($('#'+pid+'_version').text());
			$('#filename_update').val($('#'+pid+'_filename').text());
			
			oid = $('#'+pid+'_oid').attr('oid');
			$('select#oid_update option[value='+oid+']').attr('selected', true);
			return false;
	}
	);
	
	$('#btnfangqi').click(function(){
		$('#update_div').fadeOut();
	}
	);
	
	
	$('#btnUpdate').click(function(){
		pid=$('#pid').val();
		name=$.trim($('#name_update').val());
		desc=$.trim($('#desc_update').val());
		oid=$.trim($('#oid_update').val());
		version=$.trim($('#version_update').val());
		filename=$.trim($('#filename_update').val());
		post_date = $.trim($('#post_date_update').val());
		
		if(name.length==0){
			alert("请输入名称");
			return false;
		}
		
		if(desc.length==0){
			alert("请输入描述");
			return false;
		}
		
		if(version.length==0){
			alert("请输入版本号");
			return false;
		}
		
		if(filename.length==0){
			alert("请输入文件名");
			return false;
		}
		
		Better_Notify_loading({
			title: 'Loading ...',
			msg: '正在操作，请稍候...'
		});
		
		$.post(BETTER_ADMIN_URL+'/publish/update', {
			'pid': pid,
			'name': name,
			'desc': desc,
			'oid': oid,
			'version': version,
			'filename': filename,
			'post_date': post_date
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
	
	
	$('td.pager select').change(function(){
		pageToJump = $(this).val();
		$('#page').val(pageToJump);
		$('#search_form').trigger('submit');
	});
	
//----------------------------------------------------------
	
	$('#btnAddPhone').click(function(){
		name=$.trim($('#name').val());
		desc=$.trim($('#desc').val());
		oid=$.trim($('#oid').val());
		bid=$.trim($('#bid').val());
		img=$.trim($('#img').val());
		
		if(name.length==0){
			alert("请输入名称");
			return false;
		}
		
		if(desc.length==0){
			alert("请输入描述");
			return false;
		}
		
		if(img.length==0){
			alert("请输入图片");
			return false;
		}
		
		Better_Notify_loading({
			title: 'Loading ...',
			msg: '正在操作，请稍候...'
		});
		
		$.post(BETTER_ADMIN_URL+'/publish/addphone', {
			'name': name,
			'desc': desc,
			'oid': oid,
			'bid': bid,
			'img': img
		}, function(json){
			Better_Notify_clear();
			if (json.result==1) {
				alert('添加成功');
				$('#reload').val(1);
				$('#search_form').trigger('submit');
			}
		}, 'json');
	}
	);
	
	
	$('#btnDelPhone').click(function(){
		
		if (confirm('确定吗?')) {
			bids = new Array();
			var bi = 0;
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					bids[bi++] = $(this).val();
				}
			});
			
			if (bids.length<=0) {
				alert('请选择要删除的行');
			} else {
				Better_Notify_loading({
					title: 'Loading ...',
					msg: '正在删除，请稍候...'
				});
				$.post(BETTER_ADMIN_URL+'/publish/delphone', {
					'pids[]': bids
				}, function(json){
					Better_Notify_clear();
					if (json.result==1) {
						alert('删除成功');
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					} else {
						alert('删除失败');
					}
				}, 'json');
			}
		}
		
	});
});