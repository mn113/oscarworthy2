<?php
require_once('../config.php');
require_once('../inc/initpage.php');

$sth = $dbh->query("SELECT film FROM awards ORDER BY award_id ASC LIMIT 4500, 500");
$sth->execute();
$res = $sth->fetchAll();
foreach ($res as $r) {
	$f[] = utf8_encode($r['film']);
}
$jf = json_encode($f);	//utf8 warning


// Starting HTML:
$page_title = 'Detecting';
include_once('../inc/header.php'); ?>

<h2>Lang_detect</h2>

<script type="text/javascript">
var jsonfilms = <?php echo $jf; ?>;
</script>

<?
// Insert into temp table:
$q = "INSERT INTO temp_awards (award_id, lang) VALUES (1,'en'), (2,'fr')";

// Transfer to real table:
$q = "UPDATE awards, temp_awards SET awards.lang = temp_awards.lang WHERE awards.award_id = temp_awards.award_id";
