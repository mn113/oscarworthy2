<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Access control:
if(User::isLogged()) {
	Messages::create('neutral', MSG_ALREADY_LOGGED);
	header("Location: /profile/");
}

// Process forms if submitted:
if (isset($_POST['submitted1']) || isset($_POST['submitted2'])) {

	// CSRF token & referrer checks:
	if ($_SESSION['csrf_token'] != $_POST['csrf_token']) {
		Messages::create('error', 'CSRF mismatch.');
		header("Location: /");
	}
	if (!preg_match('|'.$_SERVER['HTTP_HOST'].'|', $_SERVER['HTTP_REFERER'])) {
		Messages::create('error', 'Referrer mismatch.');
		header("Location: /");	
	}

	// Process Login form:
	if (isset($_POST['submitted1']) && $_POST['submitted1'] == 'true') {
		// CLEAN IT
		$clean = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		extract($clean);
		
		// Cookie to make form perma-sticky:
		if (isset($remember) && $remember) {
			setcookie('login_username', $username, time()+3600, '/', '', 0, 0);
		}
		else {
			setcookie('login_username', '');
		}

		// Test user validity:
		$user = new User($dbh);
		if ($user->login($username, $password)) {	// Login successful!

			session_regenerate_id();
			// Store new user object:
			$_SESSION['user'] = $user;
			// Set a new session expiry:
			$session_expiry = md5(microtime());
			$_SESSION['session_expiry'] = $session_expiry;
			setcookie('session_expiry', $session_expiry, time()+24*3600, '/', '', false, true);

			Messages::create('success', MSG_WELCOME_BACK.", {$_SESSION['username']}.");
			// Redirect:
			if (isset($_SESSION['history']['prevpage'])) {
				$dest = $_SESSION['history']['prevpage'];
				header("Location: $dest");
			}
			else {
				header("Location: /profile/");			
			}
		}
	}
	// Process Reset form:
	elseif (isset($_POST['submitted2']) && $_POST['submitted2'] == 'true') {
		$email = trim($_POST['email']);
		if (Validation::validateEmail($email)) {
			// Lookup email:
			$user = new User($dbh);
			if ($user->lookupByEmail($email)) {
				// Send email:
				$eml = new Email($user);
				$eml->setBody('change_pass');
				if ($eml->sendThis()) {
					Messages::create("success", "We have sent you an email which will enable you to reset your password.");
				}
				else {
					Messages::create("error", "Oops, please try again.");
				}
			}
			else {
				Messages::create("error", "Unrecognised email address.");
			}
		}
	}
}

// Generate new token:
$token = md5(uniqid(rand(), TRUE));
$_SESSION['csrf_token'] = $token;


// Starting HTML:
$page_title = 'Login';
include_once('../inc/header.php'); ?>


<form id="login" name="login" action="" method="post" class="thin">
	<fieldset>
	<legend>Login</legend>
	<p>
		<label for="username">Username: </label>
		<input type="text" name="username" id="username" 
		<?php if (isset($_COOKIE['login_username'])) echo "value='{$_COOKIE['login_username']}'"; ?> />
	</p>
	<p>
		<label for="password">Password: </label>
		<input type="password" name="password" id="password" />
	</p>
	<p>
		<label for="remember">Remember me?</label>
		<input type="checkbox" name="remember" id="remember" value="remember" 
		<?php if (isset($_COOKIE['login_username'])) echo "checked='checked'"; ?> />
	</p>
	<p><a id="reset_link" href="#">Forgot password?</a></p>
	<p>No account? <a href="/register/">Register</a></p>
	<input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
	<input type="hidden" name="submitted1" value="true" />
	<p><input type="submit" name="submit1" value="Log in" /></p>
	</fieldset>
</form>

<form id="reset_pass" name="reset_pass" action="" method="post" class="thin">
	<fieldset>
	<legend>Reset password</legend>
	<p>Enter the email address you registered with and we'll send you a link to reset your password.</p>
	<p>
		<label for="email">Email: </label>
		<input type="text" name="email" id="email" />
	</p>
	<input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
	<input type="hidden" name="submitted2" value="true" />
	<p><input type="submit" name="submit2" value="Do it" /></p>
	</fieldset>
</form>

<?php include_once('../inc/footer.php');
