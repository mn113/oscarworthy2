<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Init & check cache:
$cache = new Cache(60);
$cache->check();

// Start Rotten Tomatoes:
require_once('../apis/RottenTomatoesApi.php');
$rth = new RTHelper($dbh, $tmdb, $geo['cc']);

$listmaker = new Listmaker($dbh);

// Starting HTML:
$page_title = 'Popular';
include_once('../inc/header.php'); ?>

<h2>What's Hot?</h2>


<?php
$movies = $rth->listMoviesBoxOffice(15, 1, 'uk');
if (is_array($movies)) {
	$rth->displayList($movies, 'Rotten Tomatoes Box Office');
}

$movies = $rth->listDvdsNewReleases(15, 1, 'uk');
if (is_array($movies)) {
	$rth->displayList($movies, 'DVD Releases');
}

//$movies = $rth->listMoviesInTheaters(15, 1, 'uk');
//if (is_array($movies)) {
//	$rth->displayList($movies);
//}

//$movies = $rth->listMoviesOpening(15, 'uk');
//if (is_array($movies)) {
//	$rth->displayList($movies);
//}

//$movies = $rth->listMoviesUpcoming(15, 1, 'uk');
//if (is_array($movies)) {
//	$rth->displayList($movies);
//}
?>


<h3>Classic Films</h3>
<?php $listmaker->display($listmaker->getList('classic')); ?>

<h3>Foreign Films</h3>
<?php $listmaker->display($listmaker->getList('foreign')); ?>

<h3>Recent searches</h3>
<?php Search::displayRecent($dbh); ?>


<?php include_once('../inc/footer.php'); ?>