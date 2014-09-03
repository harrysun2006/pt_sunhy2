$(function() {	
	if($("#had_at_kai").val()!=1){
		$("#form_center").css('height','160px');
		$("#aboutkai").css('padding','12px 0');
	}
	function changeurl() {
	  var n = $("input:checked").length;	
	  if(n==1){
		  $('#thirdurl').attr('href', $("#oauthatkai").val());
	  } else {
		  $('#thirdurl').attr('href', $("#oauthkai").val());
	  }	 
	}
	changeurl();
	$("#atkaiaccount").click(changeurl);

});
