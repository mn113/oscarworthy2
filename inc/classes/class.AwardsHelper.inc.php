<?php

class AwardsHelper {

	// Form values:
	public $cy;
	public $cat;
	public $y1;
	public $y2;
	public $wn;

	protected $dbh;


	function __construct($dbh) {
		$this->dbh	= $dbh;
	}


	/**
	* Set the form values (acts as a second constructor).
	*/
	function setData($params) {
		$this->cy	= $params['cy'];
		$this->cat	= $params['cat'];
		$this->y1	= $params['y1'];
		$this->y2	= $params['y2'];
		$this->wn	= $params['wn'];
		FB::log($this);
	}
	
	
	/**
	* Get the valid ceremony names from the db.
	*/
	function getCeremonies() {
		$sth = $this->dbh->query("SELECT DISTINCT ceremony FROM awards");
		$sth->execute();
		$res = $sth->fetchAll();
		FB::log($res);
		foreach ($res as $r) {
			$list[] = $r['ceremony'];
		}
		return $list;
	}
	

	/**
	* Display a table containing results of query from form values.
	*/
	function displayResults() {
		// Build WHERE clause:
		$where  = '';
		$where .= "ceremony = '{$this->cy}' ";
		if ($this->cat != 'All')$where .= "AND category = '{$this->cat}' ";
		if ($this->y1)			$where .= "AND ayear >= {$this->y1} ";
		if ($this->y2)			$where .= "AND ayear <= {$this->y2} ";
		if ($this->wn == 'w')	$where .= "AND won = 'W' ";
		FB::log($where);
		
		// Query db:
		$sth = $this->dbh->prepare("SELECT * FROM awards WHERE $where LIMIT 500");
		$sth->execute();
		$results = $sth->fetchAll();

		// Build table:
		if (!empty($results)) {
			// Table w/ headings:
			$html = "<table id='awards_table' class='tablesorter'>".
					"<thead><th>Year</th><th>Awarding Body</th><th>Award</th><th>For</th><th>Won?</th><th></th></thead>".
					"<tbody>";
			foreach ($results as $r) {
				extract($r);
				// Start table row:
				if ($won == 'W') {
					$won = 'Won';
					$tr = "<tr class='winner'>";				
				}
				else {
					$won = 'Nominated';
					$tr = "<tr class='nominated'>";				
				}
				// Format the easy parts of row:
				$tr .= "<td>$ayear</td>";
				$tr .= "<td>$ceremony</td>";
				$tr .= "<td>$award_name</td>";
				$tr .= "<td>$award_for</td>";
				$tr .= "<td>$won</td>";

				// Make film and/or person links:
				if (isset($film_id)) {
					$f = new Film($film_id, $this->dbh);
					if ($f->fetchLocal()) $film = $f->getTitle();
				}
				if (isset($person_id)) {
					$p = new Person($person_id, $this->dbh);
					if ($p->fetchLocal()) $person = $p->getName();
				}
				$filmlink	= "<a href='/film/$film_id'>$film</a>";
				$personlink	= "<a href='/person/$person_id'>$person</a>";

				// Format output:
				if (($category == 'Actor' || $category == 'Director') && !empty($film) && !empty($person)) {
					$tr .= "<td>$personlink for $filmlink</td>";	
				}
				elseif (!empty($person) && empty($film)) {
					$tr .= "<td>$personlink</td>";	
				}
				elseif (!empty($film) && empty($person)) {
					$tr .= "<td>$filmlink</td>";	
				}
				else {
					$tr .= "<td>$personlink / $filmlink</td>";					
				}

				// Finish up:
				$tr .= "</tr>";
				$html .= $tr;
			}
			$html .= "</tbody></table>";		
			echo $html;
		}
		else {
			echo "No results";
		}

	}

} // end class AwardsHelper