<?php
require_once('../config.php');

// Receive fid via POST:
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
	$id = $_POST['id'];
	if (isset($_POST['type']) && in_array($_POST['type'], array('film', 'person'))) {
		$page = $_POST['type'];

		// Retrieve ratings from db and put in $_SESSION:
		$starhelper = new StarHelper($dbh, $page, $id);
		$starhelper->getAvgRatings();
		if (User::isLogged()) {
			$starhelper->getMemberRatings();
		}

		// Compose js array:
		$jsarray = array();
		foreach ($_SESSION['votes'] as $hash => $data) {
			$jsarray[$hash] = $data;
		}
		
		// Return:
		echo json_encode($jsarray);
	}
}
else {
	echo 'Params error';
}
