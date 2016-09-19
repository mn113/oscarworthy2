<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="description" content="" />
  	<meta name="keywords" content="" />
	<meta name="robots" content="" />
	<title>RateIt | Acting: The GOAT</title>
	<link href="../css/actgoat.css" rel="stylesheet" media="screen" type="text/css" />
	<link href="../css/rateit.css" rel="stylesheet" type="text/css" media="screen" />
	<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<style>
		article, aside, dialog, figure, footer, header, hgroup, menu, nav, section, title {
			display: block;
		}	
	</style>
	-->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
	<script src="../js/jquery.rateit.min.js"></script>
	<script src="../js/actgoat.js"></script>
	<style type-"text/css">
		#main span {float:left; width: 8em;}
	</style>

</head>
<body>

<div id="outer">
<div id="panel_outer">
	<div id="panel">
		<a href="#">Register</a> or 
		<a href="#">Login</a>
	</div>
</div>
<div id="page">

<header>
	<h1>Oscarworthy</h1>
	<img src="../img/oscarworthy2.png" />
</header>

<nav>
	<ul class="cf">
		<li class="active"><a href="#">Home</a></li>
		<li><a href="#">Films</a></li>
		<li><a href="#">Actors</a></li>
		<li><a href="#">About</a></li>
	<form id="search" action="search.php" method="POST">
		<!--label for="search">Search:</label-->
		<input type="text" name="search" id="search" value="Films, actors" tabindex="1" />
	</form>
	</ul>
</nav>

<div id="main" class="cf">

<?php // Display any messages in session:

if (isset($_SESSION['messages'])) {
	foreach ($_SESSION['messages'] as $msg) {
		echo "<div class='message {$msg['class']}'>{$msg['text']}<span class='close_button' /></div>";
	}
	unset($_SESSION['messages']);
}
?>

<span>Meryl Streep</span>
<div id="12345678" class="rateit" data-rateit-value="4" data-rateit-ispreset="true" data-rateit-readonly="true">
</div>
<br>

<span>Robert De Niro</span>
<div id="4321edab" class="rateit" data-rateit-value="3.5" data-rateit-ispreset="true">
</div>
<br>

<span>Evan Rachel Wood</span>
<div id="0a4d2c7f" class="rateit">
</div>
<br>

<span>Lassie</span>
<div class="rateit">
</div>

<script src="../js/jquery.rateit.min.js"></script>
