<?php

class User {

// a User is a guest
//   - he can vote 20 times (msg1) or 50 (msg2) or more
//   - his votes are stored in his session (upto 50 then fifo)
//   - his votes are logged in the db
//   - his votes count for 1, then 0.1 after
//   - if he registers, his votes will be transferred to his account
//   - his votes remain visible on pages until his session dies
//   - if his session dies, he starts over

// a Member is logged in
//   - when he votes, it's stored in 2 db tables
//   - his votes define his taste
//   - his past votes remain visible on pages
//   - he has a profile
//   - he can manually add faves
//   - he can post comments


	// following properties match db column names
	private $uid;	// automatic
	private $role = 'member';
	public  $username;
	private $password;
	private $realname;
	public  $email;
	private $activation = 0;
	private $last_login;
	private $register_date;

	// DB conn handles will reference global dependencies
	protected $dbh;	

	function __construct($dbh) {
		$this->dbh = $dbh;
	}

	function __destruct() {
		// Cleanup:
		$this->dbh = null;
	}

	function __sleep() {	// Serialization is automatic
		// Props to serialize:
		return array('uid', 'role', 'username', 'email');
	}
	
	function __wakeup() {	// Unserialization is automatic
		// Re-link dependencies:
		global $dbh;
		$this->dbh = $dbh;
	}


	/**
	* Static session check.
	*/
	public static function isLogged() {
		if (isset($_SESSION['valid']) && $_SESSION['valid']) {	// valid = 1 or 0 or unset
			return true;
		}
		return false;
	}


	/**
	* Static session rights-check.
	*/
	public static function isModerator() {
		if (isset($_SESSION['role']) && ($_SESSION['role'] == 'moderator' || $_SESSION['role'] == 'admin')) {
			return true;
		}
		return false;
	}


