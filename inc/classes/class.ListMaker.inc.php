<?php

class ListMaker {

	protected $queries = array(
		'foreign'	=>	"SELECT ayear, film_id, film FROM awards
						 WHERE lang != 'en' AND lang != '' AND film != ''",
		'classic'	=>	"SELECT ayear, film_id, film FROM awards
						 WHERE category = 'Film' AND ayear < 1960"
	);
	
	
	function __construct($dbh) {
		$this->dbh = $dbh;
	}


	function getList($type, $limit = 10, $order_by = 'RAND()') {
		// Build query:
		$q = $this->queries[$type]." ORDER BY $order_by LIMIT $limit";
		// Execute it:
		$sth = $this->dbh->prepare($q);
		$sth->execute();
		if ($sth->rowCount() == 0) {
			return false;
		}
		return $sth->fetchAll();
	}


	function display(array $array) {
		$html = "<ul class='listboxthin'>";
		foreach ($array as $r) {
			extract($r);
			$html .= "<li><a href='/film/$film_id'>$film</a> ($ayear)</li>";
		}
		$html .= "</ul>";
		echo $html;
	}
	
}