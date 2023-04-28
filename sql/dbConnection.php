#!/usr/bin/php
<?php

function dbConnection() {
	$hostname = 'localhost';
	$user = 'kav32';
	$password = 'spring23';
	$dbName = 'IT490';

	$mydb = new mysqli($hostname, $user, $password, $dbName);
	
	if ($mydb->errno != 0) {
	echo "Failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
	}

	echo "Successfully connected to database.".PHP_EOL;
	return $mydb;
}

?>
