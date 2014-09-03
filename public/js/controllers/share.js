
var wifiAlert = '';
var wifiLon = betterUser.lon;
var wifiLat = betterUser.lat;
var wifiRange = 0;
var wifiMsg = '';
var wifiError = false;
var wifiMessage = '';

var ipLon = 0;
var ipLat = 0;              

$(function(){
	
	Better_Shout_Result_Title = betterLang.share.success;
	Better_Enable_Shout();
	Better_Enable_Shout_Poi_Choice();
	
	try {
		if (typeof(navigator.geolocation)=='undefined') {
			var wifiData = lbs_get_wifiData();
			$.post('/ajax/location/wifi', wifiData, function(wifiJson){
				
				wifiError = wifiJson.error;
				
				if (wifiError=='true') {
					if (Better_InDebug) {
						Better_Notify({
							msg: wifiJson.message
						});
					}
				} else { 
					
					if (parseFloat(wifiJson.lat)<200 && parseFloat(wifiJson.lon)<200) {
						wifiAlert = wifiJson.alert;
						wifiLon = wifiJson.lon;
						wifiLat = wifiJson.lat;
						wifiRange = wifiJson.range;
						wifiMsg = wifiJson.msg;
						
						wifiMessage = wifiJson.message;
					} else if (Better_InDebug) {
						Better_Notify({
							msg: 'Wifi Lon:'+wifiJson.lon+', Wifi Lat:'+wifiJson.lat
						});
					}
				}

			}, 'json');
		} else {
			navigator.geolocation.getCurrentPosition(showMap, showError);
		}
	} catch (e) {
		if (Better_InDebug) {
			alert(e.message);
		}
	}		
});