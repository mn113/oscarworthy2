<?php
require_once('config.php');

$fid = 11;

// Make the film:
$film = new Film($fid, $dbh, $tmdb);

// Check for film data locally:
$film->fetchLocal();

// Starting HTML:
$page_title = $film->title;
include_once('inc/header.php'); ?>

