<?php

class Validation {	// Static class


	/**
	* Validate individual field.
	*/
	public static function validateLength($fieldname, $input, $min = 1, $max = 255) {
		if ($input == '') {
			Messages::create('error', "Enter a $fieldname.");
			return false;			
		}
		if (strlen($input) < $min) {
			Messages::create('error', "$fieldname must be at least $min characters.");
			return false;			
		}
		if (strlen($input) > $max) {
			Messages::create('error', "$fieldname cannot be more than $max characters.");
			return false;			
		}
		return true;	
	}

	/**
	* Validate individual field.
	*/
	public static function validateAlpha($fieldname, $input) {	// OK
		if (!ctype_alnum($input)) {
			Messages::create('error', "$fieldname can only contain letters and numbers.");
			return false;			
		}
		return true;
	}

	/**
	* Validate individual field.
	*/
	public static function validateEmail($email) {
		if (!preg_match('/^[\w._-]+@[\w.-]{2,}.[a-z]{2,6}$/', $email)) {	// WRONG
			Messages::create('error', 'Invalid email.');
			return false;			
		}
		return true;
	}

	/**
	* Validate individual field.
	*/
	public static function validateMatch($fieldname, $input1, $input2) {	// OK
		if ($input2 != $input1) {
			Messages::create('error', "$fieldname confirmation doesn't match $fieldname.");
			return false;			
		}
		return true;
	}


	/**
	* Validate values from registration form.
	*/
	public static function validateRegistration($username, $pass1, $pass2, $email, $realname) {
		// Clean, sanitise form values:
		$username = trim($username);
		$realname = trim($realname);
		$email = strtolower(trim($email));	// makes regex easier
		
		$checks = array();
		
		$checks[] = self::validateLength('username', $username, 2, 20);
		$checks[] = self::validateAlpha('username', $username);
		$checks[] = self::validateLength('password', $pass1, 8);
		$checks[] = self::validateMatch('password', $pass1, $pass2);
		$checks[] = self::validateEmail($email);
		$checks[] = self::validateLength('realname', $realname, 2);

		if (in_array(false, $checks)) {
			return false;
		}
		else {	// All data passed validation
			return true;
		}		
	}
	
}