<?php

class Person extends Entity {

	public $pid;
	// following properties match db column names
	public $name;
	public $picture_url;
	public $photos;		// serialized
	public $bio;
	public $birthday;
	public $birthplace;
	public $filmography_serial;	// serialized

	public $filmography = array();
	public $rating;


	function __construct($pid, $dbh = null, $tmdb = null) {
		// Repair missed params:
		if (!$dbh) global $dbh;
		if (!$tmdb) global $tmdb;
	
		// Dependency Injection:
		parent::__construct($pid, $dbh, $tmdb);
		$this->pid = $pid;

		// Define this Entity as a Person:
		$this->guts = array(					// STINKY
			'type'				=> 'person',
			'table'				=> 'people',
			'col_id'			=> 'person_id',
			'cast_or_filmog'	=> 'filmography',
			'image'				=> 'picture'
		);
	}


	/**
	* Query TMDb for a person's info, set properties, return 1 for success.
	*/
	function tmdbLookup() {
		$time_start = microtime(true);

		// Get person data from TMDb:
		if (!($json = $this->tmdb->getPerson($this->pid))) return false; 

		$person_object = json_decode($json);
		$p = objectToArray($person_object[0]);
		FB::log($p, 'tmdbperson');
		
		// Set object properties with retrieved values:
		$this->name = $p['name'];
		$this->picture_url = @$p['profile'][0]['image']['url'];	// USING @ TO SUPPRESS CONSTANT NOTICES "Undefined offset: 0"
		$this->bio = $p['biography'];
		$this->birthday = $p['birthday'];
		$this->birthplace = $p['birthplace'];
		$this->version = $p['version'];
		$this->last_modified_at = $p['last_modified_at'];
		$this->filmography = $p['filmography'];
//		$this->filmography = $this->sortFilmography2();
		
		$this->filmography_serial = serialize($this->filmography);
		$this->photos = serialize($p['profile']);
		
		$this->permalink = $this->makePermalink($this->name);
		$this->isValid = true;
		
		$this->logVars('Person->tmdbLookup()');

		$time_end = microtime(true);
		FB::log('Person->tmdbLookup()', $time_end - $time_start);
		return true;
	}
	

	/**
	* Insert current properties into people table, return 1 for success.
	*/
	function store() {
		// Insert person record into local db
		$sth = $this->dbh->prepare("INSERT INTO people (person_id, fullname, picture_url, bio, birthday, birthplace,
											version, last_modified_at, permalink, filmography_serial, photos, import_date)
									VALUES (:pid, :name, :picture_url, :bio, :birthday, :birthplace,
											:version, :last_modified_at, :permalink, :filmography_serial, :photos, NOW())");
		// Do binding:		
		$propertiesToBind = array('pid', 'name', 'picture_url', 'bio', 'birthday', 'birthplace', 'version', 'last_modified_at',
									'permalink', 'filmography_serial', 'photos');
		foreach ($propertiesToBind as $p) {
			$sth->bindParam(':'.$p, $this->$p);
		}

		// Execute query:
		try {
			$sth->execute();
		}
		catch (PDOException $e) {
			FB::log($e);
			FB::log("Person {$this->name} not stored, continuing.");
		}

		// Check result:
		if ($sth->rowCount() != 1) {
			return false;
		}
		else {
			FB::log("Person {$this->name} added to db.");
			return true;
		}
	}


	/**
	* Select a person from local db by its id, return false if not found.
	*/
	function fetchLocal() {
		// Execute SELECT * query:
		if (parent::fetchLocal()) {
			// Check local record modification date + 1 month & x days -vs- today:
			$x = rand(0,7);
			if (!empty($this->import_date) && (strtotime("now -1 month $x days") > strtotime($this->import_date))) {
				FB::log(strtotime("now -1 month $i days"));
				FB::log(strtotime($this->import_date));
				FB::log($this->fullname.' is outdated!');
				$this->update();
				return false;
			}
			// Custom property assigmnent:
			$this->name = $this->fullname;
			$this->filmography = unserialize($this->filmography_serial);
			return true;		
		}
		else {
			return false;
		}
	}


