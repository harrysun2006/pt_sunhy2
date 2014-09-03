$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/simipoi?type='+$('#simitype').val();
	});

	
	$('#btnMerge').click(function(){	
			
			var rows = {};
			var ids = [];
			var nids = [];
			$('tr.message_row td.checkbox_td input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					ids.push($(this).val());
					
					var old_refid  = $(this).val();
					var tar_pid = $(this).closest('tr').attr('id');
					rows[tar_pid]= {};
					rows[tar_pid].old_refid = old_refid;
					rows[tar_pid].rs = [];
					$('tr.#'+tar_pid+' td.sub_checkbox_td input[type=checkbox]').each(function(){
						if($(this).attr('checked')){
							rows[tar_pid]['rs'].push($(this).attr('id'));
						}
					});
				}else{
					nids.push($(this).val());
				}
			});
			
			if (ids.length<=0) {
				Better_Notify({
					msg: '请选择要操作的行'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
							Better_Notify({
								msg: '请稍候 ...'
							});
							
							$.post(BETTER_ADMIN_URL+'/simipoi/mergemuti' ,{
								'rows': JSON.stringify(rows),
								'nids[]': nids,
								't': $('#simitype').val()
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
	
	
	$('#btnNotMerge').click(function(){	
		var ids = [];
		$('tr.message_row td.checkbox_td input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				ids.push($(this).val());
			}
		});
		
		if (ids.length<=0) {
			Better_Notify({
				msg: '请选择要操作的行'
			});					
		} else {
			Better_Confirm({
				msg: '确认要执行操作?',
				onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						
						$.post(BETTER_ADMIN_URL+'/simipoi/delmuti' ,{
							'ids[]': ids,
							't': $('#simitype').val()
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
	
	
	//手动合并
	$('#manu_btn').click(function(){	
		var manu_pids = $.trim($('#manu_pids').val());
		var manu_target_pid = $.trim($('#manu_target_pid').val());
		
		if (!manu_pids || !manu_target_pid) {
			alert('请输入POI ID');				
		} else {
			Better_Confirm({
				msg: '确认要执行操作?',
				onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						var pids = manu_pids.split(',');
						
						$.post(BETTER_ADMIN_URL+'/simipoi/mergemanu' ,{
							'pids[]': pids,
							'target_pid': manu_target_pid
						}, function(json){
							Better_Notify_clear();
							if (json.result==1) {
								Better_Notify({
									msg: '操作成功'
								});
							} else if(json.result==2) {
								Better_Notify({
									msg: '目标POI已被关闭'
								});
							}else if(json.result==3){
								Better_Notify({
									msg: '目标POI不存在'
								});
							}else{
								Better_Notify({
									msg: '未知错误'
								});
							}
						}, 'json');
					}
			});
		}

	});
	
	
});

function Better_Admin_Merge_Pois(target, old_refid){
	if(target){
		target_pid = target.closest('tr').attr('id');
		
		Better_Confirm({
			msg: '确定要合并？',
			onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				var pids = [];
				$('tr.#'+target_pid+' td.sub_checkbox_td input[type=checkbox]').each(function(){
					if($(this).attr('checked')){
						pids.push($(this).attr('id'));
					}
				});
				
				$.post(BETTER_ADMIN_URL+'/simipoi/merge',{
					'pids[]': pids,
					'target_pid': target_pid,
					'old_refid': old_refid,
					't': $('#simitype').val()
				},function(json){
					Better_Notify_clear();
					if (json.result==1) {
						Better_Notify({
							msg: '操作成功'
						});
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					} else if(json.result==2) {
						Better_Notify({
							msg: '目标POI已被关闭'
						});
					} else if(json.result==3){
						Better_Notify({
							msg: '目标POI不存在'
						});
					}else{
						Better_Notify({
							msg: '未知错误'
						});
					}
				}, 'json');
			}
		});
	}else{
		Better_Notify({
			msg: '无POI ID'
		});
	}
	
}


function Better_Admin_Simi_Del(refid){
	if(refid){
		Better_Confirm({
			msg: '确定不合并？',
			onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				$.getJSON(BETTER_ADMIN_URL+'/simipoi/del', {'refid': refid,'t': $('#simitype').val()}, function(json){
					Better_Notify_clear();
					if(json.result==1){
						Better_Notify({
							msg: '操作成功'
						});
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					}else{
						Better_Notify({
							msg: '未知错误'
						});
					}
				});
			}
		});
	}
}


function Better_Admin_Simi_Switch(id){
	var tmp = $('#div_'+id).html();
	var next_td = $('#div_'+id).parent('td').next('td').next('td');
	$('#div_'+id).html(next_td.html());
	$('#div_'+id).prev('a').prev('input').attr('id', next_td.find('a.ida').text());
	next_td.html(tmp);
	next_td.parent('tr').attr('id', next_td.find('a.ida').text());
}