<?php

/**
 * 
 * Request and traceroute handling
 * 
 */

session_start();

/* Global variables */
$GLOBALS['Database'] = 0;
$GLOBALS['Warning'] = '';
$GLOBALS['TTL'] = 1;

require('traceroute.php');

/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

$ipAddress = validateClientRequest($requestReceived);

$nextHop = traceroute1Hop($ipAddress, $GLOBALS['TTL']);

if ($nextHop == NULL) {
	$GLOBALS['Warning'] .= "Host couldn't be resolved. \n";
	$GLOBALS['TTL'] += 1;

	if (!isset($_SESSION['AttemptsNb']))
		$_SESSION['AttemptsNb'] = 0;
	$_SESSION['AttemptsNb'] += 1;
}

if (!isset($_SESSION['LastHop'])  || isset($_SESSION['LastHop']) && $_SESSION['LastHop'] != $nextHop) {
	$moreHops = TRUE;
	$_SESSION['LastHop'] = $nextHop;
}

/* Stops the traceroute after 3 unsuccessful attemps of resolving any host (To be enhanced) */
if ($_SESSION['AttemptsNb'] > 3)
	$moreHops = FALSE;

/**
 *
 * Geolocation
 * 
 */

require('geolocation.php');

$resultsArray = Array();

if ($nextHop != NULL) {
	$resultsArray['Data'] = geolocation($nextHop);
	$resultsArray['Found'] = TRUE;
} else {
	$resultsArray['Found'] = FALSE;
}

if (empty($resultsArray['Data'])) {
	$GLOBALS['Warning'] .= "No information could be retrieved from the given IP address";
	$resultsArray['Found'] = FALSE;
}

if ($moreHops == TRUE) { 
	$resultsArray["MoreHops"]=True;
}
$resultsArray['Warning']=$GLOBALS['Warning'];

$resultsArray['AttemptsNb'] = $_SESSION['AttemptsNb'];

echo json_encode($resultsArray);

exit();

?>
