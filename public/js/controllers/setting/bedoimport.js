/**
 * 导入贝多号设置
 */

$(function(){
	$('#startImport').click(function(){
		
		blog = $('#blog').attr('checked') ? 1 : 0;
		photo = $('#photo').attr('checked') ? 2 : 0;
		miniBlog = $('#miniBlog').attr('checked') ? 4 : 0;
		
		importval = blog + photo + miniBlog;
		
		if (importval != 0) {
			$.post('/setting/update', {
				todo: 'bedoimport',
				importSet: importval
			}, function(tip){
				$('#importChoose').hide();
				if (tip=='importing') {
					$('#importing').show();
				} else if (tip=='noneimport') {
					$('#noneImport').show();
				} else {
					$('#setting_menu').show();
					$('#setting_tip').hide();
					$('#importTip').show();
				}
			}, 'json');
		} else {
			Better_Notify({
				msg : betterLang.bedobind.empty
			});
		}
	});
	
	$('#skipImport').click(function(){
		window.location.href='/home';
	});
});