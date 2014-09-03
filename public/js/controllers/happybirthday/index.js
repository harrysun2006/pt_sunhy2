function   SaveAs(href,name)
{
  var   a  =  window.open(href);
  a.document.execCommand( 'Saveas ',true,name);
  a.window.close();
  return   false;
} 
function GetRadioValue(RadioName){
    var obj;   
    obj=document.getElementsByName(RadioName);
    if(obj!=null){
        var i;
        for(i=0;i<obj.length;i++){
            if(obj[i].checked){
                return obj[i].value;           
            }
        }
    }
    return null;
}
function downLoadImage(imagePathURL){
	//如果中间IFRAME不存在，则添加
	if(!document.getElementById("_SAVEASIMAGE_TEMP_FRAME")){
	var iframe = document.createElement('iframe');
	iframe.setAttribute('style','display:none;');
	iframe.setAttribute('id','_SAVEASIMAGE_TEMP_FRAME');
	iframe.setAttribute('name','_SAVEASIMAGE_TEMP_FRAME');
	iframe.setAttribute('src','about:blank');
	document.body.appendChild(iframe);
	}
	if(iframe.src!=imagePathURL){
		//图片地址发生变化，加载图片
		iframe.src = imagePathURL;
		iframe.execCommand("SaveAs");
//		_doSaveAsImage(iframe);
	}else{
	//图片地址没有变化，直接另存为
		iframe.execCommand("SaveAs");
//		_doSaveAsImage();
	}
}
function _doSaveAsImage(iframe){
	//if(iframe.src!="about:blank")
	document.frames("_SAVEASIMAGE_TEMP_FRAME").document.execCommand("SaveAs");
}

function copyToClipboard(){
	var otext = document.getElementById('inviteContent').value; 
	var txt = otext;
	  if(window.clipboardData) {   
	         window.clipboardData.clearData();   
	         window.clipboardData.setData('text',otext); 
	     } else if(navigator.userAgent.indexOf("Opera") != -1) {   
	    	 alert('请选中文字，点击鼠标右键来完成复制');
	    	 return;
	     } else if (window.netscape) {   
	          try {   
	               netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");   
	          } catch (e) { 	        	  
	        	  alert('请再Firefox地址栏输入 about:config 然后找到  signed.applets.codebase_principal_support设为true'); 
	        	  return;
	          }   
	          var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);   
	          if (!clip)   
	               return;   
	          var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);   
	          if (!trans)   
	               return;   
	          trans.addDataFlavor('text/unicode');   
	          var str = new Object();   
	          var len = new Object();   
	          var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);   
	          var copytext = txt;   
	          str.data = copytext;   
	          trans.setTransferData("text/unicode",str,copytext.length*2);   
	          var clipid = Components.interfaces.nsIClipboard;   
	          if (!clip)   
	               return false;   
	          clip.setData(trans,null,clipid.kGlobalClipboard);   
	     }  
	  	alert('复制成功');
} 

function Better_Notify_loading(options)
{
	options = typeof(options)!='undefined' ? options: {};
	msg_title = typeof(options.msg_title)!='undefined' ? options.msg_title : "请稍候...";
	msg = typeof(options.msg)!='undefined' ? options.msg : '<img src="images/loading.gif" alt="" width="180" />';
	closeCallback = $.isFunction(options.closeCallback) ? options.closeCallback : function(){};

	Better_Notify({
		msg: msg,
		msg_title: msg_title,
		btns: null,
		closeCallback: closeCallback
	});
}

function Better_Notify(options)
{
	Better_Notify_clear();
	
	if (typeof(options)=='string') {
		options = {
			msg: options
		};
	}
	msg = options.msg;
	msg_title = typeof(options.msg_title)!='undefined' ? options.msg_title : '开开提示';
	//提示消息假如超过50个字，显示加长
	defaultheight =150 + Math.floor(msg.length/50)*40;

	height = typeof(options.height)!='undefined' ? options.height : defaultheight;

	close_timer = typeof(options.close_timer)!='undefined' ? parseFloat(options.close_timer) : 0;
	msg_title = msg_title ? msg_title : '开开提示';
	btn_close = '关闭';
	btns = {};
	eval('btns.'+btn_close+'=function(){$(this).dialog("close");}')
	buttons = (typeof(options.btns)!='undefined' && options.btns) ?  options.btns : btns;

	closeCallback = (typeof(options.closeCallback)!='undefined' && $.isFunction(options.closeCallback)) ? options.closeCallback : function(){};

	if ($.browser.msie && $.browser.version<7) {
		dialogParams = {
			height: height,
			closeOnEscape: true,
			resizable: false,
			zIndex: 3999,
			buttons: buttons,
			close: closeCallback	
		}
	} else {
		dialogParams = {
				bgiframe: true,
				modal: true,
				height: height,
				closeOnEscape: true,
				resizable: false,
				zIndex: 3999,
				buttons: buttons,
				close: closeCallback	
			}		
	}
	
	$('#betterMessageBox').dialog('destroy').attr('title', msg_title).html(msg).dialog(dialogParams);
	
	if (close_timer>0) {
		setTimeout(Better_Notify_clear, close_timer*1000);
	}

}

