
<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/test_write_data/:value', 'test_write_data');
$app->get('/read_data/:query', 'read_data');
$app->get('/tsdb_query_data/', 'tsdb_query_data');

$app->post('/add_device/', 'add_device');
$app->post('/write_data/', 'write_data');
$app->post('/check_uuids/', 'check_uuids');

// Frauenhofer IOSB
$app->post('/mh_write_data/', 'mh_write_data');
$app->get('/mh_read_data/:query', 'mh_read_data');

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

// Write IOSB data to the IOSB DB.
function iosb_write_data() {
	// Read parameters from POST body and collect data.
	$app = \Slim\Slim::getInstance();
	$request = $app->request();
	$body = $request->getBody();
	$input_array = json_decode($body, true); 
	
	$curl = curl_init();
	$db = 'iosb_demo';
	$table = 'iosb_table';

	$rsp_code = 0;
	$resp = "";
	
	foreach ($input_array as $input) {
		// Timestamp
		$data_time = intval($input["time"]);
		// MAC of device that provided the data.
		$data_mac = $input["mac"];
		// Height of device.
		$data_height = $input["height"];
		// Latitude of device.
		$data_lat = $input["lat"];
		// Longitude level of device.
		$data_lon = $input["lon"];
		// Value of sensor 1. (Temperature)
		$data_temp = $input["temp"];
		// Value of sensor 2. (Humidity)
		$data_hum = $input["hum"];
		// Value of sensor 3. (CO2)
		$data_co2 = $input["co2"];
		// Value of sensor 4. (CO)
		$data_co = $input["co"];
		// Value of sensor 5. (NO2)
		$data_no2 = $input["no2"];
		// Value of sensor 6. (O3)
		$data_o3 = $input["o3"];
		// Value of sensor 7. (Fine dust)
		$data_dust = $input["dust"];
		// Value of sensor 8. (UV)
		$data_uv = $input["uv"];
		
		
		// Done collecting data. Now write it to the TSDB.
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://docker.teco.edu:8086/db/' . $db . '/series?u=root&p=root',
			CURLOPT_USERAGENT => 'GuerillaSensingPHPServer',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => '[{"name":"' . $table . '",
									"time_precision":"ms",
									"columns":["time","mac","height","lat","lon","temp","hum","co2","co","no2","o3","dust","uv"],
									"points":[[' . $data_time . ',"' . $data_mac . '","' . $data_height . '",
											   "' . $data_lat . '","' . $data_lon . '", 
											   "' . $data_temp . '","' . $data_hum . '",
											   "' . $data_co2 . '","' . $data_co . '",
											   "' . $data_no2 . '","' . $data_o3 . '",
											   "' . $data_dust . '","' . $data_uv . '"]]}]'
		));
		
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		
		// Read response.
		$info = curl_getinfo($curl);
		$rsp_code = $info['http_code'];	
	}
	
	// Close request to clear up some resources.
	curl_close($curl);
	
	// No error handling yet. Just print back results.
	$result = "Code: " . $rsp_code . ". Data written.";
	echo json_encode($result);	
}

// Write IOSB data to the IOSB DB.
function iosb_read_data($query) {
	// Get cURL resource.
	$curl = curl_init();
	
	$app = \Slim\Slim::getInstance();
	
	// URL-encode query.
	$query_url = urlencode($query);
	
	// Set some options.
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/iosb_demo/series?q=' . $query_url . '&u=root&p=root',
		CURLOPT_USERAGENT => 'GuerillaSensingPHPServer'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	// Create table with written data and show it back to user.
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	
	// Close request to clear up some resources
	curl_close($curl);
	
	// If response code is not 200, the database might be down.
	if ($rsp_code == 200) {
		// Directly return JSON from server.
		echo $resp;
	} else {
		$app->response->setStatus(404);
		echo("Error: cURL returned $rsp_code");
		
		$msg = "The GuerillaSensing database seems to be offline.\nUser got response code $rsp_code";

		// use wordwrap() if lines are longer than 70 characters
		$msg = wordwrap($msg, 70);

		// send email
		// mail("diener@teco.edu", "GuerillaSensing database issues", $msg);
		exec("echo \"From: teco <noreply@teco.edu>\nTo: diener <diener@teco.edu>\nSubject: Error\n\nThe database seems to be down.\" | msmtp --debug -a gmail diener@teco.edu");
	}
}


