function ValidateIPaddress(ipaddress) {
    if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)) {
        return (true);
    }
    alert("You have entered an invalid IP address!");
    return (false);
}

function sendTracerouteRequest(ipaddress) {

	var httpRequest = new XMLHttpRequest();
		
	httpRequest.onreadystatechange = function() {
		if (httpRequest.readyState == 4 && httpRequest.status == 200) {
		}
	};
		
	httpRequest.open("POST", "traceroute.php", true);
	httpRequest.setRequestHeader("Content-type", "application/json");
	httpRequest.send(JSON.stringify(ipaddress));
    	alert("Sent!");

	console.log(JSON.stringify(ipaddress)) // Just in case for debugging, will remove later 
}
