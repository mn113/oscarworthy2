<?php
require_once('../FirePHPCore/FirePHP.class.php');
require_once('../FirePHPCore/fb.php');
require_once('../db.inc.php');
include_once('../functions.php');

// FirePHP init:
ob_start();
$firephp = FirePHP::getInstance(true);

require_once('../inc/class.Film.inc.php');
require_once('../inc/class.Person.inc.php');
require_once('../inc/class.Role.inc.php');


$film = new Film(11, $dbh, $tmdb);

$film->tmdbLookup();
$film->store();
