<?php

/*
 *	Parent to Film & Person classes. Won't be instantiated.
 */
abstract class Entity {

	// Contains the tags which identify this as a Film or Person:
	protected $guts = array(
		'type',
		'table',
		'col_id',
		'cast_or_filmog',
		'image'
	);

	// Common properties:
	public $id;					// pid or fid
	public $version;
	public $last_modified_at;
	public $permalink;
	public $import_date;
	public $isValid = false;	// until true
	public $awards;

	// DB conn handles will reference global dependencies
	protected $dbh;
	protected $tmdb;


	function __construct($id, $dbh, $repository) {
		$this->id = $id;
		// Dependency Injection:
		$this->dbh = $dbh;
		$this->repository = $repository;
	}

	function __destruct() {
		// Cleanup:
		$this->dbh = null;
	}


	/**
	* Select a film/person from local db by its id, return false if not found.
	*/
	function fetchLocal() {
		$sth = $this->dbh->prepare("SELECT * FROM {$this->guts['table']} WHERE {$this->guts['col_id']} = ? LIMIT 1");
		$sth->bindParam(1, $this->id, PDO::PARAM_INT);
		try {
			$sth->execute();
		}
		catch (PDOException $e) {
			FB::log($e);
			return false;
		}

		if ($sth->rowCount() == 1) {
			$f = $sth->fetch();	// one row

			// Extract fetched row to $this->properties:
			foreach ($f as $key => $val) {
				$this->$key = $val;
			}
			$this->isValid = true;
//			$this->logVars("{$this->guts['type']}->fetchLocal()");

			return true;	// Record found and fetched
		}
		else {
			return false; // Unique record not found
		}
	}


	/**
	* Store all roles for current cast in roles table.
	*/
	function storeRoles($arr) {	// cast or filmography
		foreach ($arr as $corf) {
			$r = new Role($this->dbh, $corf['id'], $this->id, $corf['job']);
			$r->character = $corf['character'];
			if (!$r->lookup()) {
				$r->store();
			}
		}
	}


	/**
	* Update this record by deleting and re-fetching.
	*/
	function update() {
		// Re-fetch:
		if ($this->tmdbLookup()) {
			// Drop:
			$this->delete();
			// Save it to db:
			$this->store();
			$this->storeRoles($this->{$this->guts['cast_or_filmog']});
		}
	}


	/**
	* Delete this record from local table.
	*/
	function delete() {
		$sth = $this->dbh->prepare("DELETE FROM {$this->guts['table']} WHERE {$this->guts['col_id']} = ? LIMIT 1");
		$sth->bindParam(1, $this->id, PDO::PARAM_INT);
		$sth->execute();
	}


	/**
	* Add 1 to pageviews.
	*/
	function hitMe() {
		$sth = $this->dbh->prepare("UPDATE pageviews SET hits = hits + 1 WHERE ent_type = ? AND ent_id = ? LIMIT 1");
		$sth->bindParam(1, $this->guts['type']);
		$sth->bindParam(2, $this->id);
		$sth->execute();
	}


	/**
	* Create the SEO-friendly title to be appended to URL.
	*/
	static function makePermalink($title) {
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);
		return $clean;
	}


	/**
	* Retrieve awards (if any) from the db.
	*/
	function getAwards() {
		$sth = $this->dbh->prepare("SELECT ayear, ceremony, award_for, award_name, won FROM awards
									WHERE {$this->guts['col_id']} = ?");
		$sth->bindParam(1, $this->id, PDO::PARAM_INT);
		$sth->execute();
		if ($sth->rowCount() > 0) {
			$this->awards = $sth->fetchAll();
			FB::log($this->awards, 'awards');
			return true;
		}
		else {
			return false;
		}
	}


	/**
	* Display awards section.
	*/
	function displayAwards() {
		if (!empty($this->awards)) {
			$html = "<ul class='awards_box'>";
			foreach ($this->awards as $a) {
				extract($a);
				$html .= "<li>$ayear: $won $award_for at $ceremony</li>";
			}
			$html .= '</ul>';
			echo $html;
		}
	}


	/**
	* Log object properties to FirePHP.
	*/
	function logVars($label = null) {
		$filmkeys = array('fid', 'title', 'permalink', 'release_date', 'poster_url', 'cast_serial');
		$personkeys = array('pid', 'name', 'permalink', 'birthday', 'bio', 'picture_url','filmography_serial');

		// Differentiate between types:
		$keys = is_a($this, 'Film') ? $filmkeys : $personkeys;

		foreach ($keys as $key) {
			$arr[$key] = $this->$key;
		}
		FB::log($arr, '(Entity) '.$label);
	}

} // end class Entity
