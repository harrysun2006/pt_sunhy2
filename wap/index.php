<?php
	function isWap()
	{ 
		if((preg_match("/wap\.|\.wap/i",$_SERVER["HTTP_ACCEPT"]) && !preg_match("/(linux|nt)/i", $ua))) {
			return true;
		} else if(!preg_match("/wap\.|\.wap/i",$_SERVER["HTTP_ACCEPT"])) {
			return false;
		}

        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);

        if(isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
        	return true;
        }

        $uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|wap|m3gate|winwap|openwave)/i"; 

        if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'mobile')){//如果在访问的URL中已经找到 wap字样，表明已经在访问WAP页面，无需跳转，下一版本增加 feed访问时也不跳转 
            return true; 
        }else{ 
            return false;
        } 
    }
    
if (isWap()) {
	header('Location:http://k.ai/mobile?force_redirect=1');
} else {
	header('Location:http://k.ai');
}