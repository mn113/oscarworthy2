<?php

class Panel { // Static class
	
	public static $register_link	= "<a href='/register/:SSL' />Register</a>";
	public static $login_link		= "<a href='/login/:SSL' />Login</a>";
	public static $profile_link		= "<a href='/profile/' />Profile</a>";
	public static $logout_link		= "<a href='/logout/:NOSSL' />Logout</a>";


	static function display() {
		// Check session state:
		if(User::isLogged()) {
			return self::displayLogged();
		}
		else {
			return self::displayGuest();
		}	
	}	


	static function displayLogged() {
		$html = "<p><span>".$_SESSION['username']."</span>: ".self::$profile_link." | ".self::$logout_link."</p>";
		return $html;
	}


	static function displayGuest() {
		$html = "<p>".self::$register_link." or ".self::$login_link."</p>";
		return $html;
	}
}