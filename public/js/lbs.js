var base64 = '';

function lbs_get_wifiData()
{	
	var lbs = new Object();
	lbs.err = false;
	
	if(navigator.appName != "Microsoft Internet Explorer") {
		lbs.err='5';
		return lbs;
	}
	try {  
		bedoLocation = new  ActiveXObject("BedoLocation.Location");  
	} catch(e) {  
		lbs.err='6';
		return lbs;
	}
  
	if (!bedoLocation) {
		lbs.err='7';
		return lbs;
	}	
	
	lbs.bRet = bedoLocation.SetFrequency(60,180);
	lbs.bRet = bedoLocation.SetSensor(1);
	lbs.xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	
	
	bedoLocation.RequestLocation(function(bstrLocation){
		var locHeader = bstrLocation.substr(0,4);
		if(locHeader == "loc:") {
			lbs.base64 = bstrLocation.substr(4);
		}else if(locHeader == "err:"){
			lbs.err = bstrLocation.substr(4);
		}else if(locHeader == "vac:"){
			lbs.err = "空数据";
		}else{
			lbs.err = "定位依据错误：" + bstrLocation;
		}
	});
	
	return lbs;
	
}


function debugError (errCode) {
	 switch(errCode) {
	 case '1':
	 return "暂时无法定位，无法获得WIFI数据";
	 break;
	 case '2':
	 return "未检测到无线网卡，请确认是否有无线网卡或者无线网卡是否打开？";
	 break;
	 case '3':
	 return "暂时无法定位，无线网卡无信号";
	 break;
	 case '4':
	 return "您已拒绝网站获取您的位置信息";
	 break;
	 case '5':
	 return '驴博士定位功能目前只支持 IE 或者IE 核心的浏览器，如Maxthon、GreenBrowser、TheWorld、、腾讯TT、Mini IE和Avant Browser等';
	 break;
	 case '6':
	 return "请确认是否已安装驴博士Location组件？";
	 break;
	 case '7':
	 return "new ActiveXObject fail";
	 break;
	 default:
	 return errCode;
	 break;
	 }
	 return null;
}
