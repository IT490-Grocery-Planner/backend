<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once('login.php.inc');
require_once('dbConnection.php'); //establishes connection to database
require_once('dbClient.php'); //sends requests to dmz

//function to create session
function createSession($email) {
	$mydb = dbConnection();
	$sessionID = SHA1($email.time());
	$sessionQuery = "INSERT INTO Sessions VALUES ('$email', '$sessionID', NOW())";
	$result = $mydb->query($sessionQuery);
	return $sessionID;
}

//function to valid session
function validateSession($sessionID) {
	$mydb = dbConnection();
	$query = "SELECT UNIX_TIMESTAMP(creationTime) as epoch FROM Sessions WHERE sessionID = '$sessionID'";
	$result = $mydb->query($query);
	$row = $result->fetch_assoc();
	$epoch = intval($row['epoch']);
	$timeElapsed = time()-$epoch;
	if ($timeElapsed > 1200) {
		//$deleteSession = "DELETE * FROM Sessions";
		$deleteSession = "DELETE FROM Sessions WHERE sessionID = '$sessionID'";
		$result = $mydb->query($deleteSession);
		return json_encode(['valid' => 0]);
	}
	else {		
		$updateSession = "UPDATE Sessions SET creationTime = NOW() WHERE sessionID = '$sessionID'";
		$result = $mydb->query($updateSession);
		return json_encode(['valid' => 1]);
	
	}
}

//function for user login
function doLogin($email, $password) {
	$mydb = dbConnection();
	$hash = SHA1($password);
	$query = "SELECT * FROM Users WHERE email = '$email' AND password = '$hash'";
	$result = $mydb->query($query);
	$user = $result->fetch_assoc();
	$first = $user['first'];
	$last = $user['last'];
	if ($result->num_rows == 1) {
		return json_encode(['fname' => $first, 'lname' => $last, 'email' => $email, 'sessionID' => createSession($email)]);
	}
	else {
		return json_encode(['message' => 'wrong email/password']);
	}
}

//function for user registration
function doRegister($first, $last, $email, $password) {
	$mydb = dbConnection();
	$hash = SHA1($password);
	$query = "SELECT * FROM Users WHERE email = '$email'";
	$result = $mydb->query($query);
	if ($result->num_rows == 1 ) {
		return json_encode(['message' => 'That email address is in use']);
	}
	else {
		$registerQuery = "INSERT INTO Users VALUES ('$first', '$last','$email', '$hash')";
		$result =$mydb->query($registerQuery);
		return json_encode(['fname' => $first, 'lname' => $last, 'email' => $email, 'sessionID' => createSession($email)]);
	}
}

//function to retrieve email from sessionID
function selectEmailFromSession($sessionID) {
 	$mydb = dbConnection();
	$query = "SELECT email FROM Sessions WHERE sessionID = '$sessionID'";
	$result = $mydb->query($query);
	$session = $result->fetch_assoc();
	if ($result->num_rows == 1) {
		return $session['email'];
	}
}

//function to add user's groceries
function addGroceries($sessionID, $groceries) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	foreach($groceries as $grocery) {
		$buyDate = $grocery['buyDate'];
		$buyDate = $buyDate != null ? strtotime($buyDate) : 0;
		$expirationDate = $grocery['expirationDate'];
		$expirationDate = $expirationDate != null ? strtotime($expirationDate) : 0;
		$item = $grocery['name'];
		$image = $grocery['image'];
		$ingredientID = $grocery['id'];
		$amount = $grocery['amount'];
		$query = "INSERT INTO Groceries (item, amount, expirationDate, buyDate, email, ingredientID, image) VALUES ('$item', '$amount', '$expirationDate', '$buyDate', '$email', '$ingredientID', '$image') ON DUPLICATE KEY UPDATE amount = amount+$amount";
		$result = $mydb->query($query);
	}
	return json_encode(["message" => "added groceries"]);
}

/*
function addToGroceryList($sessionID, $item) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "INSERT INTO Groceries (email, item) VALUES ('$email, '$item')";
	$result = $mydb->($query);
	return json_encode(['message' => 'Added groceries to list']);
}
 */

