<?php
require_once('../config.php');


$fh = fopen('../awards/film_id_null.txt', 'r');
while ($line = fgets($fh)) {

	$bits = explode("\t", $line);
	$id = $bits[0];
	$film = $bits[1];
	FB::log($bits);
	
	$sth = $dbh->prepare("UPDATE awards SET film = ? WHERE award_id = ? LIMIT 1");
	$sth->bindParam(1, $film);
	$sth->bindParam(2, $id);
	$sth->execute();

}
echo 'done';