// Write MH data to the MH DB.
function mh_write_data() {
	// Read parameters from POST body and collect data.
	$app = \Slim\Slim::getInstance();
	$request = $app->request();
	$body = $request->getBody();
	$input_array = json_decode($body, true); 
	
	$curl = curl_init();
	$db = 'mh_demo';
	$table = 'mh_table';

	$rsp_code = 0;
	$resp = "";
	
	$date = new DateTime();
	

	foreach ($input_array as $input) {
		// Timestamp
		$data_time = $date->getTimestamp();
		// MAC of device that provided the data.
		$data_hum = $input["hum"];
		$data_temp = $input["temp"];
		$data_nox = $input["nox"];
		$data_co = $input["co"];
		$data_nh3 = $input["nh3"];
		$data_dust = $input["dust"];

		
		
		// Done collecting data. Now write it to the TSDB.
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://docker.teco.edu:8086/db/' . $db . '/series?u=root&p=root',
			CURLOPT_USERAGENT => 'GuerillaSensingPHPServer',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => '[{"name":"' . $table . '",
									"time_precision":"ms",
									"columns":["time","hum","temp","nox","co","nh3","dust"],
									"points":[[' . $data_time . ',"' . $data_hum . '","' . $data_temp . '",
											   "' . $data_nox . '","' . $data_co . '", 
											   "' . $data_nh3 . '","' . $data_dust . '"]]}]'
		));
		
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		
		// Read response.
		$info = curl_getinfo($curl);
		$rsp_code = $info['http_code'];	
	}
	
	// Close request to clear up some resources.
	curl_close($curl);
	
	// No error handling yet. Just print back results.
	$result = "Code: " . $rsp_code . ". Data written.";
	echo json_encode($result);	
}

// Read MH data from the MH database
function mh_read_data($query) {
	// Get cURL resource.
	$curl = curl_init();
	
	$app = \Slim\Slim::getInstance();
	
	// URL-encode query.
	$query_url = urlencode($query);
	
	// Set some options.
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/iosb_demo/series?q=' . $query_url . '&u=root&p=root',
		CURLOPT_USERAGENT => 'GuerillaSensingPHPServer'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	// Create table with written data and show it back to user.
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	
	// Close request to clear up some resources
	curl_close($curl);
	
	// If response code is not 200, the database might be down.
	if ($rsp_code == 200) {
		// Directly return JSON from server.
		echo $resp;
	} else {
		$app->response->setStatus(404);
		echo("Error: cURL returned $rsp_code");
		
		$msg = "The GuerillaSensing database seems to be offline.\nUser got response code $rsp_code";

		// use wordwrap() if lines are longer than 70 characters
		$msg = wordwrap($msg, 70);

		// send email
		// mail("diener@teco.edu", "GuerillaSensing database issues", $msg);
		exec("echo \"From: teco <noreply@teco.edu>\nTo: diener <diener@teco.edu>\nSubject: Error\n\nThe database seems to be down.\" | msmtp --debug -a gmail diener@teco.edu");
	}
}

