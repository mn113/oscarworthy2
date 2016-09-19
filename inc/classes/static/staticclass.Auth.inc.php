<?php

class Auth {	// static class

	private static $salt = 'SALTYTHING';


	static function checkpoint() {

		// For logged users:
		if (User::isLogged()) {
			// Check session expiry cookie:
			if (!isset($_COOKIE['session_expiry'])) {
				// Cookie expiry (normally 24h) has elapsed.
				User::logout();
				Messages::create('neutral', "Your session has expired after 24 hours alive. Time to reauthenticate perhaps.", false);
				header("Location: /login/");
			}

			// On user pages only:
			if (preg_match('/pages_user/', $_SERVER['PHP_SELF'])) {
				
				// User Agent check (do on all user page loads):
				if (isset($_SESSION['ua_fingerprint'])) {
					if ($_SESSION['ua_fingerprint'] != md5($_SERVER['HTTP_USER_AGENT'].self::$salt)) {
						// Inconsistent UA, require password:
						User::logout();
						Messages::create('neutral', "Your user agent changed. Time to reauthenticate perhaps.", false);
						header("Location: /login/");
					}
				}
				else {
					$_SESSION['ua_fingerprint'] = md5($_SERVER['HTTP_USER_AGENT'].self::$salt);
				}						

				if (rand(1,5) == 5) session_regenerate_id(); // For good measure
			}		
		}

		// For everyone:
		// Check if session timeout cookie has expired (do this every page load):
		if (!isset($_COOKIE['session_timeout'])) {
			User::logout();
			Messages::create('neutral', "Your session timed out (but it's ok).", false);
		}
		// Now reset short timeout:
		$_SESSION['timeout'] = time()+1*3600;
		setcookie('session_timeout', 'valid', time()+1*3600, '/', '', false, true);
	}

} // end class Auth



// What about the csrf in a cookie?