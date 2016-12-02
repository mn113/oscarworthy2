<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Getting the fid:
if (isset($_GET['fid']) && $_GET['fid'] != '') {
	$fid = (int) $_GET['fid'];		// Got it
}
else {
	trigger_error("No fid!", WARNING);
}

// Init & check cache:
$cache = new Cache(20, array($fid));
$cache->check();

// Clear $_SESSION votes to make way for new set:
$_SESSION['votes'] = array();

// Make the film:
$film = new Film($fid, $dbh, $movierepository);

// Check for film data locally:
if (!$film->fetchLocal()) {	// If we don't have it:
	// Import it from TMDb:
	if ($film->tmdbLookup()) {
		// Save it to db:
		$film->store();
		$film->storeRoles($film->cast);
	}
	else {
		Messages::create('error', 'Page does not exist', false);	// No autofade
	}
}


// Starting HTML:
$page_title = $film->title;
include_once('../inc/header.php'); ?>

<?php
// Main content output section:
if ($film->isValid):
	$film->hitMe();
	$film->fetchCast();

	// Temporary links:
	$fid0 = (int)$fid - 1;
	$fid2 = (int)$fid + 1;
	echo "<a href='/film/$fid2' class='next'>Next</a>";
	echo "<a href='/film/$fid0' class='prev'>Prev&nbsp;</a>"; ?>

	<h1><?php echo $film->getTitle(); ?> (<?php echo $film->getYear(); ?>)</h1>
	<?php if (User::isAdmin()): ?><a href="#" class="delete" id="del<?php echo $fid; ?>"></a><?php endif; ?>
	<?php if (User::isAdmin()): ?><form id="rename"><input type="text" id="ren<?php echo $fid; ?>"><a href="#">Rename</a></form><?php endif; ?>
	<p><?php echo $film->getAkaTitle(); ?></p>

	<div id="info">
		<div class="frame"><?php echo $film->getPoster('mid'); ?></div>
		<dl>
			<dt>Runtime:</dt><dd><?php echo $film->getRuntime(); ?>'</dd>
			<dt>Overview:</dt><dd><?php echo $film->getOverview(); ?></dd>
			<dt>Genre:</dt><dd><?php echo $film->getGenres(); ?></dd>
			<dt>Country:</dt><dd><?php echo $film->getCountries(); ?></dd>
			<dt>Language:</dt><dd><?php echo $film->getLanguages(); ?></dd>
			<dt>Trailer:</dt><dd><?php echo $film->getTrailer(); ?></dd>
			<dt>Tomatometer:</dt><dd><?php echo $film->getRottenScore(); ?></dd>
		</dl>
		<h4>Awards</h4>
		<?php if ($film->getAwards()) $film->displayAwards(); ?>

		<h4>Similar Films:</h4>
		<?php $film->displayRottenSimilar(); ?>
	</div>
	<?php
		// Prepare to set stars:
		$starhelper = new StarHelper($dbh, $page, $fid);
		$starhelper->getAvgRatings();
		if (User::isLogged()) {
			$starhelper->getMemberRatings();
		}
	?>
	<div id="listing">
		<h2>Crew</h2>
		<div id="crew_box">
			<?php $film->displayCrew(); ?>
		</div>
		<h2>Cast</h2>
		<div id="cast_box">
			<?php $film->displayCast(); ?>
		</div>
	</div>

<?php endif; ?>

<script type="text/javascript">
	Osc.tab = 'Films';								// jq sets tab
	Osc.fid = <?php echo $fid; ?>;						// for lazyLoad
	Osc.permalink = '<?php echo $film->permalink; ?>';		// footer fixes url
	Osc.starFiller(Osc.fid, 'film');
	Osc.lazyLoad(Osc.fid, 'cast');
</script>

<?php include_once('../inc/footer.php'); ?>
