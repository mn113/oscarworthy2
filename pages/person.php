<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Getting the pid:
if (isset($_GET['pid']) && $_GET['pid'] != '') {
	$pid = (int) $_GET['pid'];		// Got it
}
else {
	trigger_error("No pid!", WARNING);
}

// Init & check cache:
$cache = new Cache(20, array($pid));
$cache->check();

// Clear $_SESSION votes to make way for new set:
$_SESSION['votes'] = array();

// Make the person:
$person = new Person($pid, $dbh, $tmdb);

// Check for person data locally:
if (!$person->fetchLocal()) {	// If we don't have them:
	// Import it from TMDb:
	if ($person->tmdbLookup()) {
		// Save it to db:
		$person->store();
		$person->storeRoles($person->filmography);
	}
	else {
		Messages::create('error', 'Page does not exist', false);	// No autofade
	}
}


// Starting HTML:
$page_title = $person->name;
include_once('../inc/header.php'); ?>

<?php
// Main content output section:
if ($person->isValid):
	$person->hitMe();
	$person->fetchFilmography();

	// Temporary links:
	$pid0 = (int)$pid - 1;
	$pid2 = (int)$pid + 1;
	echo "<a href='/person/$pid2' class='next'>Next</a>"; 
	echo "<a href='/person/$pid0' class='prev'>Prev&nbsp;</a>"; ?>

	<h1><?php echo $person->getName(); ?></h1>
	
	<div id="info">
		<div class="frame"><?php echo $person->getPicture('profile'); ?></div>
		<?php echo $person->getMinutiae(); ?>
		<h4>Awards</h4>
		<?php if ($person->getAwards()) $person->displayAwards(); ?>	
	</div>
	<?php
		// Prepare to set stars:
		$starhelper = new StarHelper($dbh, $page, $pid);
		$starhelper->getAvgRatings();
		if (User::isLogged()) {
			$starhelper->getMemberRatings();
		}
	?>	
	<div id="listing">
		<div id="bio_box"><?php echo $person->getBio(); ?></div>
		<div id="filmography_box">
			<?php $person->displayFilmography(); ?>
		</div>
	</div>

<?php endif; ?>

<script type="text/javascript">
	Osc.tab = 'Actors';								// jq sets tab
	Osc.pid = <?php echo $pid; ?>;						// for lazyLoad
	Osc.permalink = '<?php echo $person->permalink; ?>';	// footer fixes url
	Osc.starFiller(Osc.pid, 'person');
	Osc.lazyLoad(Osc.pid, 'filmography');
</script>

<?php include_once('../inc/footer.php');