	/**
	* Get the filmography.
	*/
	function fetchFilmography() {
		$this->filmography = unserialize($this->filmography_serial);
		FB::log($this->filmography, 'fetchFilmography()');
		$s = sizeof($this->filmography);

		for ($i = 0; $i < $s; $i++) {
			$this->filmography[$i]['permalink'] = $this->makePermalink($this->filmography[$i]['name']);		
			$this->filmography[$i]['poster_url'] = $this->filmography[$i]['poster'];
		}

		$this->sortFilmography2();
	}


	/**
	* Get the filmography.
	*/
	function fetchFilmographyFull() {				// VERY SLOW -> USE ONLY W/ AJAX
		$time_start = microtime(true);

		// Now fetch local cast members using roles:
		$sth = $this->dbh->prepare("SELECT * FROM roles WHERE person_id = ?");
		$sth->bindParam(1, $this->pid, PDO::PARAM_INT);
		$sth->execute();
		$roles = $sth->fetchAll();
		$i = 0;
		// Check whether each film is in local db:
		foreach ($roles as $r) {
			$film = new Film($r['film_id'], $this->dbh, $this->tmdb);
			if (!$film->fetchLocal()) {
				// Film not in local, fetch from TMDb:
				FB::log("no local data for film {$r['film_id']}");
				$film->tmdbLookup();	// 20-75 TIMES, SLOW!
				$film->store();
				$film->storeRoles($film->cast);	// SLOW
			}
			// Set the current filmography with retrieved properties:
			$this->filmography[$i]['name'] = $film->title;
			$this->filmography[$i]['permalink'] = $this->makePermalink($this->filmography[$i]['name']);
			if ($film->poster_url == '') $film->poster_url = BLANK_POSTER;
			$this->filmography[$i]['poster'] = $film->poster_url;
			$this->filmography[$i]['id'] = $film->fid;
			$this->filmography[$i]['release'] = $film->release_date;
			$this->filmography[$i]['character'] = $r['character_name'];
			$this->filmography[$i]['job'] = $r['job'];
			$i++;
		}
		
		$this->sortFilmography2();
		
		$time_end = microtime(true);
		FB::log('Person->fetchFilmographyFull()', $time_end - $time_start);
		return true;
	}


	/**
	* Sort the filmography chronologically (desc).
	*/
	function sortFilmography1() {
		// Use usort with anonymous comparison function:
		return usort($this->filmography, function($a, $b) {
			if ($a['release'] == $b['release']) return 0;
			return (strtotime($a['release']) > strtotime($b['release']) ? -1 : 1);
		});
	}


	/**
	* Sort the filmography chronologically (desc).
	*/
	function sortFilmography2() {
		// Use usort with anonymous comparison function:
		return usort($this->filmography, function($a, $b) {
		    return strcmp($b['release'], $a['release']);
		});	
	}


	/**
	* Print HTML.
	*/
	function getName() {
		return $this->name;
	}

	// Wrapper function returns full tag:
	function getPicture($size = 'profile') {
		$html  = "<img class='poster' src='";
		$html .= $this->getPictureUrl($size);
		$html .= "' />";
		return $html;
	}

	// Returns plain URL string:	
	function getPictureUrl($size = 'profile') {
		// Prepare list of acceptable sizes:
		$sizes = array('profile', 'original', 'thumb', 'h632');
		// Prepend requested size:
		array_unshift($sizes, $size);

		// Prepare photos for searching:		
		$photos = unserialize($this->photos);
		// Find a photo of the requested size:
		foreach ($sizes as $size) {
			foreach ($photos as $p) {
				if ($p['image']['size'] == $size) {
					// Found one:
					$p_url = $p['image']['url']; 
					break;
				}
			}
			if (isset($p_url)) break;	// Look no further
		}

		// Fallback 1:
		if (!(isset($p_url)) || $p_url == '') {
			if (isset($this->picture_url)) {
				$p_url = $this->picture_url;
			}
			else {
			// Fallback 2:
				$p_url = BLANK_HEADSHOT;
			}
		}

		return $p_url;
	}
	
