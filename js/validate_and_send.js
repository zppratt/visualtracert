function ValidateIPaddress(ipaddress) {
    
    if (validator.isIP(ipaddress) || validator.isURL(ipaddress)) {
        $('#error').text("")
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
			if(!("Error" in serverResponse))
				plotOnMap(serverResponse);	// Calls for plotting points on map. TODO: verify that response is free of errors before plotting
			else
				console.log("Error detected in server's response: " + serverResponse["Error"]);
		}
	};
		
	httpRequest.open("POST", "traceroute.php", true);
	httpRequest.setRequestHeader("Content-type", "application/json");
	httpRequest.send(JSON.stringify(ipaddress));

	console.log("Sent: " + JSON.stringify(ipaddress)); // Just in case for debugging, will remove later 
}

