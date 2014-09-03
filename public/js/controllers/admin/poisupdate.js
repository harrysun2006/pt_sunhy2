$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/poisupdate';
	});
	

	if (GBrowserIsCompatible()) {
		
			map = new GMap2(document.getElementById("update_map"));
			map.setCenter(new GLatLng(0, 0), 14);
			map.addControl(new GSmallMapControl());
			map.enableScrollWheelZoom();
			
	        /*marker = new GMarker(map.getCenter(), {draggable: true});
	
	        GEvent.addListener(marker, "dragend", function(){
	        	var latlon = marker.getLatLng();
	        	$('#lonlat').val(latlon.lat()+','+latlon.lng());
				$("#poi_lon").val(latlon.lng());
				$("#poi_lat").val(latlon.lat());
				
	        });
	        map.addOverlay(marker);*/
	
		}	
	
	
	var markers = {};
	$('tr.message_row input[type=checkbox]').click(function(){
		var che = $(this);
		var id = $(this).attr('id');
		if($(this).attr('checked')){
			var latlon = $(this).attr('latlon');
			var tmp = latlon.split('|');
			if(!markers[id]){
				marker = new GMarker(new GLatLng(tmp[0], tmp[1]), {draggable: true});
				marker.bindInfoWindowHtml('<div style="text-align: left;">'+$(this).attr('poi_name')+'<br>'+$(this).attr('poi_address')+'</div>');
				map.setCenter(new GLatLng(tmp[0], tmp[1]), 14);
				
				GEvent.addListener(marker, "dragend", function(p){
		        	var lat = p.lat();
		        	var lon = p.lng();
		        	var str = lat+'|'+lon;
		        	
		        	che.attr('latlon', str);
		        });
				
				map.addOverlay(marker);
				markers[id] = marker;
			}
		}else{
			map.removeOverlay(markers[id]);
			delete markers[id];
		}
	});
	
	
	$('#chooseAll').click(function(){
		$('tr.message_row input[type="checkbox"]').trigger('click');
		$('tr.message_row input[type="checkbox"]').attr('checked', true);
		$('tr.message_row').addClass('selected');
	});
	
	$('#chooseNone').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', false);
		$('tr.message_row').removeClass('selected');
		
		map.clearOverlays();
		markers = {};
	});
	
	
	
	$('#btnUpdate').click(function(){
		var pids = [];
		$('tr.message_row input[type=checkbox]').each(function(){
			if($(this).attr('checked')){
				pids.push($(this).val()+'@'+$(this).attr('latlon')+'@'+$(this).attr('id'));
			}
		});
		
		if(pids.length<=0){
			alert('请选择一行');
			return false;
		}
		
		Better_Confirm({
			'msg':'确认要修改？',
			'onConfirm': function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
			
				$.post(BETTER_ADMIN_URL+'/poisupdate/updatexy', {
					'pids[]': pids
				},
				function(json){
					Better_Notify_clear();
					if(json.result==1){
						Better_Notify({
							msg: '操作成功'
						});
						$('#reload').val(1);
						$('#search_form').trigger('submit');
					}else{
						Better_Notify({
							msg: '操作失败'
						});
					}
				},'json');
			}
		});
	});
	
});