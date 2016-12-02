<?php

class Film extends Entity {

	public $fid;
	// following properties match db column names
	public $title;
	public $release_date;
	public $runtime;
	public $countries;	// serialized
	public $poster_url;
	public $posters;	// serialized
	public $languages;	// serialized
	public $original_name;
	public $alternative_name;
	public $preferred_title;
	public $overview;
	public $imdb_id;
	public $tagline;
	public $certification;
	public $budget;
	public $revenue;
	public $trailer;
	public $genres;		// serialized
	public $studios;	// serialized
	public $cast_serial;	// serialized

	public $cast = array();

	protected $rott;	// API
	public $rtm;		// movie

	function __construct($fid, $dbh = null, $repository = null) {
		// Repair missed params:
		if (!$dbh) global $dbh;
		if (!$repository) {
			global $movierepository;
			$repository = $movierepository;
		}

		// Dependency Injection:
		parent::__construct($fid, $dbh, $repository);
		$this->fid = $fid;
		$this->rott = new RottenTomatoesApi('ecn57mpdtgkpqm8mrn3betcw', 10, '1.0', 'uk');


		// Define this Entity as a Film:
		$this->guts = array(
			'type'				=> 'film',
			'table'				=> 'films',
			'col_id'			=> 'film_id',
			'cast_or_filmog'	=> 'cast',
			'image'				=> 'poster'
		);
	}


	/**
	* Query TMDb for a film's info, set properties, return 1 for success.
	*/
	function tmdbLookup($imdb_id = '') {
		$time_start = microtime(true);

		// Normal lookup:
		if ($imdb_id == '') {
			// Get film data from TMDb:
			if (!($json = $this->repository->load($this->fid))) return false;
		}
		// IMDb lookup:
		else {
			if (!($json = $this->repository->load($imdb_id, TMDb::IMDB))) return false;	// TODO new api
		}

		$film_object = json_decode($json);
		$f = objectToArray($film_object[0]);
		FB::log($f, 'tmdbfilm');

		// Set object properties with retrieved values:
		$this->fid = $f['id'];
		$this->title = $f['name'];
		$this->release_date = $f['released'];
		$this->runtime = $f['runtime'];
		$this->countries = quickSerialize($f['countries']);		// ERROR IF NOT ARRAY
		$this->poster_url = @$f['posters'][0]['image']['url'];	// WARNINGS, FATAL ERRORS "Cannot use string offset as an array"
		$this->posters = serialize($f['posters']);
		$this->languages = quickSerialize($f['languages_spoken']);
		$this->original_name = $f['original_name'];
		$this->alternative_name = $f['alternative_name'];
		$this->overview = $f['overview'];
		$this->imdb_id = $f['imdb_id'];
		$this->tagline = $f['tagline'];
		$this->certification = $f['certification'];
		$this->budget = $f['budget'];
		$this->revenue = $f['revenue'];
		$this->trailer = $f['trailer'];
		$this->genres = quickSerialize($f['genres']);
		$this->studios = quickSerialize($f['studios']);
		$this->version = $f['version'];
		$this->last_modified_at = $f['last_modified_at'];
		$this->cast = $f['cast'];

		$this->cast_serial = serialize($f['cast']);

		$this->permalink = $this->makePermalink($this->title);
		$this->isValid = true;

		$this->logVars('Film->tmdbLookup()');

		$time_end = microtime(true);
		FB::log('Film->tmdbLookup()', $time_end - $time_start);
		return true;
	}


