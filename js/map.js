function initialize() {
    var mapProp = {
        zoom : 5,
        mapTypeId : google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);

    var geocoder = new google.maps.Geocoder();
    var addresses = [ "Fort Wayne, Indiana", "Denver, CO", "San Fransisco, Ca" ];
    var flightPath;
    var myTrip = [];

    for (var i; i < addresses.length; i++) {
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
google.maps.event.addDomListener(window, 'load', initialize);