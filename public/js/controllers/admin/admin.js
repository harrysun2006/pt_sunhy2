blog_type = typeof(blog_type)=='undefined'? '': blog_type;

$(function(){	
	
	try {
		days = betterLang.calendar.days.toString().split(',');
		months = betterLang.calendar.months.toString().split(',');
	}catch (e) {
		alert(e.message);
	}
	if($('#searchtype').val() == 'advance'){
		$('#advanceContainer').show();
		$('#normalContainer').hide();
	}else{
		$('#normalContainer').show();
		$('#advanceContainer').hide();
	}
	
	$('#advanceButton').click(function(){
		$('#searchtype').val('advance');
		$('#advanceContainer').show();
		$('#normalContainer').hide();
	});
	$('#normalButton').click(function(){
		$('#searchtype').val('normal');
		$('#normalContainer').show();
		$('#advanceContainer').hide();
	});
	$('#from').datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-d'	,
		minDate : new Date(2009,1-1,1)	,
		yearRange : '2009:2011' , 
		dayNames : days,
		dayNamesMin :  days	,
		dayNamesShort :  days,
		monthNames : months,
		monthNamesShort : months,		
		defaultDate :  '-20y'
	});	
	
	$('#to').datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-d'	,
		minDate : new Date(2009,1-1,1)	,
		yearRange : '2009:2011' , 
		dayNames : days,
		dayNamesMin :  days	,
		dayNamesShort :  days,
		monthNames : months,
		monthNamesShort : months
	});		
	
	
	$('#chooseAll').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', true);
		$('tr.message_row').addClass('selected');
	});
	
	$('#chooseNone').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', false);
		$('tr.message_row').removeClass('selected');
	});
	
	$('#chooseReverse').click(function(){
		$('tr.message_row input[type="checkbox"]').each(function(){
			$(this).attr('checked', !$(this).attr('checked'));
			if ($(this).parent().parent().hasClass('selected')) {
				$(this).parent().parent().removeClass('selected');
			} else {
				$(this).parent().parent().addClass('selected');
			}
		});
	});
	
  // 采用name属性而非id属性选择按钮，将允许一个页面中出现多个功能相同的按钮，尤其适用于列表的顶部和底部。
	$('[name=selectAll]').click(function(){
		$('tr.message_row input[type="checkbox"]').attr('checked', true);
		$('tr.message_row').addClass('selected');
	});

	$('[name=selectReverse]').click(function(){
		$('tr.message_row input[type="checkbox"]').each(function(){
			$(this).attr('checked', !$(this).attr('checked'));
			if ($(this).parent().parent().hasClass('selected')) {
				$(this).parent().parent().removeClass('selected');
			} else {
				$(this).parent().parent().addClass('selected');
			}
		});
	});

	$('#search_form').submit(function(){
		
		//验证输入是否有误  ^\((-?\d+)(\.\d+)?,(-?\d+)(\.\d+)?\)$
		var flag=true;
		if($('#lonlatinput').val()!=null && $('#lonlatinput').val()!=""){
			var pattern = /^\((-?\d+)(\.\d+)?,(-?\d+)(\.\d+)?\)$/;
			flag = pattern.test($('#lonlatinput').val());
		}
		if(flag == true){
			Better_Notify({
				msg: '请稍候...'
			});
			return true;
		}else{
			Better_Notify({
				msg: '经纬度格式为(xxx.xxxx,xxxx.xxxx),所有字符均为半角格式，你的输入有误，请检查后重新输入！'
			});
			return false;
		}
		
	});
	
	
	$('td.pager select').change(function(){
		pageToJump = $(this).val();
		$('#page').val(pageToJump);
		$('#type').val(blog_type);
		$('#search_form').trigger('submit');
	});
	
	$('input.btnPageJump').click(function(){
		pageToJump = $(this).prev('input[type="text"]').val();
		pageToJump = parseInt(pageToJump);
		if (pageToJump>0) {
			$('#page').val(pageToJump);
			$('#type').val(blog_type);
			$('#search_form').trigger('submit');
		} else {
			Better_Notify({
				msg: '请输入正确的页码数'
			});
		}
	});
	
	$("a.viewUser").fancybox({
		'width'				: '70%',
		'height'			: '80%',
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe',
		'onStart' : function(){
		}
	});
	
	
	$('#search_form input[type="submit"]').click(function(){
		$('#page').val(1);
	});
	
});
