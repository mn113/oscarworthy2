<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Process form:
if (isset($_POST['submitted'])) {
	extract($_POST);
	$cbody = get_magic_quotes_gpc() ? stripslashes($cbody) : $cbody;

	$securimage = new Securimage();
	if ($securimage->check($_POST['captcha_code']) == true) {	// Captcha ok

		$eml = new Email(null);
		$eml->setBody('contactform');
		if ($eml->sendThis()) {
			$success = true;
			Messages::create('success', 'Thanks for your message. We will read it after lunch.', false);
		}
		else {
			$success = false;
			Messages::create('error', 'Your message was not delivered. Please try again or email system@oscarworthy.com with your thoughts.', false);
		}
	}
	else {	// Wrong captcha
		$success = false;
	 	Messages::create('error', 'The verification code you entered was wrong.', false);
	}
}


// Starting HTML:
$page_title = 'Contact';
include_once('../inc/header.php'); ?>

<h2>Contact Us</h2>

<form id="contact" name="contact" action="" method="post" class="thin">
	<fieldset>
	<legend>Contact Us</legend>
	<p><label for="name">Name: </label><input type="text" name="name" id="name"
	<?php if (isset($cbody) && !$success) {echo "value='$name'";} ?>/>
	</p>

	<p><label for="email">Email: </label><input type="text" name="email" id="email"
	<?php if (isset($cbody) && !$success) {echo "value='$email'";} ?>/>
	</p>
	
	<label for="cbody">Message: </label>
	<textarea name="cbody" rows="4" cols="40"><?php if (isset($cbody) && !$success) echo $cbody; ?></textarea>

	<p><label for="captcha_code">Captcha code: </label><input type="text" name="captcha_code" size="10" maxlength="6" /></p>
	<img id="captcha" src="/securimage/securimage_show.php" alt="CAPTCHA Image" />
	<a href="#" onclick="document.getElementById('captcha').src = '/securimage/securimage_show.php?' + Math.random(); return false"><img src="/img/icons/renew.png" alt="Renew" /></a>

	<p><input type="hidden" name="uid" value="<?php if (isset($_SESSION['uid'])) echo $_SESSION['uid']; ?>" /></p>
	<p><input type="hidden" name="submitted" value="true" /></p>
	<p><input type="submit" name="submit" value="Do it" /></p>
	</fieldset>
</form>

<?php include_once('../inc/footer.php');
