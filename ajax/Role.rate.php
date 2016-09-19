<?php
require_once('../config.php');

/*
 *	Should be called with the url parameters of 'hash' and 'vote'
 *	E.g. rate_perf.php?hash=38936a32&vote=9
 */

// $_GET checks: 
if (isset($_GET['hash']) && $_GET['hash'] != '' && ctype_alnum($_GET['hash'])) {
	$hash = $_GET['hash'];

	if (isset($_GET['vote']) && $_GET['vote'] != '' && is_numeric($_GET['vote'])) {
		$vote = (int) $_GET['vote'];
	}
	else {
		echo 'vote invalid.';
		die();
	}
}
else {
	echo 'hash invalid.';
	die();
}
// Malicious $_GET variables cannot get past this point


// Lookup the hash in roles table:
$role = new Role($dbh);
$role->setHash($hash);
$vote_success = false;
if ($role->lookup()) {
	// Apply new vote:
	$role->setRating($vote);
	FB::log($role->getRating(), 'New rate');
	
	// Store the vote for member (db) or guest (session)
	if (User::isLogged()) {
		$memvote = new Vote($_SESSION['uid'], $hash, $vote, $dbh, $tmdb);
		$memvote->store();
	}
	else {
		$_SESSION['votes'][$hash]['memvote'] = $vote;
	}
	$vote_success = true;

}
else {
	echo 'Lookup failed.';
}

// Set a feedback message:
//$msg_id = substr(microtime(), 4, 4);
//$text = $vote_success ? MSG_RATE_SUCCESS : MSG_RATE_ERROR;
//$class = $vote_success ? "success" : "error";
//Messages::create($class, $text, $msg_id);

// Return message id to js (make sure this is this script's only output):
//echo $msg_id;