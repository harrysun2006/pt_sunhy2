/**
 * 解析消息中的超链接
 * 
 * @param match
 * @return
 */
function copyToClipboard(){
	var otext = document.getElementById('linkurl').value; 
	var txt = otext;
	  if(window.clipboardData) {   
	         window.clipboardData.clearData();   
	         window.clipboardData.setData('text',otext); 
	     } else if(navigator.userAgent.indexOf("Opera") != -1) {   
	    	 alert(betterLang.opera.copy_info);   
	    	 return;
	     } else if (window.netscape) {   
	          try {   
	               netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");   
	          } catch (e) { 	        	  
	        	  alert(betterLang.firefox.copy_info); 
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
	  	alert(betterLang.copy_success);
} 




function Better_Promotion_topinvitation(row){
	
	div = $('#paihang_list');	
	div.empty();	
	$.getJSON('/promotion/topinvitation', {		
	}, function(nJson){			
		if (nJson.rows!=null) {		
			$.each(nJson.rows, function(pi, row){				
				rowdiv = "<li><div style='float:left'><img src='"+row.userinfo.avatar_url+"' class='pngfix' style='width:36px;height:36px;' /></div><div style='float:left;padding-left:5px;'><span style='color:#006fc7;'>"+row.userinfo.nickname+"</span><span>已经注册了开开并成功邀请了</span><span style='color:#006fc7;'>"+row.number+"</span><span>好友</span></div></li>";				
				if(rowdiv){
					div.append(rowdiv);
				}else{
					div.empty();
				}
			});			
		}
	});
}

$(function(){
	Better_Promotion_topinvitation();
	setInterval(function(){		
		Better_Promotion_topinvitation();
	},50000);
	
	$('#got_invitation_link').click(function(){
		if(typeof(betterUser.uid)=='undefined' || betterUser.uid==0){
			alert('请登录');
		} else {
			copyToClipboard();
		}
		return false;
	});
	
});