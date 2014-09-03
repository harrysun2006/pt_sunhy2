$(function(){
	
$('#btnUpdate').click(function(){
	id=$.trim($('#poi_id').val());
	category=$.trim($('#poi_cate').val());
	name=$.trim($('#poi_name').val());
	city=$.trim($('#poi_city').val());
	address=$.trim($('#poi_address').val());
	phone=$.trim($('#poi_tell').val());
	//major=$.trim($('#poi_major').val());
	poi_lat=$('#poi_lat').val();
	poi_lon=$('#poi_lon').val();
	label=$.trim($('#poi_label').val());
	intro=$.trim($('#poi_intro').val());
	ownerid=$.trim($('#ownerid').val());
	var certified = $('input[name="cerit"]:checked').val();
	var closed = $('input[name="close"]:checked').val();
	var forbid_major = $('input[name="forbid_major"]:checked').val();
	var level = $.trim($('#level').val());
	
	Better_Notify({
		msg: '请稍候 ...'
	});
	
	$.post(BETTER_ADMIN_URL+'/updatepoi/update', {
		'poi_id': id,
		'poi_cate': category,
		'poi_name':name,
		'poi_city': city,
		'poi_address': address,
		'poi_tell': phone,
		'poi_lat':poi_lat,
		'poi_lon':poi_lon,
		'poi_label': label,
		'poi_intro':intro,
		'ownerid':ownerid,
		'certified': certified,
		'closed': closed,
		'forbid_major': forbid_major,
		'level': level
	}, function(json){
		Better_Notify_clear();
		if (json.result==1) {
			alert('更新成功');
			
			$(window.parent.document).find('#reload').val(1);
			$(window.parent.document).find('#search_form').trigger('submit');
			parent.$.fancybox.close();
		} else {
			alert('更新失败');
			return false;
		}
	}, 'json');
}
);
$("input[name^='access_']").click(function(){		
	var id = $(this).attr('name').replace('access_','');
	var poi_id=  $(this).attr('temppoiid');	
	$.post(BETTER_ADMIN_URL+'/poi/checkspecial', {
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


	if (GBrowserIsCompatible()) {
		
		lat = Better_Poi_Detail.lat ? Better_Poi_Detail.lat : 39.917;
		lon = Better_Poi_Detail.lon ? Better_Poi_Detail.lon : 116.397;
			
			map = new GMap2(document.getElementById("admin_map"), {size:new GSize(700, 260)});
			map.setCenter(new GLatLng(lat, lon), 14);
			map.addControl(new GSmallMapControl());
			map.enableScrollWheelZoom();
			
	        marker = new GMarker(map.getCenter(), {draggable: true});
	
	        GEvent.addListener(marker, "dragend", function(){
	        	var latlon = marker.getLatLng();
				
				$("#poi_lon").val(latlon.lng());
				$("#poi_lat").val(latlon.lat());
				
	        });
	        map.addOverlay(marker);
	
		}	
	
	
});