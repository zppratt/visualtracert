<?php

/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

/* Decoding the JSON object in a php array */
$ipAddress = json_decode($requestReceived, true);

/* ipAddress is supposed to have the right format, but we check on the server side again */
$ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP);

if($ipAddress == FALSE) { // couldn't filter an ip address, therefore the format wasn't correct
	exit(json_encode("Bad IP address format. Aborting."));
}


$returnValue;
$tracerouteOutput = array();

/* 
	Is exec the best solution? Need to be parsed to retrieve only IP addresses
	TODO: Find a way to make it asynchronous so we can look for geolocation and update the client page as traceroute is running

*/
exec("traceroute ".$ipAddress, $tracerouteOutput, $returnValue); // execute the traceroute 

$HopsIpAddresses = array();	// Array of future valid ip addresses

for($i=1; $i<count($tracerouteOutput); $i++){	// Scouring all responses from traceroute (except the first line that we know is just descriptive)

	$exploded = explode(" ", $tracerouteOutput[$i]);

	foreach($exploded as $potentialIp) {
		if (filter_var($potentialIp, FILTER_VALIDATE_IP) == TRUE) // We have the first match of ip address in the response line
			break;
	}
	array_push($HopsIpAddresses, $potentialIp);
}

echo json_encode($HopsIpAddresses);

//echo json_encode($tracerouteOutput);


// was previously for the use of tracerouteimpl
//require('tracerouteimpl.php');
//$tracerouteResults = traceroute($ipAddress);


// send to client 
//echo json_encode($tracerouteResults[0]);

/*foreach($tracerouteOutput as $stringValue) {
	echo $stringValue . '<br>';
}*/



?>