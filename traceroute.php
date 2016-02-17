<?php


/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

/* Decoding the JSON object in a php array 
 * ipAddress is supposed to have the right format*/
$ipAddress = json_decode($requestReceived, true);


$returnValue;
$tracerouteOutput = array();

/* 
	Is exec the best solution? Need to be parsed to retrieve only IP addresses
	TODO: Find a way to make it asynchronous so we can look for geolocation and update the client page as traceroute is running

*/
exec("traceroute ".$ipAddress, $tracerouteOutput, $returnValue);

foreach($tracerouteOutput as $stringValue) {
	echo $stringValue . '<br>';
}

?>