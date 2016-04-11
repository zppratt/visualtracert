/**
 * Checks if the user's entered destination host/ip is generally in the correct format.
 * @param address The destination host/ip to check
 * @returns {Boolean} True if the host/ip is valid, false otherwise.
 */
function ValidateIPaddress(address) {    
    if (validator.isIP(address) || validator.isURL(address)) {
        $('#error').text("");
        $('#tracerouteOutput').empty();	// Clears traceroute output before rewritting on it
        $('#tracerouteWarnings').empty();
        return (true); // IP address matching
    } else {
        $('#error').text("You have entered an invalid IP address or hostname!");
        return (false);
    }
}

/**
 * Sends the traceroute request using either the geolite or arin method.
 * @param ipaddress The destination IP.
 * @param TTL The initial time-to-live for the traceroute packets.
 */
function sendTracerouteRequest(ipaddress, TTL) {    
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

	httpRequest.open("POST", "request.php", true);
	httpRequest.setRequestHeader("Content-type", "application/json");
	httpRequest.send(JSON.stringify({ip:ipaddress, database:selectedDB, TTL:TTL}));

	console.log("Sent: " + JSON.stringify({ip:ipaddress, database:selectedDB, TTL:TTL})); // Just in case for debugging, will remove later 
}

/**
 * Processes the server response and sends the next request.
 * @param serverResponse The response from the server.
 * @param TTL The time-to-live on the request
 */
function processResponse(serverResponse, TTL) {
	if("Error" in serverResponse) {
		$('#error').text(serverResponse["Error"]);
		console.log("Error detected in server's response: " + serverResponse["Error"]);	
	}
	else if("MoreHops" in serverResponse) {
		sendTracerouteRequest(serverResponse['FinalHop'], serverResponse['NextTTL']); // Contacting server again for next hop or same if not found
		if(serverResponse['Found']==true) {
			geolocateAndUpdate(serverResponse['Data'], false);
			//traceroute.push(serverResponse['Data']);
			//updateIPArray(traceroute);
		}
		if("Warning" in serverResponse && serverResponse['Warning'] != '')
			updateWarnings(serverResponse['Warning'], TTL);
	}
	else {
		if(serverResponse['Found']==true) {
			//traceroute.push(serverResponse['Data']);
			geolocateAndUpdate(serverResponse['Data'], true);
			//updateIPArray(traceroute);
		}
		else {	// Last result not found but no more hops: need to plot
			updateIPArray(traceroute);
			plotOnMap(traceroute);
		}
	}

}

/**
 * Updates the display of the route on the page.
 * @param traceroute The array of nodes in the route.
 */
function updateIPArray(traceroute) {
	$('#tracerouteOutput').html('<table><tr><th>#</th><th>IP</th><th>Location</th></tr></table>');
	for (var i=0; i < traceroute.length; i++) {
		$('#tracerouteOutput table').html($('#tracerouteOutput table').html() + '<tr>' 
			+ '<td>' + (i+1) + '</td><td>' + traceroute[i].IP + '</td><td>' + traceroute[i].city + ' ' 
			+ traceroute[i].region + ' ' + traceroute[i].country_code +'</td></tr>');
	}
}

/**
 * Updates the list of warnings from the server on the page.
 * @param warning The warning to add to the page.
 * @param warningNode The node/hop the warning occured on.
 */
function updateWarnings(warning, warningNode) {
	if($('#tracerouteWarnings table')[0] == null) { // Table not created yet
		$('#tracerouteWarnings').html('<table><tr><th>#</th><th>Warnings</th></tr></table>');
	}
	$('#tracerouteWarnings table').append("<tr><td>"+warningNode+"</td><td>"+warning+"</td></tr>");
}
