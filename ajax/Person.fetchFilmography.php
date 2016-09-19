<?php
require_once('../config.php');

// Receive pid via GET:
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$pid = $_GET['id'];

	// Make a blank person object - the object which existed on the calling page
	$person = new Person($pid, $dbh, $tmdb);
	
	// Get local & non-localperson data:
	if ($person->fetchLocal() && $person->fetchFilmographyFull()) {
		// JSONP return:
		echo $_GET['callback'].'('.json_encode($person->filmography).')';
	}
}