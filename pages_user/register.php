<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Check session state:
if(User::isLogged()) {
	Messages::create('neutral', MSG_ALREADY_LOGGED);
	header("Location: /profile/");
}

// Process form:
if (isset($_POST['submitted1']) && $_POST['submitted1'] == 'true') {
	// CLEAN IT
	$clean = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	extract($clean);

	// CSRF token & referrer checks:
	if ($_SESSION['csrf_token'] != $_POST['csrf_token']) {
		Messages::create('error', 'CSRF mismatch.');
		header("Location: /");
	}
	if (!preg_match('|'.$_SERVER['HTTP_HOST'].'|', $_SERVER['HTTP_REFERER'])) {
		Messages::create('error', 'Referrer mismatch.');
		header("Location: /");	
	}

	// Validate data:
	$securimage = new Securimage();
	if ($securimage->check($_POST['captcha_code']) == true) {	// Captcha ok
		if (Validation::validateRegistration($username, $pass1, $pass2, $email, $realname)) {
			$user = new User($dbh);
			if ($user->register($username, $pass1, $email, $realname)) {
				Messages::create('success', "Registered.");
				// Activation email:
				$user->prepActivation();
				if ($user->sendActivation()) {
					Messages::create('success', 'You have been sent an activation email.', false);
				}
				else {
					Messages::create('error', 'Your account has been created but the activation email could not be sent. Please contact the site administrator to activate your account.', false);
				}
			}
		}
		else {
			Messages::create('error', 'Validation failed. Please try again.', false);
		}
	}
	else {	// Wrong captcha
		Messages::create('error', 'The verification code you entered was wrong.', false);
	}
}

// Generate new token:
$token = md5(uniqid(rand(), TRUE));
$_SESSION['csrf_token'] = $token;


// Starting HTML:
$page_title = 'Register';
include_once('../inc/header.php'); ?>


<form id="register" name="register" action="" method="post" class="thin">
	<fieldset>
	<legend>Register</legend>
	<p>
		<label for="username">Username: </label>
		<input type="text" name="username" id="username" <?php if (isset($username)) echo "value='$username'"; ?> />
		<span class="hint">Between 2 and 20 characters, letters and digits only.</span>
	</p>
	<p>
		<label for="pass1">Password: </label>
		<input type="password" name="pass1" id="pass1" <?php if (isset($pass1)) echo "value='$pass1'"; ?> />
		<span class="hint">At least 8 characters</span>
	</p>
	<p>
		<label for="pass2">Re-type password: </label>
		<input type="password" name="pass2" id="pass2" <?php if (isset($pass2)) echo "value='$pass2'"; ?> />
	</p>
	<p>
		<label for="realname">Real name: </label>
		<input type="text" name="realname" id="realname" <?php if (isset($realname)) echo "value='$realname'"; ?> />
	</p>
	<p>
		<label for="email">Email: </label>
		<input type="text" name="email" id="email" <?php if (isset($email)) echo "value='$email'"; ?> />
	</p>
	<p>
		<label for="captcha_code">Captcha code: </label>
		<input type="text" name="captcha_code" size="10" maxlength="6" />
	</p>
	<p>
		<img id="captcha" src="/securimage/securimage_show.php" alt="CAPTCHA Image" /><br />
		<a href="#" onclick="document.getElementById('captcha').src = '/securimage/securimage_show.php?' + Math.random(); return false">I can't read that bollocks!</a>
	</p>

	<input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
	<input type="hidden" name="submitted1" value="true" />
	<p><input type="submit" name="submit1" value="Submit" /></p>
	</fieldset>
</form>

<?php include_once('../inc/footer.php');
