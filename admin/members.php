<?php
require_once('../config.php');

// Access control:
if(!User::isAdmin()) {
	header("Location: /login");
}


// Starting HTML:
$page_title = 'Admin';
include_once('../inc/header.php'); ?>

<h1>Admin area</h1>





<?php include_once('../inc/footer.php'); ?>
