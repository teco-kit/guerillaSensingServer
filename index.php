
<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->post('/add_device/', 'add_device');
$app->get('/write_data/:value', 'write_data');

// Test API to write some arbitrary data into the table "data" in "test_db".
function write_data($value) {
    echo "<b>Writing $value to table 'data' in database 'test_db'.<br><br></b>";
	
	// Get cURL resource
	$curl = curl_init();
	
	// Set cURL options.
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/test_db/series?u=root&p=root',
		CURLOPT_USERAGENT => 'GuerillaSensingPHPServer',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => '[{"name":"data","columns":["val"],"points":[[' . $value . ']]}]'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	// Create table with written data and show it back to user.
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
}

function add_device() {
	// Read parameters from POST body.
	$app = \Slim\Slim::getInstance();
	$request = $app->request();
	$body = $request->getBody();
	$input = json_decode($body); 
	
	// Collect the info to write in the TSDB:
	// Current time is added by TSDB, so we don't have to write it.
	
	// MAC of device.
    $nd_mac = $input->mac;
	// Latitude of device.
	$nd_lat = $input->lat;
	// Longitude of device.
	$nd_lon = $input->lon;
	// Height of device (above ground);
	$nd_height = $input->height;
	// Additional info needed to find the device.
	$nd_info = $input->info;
	// Picture of the device.
	$nd_picture = $input->picture;
	
	// Done collecting data. Now write it to the TSDB.
	$curl = curl_init();
	$db = 'data';
	$table = 'devices';
	//"points":[[' . $nd_mac . ']]}]'
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/' . $db . '/series?u=root&p=root',
		CURLOPT_USERAGENT => 'GuerillaSensingPHPServer',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => '[{"name":"' . $table . '",
								"columns":["mac","lat","lon","height","info","picture"],
								"points":[["' . $nd_mac . '","' . $nd_lat . '",
										   "' . $nd_lon . '","' . $nd_height . '",
										   "' . $nd_info . '","' . $nd_picture . '"]]}]'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	// Read response.
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	
	// Close request to clear up some resources
	curl_close($curl);
	
	// No error handling yet. Just print back results.
	//$result = array("mac"=>$nd_mac,"name"=>$nd_name);
	$result = "Code: " . $rsp_code . ". Device " . $nd_mac . " added.";
	echo json_encode($result);
}

$app->run();

exit();
?>

Something is wrong with the XAMPP installation :-(