function Better_Notify_clear()
{	
	$('#betterMessageBox').dialog('close').dialog('destroy');
}
function Better_parseAchievement(data, addMsg)
{
	str = '';
	addMsg = addMsg ? addMsg : '本次操作';
	addMsg = addMsg + '为你带来:';
	if (typeof(data)=='object') {
		str = data.achievement;
	} else {
		str = data;
	}
	
	if (!data.checkin_exception && $.trim(str)!='') {
		str = addMsg+''+str;
	}
	return str;
}
function Better_GetPostLength(txt)
{
	txt = txt.split('\r').join("");
	txt = txt.replace(/[\r\n]/g, '');
	txt = $.trim(txt);
	len = parseInt(txt.length);
	return len;
}
$(function () {
	$('#giftBtn').click( function () {
		//gift = $("input[name='gift']:checked").val();
		var gift = GetRadioValue('gift');
		$('#giftBtn').attr('disabled',true)
		$('#giftBtn').attr('value','请稍候...')
		$.post('/happybirthday/gift', {gift:gift}, 
		function (json) {
			$('#giftDetail').hide();
			alert(json.tip);
			$('#giftBtn').attr('disabled',false)
			$('#giftBtn').attr('value','确定')
			if (json.code == 1) {
				$('#giftAddress').show();
			}
		}, 'json');
	});
	$('#aBtn').click( function () {
		name = $('#aName').val();
		phone = $('#aPhone').val();
		address = $('#aAddress').val();
		if (Better_GetPostLength(name) < 1 || Better_GetPostLength(phone) < 1 || Better_GetPostLength(address) < 1) {
			alert('姓名、联系电话、收件地址不可以为空！');
			return;
		}
		$('#aBtn').attr('disabled',true)
		$('#aBtn').attr('value','请稍候...')
		$.post('/happybirthday/address', {name:name,phone:phone,address:address}, 
		function (json) {
			$('#aBtn').attr('disabled',false)
			$('#aBtn').attr('value','确定')
			if (json.code == 1) {
				$('#giftAddress').hide();
			}
			alert(json.tip);
		}, 'json');
	});
	$('#content').keyup( function () {
		len = Better_GetPostLength($('#content').val());
		num = 140 - len;
		if (len < 4) {
			html = '还可以输入<font class="red">' + num + '</font>个字';
		} else if (len < 130){
			html = '还可以输入<font class="green">' + num + '</font>个字';
		} else if (len >= 130 && len <= 140) {
			html = '还可以输入<font class="red">' + num + '</font>个字';
		} else {
			num = len - 140;
			html = '已经超出<font class="red">' + num + '</font>个字';
		}
		$('#contentTip').html(html);
	});
	$('#shoutBtn').click( function () {
		message = $('#content').val();
		need_sync = $('#sync').attr('checked') ? 1 : 0;
		var len = Better_GetPostLength($('#content').val());
		posturl = '/ajax/blog/post';
		if (len>140) {
			Better_Notify({
				msg: '不好意思哦~内容最多只能有140个字'
			});
		} else if (len<3) {
			Better_Notify({
				msg: '不好意思哦~内容至少要有3个字'
			});
		} else {
			Better_Notify_loading();
			$.post(posturl, {
				message: message, 
				upbid: 0,
				real_upbid: 0,
				attach: 0,
				priv: 'public',
				lon: 0,
				lat: 0,
				range: 0,
				poi_id: 0,
				type: 'normal',
				need_sync: need_sync
			}, function (json) {
				if (json.code=='success') {
					Better_Notify({
						msg: "发布吼吼成功!" + Better_parseAchievement(json, '发布吼吼'),
						close_timer: 2
					});
				} else if (json.code=='post_too_fast') {
					Better_Notify({
						msg: "发布的太快了，休息一下吧"
					});										
				} else if (json.code=='post_same_content') {
					Better_Notify({
						msg: "你发布了相同的内容，修改一下吧"
					});										
				} else if (json.code=='you_r_muted') {
					Better_Notify({
						msg: "对不起，你已经被管理员禁言了"
					});								
				} else if (json.code=='words_r_banned') {
					Better_Notify({
						msg: "您发送的内容中含有被禁止词汇，请修改后重新提交"
					});
				} else if (json.code=='too_short') {
					Better_Notify({
						msg: '不好意思哦~内容至少要有3个字'
					});
				} else if(json.code=='not_allow_rt'){
					Better_Notify({
						msg: '原文消息已不允许被转发'
					});	
				}else {
					Better_Notify({
						msg: 'Failed'
					});										
				}
			}, 'json');
		}
	});
});