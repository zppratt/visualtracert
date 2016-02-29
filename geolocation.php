<?php

$GLOBALS['ArinRestIp'] = 'http://whois.arin.net/rest/ip/';

/*
 * \brief Performs a REST call given the url passed in parameter
 *
 * @param $url, the url to send a request to
 * \return the response given by the REST call
 */
function restCall($url) {
	/* REST call */
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$curlResponse = curl_exec($curl);

	if ($curlResponse == FALSE) {	// Verifying the response of the call
	    $info = curl_getinfo($curl);
	    curl_close($curl);
	    echo json_encode(array('Error' => 'An error occured during the REST call: ' . var_export($info)));
	    exit(1);
	}

	curl_close($curl);
	return $curlResponse;
}

/*
 * \brief Given an IP address, retrieves information about it by performing REST calls to ARIN's APIs
 *
 * @param $ipAddress, the IP address to request information about
 * \return an associative array containing information about the IP address, NULL on failure
 *
 * TODO: loop through array instead of individually checling every single key
 */
function arinApiCall($ipAddress) {
	$curlIPRetrieval = restCall($GLOBALS['ArinRestIp'].$ipAddress.'.json');	// Calling ARIN for info on ip address
	$decodedIp = json_decode($curlIPRetrieval, TRUE);

	if(!array_key_exists('net', $decodedIp) && !array_key_exists('orgRef', $decodedIp['net']))
		return NULL;
	$orgContactAddress = $decodedIp['net']['orgRef']['$'];	// Retrieving url to contact for address

	/* Asks for information about the organization */
	$curlAddressRetrieval = restCall($orgContactAddress.'.json');
	$decodedOrg = json_decode($curlAddressRetrieval, TRUE);

	/* Retrieves the address from response / tests existence of all keys in retrieved array */
	$addressArray = array();
	if(!array_key_exists('org', $decodedOrg))
		return NULL;
	if(array_key_exists('city', $decodedOrg['org']))
		$addressArray['city'] = $decodedOrg['org']['city']['$'];

	if(array_key_exists('postalCode', $decodedOrg['org']))
		$addressArray['postalCode'] = $decodedOrg['org']['postalCode']['$'];

	if(array_key_exists('streetAddress', $decodedOrg['org']) && array_key_exists('line', $decodedOrg['org']['streetAddress'])) {
		$i = 0;
		$addressArray['streetAddress'] = "";
		while(array_key_exists(strval($i), $decodedOrg['org']['streetAddress']['line'])) {
			$addressArray['streetAddress'] .= $decodedOrg['org']['streetAddress']['line'][strval($i)]['$']." ";
			$i += 1;
		}
		if(array_key_exists('$', $decodedOrg['org']['streetAddress']['line']))
			$addressArray['streetAddress'] .= $decodedOrg['org']['streetAddress']['line']['$']." ";
	}

	if(array_key_exists('iso3166-2', $decodedOrg['org']))
		$addressArray['state'] = $decodedOrg['org']['iso3166-2']['$'];

	if(array_key_exists('iso3166-1', $decodedOrg['org']) and array_key_exists('code2', $decodedOrg['org']['iso3166-1']))
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