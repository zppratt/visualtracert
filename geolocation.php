<?php

/* Disable error reporting of "notice" errors returned by geoip when a record is not found in the database */
error_reporting(E_ALL & ~E_NOTICE);

$GLOBALS['ArinRestIp'] = 'http://whois.arin.net/rest/ip/';

/**
 * Performs a REST call given the url passed in parameter.
 * @param $url The url to send a request to.
 * @return JSON The response given by the REST call.
 */
function restCall($url) {
	/* REST call */
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // If successful call, value is returned by curl_exec()

	$curlResponse = curl_exec($curl);

	if ($curlResponse === FALSE) {
	    //$info = curl_getinfo($curl);
	    $info = curl_error($curl);
	    curl_close($curl);
	    echo json_encode(array('Error' => 'An error occured during the REST call: ' . var_export($info)));
	    exit(1);
	}

	curl_close($curl);
	return $curlResponse;
}

/**
 * Given an IP address, retrieves information about it by performing REST calls to ARIN's APIs
 *
 * @param $ipAddress The IP address to request information about.
 * @return array An associative array containing information about the IP address, NULL on failure.
 *
 * TODO: loop through array instead of individually checking every single key
 */
function arinApiCall($ipAddress) {
	$curlIPRetrieval = restCall($GLOBALS['ArinRestIp'].$ipAddress.'.json');	// Calling ARIN for info on ip address
	$decodedIp = json_decode($curlIPRetrieval, TRUE);

	if (!array_key_exists('net', $decodedIp) || !array_key_exists('orgRef', $decodedIp['net'])) {
		return NULL;
	}
	$orgContactAddress = $decodedIp['net']['orgRef']['$'];	// Retrieving url to contact for address

	/* Asks for information about the organization */
	$curlAddressRetrieval = restCall($orgContactAddress.'.json');
	$decodedOrg = json_decode($curlAddressRetrieval, TRUE);

	/* Retrieves the address from response / tests existence of all keys in retrieved array */
	$addressArray = array();
	if (!array_key_exists('org', $decodedOrg))
		return NULL;
	$addressArray['city'] = "";
	if (array_key_exists('city', $decodedOrg['org']))
		$addressArray['city'] = $decodedOrg['org']['city']['$'];

	$addressArray['postal_code'] = "";
	if (array_key_exists('postalCode', $decodedOrg['org']))
		$addressArray['postal_code'] = $decodedOrg['org']['postalCode']['$'];

	if (array_key_exists('streetAddress', $decodedOrg['org']) && array_key_exists('line', $decodedOrg['org']['streetAddress'])) {
		$i = 0;
		$addressArray['street_address'] = "";
		while(array_key_exists(strval($i), $decodedOrg['org']['streetAddress']['line'])) {
			$addressArray['street_address'] .= $decodedOrg['org']['streetAddress']['line'][strval($i)]['$']." ";
			$i += 1;
		}
		if (array_key_exists('$', $decodedOrg['org']['streetAddress']['line']))
			$addressArray['street_address'] .= $decodedOrg['org']['streetAddress']['line']['$']." ";
	}

	$addressArray['region'] = "";
	if (array_key_exists('iso3166-2', $decodedOrg['org']))
		$addressArray['region'] = $decodedOrg['org']['iso3166-2']['$'];

	$addressArray['country_code'] = "";
	if (array_key_exists('iso3166-1', $decodedOrg['org']) and array_key_exists('code2', $decodedOrg['org']['iso3166-1']))
		$addressArray['country_code'] = $decodedOrg['org']['iso3166-1']['code2']['$'];
	$addressArray['latitude'] = NULL;
	$addressArray['longitude'] = NULL;

	return $addressArray;
}

/**
 * Makes call to geolocation based on the method selected on the client.
 * @param string $ipAddress The ip address to geolocate.
 */
function geolocation($ipAddress) {
	if ($GLOBALS['Database'] == 0){
		$location = geoip_record_by_name($ipAddress);
		if ($location != FALSE) {
			$location['IP'] = $ipAddress;
			return $location;
		}
	} else if ($GLOBALS['Database'] == 1) {
		$location = arinApiCall($ipAddress);
		if ($location != NULL) {
			$location['IP'] = $ipAddress;
			return $location;
		}
	} else {
		return NULL;
	}
}

?>
