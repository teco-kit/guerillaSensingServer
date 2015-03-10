
<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/hello/:name/:bla', function ($name, $bla) {
    echo "Hello, $name $bla";
	
	// Get cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'http://docker.teco.edu:8086/db/cool_db/series?u=root&p=root',
		CURLOPT_USERAGENT => 'Codular Sample cURL Request',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => '[{"name":"foo","columns":["val"],"points":[[23]]}]'
	));
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	$info = curl_getinfo($curl);
	$rsp_code = $info['http_code'];
	
	echo "Response: $resp<br>Code $rsp_code";
	// Close request to clear up some resources
	curl_close($curl);
});

$app->run();

exit();
?>

Something is wrong with the XAMPP installation :-(
