<?php

$GLOBALS['ArinRestIp'] = 'http://whois.arin.net/rest/ip/';

function restCall($url) {
	/* REST call */
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$curlResponse = curl_exec($curl);

	if ($curlResponse == FALSE) {	// Verifying the response of the call
	    $info = curl_getinfo($curl);
	    curl_close($curl);
	    die('An error occured during the REST call: ' . var_export($info));
	}

	curl_close($curl);
	return $curlResponse;
}

function arinApiCall($ipAddress) {
	$curlIPRetrieval = restCall($GLOBALS['ArinRestIp'].$ipAddress.'.json');	// Calling ARIN for info on ip address
	$decodedIp = json_decode($curlIPRetrieval, TRUE);
	$orgContactAddress = $decodedIp['net']['orgRef']['$'];	// Retrieving url to contact for address

	/* Asks for information about the organization */
	$curlAddressRetrieval = restCall($orgContactAddress.'.json');
	$decodedOrg = json_decode($curlAddressRetrieval, TRUE);

	/* Retrieves the address from response */
	$addressArray = array();
	$addressArray['city'] = $decodedOrg['org']['city']['$'];
	$addressArray['postalCode'] = $decodedOrg['org']['postalCode']['$'];
	$addressArray['streetAddress'] = $decodedOrg['org']['streetAddress']['line']['$'];
	$addressArray['state'] = $decodedOrg['org']['iso3166-2']['$'];
	$addressArray['country'] = $decodedOrg['org']['iso3166-1']['code2']['$'];

	return $addressArray;
}









/* The following needs to be removed - we are not using the tracerouteimpl anymore */

require('tracerouteimpl.php');

/*
 * Takes an IP address and retrieves its related longitude/latitde
 */
function retrieveLatLong($ipAddress) {
	$data = ip2geo($ipAddress);
	if(is_null($data)) {
		return NULL;
	}
	return [$data->lattitude, $data->longitude];
}

?>