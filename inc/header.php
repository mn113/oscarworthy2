<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
  	<meta name="keywords" content="" />
	<meta name="robots" content="" />
	<title><?php echo $page_title; ?> | Oscarworthy</title>
	<link rel="shortcut icon" href="/img/favicon.ico" />
	<link rel="stylesheet" type="text/css" media="screen" href="/css/oscarworthy.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="/css/oscarworthy.pages.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="/css/extras.css" />
	<!--link rel="stylesheet" type="text/css" media="screen" href="/css/rateit.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="/css/range.css"/>
	<link rel="stylesheet" type="text/css" media="screen" href="/css/autocomplete.css"/-->
	<!--link href='http://fonts.googleapis.com/css?family=Neuton' rel='stylesheet' type='text/css' /-->
	<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<style>
		article, aside, dialog, figure, footer, header, hgroup, menu, nav, section, title {
			display: block;
		}	
	</style>
	-->
	<!--script src="http://code.jquery.com/jquery-latest.min.js"></script-->
	<script src="/js/jquery-1.6.4.min.js"></script>
	<!--script src="http://cdn.jquerytools.org/1.2.6/form/jquery.tools.min.js"></script-->
	<script src="/js/jquery.tools.min.js"></script>
	<script src="/js/jquery.autocomplete.min.js"></script>
	<script src="/js/jquery.rateit.min.js"></script>
	<script src="/js/modernizr-latest.min.js"></script>
	<script src="/js/oscarworthy.js"></script>
	<script src="/js/oscarworthy.forms.js"></script>
	<script src="/js/json2.min.js"></script>
	<script src="/js/jq.bingapi.js"></script>
</head>

<body <?php echo "id='$page'"; ?>>

<div id="wrap960">
	<div id="panel_border">
		<div id="panel">
		<?php echo Panel::display(); ?>
		</div>
	</div>
</div>

<div id="page_border">
<div id="page">

<header>
	<h1>Oscarworthy</h1>
	<img src="http://i.oscarworthy.local:8888/img/oscarworthy2.png" />
</header>

<nav>
	<ul class="cf">
		<li><a href="/home">Home</a></li>
		<li><a href="/random/film">Films&#8230;</a>
			<ul class="cf">
				<li><a href="/random/film">Browse</a></li>
				<li><a href="/popular">Popular</a></li>
				<li><a href="/random/film">Random</a></li>
			</ul>		
		</li>
		<li><a href="/random/person">Actors&#8230;</a>
			<ul class="cf">
				<li><a href="/random/person">Browse</a></li>
				<li><a href="/popular">Popular</a></li>
				<li><a href="/random/person">Random</a></li>
			</ul>		
		</li>
		<li><a href="/awards">Awards</a></li>
		<li><a href="/about">About</a></li>
		<form id="search" action="/search" method="GET">
			<!--label for="search">Search:</label-->
			<input type="text" name="q" id="navsearch" value="Films, actors" tabindex="1" />
			<input type="submit" class="noshow" />
		</form>
	</ul>
</nav>

<div id="main" class="cf">

	<div id="messages">
	<?php
		Messages::displayAll();
	?>
	</div>