	function getMinutiae() {
		if ($this->birthday == '0000-00-00') $this->birthday = 'unknown';
		if (strlen($this->birthplace) < 1) $this->birthplace = 'unknown';
		$html = "<dl>".
				"<dt>Birthday:</dt><dd>{$this->birthday}</dd>".
				"<dt>Birthplace:</dt><dd>{$this->birthplace}</dd>".
				"</dl>";
		return $html;		
	}

	function getBio() {
		return "<p>".Bio::stripWikiText($this->bio)."</p>";
	}
	

	/**
	* Print HTML tables.
	*/
	function displayFilmography() {
		// Set up container structure:
		$f = array('Acting', 'Directing', 'Writing', 'Producing', 'Editing', 'Camera', 'Music', 'Sound', 'Casting', 'Stunts');
		$f = array_flip($f);
		foreach ($f as $i => $cat) {
			$f[$i] = array('html' => '<table>', 'count' => 0);
		}
		
		foreach($this->filmography as $filmog) {
			extract($filmog, EXTR_PREFIX_ALL, 'f');
			$f_year = year($f_release);
			$hash = hashRole($f_id, $f_job, $this->pid);
			
			if ($f_job == 'Actor') {
				// Prepare the table row for cast (with stars):
				$actr  = '<tr>';
				$actr .= "<td><img class='thumb' src='$f_poster' /></td>";	// WOULD BE NICE TO USE Film::getPosterUrl('thumb')
				$actr .= "<td>$f_year</td>";
				$actr .= "<td><a href='/film/$f_id/$f_permalink'>$f_name</a></td>";
				$actr .= "<td>$f_character</td>";
				// Awards?
				if (0) {
					$actr .= "<td></td>";
				}				
				$actr .= "<td>".StarHelper::displayStars($hash)."</td>";
				$actr .= "</tr>";
				$acting .= $actr;
				$counts['act']++;
				$f['Acting']['html'] .= $actr;
				$f['Acting']['count']++;
			}
			else {
				// Prepare the table row for crew:
				$tr  = '<tr>';
				$tr .= "<td><img class='thumb' src='$f_poster' /></td>";	// WOULD BE NICE TO USE Film::getPosterUrl('thumb')
				$tr .= "<td>$f_job</td>";
				$tr .= "<td>$f_year</td>";
				$tr .= "<td><a href='/film/$f_id/$f_permalink'>$f_name</a></td>";
				$tr .= "</tr>";		
				
				// Put the crew table row in the appropriate table:
				switch($f_job) {
					// Directed by:
					case 'Director':
						$f['Directing']['html'] .= $tr;
						$f['Directing']['count']++;
						break;
					// Written by:
					case 'Writer':
					case 'Author':
					case 'Screenplay':
						$f['Writing']['html'] .= $tr;
						$f['Writing']['count']++;
						break;
					// Produced by:
					case 'Producer':
					case 'Executive Producer':
					case 'Associate Producer':
						$f['Producing']['html'] .= $tr;
						$f['Producing']['count']++;
						break;
					// Editing:
					case 'Editor':
						$f['Editing']['html'] .= $tr;
						$f['Editing']['count']++;
						break;
					// Photography:
					case 'Director of Photography':
						$f['Camera']['html'] .= $tr;
						$f['Camera']['count']++;
						break;
					// Music:
					case 'Music':
					case 'Original Music Composer':
					case 'Original Soundtrack Composer':
						$f['Music']['html'] .= $tr;
						$f['Music']['count']++;
						break;
					// Sound:
					case 'Foley':
					case 'Sound Design':
						$f['Sound']['html'] .= $tr;
						$f['Sound']['count']++;
						break;
					// Casting:
					case 'Casting':
						$f['Casting']['html'] .= $tr;
						$f['Casting']['count']++;
						break;
					// Stunts:
					case 'Stunts':
						$f['Stunts']['html'] .= $tr;
						$f['Stunts']['count']++;
						break;
					default:
						// nada
				} // end switch
			} // end else	
		} // end foreach
		
		// Output tables:
		foreach ($f as $cat => $arr) {
			if ($f[$cat]['count'] > 0) {
				echo "<h2>".$cat."</h2>"."<span>".$f[$cat]['count']." films</span>".$f[$cat]['html']."</table>";
			}
		}
	} // end displayFilmography()

} // end class Person