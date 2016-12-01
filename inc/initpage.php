<?php
/*
 *  MISC INIT FUNCTIONS IMMEDIATELY PRECEDING header.php
 *
 */

// Check if session valid & access allowed:
Auth::checkpoint();


// Set the page id (& body tag):
$page = preg_replace('|\.php/?$|', '', $_SERVER['PHP_SELF']);	// KEEP AN EYE ON BODY ID - AND DON'T CONFUSE FORMS
$page = preg_replace('|^/pages(_user)?/|', '', $page);			// removing subdir


// Remember some user browsing history:
$cur  =& $_SESSION['history']['thispage'];
$prev =& $_SESSION['history']['prevpage'];
if (isset($cur) && $cur != $_SERVER['REQUEST_URI']) {
	$prev = $cur;
}
$cur = $_SERVER['REQUEST_URI'];


// Geolocation:
//$geo = GeoLoc::getInfoFromIP($_SERVER['REMOTE_ADDR']);
//FB::log($_SERVER['REMOTE_ADDR'], 'ip');
//FB::log($geo, 'geoloc');
