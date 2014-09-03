function parsecontxt(text,joinstr) {
	var textarray =text.split(",");	
	endarray = new Array();

	for (i=0;i<textarray.length;i++) {		
		b = "'"+textarray[i]+"'";
		if(joinstr){
			b = "'"+joinstr+textarray[i]+joinstr+"'";
		}
		endarray.push(b);		
	}
	
	addendtxt = endarray.join(",");
	
	return addendtxt;
}

$(function(){
	
	$('#btnReset').click(function(){
		window.location = BETTER_ADMIN_URL+'/badge';
	});
	$('#clearcon').click(function(){	
		$('#condition').val("");
	});
	$('#badge_con_tips').html(betterLang.badge.admintips.is_where);
	$('#what_con').change(function(){
		what_con = $(this).val();		
		$('#con_txt').val();
		$('#badge_con_tips').html();
		switch(what_con){
			case 'had_specialsync':
				$('#con_txt').val(badge_had_specialsync);
				$('#badge_con_tips').html(betterLang.badge.admintips.had_specialsync);
				break;
			case 'blog_type':
				$('#con_txt').val(badge_blog_type);
				$('#badge_con_tips').html(betterLang.badge.admintips.blog_type);
				break;
			case 'had_syncs':
				$('#con_txt').val("");
				$('#badge_con_tips').html(betterLang.badge.admintips.had_syncs);
				break;
			case 'had_text':
				$('#con_txt').val("");
				$('#badge_con_tips').html(betterLang.badge.admintips.had_text);
				break;
			case 'is_where':
				$('#con_txt').val("");
				$('#badge_con_tips').html(betterLang.badge.admintips.is_where);
				break;
			case 'dis_range':
				$('#con_txt').val("");
				$('#badge_con_tips').html(betterLang.badge.admintips.dis_range);
				break;
			case 'poi_name':
				$('#con_txt').val("");
				$('#badge_con_tips').html(betterLang.badge.admintips.poi_name);
				break;
			case 'user_gender':
				$('#con_txt').val(badge_user_gender);
				$('#badge_con_tips').html(betterLang.badge.admintips.user_gender);
				break;
		}
	});
	
	$('#joincon').click(function(){
		what_con = $('#what_con option:selected').val();		
		con_txt = $('#con_txt').val();
		condition = $('#condition').val();
		if(con_txt.length==0){
			alert('没有输入内容');
		} else {
			switch(what_con){
				case 'had_specialsync':					
					con_txt = parsecontxt(con_txt,"");
					txt = "CC::had_specialsync(array("+con_txt+"))";
					break;
				case 'blog_type':
					con_txt = parsecontxt(con_txt,"");
					txt = "CC::blog_type(array("+con_txt+"))";
					break;
				case 'had_syncs':					
					txt = "CC::had_syncs("+con_txt+")";
					break;
				case 'had_text':
					con_txt = parsecontxt(con_txt,"/");
					txt = "CC::had_text(array("+con_txt+"))";
					break;
				case 'is_where':
					txt = "CC::is_where(array("+con_txt+"))";
					break;	
				case 'dis_range':
					txt = "CC::dis_range("+con_txt+")";
					break;
				case 'poi_name':
					con_txt = parsecontxt(con_txt,"/");
					txt = "CC::poi_name(array("+con_txt+"))";
					break;
				case 'user_gender':
					con_txt = parsecontxt(con_txt,"");
					txt = "CC::user_gender(array("+con_txt+"))";
					break;
			}
			if(condition.length>0){
				context = condition+' && '+txt;
			} else {
				context = txt;
			}
			$('#condition').val(context);
		}
	});
	
	
	$('#badge_new').click(function(){			
		$('#new_badge').submit();		
	});
	
	$('#badge_update').click(function(){			
		$('#update_badge').submit();		
	});
	
	
$('tr.message_row a.xiugai').click(function(){
	id=$(this).attr('id');
	$('#update_div').fadeIn();
	$('#xid').val(id);
	$('#name_update').val($('#'+id+'_name').val());
	$('#pic_update').val($('#'+id+'_pic').val());
	return false;
	}
);

$('#btnfangqi').click(function(){
	$('#update_div').fadeOut();
}
);


$('#btnUpdate').click(function(){
	id=$('#xid').val();
	name=$.trim($('#name_update').val());
	pic=$.trim($('#pic_update').val());
	
	if(name.length==0){
		alert("请输入名称");
		return false;
	}
	
	if(pic.length==0){
		alert("请输入图片名");
		return false;
	}
	
	
	Better_Notify_loading({
		title: 'Loading ...',
		msg: '正在操作，请稍候...'
	});
	
	$.post(BETTER_ADMIN_URL+'/badge/update', {
		'xid': id,
		'name':name,
		'pic': pic
	}, function(json){
		Better_Notify_clear();
		if (json.result==1) {
			alert('更新成功');
			$('#reload').val(1);
			$('#search_form').trigger('submit');
			
		} else {
			alert('更新失败');
			return false;
		}
	}, 'json');
}
);






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


});