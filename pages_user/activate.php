<?php
require_once('../config.php');
require_once('../inc/initpage.php');

if (isset($_GET['code']) && isset($_GET['email'])) {
	// Cleaning:
	$clean = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
	extract($clean);

	// Instantiate blank user:	
	$user = new User($dbh);
	$user->lookupByEmail($email);

	// Test code against email:
	if ($user->activate($code)) {
		Messages::create('success', 'Your account has been activated.', false);
		// Welcome email:
		$eml = new Email($user);
		$eml->setBody('welcome');
		$eml->sendThis();	// no check necessary, receipt of the email is not necessary

		// Redirect:
		header("Location: /profile/");
	}
	else {
		Messages::create('error', 'Your account could not be activated right now.', false);
	}
}
else {
	Messages::create('error', 'Invalid parameter.', false);
}


// Starting HTML:
$page_title = 'Activation';
include_once('../inc/header.php'); ?>

<h2>Activating your account</h2>

<?php include_once('../inc/footer.php');
