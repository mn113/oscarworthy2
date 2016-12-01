<?php

// Database access information & commands

DEFINE ('DB_USER', 'martin');
DEFINE ('DB_PASSWORD', 'c4n4l+!');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'oscarworthy');


function db_connect() {
	$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
	$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$dbh->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
	$dbh->exec("SET names utf8");

	// PDO Error reporting:
	//$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
	$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	//$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	return ($dbh);
}


// Initialise connection globals:

$dbh = db_connect();

$token  = new \Tmdb\ApiToken('b93175b049e96a26b1aeb18429a433d6');
$client = new \Tmdb\Client($token, [
    'log' => [
        'enabled' => true,
        'path'    => '/logs/php-tmdb-api.log'
    ]
]);
$repository = new \Tmdb\Repository\MovieRepository($client);
