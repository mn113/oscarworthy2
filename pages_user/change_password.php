<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Check session state:
if(!User::isLogged()) {
	header("Location: /login/");
}

// Process code from URL:
$code = false;
if (isset($_GET['code']) && $_GET['code'] != '') {
	$code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
	
	// Check db to see if code matches:
	if (!$_SESSION['user']->checkPasswordCode($code)) {
		$code = false;
		Messages::create('error', 'The code is not valid.');
	}
}

// CSRF token & referrer checks:
if (isset($_POST['submitted1']) || isset($_POST['submitted2'])) {

	if ($_SESSION['csrf_token'] != $_POST['csrf_token']) {
		Messages::create('error', 'CSRF mismatch.');
		header("Location: /");
	}
	if (!preg_match('|'.$_SERVER['HTTP_HOST'].'|', $_SERVER['HTTP_REFERER'])) {
		Messages::create('error', 'Referrer mismatch.');
		header("Location: /");	
	}
}

// Process form 1 if submitted:
if (isset($_POST['submitted1']) && $_POST['submitted1'] == 'true') {

	// Prep & send out the nonce:
	$_SESSION['user']->prepChangePasswordCode();
	if ($_SESSION['user']->sendChangePasswordCode()) {
		Messages::create('neutral', 'Please check your email for the link we have sent you.', false);
	}	
}

// Process form 2 if submitted:
if (isset($_POST['submitted2']) && $_POST['submitted2'] == 'true') {

	// Clean input:
	$clean = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	extract($clean);

	// Validate input:
	$v1 = Validation::validateLength('password', $new_pass1, 8);
	$v2 = Validation::validateMatch('password', $new_pass1, $new_pass2);

	// Go ahead and change db record:
	if ($v1 && $v2) {
		$_SESSION['user']->changePassword($new_pass1);
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

<?php if (!$code): // Stage 1?>

	<p>To change your password we need to send you a verification link by email.</p>
	<p>To proceed with this, click the button below:</p>
	<form name="send_password" action='' method="POST" class="thin">
		<input type="hidden" name="submitted1" value="true" />
		<input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
		<input type="submit" name="send_password" value="Send it to me">
	</form>

<?php else: // Stage 2 ?>

	<form id="change_password" action='' method="POST" class="thin">
		<fieldset>
			<legend>Change password</legend>
			<p>
				<label for="new_pass1">New password:</label>
				<input type="password" name="new_pass1" />
			</p>
			<p>
				<label for="new_pass2">Confirm:</label>
				<input type="password" name="new_pass2" />
			</p>
			<input type="hidden" name="submitted2" value="true" />
			<input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
			<input type="submit" name="submit" value="Make it so" />
		</fieldset>
	</form>

<?php endif; ?>


<?php include_once('../inc/footer.php'); ?>