<?php

/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

/* Decoding the JSON object in a php array */
$ipAddress = json_decode($requestReceived, true);

/* ipAddress is supposed to have the right format, but we check on the server side again */
$ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP);

if($ipAddress == FALSE) { // couldn't filer an ip address, therefore the format wasn't correct
	exit(json_encode("Bad IP address format. Aborting."));
}


$returnValue;
$tracerouteOutput = array();

/* 
	Is exec the best solution? Need to be parsed to retrieve only IP addresses
	TODO: Find a way to make it asynchronous so we can look for geolocation and update the client page as traceroute is running

*/
//exec("traceroute ".$ipAddress, $tracerouteOutput, $returnValue);

//echo json_encode($tracerouteOutput);

require('tracerouteimpl.php');

$tracerouteResults = traceroute($ipAddress);

// send to client 
echo json_encode($tracerouteResults[0]);

/*foreach($tracerouteOutput as $stringValue) {
	echo $stringValue . '<br>';
}*/



?>