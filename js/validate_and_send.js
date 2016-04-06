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

function sendTracerouteRequest(ipaddress, TTL) {
    $('#error').prepend('<img id="loading" src="img/ajax-loader.gif" />')
    
	var httpRequest = new XMLHttpRequest();
		
	httpRequest.onreadystatechange = function() { // When a response is received
		if (httpRequest.readyState == 4 && httpRequest.status == 200) {
			serverResponse = JSON.parse(this.responseText);
			console.log(serverResponse);	// printing the response in the console for debugging
			processResponse(serverResponse, TTL);
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
	httpRequest.send(JSON.stringify({ip:ipaddress, database:selectedDB, TTL:TTL}));

	console.log("Sent: " + JSON.stringify({ip:ipaddress, database:selectedDB, TTL:TTL})); // Just in case for debugging, will remove later 
}

function processResponse(serverResponse, TTL) {
	if("MoreHops" in serverResponse)
		sendTracerouteRequest(ipaddress, TTL+1);
	if(!("Error" in serverResponse)) {
		data = serverResponse['Data'];
		traceroute.push(data[0]);
		$('#tracerouteOutput').html('<table><tr><th>#</th><th>IP</th><th>Location</th></tr></table>');
		for (var i=0; i < traceroute.length; i++) {
			$('#tracerouteOutput table').html($('#tracerouteOutput table').html() + '<tr>' 
				+ '<td>' + i + '</td><td>' + traceroute[i].IP + '</td><td>' + traceroute[i].city + ' ' 
				+ traceroute[i].region + ' ' + traceroute[i].country_code +'</td></tr>');
		}
		plotOnMap(traceroute);	// Calls for plotting points on map. TODO: verify that response is free of errors before plotting
	}
	else {
		$('#error').text(serverResponse["Error"]);
		console.log("Error detected in server's response: " + serverResponse["Error"]);
	}
}