// Write sensor data to the TSDB.
function write_data() {
	// Example request:
	//
	//	{
	//		"time":"1387347732",
	//		"mac":"AB:22:78:E4:22:D9",
	//		"height":"9.23",
	//		"lat":"83.12314",
	//		"lon":"79.23133",
	//		"temp":"23.84",
	//		"hum":"67.26633",
	//		"co2":"7.1",
	//		"co":"8.26",
	//		"no2":"22.83",
	//		"o3":"9.54",
	//		"dust":"83.90",
	//		"uv":"33.94"
	//	}
	
	// Read parameters from POST body and collect data.
	$app = \Slim\Slim::getInstance();
	$request = $app->request();
	$body = $request->getBody();
	$input_array = json_decode($body, true); 
	
	$curl = curl_init();
	$db = 'data';
	$table = 'data';
	$upload_uuid_table = 'uuid';
	
	$rsp_code = 0;
	$resp = "";
	
	foreach ($input_array as $input) {
	
		// Upload UUID of of this data point.
		// Data that is uploaded together will have the same upload UUID.
		$data_uuid = $input["id"];
		
		// Timestamp
		$data_time = intval($input["time"]);
		// MAC of device that provided the data.
		$data_mac = $input["mac"];
		// Height of device.
		$data_height = $input["height"];
		// Latitude of device.
		$data_lat = $input["lat"];
		// Longitude level of device.
		$data_lon = $input["lon"];
		// Value of sensor 1. (Temperature)
		$data_temp = $input["temp"];
		// Value of sensor 2. (Humidity)
		$data_hum = $input["hum"];
		// Value of sensor 3. (CO2)
		$data_co2 = $input["co2"];
		// Value of sensor 4. (CO)
		$data_co = $input["co"];
		// Value of sensor 5. (NO2)
		$data_no2 = $input["no2"];
		// Value of sensor 6. (O3)
		$data_o3 = $input["o3"];
		// Value of sensor 7. (Fine dust)
		$data_dust = $input["dust"];
		// Value of sensor 8. (UV)
		$data_uv = $input["uv"];
		
		
		// Done collecting data. Now write it to the TSDB.
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://docker.teco.edu:8086/db/' . $db . '/series?u=root&p=root',
			CURLOPT_USERAGENT => 'GuerillaSensingPHPServer',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => '[{"name":"' . $table . '",
									"time_precision":"ms",
									"columns":["time","mac","height","lat","lon","temp","hum","co2","co","no2","o3","dust","uv"],
									"points":[[' . $data_time . ',"' . $data_mac . '","' . $data_height . '",
											   "' . $data_lat . '","' . $data_lon . '", 
											   "' . $data_temp . '","' . $data_hum . '",
											   "' . $data_co2 . '","' . $data_co . '",
											   "' . $data_no2 . '","' . $data_o3 . '",
											   "' . $data_dust . '","' . $data_uv . '"]]}]'
		));
		
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		
		// Read response.
		$info = curl_getinfo($curl);
		$rsp_code = $info['http_code'];
		
		
		// Now write the UUID of this upload into table of upload UUIDs.
		curl_setopt_array($curl, array(
			CURLOPT_POSTFIELDS => '[{"name":"' . $upload_uuid_table . '",
									"time_precision":"ms",
									"columns":["uuid"],
									"points":[[' . '"' . $data_uuid . '"]]}]'
		));
		
		// Send the request.
		curl_exec($curl);		

	}
	
	// Close request to clear up some resources.
	curl_close($curl);
	
	// No error handling yet. Just print back results.
	$result = "Code: " . $rsp_code . ". Data written.";
	echo json_encode($result);	
}

// Sends the query directly to the TSDB and returns the results.
// NOTE: This is only for INTERNAL testing.
function read_data($query) {
	// Get cURL resource.
	$curl = curl_init();
	
	$app = \Slim\Slim::getInstance();
	
	// URL-encode query.
	//$query_url = urlencode($query);
	$query_url = urlencode("select * from data;");
	
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
	
	// If response code is not 200, the database might be down.
	if ($rsp_code == 200) {
		// Directly return JSON from server.
		echo $resp;
	} else {
		$app->response->setStatus(404);
		echo("Error: cURL returned $rsp_code");
		
		$msg = "The GuerillaSensing database seems to be offline.\nUser got response code $rsp_code";

		// use wordwrap() if lines are longer than 70 characters
		$msg = wordwrap($msg, 70);

		// send email
		// mail("diener@teco.edu", "GuerillaSensing database issues", $msg);
		exec("echo \"From: teco <noreply@teco.edu>\nTo: diener <diener@teco.edu>\nSubject: Error\n\nThe database seems to be down.\" | msmtp --debug -a gmail diener@teco.edu");
	}
}

