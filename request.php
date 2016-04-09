<?php

/* Session 
 * 
 * Memorizes some data*/
session_start();

/* Global variables */
$GLOBALS['Database'] = 0;
$GLOBALS['Warning'] = '';
$GLOBALS['TTL'] = 1;

require('traceroute.php');

/*
 *
 * Request and traceroute handling
 *
 */

/* Getting contents from JSON string sent */
$requestReceived = file_get_contents('php://input');

$ipAddress = validateClientRequest($requestReceived);

$nextHop = traceroute1Hop($ipAddress, $GLOBALS['TTL']);
if($nextHop == NULL)
	$GLOBALS['Warning'] .= "Host couldn't be resolved\n";

if(!isset($_SESSION['LastHop'])  || isset($_SESSION['LastHop']) && $_SESSION['LastHop'] != $nextHop) {
	$moreHops = TRUE;
	$_SESSION['LastHop'] = $nextHop;
}


/*
 *
 * Geolocation
 * 
 */

require('geolocation.php');

$resultsArray = Array();

if($nextHop != NULL) {
	$resultsArray['Data'] = geolocation($nextHop);
	$resultsArray['Found'] = TRUE;
} else {
	$resultsArray['Found'] = FALSE;
}

if(empty($resultsArray['Data'])) {
	$GLOBALS['Warning'] .= "No information could be retrieved from the given IP address";
	$resultsArray['Found'] = FALSE;
}

if($moreHops == TRUE) { 
	$resultsArray["MoreHops"]=True;
}
$resultsArray['Warning']=$GLOBALS['Warning'];

echo json_encode($resultsArray);

exit();

?>
