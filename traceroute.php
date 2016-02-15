<?php


/* 
	/!\ JUST FOR TEST /!\
	Retrieves the 4 parts of the input IP address
*/
$field1 = $_GET["field1"];
$field2 = $_GET["field2"];
$field3 = $_GET["field3"];
$field4 = $_GET["field4"];

echo $field1.".";
echo $field2.".";
echo $field3.".";
echo $field4."<br>";


$returnValue;
$tracerouteOutput = array();

/* 
	Is exec the best solution? Need to be parsed to retrieve only IP addresses
	TODO: Find a way to make it asynchronous so we can look for geolocation and update the client page as traceroute is running

*/
exec("traceroute ".$field1.".".$field2.".".$field3.".".$field4, $tracerouteOutput, $returnValue);

foreach($tracerouteOutput as $stringValue) {
	echo $stringValue . '<br>';
}

?>