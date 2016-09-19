<!-- Inline login form -->
<form id="login_mini" name="login" action='/login.php' method=''>
	<h4>Login</h4>
	<a href="#" class='close_button'></a>
	<input type="text" name="username" value="Username" />
	<input type="password" name="password" />
	<input type="hidden" name="csrf_token" value="bladibla" />
	<input type="hidden" name="submitted1" value="true" />
	<input type="submit" name="submit" value="submit" />
	<button type="submit"></button>
</form>

<!-- Small box to display above filmographies & casts -->
<div id="starlegend">
	<p class="leghead">Rate performances</p>
	<p class="leg1">Your rating</p>
	<p class="leg2">Avg rating</p>
</div>


<!--div class="buttons">
	<button type="submit" class="positive"><img src="/img/icons/tick.png" alt=""/> Save</button>
	<a href="/password/reset/"><img src="/img/icons/textfield_key.png" alt=""/> Change Password</a>
	<a href="#" class="negative"><img src="/img/icons/cross.png" alt=""/> Cancel</a>
</div-->

<form id="feedback_reply" name="feedback_reply" action="" method="post">
	<fieldset>
	<legend>Reply</legend>
	<p><label for="username">Name: </label><input type="text" name="username" id="username"
	<?php if (isset($_SESSION['username'])) echo "value='{$_SESSION['username']}'"; ?> /></p>
	
	<textarea name="cbody" rows="4" cols="40"></textarea>

	<p><label for="captcha_code">Captcha code: </label><input type="text" name="captcha_code" size="10" maxlength="6" /></p>
	<img id="captcha" src="/securimage/securimage_show.php" alt="CAPTCHA Image" /><br />
	</p><a href="#" onclick="document.getElementById('captcha').src = '/securimage/securimage_show.php?' + Math.random(); return false"><img src="/img/icons/renew.png" alt="Renew" /></a>

	<p><input type="hidden" name="uid" value="<?php if (isset($_SESSION['uid'])) echo $_SESSION['uid']; ?>" /></p>
	<p><input type="hidden" name="reply_to" value="" /></p>
	<p><input type="hidden" name="submitted" value="true" /></p>
	<p><input type="submit" name="submit" value="Do it" /></p>
	</fieldset>
</form>
