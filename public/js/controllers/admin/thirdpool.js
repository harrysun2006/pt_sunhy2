function loadProtocols() {
	var ps = protocols.split(',');
	var pv = $('#protocol_value').val();
	var s1, s2;
	$('#protocol').append("<option value=''></option>");
	for (var i = 0; i < ps.length; i++) {
		s1 = (ps[i] == pv) ? 'selected' : '';
		s2 = (i == 0) ? 'selected' : '';
		$('#protocol').append("<option value='" + ps[i] + "' " + s1 + ">" + ps[i] + "</option>");
		$('#new_protocol').append("<option value='" + ps[i] + "' " + s2 + ">" + ps[i] + "</option>");
	}
}

function removeToken(uid, protocol) {
	Better_Confirm({
		msg: '确认要删除此token?',
		onConfirm: function() {
			Better_Notify({
				msg: '请稍候 ...'
			});
			$.post(BETTER_ADMIN_URL + '/thirdpool/remove', {
				'uid': uid,
				'protocol': protocol,
			}, function(json){
				Better_Notify_clear();
				if (json.result == 1) {					
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


$(function() {
	$('#btnReset').click(function() {
		window.location = BETTER_ADMIN_URL+'/thirdpool';
	});
	$('a.token').fancybox({
		'width'			: '75%',
		'height'		: '97%',
		'autoScale'		: false,
		'transitionIn'	: 'none',
		'transitionOut'	: 'none',
		'overlayShow'   : false,
	});
	$('#btnAdd').click(function() {
		var uid = $.trim($('#new_uid').val());
		var protocol = $.trim($('#new_protocol').val());
		
		if (uid && protocol) {
			$.post(BETTER_ADMIN_URL + '/thirdpool/add', {
				'uid': uid,
				'protocol': protocol,
			}, function(json){
				if(json.result == 1) {
					$('#reload').val(1);
					$('#type').val(blog_type);
				} else {
					alert('操作失败');
				}
				$('#search_form').trigger('submit');
			}, 'json');
		}
	});
	$('#btnCancel').click(function() {
		$.fancybox.close();
		// $('#addToken').fadeOut();  // iframe类型fancybox使用此方法可以关闭
	});
	loadProtocols();
});