	/**
	* Insert current properties into films table, return 1 for success.
	*/
	function store() {
		// Insert film record into local db
		$sth = $this->dbh->prepare("INSERT INTO films (film_id, title, release_date, runtime, countries, poster_url,
											languages, original_name, alternative_name, overview, imdb_id, tagline,
											certification, budget, revenue, trailer, genres, studios, version,
											last_modified_at, permalink, cast_serial, posters)
									VALUES (:fid, :title, :release_date, :runtime, :countries, :poster_url,
											:languages, :original_name, :alternative_name, :overview, :imdb_id, :tagline,
											:certification, :budget, :revenue, :trailer, :genres, :studios, :version,
											:last_modified_at, :permalink, :cast_serial, :posters)");

		// Do binding:
		$propertiesToBind = array('fid', 'title', 'release_date', 'runtime', 'countries', 'poster_url',
								  'languages', 'original_name', 'alternative_name', 'overview', 'imdb_id', 'tagline',
								  'certification', 'budget', 'revenue', 'trailer', 'genres', 'studios', 'version',
								  'last_modified_at', 'permalink', 'cast_serial', 'posters');
		foreach ($propertiesToBind as $p) {
			$sth->bindParam(':'.$p, $this->$p);
		}

		// Execute query:
		try {
			$sth->execute();
		}
		catch (PDOException $e) {
			FB::log($e);
			FB::log("Film {$this->fid} not stored, continuing.");
		}

		// Check result:
		if ($sth->rowCount() != 1) {
			return false;
		}
		else {
			FB::log($this);
			FB::log("Film {$this->fid} added to db.");
			return true;
		}
	}


	/**
	* Select a film from local db by its id, return false if not found.
	*/
	function fetchLocal() {
		// Execute SELECT * query:
		if (parent::fetchLocal()) {
			// Custom property assigmnent:
			$this->cast = unserialize($this->cast_serial);
			return true;
		}
		else {
			return false;
		}
	}


	/**
	* Get the cast members.
	*/
	function fetchCast() {
		$this->cast = unserialize($this->cast_serial);
		FB::log($this->cast, 'fetchCast()');
		$s = sizeof($this->cast);

		for ($i = 0; $i < $s; $i++) {
			$this->cast[$i]['permalink'] = $this->makePermalink($this->cast[$i]['name']);
			$this->cast[$i]['picture_url'] = $this->cast[$i]['profile'];
		}
	}


	/**
	* Get the cast members' full details.
	*/
	function fetchCastFull() {				// VERY SLOW -> USE ONLY W/ AJAX
		$time_start = microtime(true);

		// Now fetch local cast members using roles:
		$sth = $this->dbh->prepare("SELECT * FROM roles WHERE film_id = ?");
		$sth->bindParam(1, $this->fid, PDO::PARAM_INT);
		try {
			$sth->execute();
			$roles = $sth->fetchAll();
//			FB::log($roles, 'fCF-roles');
		}
		catch (PDOException $e) {
			FB::log($e);
			return false;
		}
		$i = 0;
		// Check whether each role person is in local db:
		foreach ($roles as $r) {
			$person = new Person($r['person_id'], $this->dbh, $this->repository);
			if (!$person->fetchLocal()) {
				// Person not in local, fetch from TMDb:
				FB::log("no local data for person {$r['person_id']}");
				$person->tmdbLookup();	// 20-75 TIMES
				$person->store();
				$person->storeRoles($person->filmography);	// SLOW
			}
			// Set the current cast member with retrieved properties:
			$this->cast[$i]['id'] = $person->pid;
			$this->cast[$i]['name'] = $person->name;
			$this->cast[$i]['permalink'] = $this->makePermalink($this->cast[$i]['name']);
			$this->cast[$i]['picture_url'] = $person->picture_url;
			$this->cast[$i]['character'] = $r['character_name'];
			$this->cast[$i]['job'] = $r['job'];
			$i++;
		}

		$time_end = microtime(true);
		FB::log($this->cast, 'fetchedCast');
		FB::log('Film->fetchCastFull()', $time_end - $time_start);
		return true;
	}


	/**
	* Allows admin to set a preferred title for films with bad titles.
	*/
	function setPreferredTitle($title) {
		$sth = $this->dbh->prepare("INSERT INTO preferred_titles (film_id, pref_title) VALUES (?, ?)");
		$sth->bindParam(1, $this->id);
		$sth->bindParam(2, $title);
		$sth->execute();
	}


	/**
	* Hackish way to spit back the director's pid.
	*/
	function getDirector() {
		if (isset($this->cast)) {
			foreach($this->cast as $p) {
				extract($p, EXTR_PREFIX_ALL, 'p');
				if ($p_job == 'Director') {
					return $p_id;
				}
			}
		}
		return false;
	}


	/**
	* Lookup on Rotten Tomatoes by imdb_id (should work 98% of time).
	*/
	function rottenLookup() {
		$this->rtm = $this->rott->imdbMovie(substr($this->imdb_id, 2));	// cut 'tt' prefix
		FB::log($this->rtm, 'RTM');
	}