	/**
	* Static session rights-check.
	*/
	public static function isAdmin() {
		if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
			return true;
		}
		return false;
	}


	/**
	* Find a user if their email address exists in the db.
	*/
	function lookupByEmail($email) {
		$sth = $this->dbh->prepare("SELECT * FROM users WHERE email = ?");
		$sth->bindParam(1, $email);
		$sth->execute();
		if ($sth->rowCount() == 1) {
			$row = $sth->fetch();
			$this->uid = $row['uid'];
			$this->username = $row['username'];
			$this->role = $row['role'];
			$this->email = $row['email'];
			$this->activation = $row['activation'];
			$this->last_login = $row['last_login'];
			$this->register_date = $row['register_date'];
			return true;
		}
		else {
			return false;
		}
	}


	/**
	* Do this directly from the register form.
	*/
	function register($username, $password, $email, $realname) {
		$this->username = $username;
		$this->password = $password;
		$this->email	= $email;
		$this->realname	= $realname;

		$this->prepActivation();
		// Password encryption:
		$salt = $this->createSalt();
		$dblhash = $this->doubleHash($this->password, $salt);
		$pass_salt = $dblhash .'|'. $salt;
	
		$sth = $this->dbh->prepare("INSERT INTO users (role, username, pass_salt, realname, email, activation, last_login, register_date)
									VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
		$sth->bindParam(1, $this->role);
		$sth->bindParam(2, $this->username);
		$sth->bindParam(3, $pass_salt);
		$sth->bindParam(4, $this->realname);
		$sth->bindParam(5, $this->email);
		$sth->bindParam(6, $this->activation);
		try {
			$sth->execute();
			if ($sth->rowCount() == 1) {
				return true;
			}
		}
		catch (PDOException $e) {
			FB::log($e);
			Messages::create('error', 'Error registering, please try again.', false);
			return false;
		}
	}
	
	
	/**
	* Password salting.
	*/
	function createSalt() {
		// Combine static and new random salts:
		$string = md5(STATIC_SALT . uniqid(rand(), true));
		$salt = substr($string, 0, 8);
		$this->salt = $salt;
		return $salt;
	}

	
	/**
	* Password hashing.
	*/
	function doubleHash($password, $salt) {
		$hash = hash('sha256', $password);
		$doublehash = hash('sha256', $salt . $hash);	
		return $doublehash;
	}


	/**
	* Generate activation code.
	*/
	function prepActivation() {
		// Generate verification code:
		$this->activation  = substr(md5($this->username.microtime()), 0, 16);
		// Store in db:
		$sth = $this->dbh->prepare("UPDATE users SET activation = ? WHERE uid = ? LIMIT 1");
		$sth->bindParam(1, $this->activation);
		$sth->bindParam(2, $this->uid);
		$sth->execute();
	}


	/**
	* Send the activation email .
	*/
	function sendActivation() {
		$eml = new Email($this);	// pass User object as recipient
		$eml->setCode($this->activation);
		$eml->setBody('activation');
		if ($eml->sendThis()) {
			return true;
		}
		else {
			return false;
		}
	}


	/**
	* Do this when the member clicks the activation link in the email. // WHAT ABOUT TIME LIMIT? 72 HRS?
	*/
	function activate($eml_code) {
		// Get the db activation code
		$sth = $this->dbh->prepare("SELECT activation FROM users WHERE email=? LIMIT 1");
		$sth->bindParam(1, $this->email);
		$sth->execute();
		$row = $sth->fetch();
		FB::log($row['activation'], $eml_code);

		if ($eml_code == $row['activation']) {
			$this->activation = 1;
			// store it
			$sth = $this->dbh->prepare("UPDATE users SET activation = 'ok' WHERE email=? LIMIT 1");
			$sth->bindParam(1, $this->email);
			$sth->execute();
			return true;
		}
		else {
			return false;
		}
	}


	/**
	* Try to log in from supplied credentials.
	*/
	function login($username, $password) {
		// Lookup record by username:	
		$sth = $this->dbh->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
		$sth->bindParam(1, $username);
		try {
			$sth->execute();
			if ($sth->rowCount() == 1) {
				// Retrieve pass+salt:
				$res = $sth->fetch();
				$creds = explode('|', $res['pass_salt']);
				// Test password match:
				if ($this->doubleHash($password, $creds[1]) == $creds[0]) {
					// Login allowed:
					$this->uid = $res['uid'];
					$this->role = $res['role'];
					$this->username = $res['username'];
					$this->email = $res['email'];
					$this->activation = $res['activation'];
					$this->last_login = date('Y-m-d H:i:s');
					$this->register_date = $res['register_date'];

					// Update last_login:
					$sth = $this->dbh->prepare("UPDATE users SET last_login=? WHERE username=? LIMIT 1");
					$sth->bindParam(1, $this->last_login);
					$sth->bindParam(2, $this->uid);
					$sth->execute();

					// Store in session:
					session_regenerate_id(); // this is a security measure
					$_SESSION['valid'] = 1;
					$_SESSION['uid'] = $this->uid;	
					$_SESSION['role'] = $this->role;	
					$_SESSION['username'] = $this->username;
					return true;
				}
				else {
					// incorrect password
					Messages::create('error', 'Invalid login.');
					return false;
				}
			}
			else {
				// invalid username
				Messages::create('error', 'Invalid login.');
				return false;
			}
		}
		catch (PDOException $e) {
			FB::log($e);
			return false;
		}
	}
	
	
	/**
	* Log user out.
	*/
	function logout() {
		// Unset cookies
		setcookie('session_expiry', null, time()-3600, '/');
		setcookie('session_timeout', null, time()-3600, '/');
		// unset csrf cookie
 		session_unset();		// Unset all session vars
    	session_destroy();		// Trash disk storage
		session_start();
	}
	

	/**
	* Log user out.
	*/
	function changeEmail($old_eml, $new_eml) {
		$sth = $this->dbh->prepare("UPDATE users SET email = ? WHERE email = ? AND uid = ? LIMIT 1");
		$sth->bindParam(1, $new_eml);
		$sth->bindParam(2, $old_eml);
		$sth->bindParam(3, $_SESSION['uid']);
		$sth->execute();
		if ($sth->rowCount() == 1) {
			Messages::create('success', 'Your email has been changed.');
			return true;
		}
		else {
			Messages::create('error', 'Could not update your email. Please try again later.');
			return false;
		}
	}


	/**
	* Prep nonce for user to receive via email.
	*/
	function prepChangePasswordCode() {
		// Generate verification code:
		$this->activation  = substr(md5($this->username.microtime()), 0, 16);
		// Store in db:
		$sth = $this->dbh->prepare("UPDATE users SET activation = ? WHERE uid = ? LIMIT 1");
		$sth->bindParam(1, $this->activation);
		$sth->bindParam(2, $this->uid);
		$sth->execute();
	}


	/**
	* Send out nonce for user to unlock access to form.
	*/
	function sendChangePasswordCode() {
		$eml = new Email($this);	// pass User object as recipient
		$eml->setCode($this->activation);
		$eml->setBody('change_password');
		if ($eml->sendThis()) {
			return true;
		}
		else {
			return false;
		}
	}
	

	/**
	* Check that the nonce used to access the page is valid. 
	*/
	function checkPasswordCode($eml_code) {
		$sth = $this->dbh->prepare("SELECT uid FROM users WHERE uid = ? AND activation = ? LIMIT 1");
		$sth->bindParam(1, $this->uid);
		$sth->bindParam(2, $eml_code);
		$sth->execute();
		if ($sth->rowCount() == 1) {
			return true;
		}
		else {
			return false;
		}
	}
	
	
	/**
	* Change the user's password.
	*/
	function changePassword($new_pass) {
		// Prep new password for insertion:
		$salt = $this->createSalt();
		$dblhash = $this->doubleHash($new_pass, $salt);
		$pass_salt = $dblhash .'|'. $salt;
		
		// Insert:
		$sth = $this->dbh->prepare("UPDATE users SET pass_salt = ? WHERE uid = ? LIMIT 1");
		$sth->bindParam(1, $pass_salt);
		$sth->bindParam(2, $_SESSION['uid']);
		$sth->execute();
		if ($sth->rowCount() == 1) {
			Messages::create('success', 'Your password has been changed.');
			return true;
		}
		else {
			Messages::create('error', 'Could not update your password. Please try again later.');
			return false;
		}	
	}


	function suspendAccount() {
		// manual for now
	}
	
	
	function deleteAccount() {
		// manual for now
	}
	
} // end class User