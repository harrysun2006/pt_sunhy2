var http_request = false;
function send_request(url,callback,data){
	http_request=false;
	if(window.XMLHttpRequest){
		http_request=new XMLHttpRequest();
		if(http_request.overrideMimeType){
			http_request.overrideMimeType("text/xml");
		}
	}else if(window.ActiveXObject){
		try{
			http_request=new ActiveXObject("Msxml2.XMLHTTP");
		}catch(e){
			try{
				http_request=new ActiveXObject("Microsoft.XMLHTTP");
			}catch(e){}
		}
	}
	if(!http_request){
		window.alert("Can't creat XMLHttpRequest Object.");
		return false;
	}
	nowtime	 = new Date().getTime();
	url		+= (url.indexOf("?") >= 0) ? "&nowtime=" + nowtime : "?nowtime=" + nowtime;
	if(typeof(data) =='undefined'){
		http_request.open("GET",url,true);
		http_request.send(null);
	}else{
		http_request.open('POST' , url, true);
		http_request.setRequestHeader("Content-Length",data.length);
		http_request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		http_request.send(data);
	}
	if(typeof(callback) == "function" ){
		http_request.onreadystatechange = function (){
			if (http_request.readyState == 1){
				
			}else if(http_request.readyState == 2){
				
			}else if(http_request.readyState == 3){
				
			}else if(http_request.readyState == 4){
				if(http_request.status == 200 || http_request.status == 304){
					callback(http_request);
				}else{
					alert("Error loading page\n" + http_request.status + ":" + http_request.statusText);
				}
				
			}
		}
	}
}
function ajax_convert(str){
	f = new Array(/\r?\n/g, /\+/g, /\&/g);
	r = new Array('%0A', '%2B', '%26');
	for (var i = 0; i < f.length; i++){
		str = str.replace(f[i], r[i]);
	}
	return str;
}