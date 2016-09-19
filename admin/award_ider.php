<?php
require_once('../config.php');


$sth = $dbh->query("SELECT * FROM awards WHERE award_id > 4008 LIMIT 1000");
$rows = $sth->fetchAll();

foreach ($rows as $row) {
	extract($row);	// makes variables of all db fields
	FB::log($film.' ::: '.$person);

	// Films:
	if (!$film_id && $film) {
		$json = $tmdb->searchMovie(urlencode($film));
		$film_object = json_decode($json);
		$f = objectToArray($film_object[0]);
		FB::log($f);
	
		if (is_array($f)) {
			$fid = $f['id'];
			$sth = $dbh->exec("UPDATE awards SET film_id = $fid WHERE award_id = $award_id LIMIT 1");
			FB::log("fid $fid attached to film $film");
		}
	}
	
	// People:
	if (!$person_id && $person) {
		$json = $tmdb->searchPerson(urlencode($person));
		$person_object = json_decode($json);
		$p = objectToArray($person_object[0]);
		FB::log($p);
	
		if (is_array($p)) {
			$pid = $p['id'];
			$sth = $dbh->exec("UPDATE awards SET person_id = $pid WHERE award_id = $award_id LIMIT 1");
			FB::log("pid $pid attached to person $person");
		}
	}
}

echo 'done';