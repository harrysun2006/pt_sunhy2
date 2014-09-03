/**
 * 手机号码设置
 * 
 */
$(function(){

	$('#btnBindCell').click(function(){

		$(this).attr('disabled', true);
		cell = $.trim($('#mobile').val());

		if (cell.length!=11) {
			$('#errMobile').fadeIn().html(betterLang.setting.cell.cellnumber_wrong);			
			$(this).attr('disabled', false);
		} else {
			pat = /(130|131|132|133|134|135|136|137|138|139|147|150|151|152|153|154|155|156|157|158|159|180|182|186|187|188|189)([0-9]{8})/;
			if (!pat.exec(cell)) {
				$('#errMobile').fadeIn().html(betterLang.setting.cell.cellnumber_wrong);
				$(this).attr('disabled', false);
			} else {
				Better_Notify_loading();
				
				$.post('/ajax/user/bindcell', {
					cell: cell
				}, function(bcJson){
					switch(bcJson.result) {
						case 'success':
							$('#cell_tips').addClass('err').html(betterLang.noping.setting.cell.howtosucess.toString().replace('{UID}',betterUser.uid).replace('{CELLROBOT}',cell_robot)).fadeIn();
							break;
						case 'exists':
							$('#cell_tips').addClass('err').html(betterLang.noping.setting.cell.exits.toString().replace('{CELL}',cell)).fadeIn();
							break;
						case 'failed':
							$('#cell_tips').addClass('err').html(betterLang.setting.cell.error_unknow).fadeIn();				
							break;
					}
					$('#btnBindCell').attr('disabled', false);		
					$('#errMobile').empty().hide();
					Better_Notify_clear();
				}, 'json');
			}
		}
	});
});