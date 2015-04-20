
<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/test_write_data/:value', 'test_write_data');
$app->get('/read_data/:query', 'read_data');

$app->post('/add_device/', 'add_device');
$app->post('/write_data/', 'write_data');

// Test API to write some arbitrary data into the table "data" in "test_db".
function test_write_data($value) {
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

// Add a device to the device list.
function add_device() {
	// Example request:
	//
	//	{
	//		"mac":"AB:22:78:E4:22:D9",
	//		"lat":"123768.133",
	//		"lon":"2736.123444",
	//		"height":"88.2212",
	//		"info":"Under tree",
	//		"picture":"http://www.picture.example/1237hdu.jpg"
	//	}
	
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

// Write sensor data to the TSDB.
function write_data() {
	// Example request:
	//
	//	{
	//		"mac":"AB:22:78:E4:22:D9",
	//		"bat":"76",
	//		"sens1":"12.33",
	//		"sens2":"93.26633",
	//		"sens3":"7.1",
	//		"sens4":"8229.2",
	//		"sens5":"22.83"
	//	}
	
	// Read parameters from POST body and collect data.
	$app = \Slim\Slim::getInstance();
	$request = $app->request();
	$body = $request->getBody();
	$input = json_decode($body); 
	
	// MAC of device that provided the data.
    $data_mac = $input->mac;
	// Battery level of device.
	$data_bat = $input->bat;
	// Value of sensor 1.
	$data_sens1 = $input->sens1;
	// Value of sensor 2.
	$data_sens2 = $input->sens2;
	// Value of sensor 3.
	$data_sens3 = $input->sens3;
	// Value of sensor 4.
	$data_sens4 = $input->sens4;
	// Value of sensor 5.
	$data_sens5 = $input->sens5;

	// Done collecting data. Now write it to the TSDB.
	$curl = curl_init();
	$db = 'data';
	$table = 'data';

	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/' . $db . '/series?u=root&p=root',
		CURLOPT_USERAGENT => 'GuerillaSensingPHPServer',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => '[{"name":"' . $table . '",
								"columns":["mac","bat","sens1","sens2","sens3","sens4","sens5"],
								"points":[["' . $data_mac . '","' . $data_bat . '",
										   "' . $data_sens1 . '","' . $data_sens2 . '",
										   "' . $data_sens3 . '","' . $data_sens4 . '",
										   "' . $data_sens5 . '"]]}]'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	// Read response.
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	
	// Close request to clear up some resources.
	curl_close($curl);
	
	// No error handling yet. Just print back results.
	$result = "Code: " . $rsp_code . ". Data written.";
	echo json_encode($result);	
}

// Sends the query directly to the TSDB and returns the results.
// NOTE: This is only for testing.
function read_data($query) {
	// Get cURL resource.
	$curl = curl_init();
	
	// URL-encode query.
	$query_url = urlencode($query);
	
	// Set some options.
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/data/series?q=' . $query_url . '&u=root&p=root',
		CURLOPT_USERAGENT => 'GuerillaSensingPHPServer'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	// Create table with written data and show it back to user.
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	
	// Close request to clear up some resources
	curl_close($curl);
	
	// Directly return JSON from server.
	echo $resp;
}

// Start REST API.
$app->run();

exit();
?>

Something is wrong with the XAMPP installation :-(
