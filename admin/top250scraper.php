<?php
require_once('../config.php');

$url = 'http://www.imdb.com/chart/top';

// Retrieval
$raw = file_get_contents($url);	// worked, now 404
//$raw = fopen('imdb250.html');

// Cleaning
$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
$content = str_replace($newlines, "", html_entity_decode($raw));

// Build a list of the 250 imdb_ids:
$needle = '/title/tt';
preg_match_all('|'.$needle.'|', $content, $matches, PREG_OFFSET_CAPTURE);
//print_r($matches); // ok
foreach($matches as $match => $bits) {
	foreach ($bits as $b) {
	FB::log($b[1]);
	$s = (int)$b[1];
	$ids[] = substr($content, $s+9, 7);	
	}
}
echo '<hr>';
print_r($ids);


// Start importing to my db:

foreach ($ids as $id) {
	if ($id == 0) continue;	// skip duds

	$imdb_id = 'tt'.str_pad($id, 7, '0', STR_PAD_LEFT);

	$film = new Film(0, $dbh, $tmdb);
	
	if ($film->tmdbLookup($imdb_id, TMDB::IMDB)) {
		// Save it to db:
		$film->store();
		$film->storeRoles($film->cast);
		$film->fetchCastFull();
		FB::log($imdb_id.' imported.');
	}
	else {
		FB::log($imdb_id.' not imported.');
	}
}
