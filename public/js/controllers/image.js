	$(function() {
		//$('.tabs').tabs();
		
		$('#half_right .box h3').click(function(){
			$(this).next().toggle();
			return false;
		});
		
		var tabContainers = $('div.tabs > div');
        tabContainers.hide().filter(':first').show();
        
        $('div.tabs ul.tabNavigation a').click(function () {
                tabContainers.hide();
                tabContainers.filter(this.hash).show();
                $('div.tabs ul.tabNavigation a').removeClass('selected');
                $(this).addClass('selected');
                return false;
        }).filter(':first').click();
        
        $('img.needResize').ready(function(){

        });
        
      //  $("#photos").imageScroll({direction:"left",manualControl:true});
        $("div.photos").jCarouselLite({
            btnNext: ".next",
            btnPrev: ".prev",
            visible: 6
        });

        
	});