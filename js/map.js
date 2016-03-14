function initialize() {
    var mapProp = {
        zoom: 4,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: {lat: 28.540, lng: -100.546}
    };
    var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
    var geocoder = new google.maps.Geocoder();
    var addresses = [ "Fort Wayne, Indiana", "Denver, CO", "San Francisco, Ca" ];
    var flightPath;
    var myTrip = [];
    
    for (var i=0; i < addresses.length; i++) {
        geocoder.geocode({
            'address' : addresses[i]
        }, function(geocode_results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                myTrip.push({
                    lat : geocode_results[0].geometry.location.lat(),
                    lng : geocode_results[0].geometry.location.lng()
                });
                console.log(myTrip);
                flightPath = new google.maps.Polyline({
                    path : myTrip,
                    strokeColor : "#0000FF",
                    strokeWeight : 2,
                    strokeOpacity : 0.8,
                    map : map
                });
            }
        });
    }
}

/* 
	Plots polylines on map 
	TODO: verify each field before trying to retrieve lat/long from the address (field might be null)
*/
function plotOnMap(serverResponse){
	var mapProp = {
        zoom: 4,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: {lat: 28.540, lng: -100.546}
    };
    var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
	var geocoder = new google.maps.Geocoder();
	var myTrip = [];

    for (var i=0; i < serverResponse.length; i++) {
        geocoder.geocode({
            'address' : serverResponse[i].city + ", " + serverResponse[i].state + ", " + serverResponse[i].postalCode + ", " + serverResponse[i].country
        }, function(geocode_results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                myTrip.push({
                    lat : geocode_results[0].geometry.location.lat(),
                    lng : geocode_results[0].geometry.location.lng()
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
        });
    }

}