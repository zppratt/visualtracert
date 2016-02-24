<?php

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