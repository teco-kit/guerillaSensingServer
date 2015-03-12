
<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/write_data/:value', function ($value) {
    echo "<b>Writing $value to table 'data' in database 'test_db'.<br><br></b>";
	
	// Get cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/test_db/series?u=root&p=root',
		CURLOPT_USERAGENT => 'Codular Sample cURL Request',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => '[{"name":"data","columns":["val"],"points":[[' . $value . ']]}]'
	));
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	echo "<table style=\"border: 1px solid black;\">";
	if (empty($resp)){
		echo "<tr><td>Response:</td><td>All OK.</td></tr><tr><td>Code:</td><td>$rsp_code</td></tr>";
	} else {
		echo "<tr><td>Response:</td><td>$resp</td></tr><tr><td>Code:</td><td>$rsp_code</td></tr>";
	}
	echo "</table>";
	
	// Close request to clear up some resources
	curl_close($curl);
});

$app->run();

exit();
?>

Something is wrong with the XAMPP installation :-(
