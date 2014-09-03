$(function(){
	
	$('#btnReset').click(function(){
		window.location.href = BETTER_ADMIN_URL+'/searchpoi';
	});

	
	$('#btnMerge').click(function(){
		var target_pid = $.trim($('#target_pid').val());
		var pids = [];
		$('tr.message_row input[type=checkbox]').each(function(){
			if($(this).attr('checked')){
				pids.push($(this).val());
			}
		});
		
		if(target_pid.length==0){
			alert('目标POI不能为空');
			return false;
		}
		if(pids.length<=0){
			alert('选择一个吧');
			return false;
		}
		
		Better_Confirm({
			msg: '确认要执行操作?',
			onConfirm: function(){
					Better_Notify({
						msg: '请稍候 ...'
					});
					
					$.post(BETTER_ADMIN_URL+'/searchpoi/mergemuti' ,{
						'target_pid': target_pid,
						'pids[]': pids
					}, function(json){
						Better_Notify_clear();
						if (json.result==1) {
							Better_Notify({
								msg: '操作成功'
							});
							$('#reload').val(1);
							$('#search_form').trigger('submit');
						}else if(json.result==2){
							Better_Notify({
								msg: '目标POI已被关闭'
							});
						}else if(json.result==3){
							Better_Notify({
								msg: '目标POI不存在'
							});
						} else {
							Better_Notify({
								msg: '操作失败'
							});
						}			
					}, 'json');
				}
		});
	});
	
	
	$('#btnDel').click(function(){
		$('tr.message_row input[type=checkbox]').each(function(){
			if($(this).attr('checked')){
				$(this).parent('td').parent('tr').remove();
			}
		});
		
	});
	
	//map
	if($('#admin_map').length >0){
		if (GBrowserIsCompatible()) {
			map = new GMap2(document.getElementById("admin_map"), {size: new GSize(780, 350)});
			map.setCenter(new GLatLng($("#city_lat").val(),$("#city_lon").val()), 14);
			map.addControl(new GSmallMapControl());
			map.enableScrollWheelZoom();
		}
	}
	
	
	$('#show_map').toggle(function(){
		$('#admin_map').slideDown();
	}, function(){
		$('#admin_map').slideUp();
	});
	
	
	
	//city map
	if (GBrowserIsCompatible()) {
			
		map2 = new GMap2(document.getElementById("city_map"), {size: new GSize(300, 200)});
		map2.setCenter(new GLatLng($("#city_lat").val(),$("#city_lon").val()), 14);
		map2.addControl(new GSmallMapControl());
		map2.enableScrollWheelZoom();
		
		var marker2 = new GMarker(map2.getCenter(),{draggable: true});
		GEvent.addListener(marker2, "dragend", function(latlon){
			$("#city_lon").val(latlon.lng());
			$("#city_lat").val(latlon.lat());
			
       });
		map2.addOverlay(marker2);
	}	
	
	
	$('#city').change(function(){
		var center = $(this).val();
		if(center.length==0){
			$("#city_lon").val('');
			$("#city_lat").val('');
			map2.setCenter(new GLatLng('', ''));
		}else{
			var tmp = center.split(',');
			map2.setCenter(new GLatLng(tmp[0],tmp[1]));
			$("#city_lon").val(tmp[1]);
			$("#city_lat").val(tmp[0]);
		}
		
		map2.clearOverlays();
		var marker2 = new GMarker(map2.getCenter(), {draggable: true});
		 GEvent.addListener(marker2, "dragend", function(latlon){
				$("#city_lon").val(latlon.lng());
				$("#city_lat").val(latlon.lat());
				
	       });
		map2.addOverlay(marker2);
	});
	
	
	var markers = {};
	$('tr.message_row input[type=checkbox]').click(function(){
		if($(this).attr('checked')){
			var latlon = $(this).attr('latlon');
			var tmp = latlon.split('|');
			marker = new GMarker(new GLatLng(tmp[0], tmp[1]));
			map.setCenter(new GLatLng(tmp[0], tmp[1]), 12);
			map.addOverlay(marker);
			markers[$(this).val()] = marker;
		}else{
			map.removeOverlay(markers[$(this).val()]);
		}
	});
	
	
});


function POI_setTargetPoi(opts){
	var pid = opts.pid;
	var name = opts.name;
	var address = opts.address;
	var latlon = opts.latlon;
	
	$('#target_pid').val(pid);
	$('#target_name').text(name);
	$('#target_address').text(address);
	
	var tmp = latlon.split('|');
	map.setCenter(new GLatLng(tmp[0], tmp[1]), 14);
	
	return false;
}


function POI_show_infowindow(latlon, name){
	var tmp = latlon.split('|');
	map.openInfoWindow(new GLatLng(tmp[0], tmp[1]), name);
}