//function to get the user's current groceries and grocery list
function getUserGroceries($sessionID) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$bought = "SELECT * FROM Groceries WHERE email = '$email' AND buyDate != 0";
	$groceryList = "SELECT * FROM Groceries WHERE email = '$email' AND buyDate = 0";
	$boughtResults = $mydb->query($bought);
	$listResults = $mydb->query($groceryList);	
	/*
	if ($boughtResults->num_rows == 0 && $listResults->num_rows == 0) {
		echo "no groceries";
		return json_encode(["groceries" => [], "listItems" =>;
	}*/
	$groceries = $boughtResults->fetch_all(MYSQLI_ASSOC);
	$listItems = $listResults->fetch_all(MYSQLI_ASSOC);
	var_dump($groceries, $listItems);
	return json_encode(["groceries" => $groceries, "listItems" => $listItems]);
}

//function to move items from grocery list to fridge 
function listToFridge($sessionID, $grocery) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$item = $grocery['item'];
	$buyDate = $grocery['buyDate'];
	$buyDate = $buyDate != null ? strtotime($buyDate) : 0;
	$expirationDate = $grocery['expirationDate'];
	$expirationDate = $expirationDate != null ? strtotime($expirationDate) : 0;
	$query = "UPDATE Groceries SET buyDate = '$buyDate', expirationDate = '$expirationDate' WHERE item = '$item' AND email = '$email' AND buyDate = 0";
	$result = $mydb->query($query);
	return json_encode(["message" => "successfully moved item from grocery list to fridge"]);
}

//function to get user's groceries that are expiring in a week
function getExpiringGroceries($sessionID) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT * FROM Groceries WHERE expirationDate < UNIX_TIMESTAMP(NOW()) + 604800)";
	$expiringItems = $mydb->query($query);
	if ($expiringItems->num_rows == 0) {
		echo "no groceries are expiring this week";
		return false;
	}
	else {
		$expired = $result->fetch_all(MSQLI_ASSOC);
		return (['expiringItems' => $expiringGroceries]);
	}
}

//function to save/rate recipe
function saveRateRecipe($sessionID, $recipe) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$recipeID = $recipe["id"];
	$title = $recipe["title"];
	$imgURL = $recipe["image"];
	$sourceURL = $recipe["sourceUrl"];
	$rating = $recipe["rating"];
	$query = "INSERT INTO Saved_Rated_Recipes (email, recipeID, title, image, sourceUrl, rating) VALUES ('$email','$recipeID', '$title', '$imgURL', '$sourceURL', '$rating') ON DUPLICATE KEY UPDATE rating = $rating";
	$result = $mydb->query($query);
	return json_encode(["message" => "succesfully saved/rated"]);
}
	
/*S
function rateRecipe($sessionID, $recipe) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "INSERT INTO Saved_Rated_Recipes (email, recipeID, title, imgURL, sourceURL, rating) VALUES ('$email','$recipe['recipeID'], '$recipe['title]', '$recipe['imgURL'], '$recipe['sourceURL'], '$recipe['rating'])";
	$result = $mydb->query($query);
}
 */

//function to view rated recipes for each user
function viewRatedRecipes($sessionID) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT * FROM Saved_Rated_Recipes WHERE email = '$email'"; 
	$result = $mydb->query($query);
	if ($result->num_rows == 0) {
		echo "you have not rated any recipes";
		return json_encode(["message" => "you have not rated any recipes"]);
	}
	else {
		$userRatedRecipes = $result->fetch_all(MYSQLI_ASSOC);
		return json_encode(["userRatedRecipes" => $userRatedRecipes]);
	}
}

//function to store user-created recipes
function storeUserRecipe($sessionID, $userRecipe) {
	$mydb = dbConnection();
	$title = $userRecipe['title'];
	$description = $userRecipe['description'];
	$instructions = $userRecipe['instructions'];
	$maxReadyTime = $userRecipe['maxReadyTime'];
	$makerOfRecipe = selectEmailFromSession($sessionID);
	$query = "INSERT INTO User_Recipes (title, description, instructions, maxReadyTime, makerOfRecipe) VALUES ('$title', '$description', '$instructions', '$maxReadyTime', '$makerOfRecipe')";
	$result = $mydb->query($query);
	return json_encode(['messasge' => 'your recipe has been stored']);
}

