#!/usr/bin/php
<?php
require_once('path.inc');
//require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

function logerror($type, $error) {
	$file_data = $error;
	$error_log_name = $type.".txt";
	$file_data .= file_get_contents($error_log_name);

	return json_encode(["message" => "Error Logged"]);
}

function requestProcessor($request) {
	echo "received request".PHP_EOL;
	var_dump($request);
	if(!isset($request['type'])) {
		return "ERROR: unsupported error type";
	}
	switch ($request['type']) {
		case "DBerrors":
		case "Frontenderrors":
		case "DMZerrors":
		case "RabbitMQerrors":
			return logerror($request['type'],$request['error']);
	}

	return array("returnCode" => '0', 'message' => "unsupported error type");
}

$server = new rabbitMQServer("serversMQ.ini", "ErrorLogging");

echo "Error Logger BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');

echo "Error Logger END".PHP_EOL;
exit();

?>