// Takes a list of upload UUIDs and returns a subset of those IDs.
// The returned IDs are the IDs of uploads that are not on the server yet.
function check_uuids(){
	// Get cURL resource.
	$curl = curl_init();
	
	$app = \Slim\Slim::getInstance();
	
	// Read parameters from POST body and collect data.
	$app = \Slim\Slim::getInstance();
	$request = $app->request();
	$body = $request->getBody();
	$uuid_array = json_decode($body, true); 
	$upload_uuid_table = 'uuid';

	$query_url = "SELECT uuid FROM uuid";
	$i = 0;

	foreach ($uuid_array as $uuid) {
		// Build query that returns all UUIDs that we do not need (already in DB).
		if ($i == 0) {
			$query_url .= " WHERE uuid = '" . $uuid . "'";
		} else {
			$query_url .= " OR uuid = '" . $uuid . "'";
		}

		$i++;
	}
		
	// Encode as URL.
	$query_url = urlencode($query_url);
	
	// Set some options.
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/data/series?q=' . $query_url . '&u=root&p=root',
		CURLOPT_USERAGENT => 'GuerillaSensingPHPServer'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	
	
	// Close request to clear up some resources
	curl_close($curl);

	// If response code is not 200, the database might be down.
	if ($rsp_code == 200) {
	
		// Check which IDs we need.
		$needed_ids = "";
		foreach ($uuid_array as $uuid) {
			// Build query that returns all UUIDs that we do not need (already in DB).
			if (strpos($resp,$uuid) !== false) {
				// UUID already in DB, so it is not needed.
			} else {
				$needed_ids .= $uuid . ";";
			}

			$i++;
		}
		
		// Directly return JSON from server.
		echo $needed_ids;
	} else {
		$app->response->setStatus(404);
		echo("Error: cURL returned $rsp_code");
				
		$msg = "The GuerillaSensing database seems to be offline.\nUser got response code $rsp_code";

		// use wordwrap() if lines are longer than 70 characters
		$msg = wordwrap($msg, 70);

		// send email
		exec("echo \"From: teco <noreply@teco.edu>\nTo: diener <diener@teco.edu>\nSubject: Error\n\nThe database seems to be down.\" | msmtp --debug -a gmail diener@teco.edu");
	}
}



function tsdb_query_data() {
	// Get cURL resource.
	$curl = curl_init();
	
	$app = \Slim\Slim::getInstance();
	
	// URL-encode query.
	$query_url = urlencode("select * from data;");
	
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
	
	// If response code is not 200, the database might be down.
	if ($rsp_code == 200) {
		// Directly return JSON from server.
		echo $resp;
	} else {
		$app->response->setStatus(404);
		echo("Error: cURL returned $rsp_code");
		
		$msg = "The GuerillaSensing database seems to be offline.\nUser got response code $rsp_code";

		// use wordwrap() if lines are longer than 70 characters
		$msg = wordwrap($msg, 70);

		// send email
		// mail("diener@teco.edu", "GuerillaSensing database issues", $msg);
		exec("echo \"From: teco <noreply@teco.edu>\nTo: diener <diener@teco.edu>\nSubject: Error\n\nThe database seems to be down.\" | msmtp --debug -a gmail diener@teco.edu");
	}
}

// Start REST API.
$app->run();

exit();
?>

Something is wrong with the XAMPP installation :-(
