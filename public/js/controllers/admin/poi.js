
function checkHandler(id,status)
{
	if(status==1){
		message = '确认通过审核？';
	}else{
		message = '确认不通过审核？';
	}
	Better_Confirm({
	msg:message,
	onConfirm: function(){
		 Better_Notify({
				msg: '请稍候 ...'
			});	 
		 $.post(BETTER_ADMIN_URL+'/poi/updatecheckpoi', {
				'id': id,
				'status':status
			}, function(json){
				Better_Notify_clear();
				if (json.result==1) {
					alert('审核成功');
					$(document).find('#reload').val(1);
					$(document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else if(json.result==2){
					alert('更新成功');
					$(document).find('#reload').val(1);
					$(document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else if(json.result==3){
					alert('此POI已经被审核通过，无需再次审核');
					$(document).find('#reload').val(1);
					$(document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else {
					alert('审核失败');
					return false;
				}
			}, 'json');
		}			
	});	
}

function deleteCheckHandler(id)
{
	 Better_Confirm({
		msg:"确认删除该记录？"	,
		onConfirm: function(){
		 Better_Notify({
				msg: '请稍候 ...'
			});	 
		 $.post(BETTER_ADMIN_URL+'/poi/deletecheckpoi', {
				'id': id,
			}, function(json){
				Better_Notify_clear();
				if (json.result==1) {
					alert('更新成功');
					$(document).find('#reload').val(1);
					$(document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				} else {
					alert('更新失败');
					return false;
				}
			}, 'json');
	 	}
	 });	
}


function resetMajor(poi_id){
	poids = [poi_id];
	Better_Confirm({
		msg: '确认重置掌门?',
		onConfirm: function(){
				Better_Notify({
					msg: '请稍候 ...'
				});
				
				$.post(BETTER_ADMIN_URL+'/poi/resetmajor', {
					'poids[]' : poids
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


$(function(){
	
	
	$('#manu_btn').click(function(){
		var pids = $('#manu_pids').val();
		if(pids){
			Better_Notify({
				msg: '请稍候 ...'
			});
			$.post(BETTER_ADMIN_URL+'/poi/reopen', {
				'pois': pids
			}, function(json){
				Better_Notify_clear();
				if(json.result==1){
					alert('成功');
				}else{
					alert('失败');
				}
			}, 'json');
		}else{
			alert('POI ID不能为空');
		}
	});
	
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/poi?doubt='+$('#doubt').val()+'&poi_from='+$('#poi_from').val();
	});

	$('#btnResetCheck').click(function(){
		window.location = BETTER_ADMIN_URL+'/poi/getpois';
	});
	
	$('[name=btnDel]').click(function(){	
			
			var poids = new Array();
			var bi = 0;
			
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					poids[bi++] = $(this).val();
				}
			});
			
			if (poids.length<=0) {
				Better_Notify({
					msg: '请选择要操作的POI'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
							Better_Notify({
								msg: '请稍候 ...'
							});
							
							$.post(BETTER_ADMIN_URL+'/poi/del', {
								'poids[]' : poids
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
										msg: '目标poi已被关闭'
									});
								}else if(json.result==3){
									Better_Notify({
										msg: '目标poi不存在'
									});
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
	
	$('[name=btnApproveSelected]').click(function(){
			
			var poids = new Array();
			var bi = 0;
			
			$('tr.message_row input[type="checkbox"]').each(function(){
				if ($(this).attr('checked')) {
					poids[bi++] = $(this).val();
				}
			});
			
			if (poids.length<=0) {
				Better_Notify({
					msg: '请选择要操作的POI'
				});					
			} else {
				Better_Confirm({
					msg: '确认要执行操作?',
					onConfirm: function(){
							Better_Notify({
								msg: '请稍候 ...'
							});
							
							$.get(BETTER_ADMIN_URL+'/poi/approve', {
								'id[]' : poids
							}, function(data){
								Better_Notify_clear();
                //alert(data);
                location.reload();
							});
				}
        });
      }
  });
	$('#btnCheckDel').click(function(){	
		
		var ids = new Array();
		var bi = 0;
		$('tr.message_row input[type="checkbox"]').each(function(){
			if ($(this).attr('checked')) {
				ids[bi++] = $(this).val();
			}
		});
		
		if (ids.length<=0) {
			Better_Notify({
				msg: '请选择要操作的POI'
			});					
		} else {
			Better_Confirm({
				msg: '确认要执行操作?',
				onConfirm: function(){
						Better_Notify({
							msg: '请稍候 ...'
						});
						$.post(BETTER_ADMIN_URL+'/poi/deletecheckpoi', {
							'id[]' : ids
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
	
	$('#checkSuccess').click(function(){
		
	}
			
	)
	$('#submit_btn').click(function(){
		
		poi_id = $.trim($('#poi_id').val());
		ref_poi_id = $("input[name='poiid'][type='radio']:checked").val();
		
		if('undefined' == typeof ref_poi_id){
			alert('请选择一行');
			return false;
		}
		
		Better_Notify({
			msg: '请稍候 ...'
		});
		
		$.post(BETTER_ADMIN_URL+'/poi/ref', {
			poi_id: poi_id,
			ref_poi_id: ref_poi_id
		},function(json){
			Better_Notify_clear();
			if(json.result==1){
				alert('成功！');
				$(window.parent.document).find('#reload').val(1);
				$(window.parent.document).find('#search_form').trigger('submit');
				parent.$.fancybox.close();
				
			}else{
				alert('操作失败！');
				return false;
			}
			
			
		}, 'json');
	});
	
	
	$('#merge_btn').click(function(){
		
		//poi_id = $.trim($('#poi_id').val());
		target_pid = $("input[name='poiid'][type='radio']:checked").val();
		var pids = [];
		$("input[name='target_pid'][type='checkbox']").each(function(){
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
	
	if(category.length==0){
		alert("请选择分类");
		return false;
	}
	 
	if(name.length==0){
		alert("请输入名称");
		return false;
	}
	
	/*if(city.length==0){
		alert("请输入城市");
		return false;
	}
	
	if(address.length==0){
		alert("请输入地址");
		return false;
	}
	
	if(level.length==0){
		alert("请输入Level");
		return false;
	}*/
	
	Better_Notify({
		msg: '请稍候 ...'
	});
	autochecked= $('#autochecked').attr('checked') ? '1' : '0',
	autodmessage= $('#autodmessage').attr('checked') ? '1' : '0',
	denounce_uid=$.trim($('#denounce_uid').val())? $.trim($('#denounce_uid').val()):0;
	denounce_id=$.trim($('#denounce_id').val())? $.trim($('#denounce_id').val()):0;
	$.post(BETTER_ADMIN_URL+'/poi/update', {
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
		'level': level,
		'autochecked':autochecked,
		'autodmessage':autodmessage,
		'denounce_id':denounce_id,
		'denounce_uid':denounce_uid
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

function checkSuccessHandler()
{
	
	 Better_Confirm({
		msg:"确认通过审核？"	,
		onConfirm: function(){
		 id=$.trim($('#check_id').val());
		 Better_Notify({
				msg: '请稍候 ...'
			});	 
		 $.post(BETTER_ADMIN_URL+'/poi/updatecheckpoi', {
				'id': id,
				'status':1
			}, function(json){
				Better_Notify_clear();
				if (json.result==1) {
					alert('审核成功');
					$(window.parent.document).find('#reload').val(1);
					$(window.parent.document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else if(json.result==2){
					alert('更新成功');
					$(window.parent.document).find('#reload').val(1);
					$(window.parent.document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else if(json.result==3){
					alert('此POI已经被审核通过，无需再次审核');
					$(window.parent.document).find('#reload').val(1);
					$(window.parent.document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else {
					alert('审核失败');
					return false;
				}
			}, 'json');
	 	}
	 });	
}
function checkFailureHandler()
{
	Better_Confirm({
	msg:"确认不通过该审核？"	,
	onConfirm: function(){
		 id=$.trim($('#check_id').val());
		 Better_Notify({
				msg: '请稍候 ...'
			});	 
		 $.post(BETTER_ADMIN_URL+'/poi/updatecheckpoi', {
				'id': id,
				'status':2
			}, function(json){
				Better_Notify_clear();
				if (json.result==1) {
					alert('审核成功');
					$(window.parent.document).find('#reload').val(1);
					$(window.parent.document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else if(json.result==2){
					alert('更新成功');
					$(window.parent.document).find('#reload').val(1);
					$(window.parent.document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else if(json.result==3){
					alert('此POI已经被审核通过，无需再次审核');
					$(window.parent.document).find('#reload').val(1);
					$(window.parent.document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				}else {
					alert('审核失败');
					return false;
				}
			}, 'json');
		}
	});	
}

function deleteCheckHandler(){
	Better_Confirm({
		msg:"确认删除该记录？"	,
		onConfirm: function(){
		id=$.trim($('#check_id').val());
		 Better_Notify({
				msg: '请稍候 ...'
			});	 
		 $.post(BETTER_ADMIN_URL+'/poi/deletecheckpoi', {
				'id': id
			}, function(json){
				Better_Notify_clear();
				if (json.result==1) {
					alert('删除成功');
					$(window.parent.document).find('#reload').val(1);
					$(window.parent.document).find('#search_form').trigger('submit');
					parent.$.fancybox.close();
				} else {
					alert('删除失败');
					return false;
				}
			}, 'json');
		}
	});	
}
$('#checkSuccess').click(checkSuccessHandler); //审核通过
$('#checkFailure').click(checkFailureHandler); //审核不通过
$('#checkDelete').click(deleteCheckHandler); //删除此用户的POI更新
$("a.viewdetail").fancybox({
	'width'				: '75%',
	'height'			: '98%',
	'autoScale'			: false,
	'transitionIn'		: 'none',
	'transitionOut'		: 'none',
	'type'				: 'iframe',
	'onStart' : function(){
	}
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

	if (GBrowserIsCompatible()) {
		
		lat = Better_Poi_Detail.lat ? Better_Poi_Detail.lat : 39.917;
		lon = Better_Poi_Detail.lon ? Better_Poi_Detail.lon : 116.397;
		

			map = new GMap2(document.getElementById("admin_map"), {size:new GSize(450, 260)});
			map.setCenter(new GLatLng(lat, lon), 14);
			map.addControl(new GSmallMapControl());
			map.enableScrollWheelZoom();
			
	        marker = new GMarker(map.getCenter(), {draggable: true});
	
	        GEvent.addListener(marker, "dragend", function(){
	        	var latlon = marker.getLatLng();
	        	$('#lonlat').val(latlon.lat()+','+latlon.lng());
				$("#poi_lon").val(latlon.lng());
				$("#poi_lat").val(latlon.lat());
				
	        });
	        map.addOverlay(marker);
	        
	        if(typeof Better_Poi_new_Detail!='undefined'){
	        	latnew = Better_Poi_new_Detail.lat ? Better_Poi_new_Detail.lat : 39.917;
	    		lonnew = Better_Poi_new_Detail.lon ? Better_Poi_new_Detail.lon : 116.397;
	        	
	    		newmap = new GMap2(document.getElementById("admin_canavs"), {size:new GSize(450, 260)});
				newmap.setCenter(new GLatLng(latnew, lonnew), 14);
				newmap.addControl(new GSmallMapControl());
				newmap.enableScrollWheelZoom();
				
				newmarker = new GMarker(newmap.getCenter(), {draggable: true});
				
				GEvent.addListener(newmarker, "dragend", function(){
		        	var latlon = newmarker.getLatLng();
		        	$('#new_lonlat').val(latlon.lat()+','+latlon.lng());
					$("#poi_new_lon").val(latlon.lng());
					$("#poi_new_lat").val(latlon.lat());
					
		        });
				
				 newmap.addOverlay(newmarker);
	        }
	
		}	
	
	
	$('#lonlat').blur(function(){
		var latlon = $(this).val();
		var tmp = latlon.split(',');
		var lat = tmp[0];
		var lon = tmp[1];
		if(window.lat && window.lon && ''!=lat && ''!=lon ){
			map.setCenter(new GLatLng(lat, lon), 14);
			marker.setLatLng(new GLatLng(lat, lon));
			$("#poi_lon").val(lon);
			$("#poi_lat").val(lat);
		}else{
			alert('纬度或经度不存在');
			return false;
		}
	});	
});

function approve(poi_id)
{
  var url = BETTER_ADMIN_URL + '/poi/approve?id=' + poi_id;
  $.get(url, function(data){
    //alert(data);
    location.reload();
  });
}
