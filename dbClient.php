#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function dbClient($request) {
	$client = new rabbitMQClient("serversMQ.ini","DMZServer");
	$response = $client->send_request($request);
	return $response;
}

/*$request = array();
$request['type'] = "login";
$request['email'] = $argv[1];
$request['password'] = $argv[2];
$request['message'] = "HI";
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
 */
