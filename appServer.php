#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

require_once('dbConnection.php'); //connects to the database
require_once('dbFunctions.php'); //functions for the database

function requestProcessor($request) {
	echo "received request".PHP_EOL;
	var_dump($request);
	if(!isset($request['type'])) {
		return "ERROR: unsupported message type";
	}
	switch ($request['type']) {
 		case "keywordrecipe":
			return searchKeywordRecipe($request['keyword']);
		case "groceryrecipe":
			return searchGroceryRecipe($request['sessionID'], $request['item']);
		case "expirerecipe":
			return searchExpireRecipe($request['sessionID'], $request['item']);
		case "grocerylist":
			return genGroceryList($request['sessionID'], $request['search']);
		case "rateRecipe":
			return rateRecipe($request['sessionID'], $request['rating']);
		case "storeRecipe";
			return storeRecipe($request['sessionID'], $request['name']);
	}

	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$appServer = new rabbitMQServer("testRabbitMQ.ini","testServer");

$appServer->process_requests('requestProcessor');
exit();
?>
