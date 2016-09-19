<?php
require_once('../config.php');

// Receive query via GET:
if (isset($_GET['query']) && $_GET['query'] != '') {
	// Add wildcards:
	$q = $_GET['query'];
	$wcq = '*'.$q.'*';

	$s = new Search($q, $dbh, $tmdb, false);

	// Search only one of two sources:
	switch($_GET['source']) {
		case 'local':	
			$res1 = $s->localFilm();
			$res2 = $s->localPerson();
			break;
		case 'tmdb':
			$res1 = $s->tmdbFilm();
			$res2 = $s->tmdbPerson();
			break;
		default:
			die('Source error');
	}

	// Prepare film matches:
	$titles = $urls = array();
	if (!empty($res1)) {
		$i = 1;
		foreach ($res1 as $r) {
			$titles[]	= utf8_encode($r['title']);
			$urls[]		= '/film/'.$r['id'];
			$i++;
			if ($i > 10) break;
		}
	}
	// Prepare person matches:
	if (!empty($res2)) {
		$i = 1;
		foreach ($res2 as $r) {
			$titles[]	= utf8_encode($r['name']);
			$urls[]		= '/person/'.$r['id'];
			$i++;
			if ($i > 10) break;
		}
	}

	// Format output for autocomplete.js:
	$arr = array(
		'query'			=> $q,
		'suggestions'	=> $titles,
		'data'			=> $urls
	);

	// Spit it:
	echo json_encode($arr);
}
else {
	echo 'nothing found';
}