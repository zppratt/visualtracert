
/**
 * Global variables
 */
var traceroute = [];

/**
 * Initializes a blank map (nothing drawn on it) in the HTML page.
 */
function initialize() {
    var mapProp = {
        zoom: 4,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: {lat: 28.540, lng: -100.546}
    };
    $.get("http://ipinfo.io", function(response) {
        var currentLocation = response.city + ", " + response.region
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            'address' : currentLocation
            }, function(geocode_results, status) {
                if (status != google.maps.GeocoderStatus.OK) {
                    console.log(status);
                }
                else if (status == google.maps.GeocoderStatus.OK) {
                    var mapProp = {
                        zoom: 6,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        center: {
                            lat: geocode_results[0].geometry.location.lat(),
                            lng: geocode_results[0].geometry.location.lng()
                        }
                    };
                }
                var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        });
    }, "jsonp");
}

/**
 * Plots the points on the map using either method.
 * @param serverResponse
 */
function plotOnMap(serverResponse) {
    if (serverResponse.length == 0)
        return;
    if (serverResponse[0].latitude == null) // Arin specific answer
        plotOnMapArin(serverResponse);
    else
        plotOnMapGeoLite(serverResponse);
    // Clear the loading gif and error messages
    $('#error').empty();
}

/**
 * Plots polylines on map for GeoLite specific answer
 * @param serverResponse
 */
function plotOnMapGeoLite(serverResponse){
    var mapProp = {
        zoom: 4,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: {lat: 28.540, lng: -100.546}
    };
    var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
    var myTrip = [];

    for (var i=0; i < serverResponse.length; i++) {
       
        myTrip.push({
            lat : serverResponse[i].latitude,
            lng : serverResponse[i].longitude,
        });
        console.log(myTrip);
        flightPath = new google.maps.Polyline({
            path : myTrip,
            strokeColor : "#FF0000",
            strokeWeight : 2,
            strokeOpacity : 0.8,
            map : map
        });
    }
}

/**
 * Maps plotting with geocoding - to be reused when not enough data on server response (no lat/long)
 * TODO: verify each field before trying to retrieve lat/long from the address (field might be null)
 * @param serverResponse
 */
function plotOnMapArin(serverResponse){
	var mapProp = {
        zoom: 4,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: {lat: 28.540, lng: -100.546}
    };
    var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
	var geocoder = new google.maps.Geocoder();
	var myTrip = [];

    for (var i=0; i < serverResponse.length; i++) {
    	/* 
    	 * Terrible code but only temporary - for testing purposes 
    	 * Reduces the probability of getting an OVER_QUERY_LIMIT response and can actually display every point on the map
    	 * Some points are not displayed when too many queries - doesn't retry automatically 
    	 */
    	if (i != 0 && serverResponse[i].city == serverResponse[i-1].city)
    		continue;

    	/*
		 * The geocoder's callback function makes the plotting out f order - and it matters for polylines
		 * Consider Reordering "my trip" each time so we keep a consistent path
    	 */
        geocoder.geocode({
            'address' : /*serverResponse[i].streetAddress + ", " + */serverResponse[i].city + ", " + serverResponse[i].state + " " 
            + serverResponse[i].postalCode + ", " + serverResponse[i].country // Street address returns sometimes no results -- Disabled for presentation
        }, function(geocode_results, status) {
        	if (status != google.maps.GeocoderStatus.OK) {
        		console.log(status);
        	}
            else if (status == google.maps.GeocoderStatus.OK) {
                myTrip.push({
                    lat : geocode_results[0].geometry.location.lat(),
                    lng : geocode_results[0].geometry.location.lng()
                });
                flightPath = new google.maps.Polyline({
                    path : myTrip,
                    strokeColor : "#FF0000",
                    strokeWeight : 2,
                    strokeOpacity : 0.8,
                    map : map
                });
            }
        });
    }
}
