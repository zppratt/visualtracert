<?php

/**
 * Ensures that the IP sent by the client is in the correct format and returns the ip address extracted.
 * @param JSON $request A client request in JSON form, most likely contains hostname or IP to be translated.
 * @return The translated ip address or error message. 
 */
function validateClientRequest($request) {
	/* Decoding the JSON object in a php array */
	$request = json_decode($request, true);

	/* Translating request (hostname, IP address, or else) into an IPV4 IP address*/
	$ipAddress = gethostbyname($request['ip']);

	/* 
	 * ipAddress is supposed to have the format of an IP address. If it doesn't, it means the translation into an IP
	 * has failed (invalid hostname or else) 
	*/
	$ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP);
	if($ipAddress == FALSE) {
		exit(json_encode(array('Error' => "Couldn't translate the hostname into an IP address")));
	}

	/* When a request for a new IP is sent, reset the globals*/
	if($request['TTL'] == 1) {
		session_destroy();
		session_start();
		$GLOBALS['TTL'] = 1;
		$_SESSION['AttemptsNb'] = 0;
		$GLOBALS['Warning'] = '';
	}

	/* Retrieving the selected database to use */
	if($request['database'] != 0 && $request['database'] != 1) {
		$GLOBALS['Warning'] .= "Invalid database selected, using GeoLite instead. \n";
	}
	else
		$GLOBALS['Database'] = $request['database'];

	/* Setting the TTL */
	if($request['TTL'] < 1 || $request['TTL'] > 64) {
		$GLOBALS['Warning'] .= "Invalid TTL value, set to 1 instead\n";
		$GLOBALS['TTL'] = 1;
	}
	else {
		$GLOBALS['TTL'] = $request['TTL'];
	}

	return $ipAddress;
}

/**
 * Parses the results of traceroute and returns the first valid IP address found.
 * @param JSON $tracerouteOutput The results of the traceroute.
 * @return The first valid ip, otherwise null.
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

/**
 * Execute the traceroute call and returns the IP addresses of the hops
 * TODO: make sure exec() finishes its execution and take care of the timeout
 * @param JSON $ipAddress The destination IP.
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

/**
 * Execute the 1 traceroute call to find 1 hop located at $TTL hops and return its IP address, or NULL if not found
 * @param string $ipAddress The ip address to execute the traceroute call on.
 * @param int $TTL The incremental TTL on the packet.
 * @param int $timeout The waiting time of an answer from the hop before aborting
 * @return The IP address of the hop found, or NULL if nothing found
 */
function traceroute1Hop($ipAddress, $TTL, $timeout) {
    $returnValue;
    $tracerouteOutput = NULL;
    if(strtoupper(substr(PHP_OS, 0, 3)) === 'DAR')
    {
    	exec("traceroute -n -q 2 -w ".$timeout." -M " .$TTL. " -m " .($TTL+1)." " .$ipAddress, $tracerouteOutput, $returnValue);
    }
    else
    {
    	exec("traceroute -n -q 2 -w ".$timeout." -f ".$TTL." -m ".($TTL+1)." ".$ipAddress, $tracerouteOutput, $returnValue);
    }
    if($returnValue != 0) {	// Error during the execution of traceroute
        echo json_encode(array("Error" => "Traceroute returned an error code "));
        exit(1);
    }

    return(parseTraceroute($tracerouteOutput)); // Returns the IP address of the hop found, or NULL if nothing found
}

?>
