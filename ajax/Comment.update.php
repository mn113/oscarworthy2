<?php
require_once('../config.php');

if (isset($_POST['cid']) && $_POST['cid'] != '') {
	$cid = (int) $_POST['cid'];
	$action = $_POST['action'];

	$comm = new Comment($dbh, '', $cid);
	$success = false;

	// Select action to perform:
	$query = "";
	switch($action) {
		case 'upvote':
			if ($comm->upvote()) $success = true;
			break;
		case 'flag':
			if ($comm->flag()) $success = true;
			break;
		case 'delete':
			if (User::isAdmin() && $com->delete()) $success = true;
			break;
	}

	if ($success) {
		echo "Ok, $action $cid";	
	}
	else {
		echo "Error, could not $action $cid";
	}
}