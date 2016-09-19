<?php
require_once('../config.php');
require_once('../inc/initpage.php');

$deep = 'false';
// Getting the query:
if (isset($_GET['q']) && $_GET['q'] != '') {
	// Cleaning:
	$clean = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
	extract($clean);
}
else {
	$q = '';
	Messages::create('neutral', 'No search query.');
}

// Init & check cache:
$cache = new Cache(60, array($q, $deep));
$cache->check();


// Starting HTML:
$page_title = 'Search: '.$q;
include_once('../inc/header.php'); ?>

<form id="searchform" name="searchform" action="" method="GET" class="thin">
	<fieldset>
		<legend>Search</legend>
		<label for="q">Search:</label>
		<input type="text" name="q" value="<?php if (isset($q)) echo $q; ?>" />
		<label for="deep">Deep search</label>
		<input type="checkbox" name="deep" value="true" />
		<input type="submit" />
	</fieldset>
</form>

<?php if ($q): ?>
<h2>Search results for <span class="query"><?php echo $q; ?></span>:</h2>
<?php endif;

// Search local db:
$search = new Search($q, $dbh, $tmdb);
$f = $search->localFilm();
$p = $search->localPerson();

// Search TMDb as fallback:
if (!$f || (isset($deep) && $deep == 'true')) $f = $search->tmdbFilm();
if (!$p || (isset($deep) && $deep == 'true')) $p = $search->tmdbPerson();
?>

<div class="primary left">
	<?php $search->displayResults($f, 'films'); ?>
	<?php $search->displayResults($p, 'people'); ?>
</div>

<div class="secondary right">
	<h3>Recent searches</h3>
	<?php Search::displayRecent($dbh); ?>
</div>


<script language="javascript">
	Osc.tab='Search';
	<?php if (isset($q) && $q != '') echo "Osc.q='".$q."';"; 	// DANGEROUS IF $q NOT CLEANED ?>;
</script>

<?php include_once('../inc/footer.php'); ?>
