<?php

class Filmography {

	public $pid;
	public $data;
	protected $dbh;
	protected $tmdb;


	function __construct($dbh, $tmdb, $pid, array $filmog, string $serialized) {
		$this->dbh = $dbh;
		$this->tmdb = $tmdb;
		$this->pid = $pid;

		if ($filmog) {
			$this->data = $filmog;	
		}
		elseif ($serialized) {
			$this->data = unserialize($serialized);
		}
	}


	/**
	* Flesh out the filmography.
	*/
	function fetchLocal() {
		for ($i = 0, $s = sizeof($this->data); $i < $s; $i++) {
			$this->data[$i]['permalink'] = $this->makePermalink($this->data[$i]['name']);		
			$this->data[$i]['poster_url'] = $this->data[$i]['poster'];
		}
		$this->sortIt();
	}


	/**
	* Look up the filmography using roles.
	*/
	function fetchDeep() {
		$time_start = microtime(true);

		// Now fetch local cast members using roles:
		$sth = $this->dbh->prepare("SELECT * FROM roles WHERE person_id = ?");
		$sth->bindParam(1, $this->pid, PDO::PARAM_INT);
		$sth->execute();
		$roles = $sth->fetchAll();
		$i = 0;
		// Check whether each film is in local db:
		foreach ($roles as $r) {
			extract($r, EXTR_PREFIX_ALL, 'r');
			$film = new Film($r_film_id, $this->dbh, $this->tmdb);
			if (!$film->fetchLocal()) {
				// Film not in local, fetch from TMDb:
				FB::log("no local data for film $r_film_id");
				$film->tmdbLookup();	// 20-75 TIMES, SLOW!
				$film->store();
				$film->storeRoles($film->cast);	// SLOW
			}
			// Set the current filmography with retrieved properties:
			$this->data[$i]['name'] = $film->title;
			$this->data[$i]['permalink'] = $this->makePermalink($this->data[$i]['name']);
			if ($film->poster_url == '') $film->poster_url = BLANK_POSTER;
			$this->data[$i]['poster'] = $film->poster_url;
			$this->data[$i]['id'] = $film->fid;
			$this->data[$i]['release'] = $film->release_date;
			$this->data[$i]['character'] = $r_character_name;
			$this->data[$i]['job'] = $r_job;
			$i++;
		}
		
		$this->sortIt();
		
		$time_end = microtime(true);
		FB::log('Filmography->fetchDeep()', $time_end - $time_start);
		return true;
	}


	/**
	* Sort the filmography chronologically (desc).
	*/
	function sortIt() {
		// Use usort with anonymous comparison function:
		return usort($this->data, function($a, $b) {
		    return strcmp($b['release'], $a['release']);
		});	
	}


	/**
	* Print HTML.
	*/
	function display() {
	
	
	}

}