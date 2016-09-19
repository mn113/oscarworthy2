<?php
require_once('../config.php');

// Receive array via POST:
if (isset($_POST['query'])) {
	$query		= $_POST['query'];
	$res		= $_POST['res'];
	$clicked	= $_POST['clicked'];
	$dropdown	= $_POST['dropdown'];
	
	// Insert into db:
	Search::store($dbh, $query, $res, $clicked, $dropdown);
}