
$(function(){
	
	$('#chooseAll').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', true);
	});
	
	$('#chooseNone').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', false);
	});
	
	$('#chooseReverse').click(function(){
		$('tr.message_row input[type="checkbox"]').each(function(){
			$(this).attr('checked', !$(this).attr('checked'));
		});
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
				alert('请选择要删除的管理员');
			} else {
				Better_Notify_loading({
					title: 'Loading ...',
					msg: '正在删除，请稍候...'
				});
				$.post(BETTER_ADMIN_URL+'/administrator/del', {
					'bids[]': bids
				}, function(json){
					Better_Notify_clear();
					if (json.result==1) {
						alert('所选管理员已经成功删除');
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
		uid=$.trim($('#uid').val());
		username=$.trim($('#username').val());
		password=$.trim($('#pwd').val());
		
		if(uid.length==0){
			alert("请输入ID");
			return false;
		}
		
		if(username.length==0){
			alert("请输入用户名");
			return false;
		}
		
		if(password.length==0){
			alert("请输入密码");
			return false;
		}
		
		Better_Notify_loading({
			title: 'Loading ...',
			msg: '正在操作，请稍候...'
		});
		
		$.post(BETTER_ADMIN_URL+'/administrator/add', {
			'uid': uid,
			'username':username,
			'pwd': password
		}, function(json){
			Better_Notify_clear();
			if (json.result==0) {
				alert('用户ID已存在');
				return false;
			} else {
				alert('添加成功');
				$('#reload').val(1);
				$('#search_form').trigger('submit');
			}
		}, 'json');
	}
	);
	
	
	$('tr.message_row a').click(function(){
			uid=$(this).attr('id');
			$('#update_div').fadeIn();
			$('#adid').val(uid);
			$('#uid_update').val($('#'+uid+'_id').val());
			$('#username_update').val($('#'+uid+'_username').val());
			$('#pwd_update').val($('#'+uid+'_pwd').val());
			return false;
	}
	);
	
	$('#btnfangqi').click(function(){
		$('#update_div').fadeOut();
	}
	);
	
	
	$('#btnUpdate').click(function(){
		id=$('#adid').val();
		uid=$.trim($('#uid_update').val());
		username=$.trim($('#username_update').val());
		password=$.trim($('#pwd_update').val());
		
		if(uid.length==0){
			alert("请输入ID");
			return false;
		}
		
		if(username.length==0){
			alert("请输入用户名");
			return false;
		}
		
		if(password.length==0){
			alert("请输入密码");
			return false;
		}
		
		Better_Notify_loading({
			title: 'Loading ...',
			msg: '正在操作，请稍候...'
		});
		
		$.post(BETTER_ADMIN_URL+'/administrator/update', {
			'id': id,
			'uid': uid,
			'username':username,
			'pwd': password
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
});