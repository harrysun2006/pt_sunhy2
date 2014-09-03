$(function() {
	var i = 0;
	setInterval(function(){
		/*if(i%2==0){
			$('body').css('background-image', 'url(../images/back-shop.jpg)');
			$('#font_caff').slideUp(2000, function(){
				$('#font_shop').slideDown(2000);
			});
		}else{
			$('body').css('background-image', 'url(../images/back-caff.jpg)');
			$('#font_shop').slideUp(2000, function(){
				$('#font_caff').slideDown(2000);
			});
		}*/
		if(i%3==0){
			$('body').css('background-image', 'url(../images/back-bj.png)');
			$('#font_bj').show();
			$('#font_gz').hide();
			$('#font_sh').hide();
		}else if(i%3==1){
			$('body').css('background-image', 'url(../images/back-gz.png)');
			$('#font_bj').hide();
			$('#font_gz').show();
			$('#font_sh').hide();
		}else{
			$('body').css('background-image', 'url(../images/back-sh.png)');
			$('#font_bj').hide();
			$('#font_gz').hide();
			$('#font_sh').show();
		}
		i++;
	}, 5000);
	
});