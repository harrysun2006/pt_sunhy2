$(function(){
	upmouseenter =  $('div.learnmore_backup');			
	// 设置用户结果行鼠标上移效果
	nomovemess = $('#learnmore-remark').html();
	upmouseenter.mouseenter(function(e){
		divmess = $(this);
		$('#learnmore-message').html(divmess.attr('message'));
	}).mouseleave(function(){
		$('#learnmore-message').html(nomovemess);
	});		
	downmouseenter =  $('div.learnmore_backdown');			
	// 设置用户结果行鼠标上移效果
	downmouseenter.mouseenter(function(e){
		divmess = $(this);
		$('#learnmore-message').html(divmess.attr('message'));
	}).mouseleave(function(){
		$('#learnmore-message').html(nomovemess);
	});	

});



