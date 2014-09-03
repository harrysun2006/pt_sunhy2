var Gmap;
var marker;

function initialize() {
	lat = betterUser.lat ? betterUser.lat : 39.917;
	lon = betterUser.lon ? betterUser.lon : 116.397;

	if (GBrowserIsCompatible()) {
		var map = new GMap2(document.getElementById("map_canvas"));
		var customUI = map.getDefaultUI();
		map.setMapType(G_DEFAULT_MAP_TYPES[0]);
		map.setCenter(new GLatLng(lat, lon), 14);
		map.setUI(customUI);
		map.enableScrollWheelZoom();
	/*	GEvent.addListener(map, "moveend", function() {
			setCenterIcon();
		} );*/
	}
	Gmap = map;
	setCenterIcon();
	
	//增加驴博士定位
	//wifi = getLLByWifi(); 
}

function setCenterIcon() {
	marker = new GMarker(Gmap.getCenter(), {draggable: true});
	Gmap.clearOverlays();
	Gmap.addOverlay(marker);
	var latlon = marker.getLatLng();
	
	//alert(latlon.lat());alert(latlon.lng());
	setlonlat(latlon.lng(),latlon.lat());

	GEvent.addListener(marker, "dragend", function() {  
			var latlon = marker.getLatLng();
			//alert(latlon.lat());alert(latlon.lng());
			document.getElementById("location_range").value = 0;
			setlonlat(latlon.lng(),latlon.lat());
	});
}

function setlonlat(lon,lat) {
	document.getElementById("location_lon").value = lon;
	document.getElementById("location_lat").value = lat;
}
