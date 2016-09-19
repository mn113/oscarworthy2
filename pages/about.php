<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Process form:
if (isset($_POST['submitted'])) {
	// CLEAN IT
	$clean = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	extract($clean);
	$cbody = get_magic_quotes_gpc() ? stripslashes($cbody) : $cbody;

	// CSRF token & referrer checks:
	if ($_SESSION['csrf_token'] != $_POST['csrf_token']) {
		Messages::create('error', 'CSRF mismatch.');
		header("Location: /");
	}
	if (!preg_match('|'.$_SERVER['HTTP_HOST'].'|', $_SERVER['HTTP_REFERER'])) {
		Messages::create('error', 'Referrer mismatch.');
		header("Location: /");	
	}

	$securimage = new Securimage();
	if ($securimage->check($_POST['captcha_code']) == true) {	// Captcha ok
		if (!is_numeric($reply_to)) $reply_to = 0;
		$comm = new Comment($dbh, $cbody, $username, $uid, $reply_to);
		$success = $comm->post();
	}
	else {	// Wrong captcha
		$success = false;
		Messages::create('error', 'The verification code you entered was wrong.', false);
	}
}

// Generate new token:
$token = md5(uniqid(rand(), TRUE));
$_SESSION['csrf_token'] = $token;


// Starting HTML:
$page_title = 'About';
include_once('../inc/header.php'); ?>

<h2>Why?</h2>

<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

<h2>Feedback</h2>

<form id="comments_filter" class="cf thin">
	<label for="range">Show comments:</label>
	<input type="range" min="0" max="5" step="1" value="1" />
	<span id="comment_counter">comments displayed</span>
</form>


<div id="comments">
	<?php Feedback::getComments(); ?>
</div>

<form id="feedback" name="feedback" action="" method="post" class="thin">
	<fieldset>
	<legend>Have your say</legend>
	<p><label for="username">Name: </label><input type="text" name="username" id="username"
	<?php if (isset($_SESSION['username'])) {echo "value='{$_SESSION['username']}'";}
			elseif (isset($cbody) && !$success) {echo "value='$username'";} ?>
	/></p>
	
	<label for="cbody">Comment: </label>
	<textarea name="cbody" rows="4" cols="40"><?php if (isset($cbody) && !$success) echo $cbody; ?></textarea>

	<p><label for="captcha_code">Captcha code: </label><input type="text" name="captcha_code" size="10" maxlength="6" /></p>
	<img id="captcha" src="/securimage/securimage_show.php" alt="CAPTCHA Image" />
	<a href="#" onclick="document.getElementById('captcha').src = '/securimage/securimage_show.php?' + Math.random(); return false"><img src="/img/icons/renew.png" alt="Renew" /></a>

	<input type="hidden" name="uid" value="<?php if (isset($_SESSION['uid'])) echo $_SESSION['uid']; ?>" />
	<input type="hidden" name="reply_to" value="" />
	<input type="hidden" name="csrf_token" value="<?php echo $token; ?>" />
	<input type="hidden" name="submitted" value="true" />
	<p><input type="submit" name="submit" value="Do it" /></p>
	</fieldset>
</form>

<?php include_once('../inc/footer.php');
