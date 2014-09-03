function getNewBlog(page) 
{
	if (!page) page = 1;
	data = 'page=' + page;
	send_request('/ajax/adminblog/do/new',displayNewblog,data);
}

function displayNewblog()
{
	document.getElementById("display_blog").innerHTML  = http_request.responseText;
}


function getFilterBlog(page)
{
	if (!page) page = 1;
	data = 'page=' + page;
	send_request('/ajax/adminblog/do/filter',displayFilterblog,data);	
}

function displayFilterblog()
{
	document.getElementById("display_blog").innerHTML  = http_request.responseText;
}


function getAvatarImg(page)
{
	if (!page) page = 1;
	data = 'page=' + page;
	send_request('/ajax/adminimg/do/avatar',displayAvatarImg,data);	
}

function displayAvatarImg()
{
	document.getElementById("display_img").innerHTML  = http_request.responseText;
}


function getAttachImg(page)
{
	if (!page) page = 1;
	data = 'page=' + page;
	send_request('/ajax/adminimg/do/attach',displayAttachImg,data);	
}

function displayAttachImg()
{
	document.getElementById("display_img").innerHTML  = http_request.responseText;
}


function actionQuery(page)
{
	username = document.getElementById("username").value;
	if (!username) {
		alert('名称不能为空！');
		return false;
	}
	by = document.getElementById("by").value;
	start = document.getElementById("start").value;
	end = document.getElementById("end").value;
	
	if (!page) page = 1;
	data = 'page=' + page + '&username=' + username + '&by=' + by + '&start=' + start + '&end=' + end;
	send_request('/ajax/actionquery/do/attach',displayActionQuery,data);
}

function displayActionQuery()
{
	document.getElementById("work").innerHTML  = http_request.responseText;
}


function next(funname)
{
	page = document.getElementById("page").value;
	page = 1 + Number(page);
	funname(page);
}

function back(funname)
{
	page = document.getElementById("page").value;
	page = Number(page) - 1;
	if (page<1) page = 0;
	funname(page);
}

function setStartEndDate()
{
	var myDate=new Date();
	y = myDate.getFullYear();
	m = myDate.getMonth() + 1;
	d = myDate.getDate();
	end = y.toString() + '-' + m.toString() + '-' + d.toString();
	start = y.toString() + '-' + m.toString() + '-' + '1';
//	if (m == 1) {
//		y = y - 1;
//		start = y.toString() + '-' + m.toString() + '-' + d.toString();
//	} else {
//		l_m = m - 1;
//		start = y.toString() + '-' + l_m.toString() + '-' + d.toString();
//	}
	
	document.getElementById("start").value = start;
	document.getElementById("end").value = end;
}

function deleteBlog(bid)
{
	data = 'bid=' + bid;
	send_request('/ajax/adminblog/do/delete',deleteBlogDone,data);
}

function deleteBlogDone()
{
	if (http_request.responseText==1) {
		alert('删除成功!');
		window.location.reload();	
	} else {
		alert(http_request.responseText);		
	}
	
}


function delAttch(bid,fid)
{
	data = 'bid=' + bid + '&fid=' + fid;
	send_request('/ajax/adminimg/do/deleteAttch',delAttchDone,data);
}

function delAttchDone()
{
	alert('delete ok!');
	window.location.reload();
}


function delAvatar(refid,uid)
{
	data = 'refid=' + refid + '&uid=' + uid;
	send_request('/ajax/adminimg/do/deleteAvatar',delAvatarDone,data);
}

function delAvatarDone()
{
	alert('delete ok!');
	window.location.reload();
}
