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
		exit(json_encode(array('Error' => "Bad IP address format. Aborting.")));
	}

	return $ipAddress;
}

/* 
 * Parses the results of traceroute and returns an array of valid ip addresses for the hops
 */
function parseTraceroute($tracerouteOutput) {
	$hopsIpAddresses = array();	// Array of future valid ip addresses

	for($i=1; $i<count($tracerouteOutput); $i++){	// Scouring all responses from traceroute (except the first line that we know is just descriptive)

		$tracerouteOutput[$i] = str_replace("(", "", $tracerouteOutput[$i]);	// Removes the parenthesis in the output before it is exploded according to spaces
		$tracerouteOutput[$i] = str_replace(")", "", $tracerouteOutput[$i]);
		$exploded = explode(" ", $tracerouteOutput[$i]);
		$hasValidIp = FALSE;

		foreach($exploded as $potentialIp) {
			if (filter_var($potentialIp, FILTER_VALIDATE_IP) == TRUE) { // We have the first match of ip address in the response line
				$hasValidIp = TRUE;
				break;
			}
		}
		if($hasValidIp == TRUE) {
			array_push($hopsIpAddresses, $potentialIp);
		}
	}
	return $hopsIpAddresses;
}

/* 
 * Execute the traceroute call and returns the IP addresses of the hops
 * TODO: make sure exec() finishes its execution and take care of the timeout
 */
function executeTraceroute($ipAddress) {
	$returnValue;
	$tracerouteOutput = array();

	exec("traceroute ".$ipAddress, $tracerouteOutput, $returnValue); // execute the traceroute 
	if($returnValue != 0) {	// Error during the execution of traceroute
		echo json_encode(array("Error" => "Traceroute returned an error code"));
		exit(1);
	}

	$hopsIpAddresses = parseTraceroute($tracerouteOutput);

	return $hopsIpAddresses;
}


/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

$ipAddress = validateClientRequest($requestReceived);

$hopsIpAddresses = executeTraceroute($ipAddress);


/* 
 * 
 * Geolocation of each IP address 
 *
 */
require('geolocation.php');

$addressPerIp = array();

// TODO: verify the curl answer each time. If null, inform client side
/* TODO: optimize the number of REST calls by looking at the range of the ip address for each: if next ip address in range of
 * 		 the previous one, no need to call again, the information retrieved will be the same
 * It really takes a long time to execute this code right now 
 */

foreach($hopsIpAddresses as $ipAddress) {
	$temp = arinApiCall($ipAddress);
	if($temp != NULL)
		$addressPerIp[$ipAddress] = $temp;
}

if(empty($addressPerIp)) {
	echo json_encode(array('Error' => 'No information could be retrieved from the given IP addresses'));
	exit(1);
}

echo json_encode($addressPerIp);

exit();

?>