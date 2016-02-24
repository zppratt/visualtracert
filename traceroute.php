<?php

/* 
 * Ensures that the IP sent by the client is in the correct format and returns the ip address extracted
 */
function validateClientRequest($request) {
	/* Decoding the JSON object in a php array */
	$ipAddress = json_decode($request, true);

	/* ipAddress is supposed to have the right format, but we check on the server side again */
	$ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP);

	if($ipAddress == FALSE) { // couldn't filter an ip address, therefore the format wasn't correct
		exit(json_encode("Bad IP address format. Aborting."));
	}

	return $ipAddress;
}

/* 
 * Parses the results of traceroute and returns an array of valid ip addresses for the hops
 */
function parseTraceroute($tracerouteOutput) {
	$HopsIpAddresses = array();	// Array of future valid ip addresses

	for($i=1; $i<count($tracerouteOutput); $i++){	// Scouring all responses from traceroute (except the first line that we know is just descriptive)

		$exploded = explode(" ", $tracerouteOutput[$i]);

		foreach($exploded as $potentialIp) {
			if (filter_var($potentialIp, FILTER_VALIDATE_IP) == TRUE) // We have the first match of ip address in the response line
				break;
		}
		array_push($HopsIpAddresses, $potentialIp);
	}

	return $HopsIpAddresses;
}

/* 
 * Execute the traceroute call and returns the IP addresses of the hops
 * TODO: make sure exec() finishes its execution and take care of the timeout
 */
function executeTraceroute($ipAddress) {
	$returnValue;
	$tracerouteOutput = array();

	exec("traceroute ".$ipAddress, $tracerouteOutput, $returnValue); // execute the traceroute 

	$HopsIpAddresses = parseTraceroute($tracerouteOutput);

	return $HopsIpAddresses;
}


/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

$ipAddress = validateClientRequest($requestReceived);

$HopsIpAddresses = executeTraceroute($ipAddress);

echo json_encode($HopsIpAddresses);	// sends to client for now

//echo json_encode($tracerouteOutput);


// was previously for the use of tracerouteimpl
//require('tracerouteimpl.php');
//$tracerouteResults = traceroute($ipAddress);


?>