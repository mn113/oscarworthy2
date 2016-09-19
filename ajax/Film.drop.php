<?php
require_once('../config.php');

// Receive fid via POST:
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
	$fid = $_POST['id'];

	FB::log($fid, 'dropping');
	// Instantiate:
	$film = new Film($fid, $dbh, $tmdb);
	// And drop:
	$film->delete();

	$msg_id = substr(microtime(), 4, 4);
	Messages::create('neutral', 'Deleted! :O', $msg_id);
	// Return tells javascript which msg to pop up:
	echo $msg_id;
}