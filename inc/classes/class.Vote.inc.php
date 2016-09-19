<?php

class Vote {

	public $vote_id;	// automatic
	public $uid;
	public $role_hash;
	public $rating;
	
	// DB conn handles will reference global dependencies
	protected $tmdb;
	protected $dbh;


	function __construct($uid, $role_hash, $rating, $dbh, $tmdb) {
		$this->uid = $uid;
		$this->role_hash = $role_hash;
		$this->rating = $rating * 2;	// @RATING DOUBLED

		// Dependency Injection:
		$this->dbh = $dbh;
		$this->tmdb = $tmdb;
	}
	
	function __destruct() {
		// Cleanup:
		$this->dbh = null;
	}


	/**
	* Save the vote to the db.
	*/
	function store() {
		$sth = $this->dbh->prepare("INSERT INTO votes (uid, role_hash, rating, rating_date)
									VALUES (?, ?, ?, NOW())");
		$sth->bindParam(1, $this->uid);
		$sth->bindParam(2, $this->role_hash);
		$sth->bindParam(3, $this->rating);

		try {
			$sth->execute();
		}
		catch (PDOException $e) {
			FB::log($e);
			FB::log("Vote not stored, continuing.");
		}
	}


	/**
	* Update an existing vote.
	*/
	function update() {
	
	}



}