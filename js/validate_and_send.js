function ValidateIPaddress(ipaddress) {
    
    if (validator.isIP(ipaddress) || validator.isURL(ipaddress)) {
        $('#error').text("");
        $('#tracerouteOutput').empty();	// Clears traceroute output before rewritting on it
        return (true); // IP address matching
    } else {
        $('#error').text("You have entered an invalid IP address or hostname!");
        return (false);
    }
}

function sendTracerouteRequest(ipaddress) {
    $('#error').prepend('<img id="loading" src="img/ajax-loader.gif" />')
    
	var httpRequest = new XMLHttpRequest();
		
	httpRequest.onreadystatechange = function() { // When a response is received
		if (httpRequest.readyState == 4 && httpRequest.status == 200) {
			serverResponse = JSON.parse(this.responseText);
			console.log(serverResponse);	// printing the response in the console for debugging
			processResponse(serverResponse);
		}
	};
		
	// Selecting database method
	var selectedDB;
	if($('#dbGeoLite').prop("checked"))
		selectedDB = 0;
	else
		selectedDB = 1;

	httpRequest.open("POST", "traceroute.php", true);
	httpRequest.setRequestHeader("Content-type", "application/json");
	httpRequest.send(JSON.stringify({ip:ipaddress, database:selectedDB}));

	console.log("Sent: " + JSON.stringify(ipaddress)); // Just in case for debugging, will remove later 
}

function processResponse(serverResponse) {
	if(!("Error" in serverResponse)) {
		$('#tracerouteOutput').html('<table><tr><th>#</th><th>IP</th><th>Location</th></tr></table>');
		for (var i=0; i < serverResponse.length; i++) {
			$('#tracerouteOutput table').html($('#tracerouteOutput table').html() + '<tr>' 
				+ '<td>' + i + '</td><td>' + serverResponse[i].IP + '</td><td>' + serverResponse[i].city + ' ' 
				+ serverResponse[i].region + ' ' + serverResponse[i].country_code +'</td></tr>');
		}
		plotOnMap(serverResponse);	// Calls for plotting points on map. TODO: verify that response is free of errors before plotting
	}
	else
		console.log("Error detected in server's response: " + serverResponse["Error"]);
}
