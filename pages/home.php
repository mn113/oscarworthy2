<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Init & check cache:
$cache = new Cache(60);
$cache->check();

// Starting HTML:
$page_title = 'Home';
include_once('../inc/header.php'); ?>

<h2>A Site Is Born!</h2>

<?php include_once('../inc/footer.php');
