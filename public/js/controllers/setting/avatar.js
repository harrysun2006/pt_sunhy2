/**
 * 头像设置
 * 
 */

$(function(){

	var ajaxUploader = new AjaxUpload('btnFile', {
		action: '/setting/update',
		name: 'myfile',
		autoSubmit: false,
		data: {
				todo: 'avatar',
				avatar: $('#avatar').val()
				},
		onSubmit: function(file, ext){
			ext = ext.toString().toLowerCase();
			
			if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){
				Better_Notify({
					msg: betterLang.post.invalid_file_format,
					close_timer: 3
				});			
				return false;
			}else{
				Better_Notify_loading({
					title: betterLang.post.uploading
				});
				$('#btnUpload').attr('disabled', true);
				$('#btnFile').attr('disabled', true);
			}
		},
		onComplete: function(file,response){
			Better_Notify_clear();

			try {
				eval('rt='+response);
			} catch (rte) {
				rt = {
					has_err: 1,
					err: rte.description
				};
			}
			
			$('#btnUpload').attr('disabled', false);
			$('#btnFile').attr('disabled', false);

			if (rt.has_err) {
				switch (rt.err) {
					case 1001:
					case 1003:
						errorMsg = betterLang.global.upload.too_large
						break;
					case 1006:
						errorMsg = betterLang.global.upload.image_not_supported;
						break;
					default:
						errorMsg = betterLang.global.upload.failed;
						break;
				}
				$('#set_form .snotice').html(betterLang.avatar.error+', '+errorMsg).fadeIn();
			} else {
				$('#set_form .snotice').html(betterLang.setting.avatar.update_success).fadeIn();
				$('#myAvatar').empty().append("<img src='"+rt.data.url+"' class='now_avatar' alt='' />");
				$('#avatar').val( rt.data.file_id );
			}
			
			$('#avatar_path').val('');
		},
		onChange: function(file){
			$('#avatar_path').val(file);
		}
	});
	
	$('#btnUploadAvatar').click(function(){
		if ($('#avatar_path').val()=='') {
			Better_Notify({
				msg: betterLang.setting.avatar.choose_avatar
			});
		} else {
			ajaxUploader.submit();
		}
	});
	
	$('#btnClearAvatar').click(function(){

		if ($('#avatar').val()) {
			Better_Confirm({
				msg: betterLang.avatar.delete_confirm,
				onConfirm: function(){
					Better_Confirm_clear();
					Better_Notify_loading();
					
					$.post('/setting/update', {
						todo: 'del_avatar',
						avatar: $('#avatar').val()
					}, function(dav_json){
						Better_Notify_clear();
						
						if (dav_json.has_err) {
							$('#set_form .snotice').html(dav_json.err).fadeIn();
						} else {
							$('#myAvatar').empty().append("<img src='"+Better_AvatarOnError+"' class='now_avatar' width='96' alt='' />");
							$('#set_form .snotice').html(betteLang.setting.avatar.delete_avatar).fadeIn();
						}
					}, 'json');				
				}
			});
		} else {
		}
	});
});