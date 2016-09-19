<?php
require_once('../config.php');
require_once('../inc/initpage.php');


// Init & check cache:
//$cache = new Cache(60);	// EITHER DON'T CACHE PAGE, OR CACHE FOR EACH QUERY STRING
//$cache->check();

// Default query:
$cy = 0;
$cat = 0;
$y1 = $y2 = 2010;
$wn = 'w';

// Process form:
if (isset($_GET['cy'])) {
	// Cleaning:
	$clean = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
	extract($clean);
}

// Start results getter/builder:
$awardshelper = new AwardsHelper($dbh);
$ceremonies = $awardshelper->getCeremonies();
$categories = array('All', 'Film', 'Actor', 'Director', 'Honorary');
// Pass form data to helper class:
$awardshelper->setData(array('cy' => $ceremonies[$cy], 'cat' => $categories[$cat], 'y1' => $y1, 'y2' => $y2, 'wn' => $wn));


// Starting HTML:
$page_title = 'Awards';
include_once('../inc/header.php'); ?>

<h2>Awards</h2>

<form id="awards" name="awards" action="" method="GET">

	<fieldset>
	<legend>Filters</legend>
	<label for="cy">Awards</label>
	<select name="cy">
		<?php //populate dropdown
		$cers = '';
		foreach ($ceremonies as $i => $name) {
			$cers .= "<option value='$i'";
			if ($i == $cy) $cers .= " selected";
			$cers .= ">$name</option>";
		}
		echo $cers;
		?>
	</select>	

	<label for="cat">Category</label>
	<select name="cat">
		<?php //populate dropdown
		$cats = '';
		foreach ($categories as $i => $name) {
			$cats .= "<option value='$i'";
			if ($i == $cat) $cats .= " selected";
			$cats .= ">$name</option>";
		}
		echo $cats;
		?>
	</select>	

	<label for="y1">From</label>
	<select name="y1">
		<?php //populate dropdown
		$years = '';
		for ($i = date('Y'); $i >= 1928 ; $i--) {
			$years .= "<option";
			if ($i == $y1) $years .= " selected";
			$years .= ">$i</option>\n";
		}
		echo $years;
		?>
	</select>	

	<label for="y2">To</label>
	<select name="y2">
		<?php //populate dropdown
		$years = '';
		for ($i = date('Y'); $i >= 1928 ; $i--) {
			$years .= "<option";
			if ($i == $y2) $years .= " selected";
			$years .= ">$i</option>\n";
		}
		echo $years;
		?>
	</select>	

	<label for="wn">Show</label>
	<select name="wn">
		<option value="w" <?php if ($wn == 'w') echo "selected"; ?>>Winners</option>
		<option value="wn" <?php if ($wn == 'wn') echo "selected"; ?>>Winners & Nominees</option>
	</select>

	<p><input type="submit" /></p>
	</fieldset>

</form>

<div id="awards_list">
	<?php if (isset($_GET['cy'])) $awardshelper->displayResults(); ?>
</div>


<script src="/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
	Osc.tab = 'Awards';

	// Sortable awards:
	$("#awards_table").tablesorter({
		debug: false,
		headers: { 
			1: {sorter: false}, 
			2: {sorter: false}, 
			5: {sorter: false}
		} 
	}); 
</script>


<?php include_once('../inc/footer.php');
