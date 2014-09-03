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
	
	
	$('#checkall').click(function(){
		var poi_id = $('#poi_id').val();
		
		var bids = [];
		$('tr.message_row input[type=checkbox]').each(function(){
			if($(this).attr('checked')){
				bids.push($(this).val());
			}
		});
		
		if(bids && bids.length>0){
			$.getJSON('/market/index/checkall', {
				bids: bids,
				poi_id: poi_id
			}, function(json){
				if(json.result==1){
					window.location.href='/market/index?status=not_check';
				}else{
					alert('出错了...');
				}
			});
		}else{
			alert('请选择...');
		}
	});
});