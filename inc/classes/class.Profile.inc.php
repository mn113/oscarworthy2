<?php

class Profile {

	protected $uid;
	public $username;
	private $email;

	public $rated_films = array();
	public $rated_people = array();
	
	public $sugg_films = array();
	
	protected $dbh;
	protected $tmdb;

	function __construct($uid, $dbh, $tmdb) {
		$this->uid = $uid;

		$this->dbh = $dbh;
		$this->tmdb = $tmdb;
	}


	function __destruct() {
		$this->dbh = null;
	}


	function displayVotes() {
		$sth = $this->dbh->prepare("SELECT v.uid, v.rating, v.rating_date, f.title, f.film_id, p.fullname, p.person_id
									FROM votes AS v
									JOIN roles  AS r ON v.role_hash = r.unique_hash 
									JOIN people AS p ON p.person_id = r.person_id
									JOIN films  AS f ON f.film_id = r.film_id
									WHERE v.uid = ?
									ORDER BY v.rating DESC");
		$sth->bindParam(1, $this->uid);
		try {
			$sth->execute();
			if ($sth->rowCount() > 0) {
				$rows = $sth->fetchAll();
				foreach ($rows as $row) {
					extract($row, EXTR_PREFIX_ALL, 'v');
					
					// Output:
					$v_time = date('d M Y @ H:i' , strtotime($v_rating_date));
					$html = "<li><a href='/person/$v_person_id/'>$v_fullname</a> 
							in <a href='/film/$v_film_id/'>$v_title</a> 
							rated <strong style='color:#f30'>$v_rating</strong> on $v_time</li>";
					echo $html;
					
					// Storage:
					$this->rated_films[$v_film_id] += $v_rating;		// NOTICES, UNDEFINED INDEX
					$this->rated_people[$v_person_id] += $v_rating;
				}
			}
			else {
				echo 'No votes to display';
			}
		}
		catch (PDOException $e) {
			FB::log($e);
		}
	}


	function suggestFilms() {
		// most recent film from director of highly-rated films
		// RT-freshest film from director of highly-rated films
		// most recent film starring highly-rated actors
		// RT-freshest film starring highly-rated actors
		FB::log($this->rated_films, 'trf');	

		// Sort to prioritise highest-rated:
		uasort($this->rated_films, function($a, $b) {
			if ($a == $b) {
				return 0;
			}
			return ($a > $b) ? -1 : 1;
		});

		// Get related people & films:
		foreach ($this->rated_films as $fid => $score) {
			FB::log($this->rated_films, 'trf');	
			FB::log($fid, 'fid');
			$film = new Film($fid, $this->dbh, $this->tmdb);
			if (!$film->fetchLocal()) $film->tmdbLookup();
			FB::log($film->title, 'ft');

			// Director's films:
			$dirid = $film->getDirector();
			FB::log($dirid, 'did');
			$dir = new Person($dirid, $this->dbh, $this->tmdb);
			if (!$dir->fetchLocal()) $dir->tmdbLookup();
			$dir->fetchFilmography();
			// Most recent:
			array_reverse($dir->filmography);
			$this->sugg_films[] = $dir->filmography[0];
			FB::log($dir->filmography, 'df');
			FB::log($this->sugg_films, 'sf');
			// RT-freshest:


			// Stars' films:
//			$starids = array();
//			$film->fetchCast();
		}
		
		// Exclude already seen:
		
		
		// Output:
		$ul = '<ul>';
		foreach ($this->sugg_films as $sf) {
			FB::log($sf, 'sfo');
			$ul .= "<li><a href='/film/{$sf['id']}'>{$sf['name']}</a></li>";
		}
		$ul .= "</ul>";
		return $ul;
	}

} // end class Profile