//function to retrieve user-created recipes from database
function getUserRecipe($sessionID) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT * FROM User_Recipes WHERE makerOfRecipe = '$email'";
	$result = $mydb->query($query);
	$getUserRecipes = $result->fetch_all(MYSQLI_ASSOC);
	return json_encode(["getUserRecipes" => $getUserRecipes]);
}

//function to search for a recipe using a keyword
function searchKeywordRecipe($keyword) {
	$mydb = dbConnection();
	$query = "SELECT * FROM Recipes WHERE title LIKE '%$keyword%'";
	$result = $mydb->query($query);
	if ($result->num_rows == 0) {
		$response = dbClient(["type" => "keywordrecipe", "keywordrecipe" => $keyword]);
		echo $response;
		return $response;
	}
}

/*
function dmzRecipe($title, $description, $instructions, $maxReadyTime) {
	$request = array();
	$request['type'] = array('keywordrecipe', 'groceryrecipe', 'expirerecipe');
	$request['titleMatch'] = $title;
	$request['addRecipeInformation'] = $description;
	$request['instructionsRequired'] = $instructions;
	$request['maxReadyTime'] = $maxReadyTime;
	$response = dbClient($request);
	echo var_dump($response);
	return $response;
}

function addRecipeDB($title, $description, $instrictions, $maxReadyTime) {
	$mydb = dbConnection();
	for ($i = 0; $i < count($recipes); $i++) {
		$recipeID = $recipes[$i]['recipeID'];
		$title = $recipes[$i]['title'];
		$description = $recipes[$i]['description'];
		$instructions = $recipes[$i]['instructions'];
		$maxReadyTiem = $recipes[$i]['maxReadyTime'];
		$query = "INSERT INTO Recipes VALUES ('$recipeID', '$title', '$description', '$instructions', '$maxReadyTime')";
		$result = $mydb->query($query);
 */

//function to search for recipes based on current groceries
function searchGroceryRecipe($sessionID) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT item FROM Groceries WHERE email = '$email' AND buyDate != 0 AND expirationDate > UNIX_TIMESTAMP(NOW())";
	$result = $mydb->query($query);
	$groceryItems = $result->fetch_all();
	/*
	if ($groceryItems != false) {
		$groceryRecipeQuery = "SELECT * FROM Recipes r JOIN Recipe_Ingredients ri ON r.recipeID = ri.recipeID JOIN Ingredients i ON i.ingredientID = ri.ingredientID JOIN Groceries g ON g.item = i.ingredient AND i.buyDate IS NOT NULL";
		$groceryRecipeResult = $mydb->query($groceryRecipeQuery);
		if ($groceryRecipeResult->num_rows == 0) {
	 */
	$response = dbClient(["type" => "groceryrecipe", "groceryrecipe" => $groceryItems]);
	echo $response;
	return $response;
}
 
//function to search for recipes based on user's expiring groceries
function searchExpireRecipe ($sessionID) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
$query = "SELECT item FROM Groceries WHERE email = '$email' AND expirationDate > UNIX_TIMESTAMP(NOW()) AND expirationDate < UNIX_TIMESTAMP(NOW()) + 604800 AND buyDate != 0";
	$result = $mydb->query($query);
	/*
		$expireRecipeQuery = "SELECT * FROM Recipes r JOIN Recipe_Ingredients ri ON r.recipeID = ri.recipeID JOIN Ingredients i ON i.ingredientID = ri.ingredientID JOIN Groceries g ON g.item = i.ingredient AND g.expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
	 */
	$expired = $result->fetch_all();
	$response = dbClient(["type" => "expirerecipe", "expirerecipe" => $expired]);
	echo $response;
	return $response;
}

//function to generate a grocery list by searching
function genGroceryList ($sessionID, $search) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$response = dbClient(["type" => "grocerylist", "grocerylist" => $search]);
	echo $response;
	return $response;
}

?>

