<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Check session state:
if(!User::isLogged()) {
	header("Location: /login/");
}

// Process forms if submitted:
if (isset($_POST['submitted']) && $_POST['submitted'] == 'true') {

	// CSRF token & referrer checks:
	if ($_SESSION['csrf_token'] != $_POST['csrf_token']) {
		Messages::create('error', 'CSRF mismatch.');
		header("Location: /");
	}
	if (!preg_match('|'.$_SERVER['HTTP_HOST'].'|', $_SERVER['HTTP_REFERER'])) {
		Messages::create('error', 'Referrer mismatch.');
		header("Location: /");	
	}

	// Cleaning input:
	$clean = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	extract($clean);

	// Validate input:
	$v1 = Validation::validateEmail($old_email);
	$v2 = Validation::validateEmail($new_email1);
	$v3 = Validation::validateMatch('Email', $new_email1, $new_email2);

	// Go ahead and change db record:
	if ($v1 && $v2 && $v3) {
		$_SESSION['user']->changeEmail($old_email, $new_email1);
	}
}

// Generate new token:
$token = md5(uniqid(rand(), TRUE));
$_SESSION['csrf_token'] = $token;

// Starting HTML:
$page_title = 'Profile';
include_once('../inc/header.php');
include_once('../inc/profile_menu.php'); ?>

<h2>Profile: <?php echo $_SESSION['user']->username; ?></h2>

<form id="change_email" action="" method="POST" class="thin">
	<fieldset>
		<legend>Change email address</legend>
		<p>
			<label for="old_email">Old email:</label>
			<input type="text" name="old_email" />
		</p>
		<p>
			<label for="new_email1">New email:</label>
			<input type="text" name="new_email1" />
		</p>
		<p>
			<label for="new_email2">Confirm:</label>
			<input type="text" name="new_email2" />
		</p>
		<input type="hidden" name="submitted" value="true" />
		<input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
		<input type="submit" name="submit" value="Make it so" />
	</fieldset>
</form>

<?php include_once('../inc/footer.php'); ?>