	/**
	* Return Rotten Tomatoes score.
	*/
	function getRottenScore() {
		// Make sure it has been looked up:
		if (!$this->rtm) $this->rottenLookup();

		if ($this->rtm) {
			return ROTT_ICON.$this->rtm['ratings']['critics_score']."%";
		}
		else {
			return false;
		}
	}


	/**
	* Return Rotten Tomatoes similar movies.
	*/
	function displayRottenSimilar() {
		// Make sure it has been looked up:
		if (!$this->rtm) $this->rottenLookup();

		if ($this->rtm) {
			// Try to get the similar movies:
			if ($similar = $this->rott->movieSimilar($this->rtm['id'])) {
				FB::log($similar, 'sim');
				// Build list:
				$html = "<ul id='similar_movies'>";
				foreach ($similar as $rtm) {
					FB::log($rtm, 'rtm');
					// imdb_id is a must-have:
					if ($rt_imdb = $rtm['alternate_ids']['imdb']) {
						$imdb_id = 'tt'.str_pad($rt_imdb, 7, 0);	// convert RT imdb_id to full imdb_id
						FB::log($imdb_id, 'imdbid');
						// Create a new film and lookup by its imdb_id:
						$film = new Film(0, $this->dbh, $this->repository);
						$film->tmdbLookup($imdb_id);
						// Make html:
						$html .= "<li><img src='".$film->getPosterUrl('thumb')."' class='small' />".
								 "<a href='/film/".$film->fid."'>".$film->title."</a> (".year($film->release_date).")</li>";
					}
				}
				$html .= '</ul>';
				echo $html;
			}
		}
	}


	/**
	* Print HTML.
	*/
	function getTitle() {
		return $this->title;
	}

