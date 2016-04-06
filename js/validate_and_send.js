function ValidateIPaddress(ipaddress) {
    
    if (validator.isIP(ipaddress) || validator.isURL(ipaddress)) {
        $('#error').text("");
        $('#tracerouteOutput').empty();	// Clears traceroute output before rewritting on it
        $('#tracerouteWarnings').empty();
        return (true); // IP address matching
    } else {
        $('#error').text("You have entered an invalid IP address or hostname!");
        return (false);
    }
}

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

	httpRequest.open("POST", "traceroute.php", true);
	httpRequest.setRequestHeader("Content-type", "application/json");
	httpRequest.send(JSON.stringify({ip:ipaddress, database:selectedDB, TTL:TTL}));

	console.log("Sent: " + JSON.stringify({ip:ipaddress, database:selectedDB, TTL:TTL})); // Just in case for debugging, will remove later 
}

function processResponse(serverResponse, TTL) {
	if("Error" in serverResponse) {
		$('#error').text(serverResponse["Error"]);
		console.log("Error detected in server's response: " + serverResponse["Error"]);	
	}
	else if("MoreHops" in serverResponse) {
		sendTracerouteRequest(ipaddress, TTL+1);
		traceroute.push(serverResponse['Data'][0]);
		updateIPArray(traceroute);
		if("Warning" in serverResponse && serverResponse['Warning'] != '')
			updateWarnings(serverResponse['Warning'], traceroute.length);
	}
	else {
		traceroute.push(serverResponse['Data'][0]);
		updateIPArray(traceroute);		
		plotOnMap(traceroute);	// Calls for plotting points on map
	}

}

function updateIPArray(traceroute) {
	$('#tracerouteOutput').html('<table><tr><th>#</th><th>IP</th><th>Location</th></tr></table>');
	for (var i=0; i < traceroute.length; i++) {
		$('#tracerouteOutput table').html($('#tracerouteOutput table').html() + '<tr>' 
			+ '<td>' + i+1 + '</td><td>' + traceroute[i].IP + '</td><td>' + traceroute[i].city + ' ' 
			+ traceroute[i].region + ' ' + traceroute[i].country_code +'</td></tr>');
	}
}

function updateWarnings(warning, warningNb) {
	if($('#tracerouteWarnings table')[0] == null) { // Table not created yet
		$('#tracerouteWarnings').html('<table><tr><th>#</th><th>Warning</th></tr></table>');
	}
	$('#tracerouteWarnings table').append("<tr><td>"+warningNb+"</td><td>"+warning+"</td></tr>");
}
