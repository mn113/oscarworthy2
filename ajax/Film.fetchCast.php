<?php
require_once('../config.php');

// Receive fid via GET:
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$fid = $_GET['id'];

	// Make a blank film object - the object which existed on the calling page
	$film = new Film($fid, $dbh, $tmdb);
	
	// Get local & non-local film data:
	if ($film->fetchLocal() && $film->fetchCastFull()) {
		// JSONP return:
		echo $_GET['callback'].'('.json_encode($film->cast).')';
	}
//	else {
//		echo 'film->fetchCastFull() error';
//	}
}