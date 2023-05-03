#!/usr/bin/php
<?php
$ROOT = "/home/it490/git/IT490-backend";
require_once("$ROOT/rabbit/path.inc");
require_once("$ROOT/rabbit/get_host_info.inc");
require_once("$ROOT/rabbit/rabbitMQLib.inc");

require_once("$ROOT/app/appFunctions.php"); //functions for the database

function requestProcessor($request) {
	echo "received request".PHP_EOL;
	var_dump($request);
	$errorClient = new rabbitMQClient("$ROOT/error_log/errorServerMQ.ini", "errorLogging");
	try {
		if(!isset($request['type'])) {
			return "ERROR: unsupported message type";
		}
		
		$validSession = json_decode(validateSession($request['sessionID']), true)['valid'];

		if($validSession == 0) {
			return json_encode(['valid' => 0]);
		}

		switch ($request['type']) {
			case "keywordrecipe":
				return searchKeywordRecipe($request['keyword']);
			case "videorecipe":
				return searchVideoRecipe($request['keyword']);
			case "groceryrecipe":
				return searchGroceryRecipe($request['sessionID']);
			case "expirerecipe":
				return searchExpireRecipe($request['sessionID']);
			case "grocerylist":
				return genGroceryList($request['sessionID'], $request['search']);
			case "saveRecipe":
				return saveRateRecipe($request['sessionID'], $request['recipe']);
			case "rateRecipe":
				return saveRateRecipe($request['sessionID'], $request['recipe']);
			case "viewRated":
				return viewRatedRecipes($request['sessionID']);
			case "userRecipe":
				return storeUserRecipe($request['sessionID'], $request['userRecipe']);
			case "getUserRecipe":
				return getUserRecipe($request['sessionID']);
			case "addGroceries":
				return addGroceries($request['sessionID'], $request['groceries']);
			case "userGroceries":
				return getUserGroceries($request['sessionID']);
			case "listToFridge":
				return listToFridge($request['sessionID'], $request['grocery']);
			default:
				return logerror($request['type'], $request['error']);
		}
	}

	catch (Exception $e) {
		$errorClient->send_request(['type' => 'DBerrors', 'error' => $e->getMessage()]);
	}

	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

function logerror($type, $error) {
	$file_data = $error;
	$file_data .= file_get_contents($type.'.txt');
	file_put_contents($type.'.txt', $file_data);
	return json_encode(["message" => "Error received"]);
}
$appServer = new rabbitMQServer("$ROOT/app/appServerMQ.ini", "appServer");

$appServer->process_requests('requestProcessor');
exit();
?>