	function getAkaTitle() {
		// Set by me:
		$sth = $this->dbh->prepare("SELECT pref_title FROM preferred_titles WHERE film_id = ? LIMIT 1");
		$sth->bindParam(1, $this->fid);
		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_NUM);
		$row = $sth->fetch();
		if ($row) return $row[0];
		return false;
	}

	function getYear() {
		return year($this->release_date);
	}

	function getRuntime() {
		return $this->runtime;
	}

	function getOverview() {
		return $this->overview;
	}

	// Wrapper function returns full tag:
	function getPoster($size = 'original') {
		$html  = "<img class='poster' src='";
		$html .= $this->getPosterUrl($size);
		$html .= "' />";
		return $html;
	}

	// Returns plain URL string
	function getPosterUrl($size = 'original') {
		// Prepare list of acceptable sizes:
		$sizes = array('mid', 'original', 'thumb', 'h632');
		// Prepend requested size:
		array_unshift($sizes, $size);

		// Prepare posters for searching:
		$posters = unserialize($this->posters);
		// Find a poster of the requested size:
		foreach ($sizes as $size) {
			foreach ($posters as $wrap => $p) {
				if ($p['image']['size'] == $size) {
					// Found one:
					$p_url = $p['image']['url'];
					break;
				}
			}
			if ($p_url) break;
		}

		// Fallback 1:
		if (!(isset($p_url)) || $p_url == '') {
			if (isset($this->poster_url)) {
				$p_url = $this->poster_url;
			}
			else {
			// Fallback 2:
				$p_url = BLANK_POSTER;
			}
		}

		return $p_url;
	}

	function getGenres() {
		return $this->genres;
	}

	function getCountries() {
		if (empty($this->countries)) return 'unknown';
		return $this->countries;
	}

	function getLanguages() {
		if (empty($this->languages)) return 'unknown';
		// Fix bad data:
		$langlist = $this->languages;
		$langlist = preg_replace('/\ben\b/', 'English', $langlist, 1);
		$langlist = preg_replace('/\bfr\b/', 'French', $langlist, 1);
		$langlist = preg_replace('/\bru\b/', 'Russian', $langlist, 1);
		$langlist = preg_replace('/\bit\b/', 'Italian', $langlist, 1);
		return $langlist;
	}

	function getTrailer() {
		if (isset($this->trailer) && $this->trailer != '') {
			$video_id = substr($this->trailer,-11,11);
			$html = '<iframe width="240" height="180" src="http://www.youtube.com/embed/'.$video_id.'" frameborder="0" allowfullscreen></iframe>';
			return $html;
		}
		else return 'not found';
	}


	/**
	* Print HTML table.
	*/
	function displayCast() {
		if (isset($this->cast)) {
			echo "<table>";
			foreach($this->cast as $p) {
				extract($p, EXTR_PREFIX_ALL, 'p');
				$hash = hashRole($this->fid, $p_job, $p_id);
				if (!isset($p_picture_url) || $p_picture_url == '') $p_picture_url = BLANK_HEADSHOT;
				if ($p_job == 'Actor') {
					echo "<tr>";
					echo "<td><img class='thumb' src='$p_picture_url' /></td>";	// WOULD BE NICE TO USE Person::getPictureUrl('thumb')
					echo "<td><a href='/person/$p_id/$p_permalink'>$p_name</a></td>";
					echo "<td>$p_character</td>";
					echo "<td>".StarHelper::displayStars($hash)."</td>";
					echo "</tr>";
				}
			}
			echo "</table>";
		}
		else {
			echo "Cast not available.";
		}
	}

	function displayCrew() {
		if (isset($this->cast)) {
			foreach($this->cast as $p) {
				extract($p, EXTR_PREFIX_ALL, 'p');

				$crewlink = "<a href='/person/$p_id/$p_permalink'>$p_name</a>";

				switch ($p_job) {
					// Directed by:
					case 'Director':
						$directorLinks[] = $crewlink;
						break;
					// Written by:
					case 'Writer':
					case 'Author':
					case 'Screenplay':
						$writerLinks[] = $crewlink;
						break;
					// Produced by:
					case 'Producer':
					case 'Executive Producer':
					case 'Associate Producer':
						$producerLinks[] = $crewlink;
						break;
					// Editing:
					case 'Editor':
						$editorLinks[] = $crewlink;
						break;
					// Photography:
					case 'Director of Photography':
						$dopLinks[] = $crewlink;
						break;
					// Music:
					case 'Music':
					case 'Original Music Composer':
					case 'Original Soundtrack Composer':
						$musicianLinks[] = $crewlink;
						break;
					// Sound:
					case 'Foley':
					case 'Sound Design':
						$soundLinks[] = $crewlink;
						break;
					// Effects:
					case 'Visual Effects':
					case 'Special Effects':
						$vfxLinks[] = $crewlink;
						break;
					// Casting:
					case 'Casting':
						$castingLinks[] = $crewlink;
						break;
					// Stunts:
					case 'Stunts':
						$stuntLinks[] = $crewlink;
						break;
					default:
						// nada
				} // end switch
			} // end foreach

			// Start printing HTML:
			echo "<dl>";
			if (isset($directorLinks))	echo "<dt>Directed by:</dt><dd>".	implode(', ', array_unique($directorLinks))."</dd>";
			if (isset($writerLinks))	echo "<dt>Written by:</dt><dd>".	implode(', ', array_unique($writerLinks))."</dd>";
			if (isset($producerLinks))	echo "<dt>Producers:</dt><dd>".		implode(', ', array_unique($producerLinks))."</dd>";
			if (isset($editorLinks))	echo "<dt>Editing:</dt><dd>".		implode(', ', array_unique($editorLinks))."</dd>";
			if (isset($dopLinks))		echo "<dt>Photography:</dt><dd>".	implode(', ', array_unique($dopLinks))."</dd>";
			if (isset($musicianLinks))	echo "<dt>Music:</dt><dd>".			implode(', ', array_unique($musicianLinks))."</dd>";
			if (isset($soundLinks))		echo "<dt>Sound:</dt><dd>".			implode(', ', array_unique($soundLinks))."</dd>";
			if (isset($castingLinks))	echo "<dt>Casting:</dt><dd>".		implode(', ', array_unique($castingLinks))."</dd>";
			if (isset($stuntLinks))		echo "<dt>Stunts:</dt><dd>".		implode(', ', array_unique($stuntLinks))."</dd>";
			if (isset($vfxLinks))		echo "<dt>Special Effects:</dt><dd>".implode(', ', array_unique($vfxLinks))."</dd>";
			echo "</dl>";
		}
		else {
			echo "Crew not available.";
		}
	} // end displayCrew()

} // end class Film
