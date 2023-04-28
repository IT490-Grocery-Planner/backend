#!/usr/bin/php
<?php
$ROOT = "/home/it490/git/IT490-backend";
require_once("$ROOT/rabbit/path.inc");
require_once("$ROOT/rabbit/get_host_info.inc");
require_once("$ROOT/rabbit/rabbitMQLib.inc");

require_once("$ROOT/auth/authFunctions.php"); //functions for the database

function requestProcessor($request) {
	echo "received request".PHP_EOL;
	$errorClient = new rabbitMQClient("$ROOT/error_log/errorServerMQ.ini", "errorLogging");
	try {
	var_dump($request);
	if(!isset($request['type'])) {
		return "ERROR: unsupported message type";
       	}
	switch ($request['type'])
  	{
        	case "login":
			return doLogin($request['email'],$request['password']);
        	case "validateSession":
                	return validateSession($request['sessionID']);
        	case "register":
                	return doRegister($request['fname'],$request['lname'],$request['email'],$request['password']);
		default:
			return logerror($request['type'], $request['error']);
	}
	}
	catch (Exception $e) {
		$errorClient->send_request(['type' => 'DBerrors', 'error' => $e->getMessage()]);
		echo $e->getMessage();
	}

	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

function logerror($type, $error) {
        $file_data = $error;
        $file_data .= file_get_contents($type.'.txt');
        file_put_contents($type.'.txt', $file_data);
        return json_encode(["message" => "Error received"]);
}

$authServer = new rabbitMQServer("$ROOT/auth/authServerMQ.ini", "authServer");
$authServer->process_requests('requestProcessor');
exit();
?>
