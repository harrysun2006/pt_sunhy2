function s60downloadInit(){
	
	send_request('/ajax/download/initnokia',InitNokiadownload);
}

function InitNokiadownload()
{	
	
	document.getElementById("brands").innerHTML  = http_request.responseText;
	getPhone('1','nokia.gif');
}




function downloadInit()
{	
	send_request('/ajax/download/init',Initdownload);
}

function Initdownload()
{
	document.getElementById("brands").innerHTML  = http_request.responseText;
	
	option = $("#brands").find("option[value="+id+"]");
	if(option.length>0){
		option.attr("selected", true);
		getPhone(id,'');
	}else{
		getPhone('26','iphone.png');
	}
	
	
	
}

function getPhone(bid,img)
{
	data = "bid=" + bid;
	send_request('/ajax/download/getphone',displayPhone,data);
	setBrandImg(img);
}

function getPhoneOnchange(n)
{
	var bid = n.value;
	var data = "bid=" + bid;
	document.getElementById("phone").disabled = 'disabled';
	send_request('/ajax/download/getphone',displayPhone,data);
}

function displayPhone()
{
	document.getElementById("phones").innerHTML  = http_request.responseText;
	setPhoneImg(document.getElementById("c_phone_img").value);
	setBrandImg(document.getElementById("c_brand_img").value);
	download();
}


function setPhoneImgOnchange(n)
{
	var pid = n.value;
	var data = 'pid=' + pid;
	$("#p_desc").hide();
	send_request('/ajax/download/getphoneimg',displayOnePhone,data);
}

function displayOnePhone()
{
	var img = http_request.responseText;
	setPhoneImg(img);
	download();
}

function setBrandImg(img)
{
	if(img!=''){
		var img = 'files/download/images' + '/' + img;
		document.getElementById("brand_img").src = img;
	}
}

function setPhoneImg(img)
{
	var img = 'files/download/images' + '/' + img;
	document.getElementById("phone_img").src = img;	
}

function download()
{
	pid = document.getElementById('phone').value;
	bid = document.getElementById('brand').value;
	var data = 'pid=' + pid + '&bid=' + bid;
	send_request('/ajax/download/getproduct',displayProduct,data);
	$("#p_desc").show();
}

function displayProduct()
{
	document.getElementById('p_desc').innerHTML = http_request.responseText;
	$("#p_desc").show("slow");
}
$(function(){
	downloadInit();	
});