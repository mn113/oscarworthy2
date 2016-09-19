<?php

class Search {

	private $q;
	private $qbool;
	
	// DB conn handles will reference global dependencies
	protected $tmdb;
	protected $dbh;


	function __construct($q, $dbh, $tmdb, $partial = false) {
		$this->q = $q;
		// Dependency Injection:
		$this->dbh = $dbh;
		$this->tmdb = $tmdb;
		
		if (!$partial) $this->prepareQuery();		
	}
	
	function __destruct() {
		// Cleanup:
		$this->dbh = null;
	}


	/**
	* Work on the query string.
	*/
	function prepareQuery() {
		$q = $this->q;
		// Remove bad chars:
		preg_replace('/\s/', ' ', $q);		// Whitespace				-> a space
		preg_replace('/\S\W\D/', '', $q);	// Not space, word or num	-> chopped

		// Split query up into separate terms:
		$terms = explode(' ', $q);

		// Sort by length:
		usort($terms, 'sortByLength');

		// Append wildcard:
		foreach ($terms as $i => $t) {
			$terms[$i] = $t.'*';
		}

		// Add boolean ranking operators:
		$s = sizeof($terms);
		$terms[0] = '+>'.$terms[0];						// Longest is required
		if ($s >= 2) $terms[$s-1] = '<'.$terms[$s-1];	// Shortest is less important
		if ($s >= 3) $terms[1] = '>'.$terms[1];			// Second is important

		$this->qbool = implode(' ', $terms);
		FB::log($this->qbool);
	}


	/**
	* Fulltext search on the local films table.
	*/
	function localFilm() {
		$sth = $this->dbh->prepare("SELECT f.title, f.original_name, f.film_id, f.permalink, f.release_date, v.hits
									FROM films AS f
									LEFT JOIN pageviews AS v 
									ON f.film_id = v.ent_id 
									WHERE MATCH (f.title, f.alternative_name) AGAINST (? IN BOOLEAN MODE)
									ORDER BY v.hits DESC");
		$sth->bindParam(1, $this->qbool);
		$sth->execute();
		if ($sth->rowCount() > 0) {
			$results = array();
			$rows = $sth->fetchAll();
			FB::log($rows);
			foreach ($rows as $r) {
				// Build nice result:
				$results[] = array('id'			=> $r['film_id'],
								   'title'		=> $r['title'],
								   'permalink'	=> $r['permalink'],
								   'original_name' => $r['original_name'],
								   'year'		=> year($r['release_date']),
								   'hits'		=> $r['hits']
								  );
			}
		}
		return isset($results) ? $results : null;
	}


	/**
	* Fulltext search on the local people table.
	*/
	function localPerson() {
		$sth = $this->dbh->prepare("SELECT p.fullname, p.person_id, p.permalink, v.hits
									FROM people AS p
									LEFT JOIN pageviews AS v 
									ON p.person_id = v.ent_id 
									WHERE MATCH (p.fullname) AGAINST (? IN BOOLEAN MODE) 
									ORDER BY v.hits DESC");
		$sth->bindParam(1, $this->qbool);
		$sth->execute();
		if ($sth->rowCount() > 0) {
			$rows = $sth->fetchAll();
			foreach ($rows as $r) {
				// Build nice result:
				$results[] = array('id'			=> $r['person_id'],
								   'name'		=> $r['fullname'],
								   'permalink'	=> $r['permalink'],
								   'hits'		=> $r['hits']
								  );
			}
		}
		return isset($results) ? $results : null;
	}


	/**
	* TMDb API search.
	*/
	function tmdbFilm() {
		if ($json = $this->tmdb->searchMovie($this->q)) {
			$films = objectToArray(json_decode($json));
			$results = array();
			foreach ($films as $film) {
				// Build nice result:
				$results[] = array('id'			=> $film['id'],
								   'title'		=> $film['name'],
								   'permalink'	=> Entity::makePermalink($film['name']),
								   'original_name' => $film['original_name'],
								   'year'		=> year($film['released'])
								  );
			}
		}
		return isset($results) ? $results : null;		
	}


	/**
	* TMDb API search.
	*/
	function tmdbPerson() {
		if ($json = $this->tmdb->searchPerson($this->q)) {
			$people = objectToArray(json_decode($json));
			foreach ($people as $person) {
				// Build nice result:
				$results[] = array('id'			=> $person['id'],
								   'name'		=> $person['name'],
								   'permalink'	=> Entity::makePermalink($person['name'])
								  );
			}
		}
		return isset($results) ? $results : null;
	}


	/**
	* Render results.
	*/
	function displayResults($results, $type) {
		if ($results) {
			$html = "<ul id='results_$type'>";
			foreach ($results as $r) {
				extract($r);
				// Convert page hits to search rank bar:
				$rank = 1;
				if (isset($hits)) $rank += (int)$hits;

				switch($type) {
					case 'films':
						if (is_string($title)) {
							$html .= "<li><a href='/film/$id/$permalink'>$title</a> ($year)";
							if ($original_name != $title) $html .= " <span class='aka'>aka $original_name</span>";
							$html .= "<div class='rankbar' style='width:{$rank}px' />";
							$html .= "</li>";
						}
						break;
					
					case 'people':
						if (is_string($name)) {
							$html .= "<li><a href='/person/$id/$permalink'>$name</a>";
							$html .= "<div class='rankbar' style='width:{$rank}px' />";
							$html .= "</li>";
						}
						break;
				}
			}
			$html .= "</ul>";
			echo $html;
		}
		else {
			echo "<p>No matching $type found.</p>";
		}
	}
	
	
	/**
	* Render a list of distinct recent stored searches.
	*/
	static function displayRecent($dbh, $num = 10) {
		$sth = $dbh->query("SELECT DISTINCT result_title, clicked_link FROM searches ORDER BY sdate DESC LIMIT $num");
		if ($sth->rowCount() > 0) {
			$urows = $sth->fetchAll();
			FB::log($urows);
			// Remove dupes:
//			$urows = array();
//			foreach ($rows as $row) {
//				if (!array_search($row, $urows)) $urows[] = $row;
//			}
			// Build output:
			$html = '<ul>';
			foreach ($urows as $urow) {
				$title = $urow['result_title'];
				$href = $urow['clicked_link'];
				$html .= "<li><a href='$href'>$title</a></li>";
			}
			$html .= '</ul>';
			echo $html;
		}
	}
	
	
	/**
	* Store search terms and results in db (called when user clicks a search result link).
	*/
	static function store($dbh, $q, $res, $clicked, $dropdown = 0) {
		$sth = $dbh->prepare("INSERT INTO searches (terms, result_title, clicked_link, from_dropdown, sdate) VALUES (?, ?, ?, ?, NOW())");
		$sth->bindParam(1, $q);
		$sth->bindParam(2, $res);
		$sth->bindParam(3, $clicked);
		$sth->bindParam(4, $dropdown);
		$sth->execute();
		// No chance to do much else, the user has moved on.
	}

} // end class Search