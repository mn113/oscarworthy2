<?php
require_once('../FirePHPCore/FirePHP.class.php');
require_once('../FirePHPCore/fb.php');
include('../functions.php');

// FirePHP init:
ob_start();
$firephp = FirePHP::getInstance(true);

// Initialise the API class:
require('../apis/TMDb.php');
$tmdb = new TMDb('b93175b049e96a26b1aeb18429a433d6');

?>

<html>
<body>
<pre>
Ok:

<?php

// Get film data:
$swj = $tmdb->getMovie(11);	// Star Wars
$swo = json_decode($swj);
$swa = objectToArray($swo[0]);
echo "<h1>".$swa['name']."</h1>";
echo '<img src="' . $swa['posters'][0]['image']['url'] . '" width="200" />';

foreach($swa['cast'] as $c) {
	print "\n" . $c['job'] . ' - ' . $c['name'] . ' - ' . $c['character'];
}

FB::log($swa);


// Get person data:
$hgj = $tmdb->getPerson(69122); // Heather Graham
$hgo = json_decode($hgj);
$hga = objectToArray($hgo[0]);
echo "<h1>".$hga['name']."</h1>";
echo '<img src="' . $hga['profile'][0]['image']['url'] . '" width="200" />';

foreach($hga['filmography'] as $f) {
	print "\n<img src='" . $f['poster'] . "' width='50' height='50' />" . $f['name'] . ' (' . year($f['release']) . ') - ' . $f['character'];
}

FB::log($hga);



FB::log(quickSerialize($swa['genres']));
FB::log(quickSerialize($swa['countries']));
FB::log(quickSerialize($swa['studios']));
FB::log(quickSerialize($swa['languages_spoken']));
