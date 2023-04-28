<?php

$ROOT = "/home/it490/git/IT490-backend";
require_once("$ROOT/rabbit/path.inc");
require_once("$ROOT/rabbit/get_host_info.inc");
require_once("$ROOT/rabbit/rabbitMQLib.inc");

require_once("$ROOT/sql/dbConnection.php"); //establishes connection to database

//function to create session
function createSession($email) {
	$mydb = dbConnection();
	$sessionID = SHA1($email.time());
	$sessionQuery = "INSERT INTO Sessions(email, sessionID, creationTime) VALUES ('$email', '$sessionID', NOW())";
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
		$registerQuery = "INSERT INTO Users(first, last, email, password) VALUES ('$first', '$last','$email', '$hash')";
		$result =$mydb->query($registerQuery);
		return json_encode(['fname' => $first, 'lname' => $last, 'email' => $email, 'sessionID' => createSession($email)]);
	}
}
