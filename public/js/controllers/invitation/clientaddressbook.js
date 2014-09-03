/**
 * 上传CSV通讯薄设置
 * 
 */

$(function(){
	var ajaxUploader = new AjaxUpload('btnFile', {
		action: '/invitation/uploadbook',
		name: 'myfile',
		autoSubmit: false,
		data: {
				todo: 'avatar',
				avatar: $('#avatar').val()
				},
		onSubmit: function(file, ext){
			ext = ext.toString().toLowerCase();
			
			if (! (ext && /^(csv|vcf)$/.test(ext))){
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
			} catch (rt) {
				rt = {
					has_err: 1,
					err: rte.description
				};
			}	
			//alert(rt.mailliststr);
			/*
			for(i in rt.mailliststr) {
				alert(rt.data[i].mail);
			}
			*/
			$('#btnUpload').attr('disabled', true);
			$('#btnFile').attr('disabled', true);
			if (rt.has_err) {
				msg = (rt.err=='1001' || rt.err=='1003')? betterLang.global.upload.too_large : rt.err;
				$('#set_form .snotice').html(betterLang.avatar.error+', '+msg).fadeIn();
			} else {
				
				var tbl = $('#tbl_findEmail');
				tbl.empty();
				$('#tbl_revertEmail').empty();
				$('#email_result').show();				
				Better_loadFindFriends({
					id: 'findEmail',
					url: '/ajax/service/emailbookcontacts',
					posts: {
						mailliststr: rt.mailliststr,
						nameliststr: rt.nameliststr
					},
					callbacks: {
						errorCallback: function(){},
						emptyCallback: function(){
							Better_EmptyResults('findEmail', betterLang.people.better.no_user);
						},
						completeCallback: function(){
							$('#tbl_revertEmail').empty();
							 
							Better_loadFriendsList({
								id: 'revertEmail',
								url: '/ajax/service/inviteemailbookfriends',
								posts: {
									mailliststr: rt.mailliststr,
									nameliststr: rt.nameliststr,
									use_last_cache: 1
								},
								callbacks: {
									errorCallback: function(){},
									emptyCallback: function(){
										Better_EmptyResults('revertEmail', betterLang.people.better.no_user);
									}
								}
							});
						}
					}
				});
				$(this).attr('disabled', false);
			
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
				msg: betterLang.invitation.avatar.choose_file
			});
		} else {
			ajaxUploader.submit();
		}
	});
});