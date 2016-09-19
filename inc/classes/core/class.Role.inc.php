<?php

class Role {

	public $pid;
	public $fid;
	public $character;
	public $job;
	public $hash;
	private $score;
	private $votes;
	private $mem_score;
	private $mem_votes;
	private $guest_score;
	private $guest_votes;

	protected $dbh;


	function __construct($dbh, $pid = null, $fid = null, $job = null) {
		$this->pid = $pid;
		$this->fid = $fid;
		$this->job = $job;
		$this->hash = hashRole($this->fid, $this->job, $this->pid);

		// Dependency Injection:
		$this->dbh = $dbh;
	}
	
	function __destruct() {
		// Cleanup:
		$this->dbh = null;
	}


	/**
	* Lookup this role in the roles table by its unique hash.
	*/
	function lookup() {
		$sth = $this->dbh->prepare("SELECT * FROM roles WHERE unique_hash = ? LIMIT 1");
		$sth->bindParam(1, $this->hash);

		try {
			$sth->execute();
		}
		catch (PDOException $e) {
			FB::log($e);
			FB::log("DB error in Role->lookup().");
			return false;
		}
		if ($sth->rowCount() != 1) {
			return false;	
		}
		else {
			$r = $sth->fetch();
			// Update object properties:
			$this->pid = $r['person_id'];
			$this->fid = $r['film_id'];
			$this->character = $r['character_name'];
			$this->job = $r['job'];
			$this->mem_score = $r['mem_score'];
			$this->mem_votes = $r['mem_votes'];
			$this->guest_score = $r['guest_score'];
			$this->guest_votes = $r['guest_votes'];

			return true;
		}
	}	
	

	/**
	* Save the role to the db.
	*/
	function store() {
		// Make a unique hash:
		if (!isset($this->hash)) {
			$this->hash = hashRole($this->fid, $this->job, $this->pid);
		}
		
		$sth = $this->dbh->prepare("INSERT INTO roles (film_id, person_id, character_name, job, unique_hash)
									VALUES (?, ?, ?, ?, ?)");
		$sth->bindParam(1, $this->fid);
		$sth->bindParam(2, $this->pid);
		$sth->bindParam(3, $this->character);
		$sth->bindParam(4, $this->job);
		$sth->bindParam(5, $this->hash);

		try {
			$sth->execute();
		}
		catch (PDOException $e) {
			FB::log($e);
			FB::log("Role ({$this->fid}), ({$this->pid}), ({$this->job}), ({$this->hash}) not stored, continuing.");
		}
	}


	/**
	* Update the scores and votes for an existing role's record.
	*/
	function update() {
		if (!isset($this->hash)) {
			// Make a unique hash:
			$this->hash = hashRole($this->fid, $this->job, $this->pid);		
		}

		$my_score = User::isLogged() ? 'mem_score' : 'guest_score';
		$my_votes = User::isLogged() ? 'mem_votes' : 'guest_votes';

		// Build update query
		$sth = $this->dbh->prepare("UPDATE roles SET $my_score=?, $my_votes=? WHERE unique_hash=?");
		$sth->bindParam(1, $this->$my_score);
		$sth->bindParam(2, $this->$my_votes);
		$sth->bindParam(3, $this->hash);

		try {
			$sth->execute();		
		}
		catch (PDOException $e) {
			FB::log($e);
			FB::log("Role not updated, continuing.");
		}
	}

	
	/**
	* Return the overall user rating for this role.
	*/
	function getRating() {
		$my_score = User::isLogged() ? 'mem_score' : 'guest_score';
		$my_votes = User::isLogged() ? 'mem_votes' : 'guest_votes';
		// do math
		$rating = $this->$my_score / ($this->$my_votes * 2);	// @RATING HALVED
		return $rating;
	}

	
	/**
	* Update the role's rating with a new vote, or change last vote.
	*/
	function setRating($newrating, $oldrating = 0) {
		$my_score = User::isLogged() ? 'mem_score' : 'guest_score';
		$my_votes = User::isLogged() ? 'mem_votes' : 'guest_votes';

		$this->$my_votes += 1;
		$this->$my_score += ($newrating - $oldrating) * 2;	// @RATING DOUBLED
		$this->update();
	}
		

	/**
	* Set the hash with given string.
	*/
	function setHash($hash) {
		// Test integrity:
		if (ctype_alnum($hash) && strlen($hash) == 8) {
			$this->hash = $hash;
		}
	}
	
	
	/**
	* Log object properties to FirePHP.
	*/
	function logVars($label) {
		$vars = array(
			'pid'			=>	$this->pid,
			'fid'			=>	$this->fid,
			'job'			=>	$this->job,
			'character'		=>	$this->character,
			'votes'			=>	$this->votes,
			'mem_score'		=>	$this->mem_score,
			'guest_score'	=>	$this->guest_score
		);
		FB::log($vars, $label);
	}
}