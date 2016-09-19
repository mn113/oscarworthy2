<?php
require_once('../FirePHPCore/FirePHP.class.php');
require_once('../FirePHPCore/fb.php');
require_once('../db.inc.php');
require_once('../functions.php');

// FirePHP init:
ob_start();
$firephp = FirePHP::getInstance(true);

echo '<pre>';
$title = 'The Prestige';


//	$tmdb->browseMovies()
// ?order_by=rating&order=desc&genres=18&min_votes=5&page=1&per_page=10
echo "<html>";
echo "<h1>TMDb browse</h1>";
$order_by = 'rating';	// rating/release/title
$order = 'desc';		// asc/desc
$params = array('genres'=>18,'min_votes'=>5,'page'=>1,'per_page'=>5);
$movies = objectToArray(json_decode($tmdb->browseMovies($order_by, $order, $params)));
//print_r($movies);
FB::log($movies, 'TMDbPop');
foreach ($movies as $m) {
	echo $m['name'] .' ('. $m['released'] .') '. $m['overview'] . '<br>';
}




// Rotten Tomatoes
require_once('../apis/RottenTomatoesApi.php');

//	get RT current movies
echo "<h1>RT current</h1>";
$rtapi = new RottenTomatoesApi('ecn57mpdtgkpqm8mrn3betcw');
$boxoff = $rtapi->listMoviesBoxOffice();
FB::log($boxoff, 'RTBox');
foreach ($boxoff['movies'] as $b) {
	echo $b['title'] . ' (' . $b['year'] . ') ' . $b['critics_consensus'] . '<br>';
}

//	get RT API score ()
echo "<h1>RT score</h1>";
$movie = $rtapi->searchFeelingLucky('Inception');	// NADA
$movie = $rtapi->movie(11);
FB::log($movie, 'RTScore');
echo $movie['title'] . ': ' . $movie['ratings']['critics_score'];





//	scrape criticker avg tier
echo "<h1>Criticker</h1>";
$url = 'http://www.criticker.com/';




//	scrape IMDb awards
echo "<h1>IMDb awards</h1>";
$url = 'http://www.imdb.com/';




//	possibly scrape joox awards
echo "<h1>Joox</h1>";


