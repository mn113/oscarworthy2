<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Getting the type param:
if ($type = @$_GET['type']) {

	// Is it film:
	if ($type == 'film') {
		$table = 'films';
		$id = 'film_id';
	}
	// Or person:
	elseif ($type == 'person') {
		$table = 'people';
		$id = 'person_id';
	}
}

if ($id && $table) {
	// Count records:
	$sth = $dbh->query("SELECT COUNT(*) AS c FROM $table");
	$count = $sth->fetch();

	// Select random row:
	$rand = rand(0,((int)$count['c'])-1);

	// Fetch entity id for that row:
	$sth = $dbh->query("SELECT $id from $table LIMIT $rand, 1");
	$result = $sth->fetch();

	// Go there:
	header("Location: /$type/{$result['film_id']}/");
}