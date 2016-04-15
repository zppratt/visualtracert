
/**
 * Global variables
 */
var traceroute = [];
var map;

/**
 * Initializes a blank map (nothing drawn on it) in the HTML page.
 */
function initialize() {
    var mapProp = {
        zoom: 4,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: {lat: 28.540, lng: -100.546},
        scrollwheel: false
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
                    mapProp.zoom = 6;
                    mapProp.center = {
                            lat: geocode_results[0].geometry.location.lat(),
                            lng: geocode_results[0].geometry.location.lng()
                        };
                }
                map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        });
    }, "jsonp");
}

/**
 * Plots the points on the map using either method.
 * @param traceroute
 */
function plotOnMap(traceroute) {
    if (traceroute.length == 0)
        return;
    plotOnMapGeoLite(traceroute);
    // Clear the loading gif and error messages

    $('#error').empty();
}

/**
 * Plots polylines on map for GeoLite specific answer
 * @param traceroute
 */
function plotOnMapGeoLite(traceroute){
    map.setZoom(4);
    var myTrip = [];

    for (var i=0; i < traceroute.length; i++) {
       
        myTrip.push({
            lat : traceroute[i].latitude,
            lng : traceroute[i].longitude,
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
    // add here where we want the center of the map to be displayed
    //map.setCenter(lat: , lng: );
}


function drawMarker(lat, long) {
    var position = {lat: lat, lng: long};
    var marker = new google.maps.Marker({
        position: position,
        map: map
    });
    map.setZoom(4);
    map.setCenter(position);
}


/**
 * Maps geolocation with geocoding - if no lat/long in response, geocodes the address returned and updates the display 
 * (IP's and plotting when done)
 * @param result, data result from the server containing IP, address and potentially lat/long
 * @param plotting, boolean: plots the points on the map when true, only updates the IP array otherwise
 */
function geolocateAndUpdate(result, plotting) {
    if(result['latitude'] != null && result['longitude'] != null) {
        traceroute.push(result);
        updateIPArray(traceroute);
        drawMarker(result['latitude'], result['longitude']);
        if(plotting == true)
            plotOnMap(traceroute);
        return;
    }
    var geocoder = new google.maps.Geocoder();

    geocoder.geocode({
                        'address' : result.city + ", " + result.region + " " 
                        + result.postal_code + ", " + result.country_code 
            }, function(geocode_results, status) {
                if (status != google.maps.GeocoderStatus.OK) {
                    console.log(status);
                }
                else if (status == google.maps.GeocoderStatus.OK) {
                    result['latitude'] = geocode_results[0].geometry.location.lat();
                    result['longitude'] = geocode_results[0].geometry.location.lng();
                    drawMarker(result['latitude'], result['longitude']);
                    traceroute.push(result);
                    updateIPArray(traceroute);
                    if(plotting == true) {  // Plots only when no more hops are to be geocoded
                        plotOnMap(traceroute);  // Calls for plotting points on map after all data has been received (How to plot when still receiving data?)
                    }
                }
    });
}
