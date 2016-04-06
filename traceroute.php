<?php

/* Session 
 * 
 * Memorizes some data*/
session_start();

/* Global variables */
$GLOBALS['Database'] = 0;
$GLOBALS['Warning'] = '';
$GLOBALS['TTL'] = 1;


/* 
 * Ensures that the IP sent by the client is in the correct format and returns the ip address extracted
 */
function validateClientRequest($request) {
	/* Decoding the JSON object in a php array */
	$request = json_decode($request, true);

	/* Translating request (hostname, IP address, or else) into an IPV4 IP address*/
	$ipAddress = gethostbyname($request['ip']);

	/* ipAddress is supposed to have the format of an IP address. If it doesn't, it means the translation into an IP has failed (invalid hostname or else) */
	$ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP);
	if($ipAddress == FALSE) {
		exit(json_encode(array('Error' => "Couldn't translate the hostname into an IP address")));
	}

	/* Retrieving the selected database to use */
	if($request['database'] != 0 && $request['database'] != 1) {
		$GLOBALS['Warning'] .= "Invalid database selected, using GeoLite instead".'<br/>';
	}
	else
		$GLOBALS['Database'] = $request['database'];

	if($request['TTL'] < 1 || $request['TTL'] > 64) {
		$GLOBALS['Warning'] .= "Invalid TTL value, set to 1 instead".'<br/>';
	}
	else {
		$GLOBALS['TTL'] = $request['TTL'];
		if($request['TTL'] == 1) {
			session_destroy();
			session_start();
		}
	}

	return $ipAddress;
}

/* 
 * Parses the results of traceroute and returns the first valid IP address found
 * Returns NULL if no IP addresses found
 */
function parseTraceroute($tracerouteOutput) {

	for($i=1; $i<count($tracerouteOutput); $i++){	// Scouring all responses from traceroute (except the first line that we know is just descriptive)

		$tracerouteOutput[$i] = str_replace("(", "", $tracerouteOutput[$i]);	// Removes the parenthesis in the output before it is exploded according to spaces
		$tracerouteOutput[$i] = str_replace(")", "", $tracerouteOutput[$i]);
		$exploded = explode(" ", $tracerouteOutput[$i]);
		$hasValidIp = FALSE;

		foreach($exploded as $potentialIp) {
			if (filter_var($potentialIp, FILTER_VALIDATE_IP) == TRUE) { // We have the first match of ip address in the response line
				return $potentialIp;	// Return very first IP address encountered because we're considering only 1 hop at a time
			}
		}
	}
	return NULL;	// Return NULL if no IP addresses returned by traceroute
}


/* 
 * Execute the 1 traceroute call to find 1 hop located at $TTL hops and return its IP address, or NULL if not found
 */
function traceroute1Hop($ipAddress, $TTL) {
	$returnValue;
	$tracerouteOutput = NULL;
	exec("traceroute -n -q 1 -w 2 -f ".$TTL." -m ".$TTL." ".$ipAddress, $tracerouteOutput, $returnValue);

	if($returnValue != 0) {	// Error during the execution of traceroute
		echo json_encode(array("Error" => "Traceroute returned an error code "));
		exit(1);
	}

	return(parseTraceroute($tracerouteOutput)); // Returns the IP address of the hop found, or NULL if nothing found
}

/* 
 * Execute the traceroute call and returns the IP addresses of the hops
 * TODO: make sure exec() finishes its execution and take care of the timeout
 */
function executeTraceroute($ipAddress) {
	$returnValue;
	$tracerouteArray = array();
	array_push($tracerouteArray, gethostbyname(gethostname()));	// initialize tracerouteArray with the server's IP address to display first
	$TTL = 1;
	$hopNotFoundNb = 0;

	do {
		$tracerouteOutput = NULL;
		$hopFound = NULL;
		
		$hopFound = traceroute1Hop($ipAddress, $TTL); // Returns the IP address of the hop found, or NULL if nothing found
		$TTL += 1;

		if($hopFound == NULL) {
			$hopNotFoundNb += 1;
			continue;
		}

		if($hopFound != $tracerouteArray[count($tracerouteArray)-1]) // Assuming gethostname() differs from first hop encountered!! /!\ (which is the case for now because we are localhost)
			array_push($tracerouteArray, $hopFound);
		else
			break;	// Stop traceroute after 2 same hops have been found for 2 different TTL values (means destination reached)

	} while($hopNotFoundNb <= 3);	// Stop after 3 hops not found (hopefully means that distant host unreachable)
									// Might consider modifying so that counts only consecutive not found hops

	return $tracerouteArray;
}


/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

$ipAddress = validateClientRequest($requestReceived);

//$nextHop = executeTraceroute($ipAddress);
$nextHop = array(traceroute1Hop($ipAddress, $GLOBALS['TTL']));

if(!isset($_SESSION['LastHop'])  || isset($_SESSION['LastHop']) && $_SESSION['LastHop'] != $nextHop[0]) { // TODO: take care of case where $nextHop is NULL
	$moreHops = TRUE;
	$_SESSION['LastHop'] = $nextHop[0];
}


/*
 *
 * Geolocation
 * 
 */

require('geolocation.php');

$resultsArray = Array();

$resultsArray['Data'] = geolocation($nextHop);

if(empty($resultsArray['Data'])) {
	echo json_encode(array('Error' => 'No information could be retrieved from the given IP address'));
	exit(1);
}

if($moreHops == TRUE) { 
	$resultsArray["MoreHops"]=True;
}

echo json_encode($resultsArray);

exit();

/* 
 * 
 * Geolocation of each IP address 
 *
 * TO BE DELETED WHEN WE AGREE ON WHAT IS DONE
 *
 */
require_once('geolocation.php');

$addressPerIp = array();

// TODO: verify the curl answer each time. If null, inform client side
/* TODO: optimize the number of REST calls by looking at the range of the ip address for each: if next ip address in range of
 * 		 the previous one, no need to call again, the information retrieved will be the same
 * It really takes a long time to execute this code right now 
 */

foreach($nextHop as $ipAddress) {
	$temp = arinApiCall($ipAddress);
	if($temp != NULL)
		array_push($addressPerIp, $temp);
}

if(empty($addressPerIp)) {
	echo json_encode(array('Error' => 'No information could be retrieved from the given IP address'));
	exit(1);
}

echo json_encode($addressPerIp);

exit();

?>
