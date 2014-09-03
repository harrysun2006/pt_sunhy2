$(function(){

	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/filter'+'?act_type='+act_type+'&need_check='+need_check+'&menu_show='+menu_show;
	});
	
	$('#btnPass').click(function(){
		var bids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				id = $(this).val();
				type = $(this).attr('text_type');
				filter_id = $(this).attr('filter_id');
				if(type=='blog'){
					bids[bi++] = id;
				}
				
				fids[fi++] = filter_id;
			}
		});
		
			if (bids.length<=0) {
				Better_Notify({
					msg: '请选择操作对象'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						
						$.post(BETTER_ADMIN_URL+'/filter/pass', {
							'bids[]' : bids,
							'fids[]': fids
						}, function(json){
							Better_Notify_clear();
							if (json.result==1) {
								Better_Notify({
									msg: '操作成功'
								});
								$('#reload').val(1);
								$('#search_form').trigger('submit');
							} else {
								Better_Notify({
									msg: '操作失败'
								});
							}		
							
						}, 'json');
					}
				});
			}
	});
	
	
	$('#btnDel').click(function(){
		var bids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				id = $(this).val();
				type = $(this).attr('text_type');
				filter_id = $(this).attr('filter_id');
				if(type=='blog'){
					bids[bi++] = id;
				}
				
				fids[fi++] = filter_id;
			}
		});
		
			if (bids.length<=0) {
				Better_Notify({
					msg: '请选择操作对象'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						
						$.post(BETTER_ADMIN_URL+'/filter/del', {
							'bids[]' : bids,
							'fids[]': fids
						}, function(json){
							Better_Notify_clear();
							if (json.result==1) {
								Better_Notify({
									msg: '操作成功'
								});
								$('#reload').val(1);
								$('#search_form').trigger('submit');
							} else {
								Better_Notify({
									msg: '操作失败'
								});
							}		
							
						}, 'json');
					}
				});
			}
	});
	
	
	$('#msg_btnDel').click(function(){
		var mids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				id = $(this).val();
				type = $(this).attr('text_type');
				filter_id = $(this).attr('filter_id');
				if(type=='direct_message'){
					mids[bi++] = id;
				}
				
				fids[fi++] = filter_id;
			}
		});
		if (mids.length<=0) {
			Better_Notify({
				msg: '请选择操作对象'
			});					
		} else {
			Better_Confirm({
				msg: '确认要执行操作?',
				onConfirm: function(){
					Better_Notify({
						msg: '请稍候 ...'
					});					
					$.post(BETTER_ADMIN_URL+'/filter/delsecretmsg', {
						'ids[]' : mids,
						'fids[]': fids
					}, function(json){
						Better_Notify_clear();
						if (json.result==1) {
							Better_Notify({
								msg: '操作成功'
							});
							$('#reload').val(1);
							$('#search_form').trigger('submit');
						} else {
							Better_Notify({
								msg: '操作失败'
							});
						}		
						
					}, 'json');
				}
			});
		}
	});
	
	$('#msg_btnPass').click(function(){
		var mids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				id = $(this).val();
				type = $(this).attr('text_type');
				filter_id = $(this).attr('filter_id');
				if(type=='direct_message'){
					mids[bi++] = id;
				}
				
				fids[fi++] = filter_id;
			}
		});
		if (mids.length<=0) {
			Better_Notify({
				msg: '请选择操作对象'
			});					
		} else {
			Better_Confirm({
				msg: '确认要执行操作?',
				onConfirm: function(){
					Better_Notify({
						msg: '请稍候 ...'
					});					
					$.post(BETTER_ADMIN_URL+'/filter/passmsg', {
						'ids[]' : mids,
						'fids[]': fids
					}, function(json){
						Better_Notify_clear();
						if (json.result==1) {
							Better_Notify({
								msg: '操作成功'
							});
							$('#reload').val(1);
							$('#search_form').trigger('submit');
						} else {
							Better_Notify({
								msg: '操作失败'
							});
						}		
						
					}, 'json');
				}
			});
		}
	});
	
	
	$('#btnDel_msg').click(function(){
		var mids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				id = $(this).val();
				type = $(this).attr('text_type');
				filter_id = $(this).attr('filter_id');
				if(type=='direct_message'){
					mids[bi++] = id;
				}
				
				fids[fi++] = filter_id;
			}
		});
		
			if (mids.length<=0) {
				Better_Notify({
					msg: '请选择操作对象'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						
						$.post(BETTER_ADMIN_URL+'/filter/delmsg', {
							'mids[]' : mids,
							'fids[]': fids
						}, function(json){
							Better_Notify_clear();
							if (json.result==1) {
								Better_Notify({
									msg: '操作成功'
								});
								$('#reload').val(1);
								$('#search_form').trigger('submit');
							} else {
								Better_Notify({
									msg: '操作失败'
								});
							}		
							
						}, 'json');
					}
				});
			}
	});
	
	//评论通过审核
	$('#re_btnPass').click(function(){
		var bids = new Array();
		var bi = 0;
		var fids = new Array();
		var fi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				var id = $(this).val();
				var type = $(this).attr('text_type');
				var filter_id = $(this).attr('filter_id');
				if(type=='reply'){
					bids[bi++] = id;
				}				
				fids[fi++] = filter_id;
			}
		});
		
			if (bids.length<=0) {
				Better_Notify({
					msg: '请选择操作对象'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						
						$.post(BETTER_ADMIN_URL+'/filter/repass', {
							'bids[]' : bids,
							'fids[]': fids
						}, function(json){
							Better_Notify_clear();
							if (json.result==1) {
								Better_Notify({
									msg: '操作成功'
								});
								$('#reload').val(1);
								$('#search_form').trigger('submit');
							} else {
								Better_Notify({
									msg: '操作失败'
								});
							}		
							
						}, 'json');
					}
				});
			}
	});
	
	
	/**
	 * 删除评论
	 */
	$('#re_btnDel').click(function(){
		var bids = new Array();
		var bi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				id = $(this).val();
				type = $(this).attr('text_type');
				filter_id = $(this).attr('filter_id');
				if(type=='reply'){
					bids[bi++] = id;
				}
				
			}
		});
		
			if (bids.length<=0) {
				Better_Notify({
					msg: '请选择操作对象'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						
						$.post(BETTER_ADMIN_URL+'/filter/redel', {
							'bids[]' : bids
						}, function(json){
							Better_Notify_clear();
							if (json.result==1) {
								Better_Notify({
									msg: '操作成功'
								});
								$('#reload').val(1);
								$('#search_form').trigger('submit');
							} else {
								Better_Notify({
									msg: '操作失败'
								});
							}		
							
						}, 'json');
					}
				});
			}
	});

});