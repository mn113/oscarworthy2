<?php
require_once('../config.php');
require_once('../inc/initpage.php');

// Check session state:
if(!User::isLogged()) {
	header("Location: /login/");
}

// Starting HTML:
$page_title = 'Profile';
include_once('../inc/header.php');
include_once('../inc/profile_menu.php'); ?>

<h2>Profile: <?php echo $_SESSION['user']->username; ?></h2>

<?php
	$profile = new Profile($_SESSION['uid'], $dbh, $tmdb);
	$profile->displayVotes();
	
	// debug:
	echo '<hr />ratedFilms';
	print_r(array_unique($profile->rated_films));
	echo '<hr />ratedPeople';
	print_r(array_unique($profile->rated_people));
	echo '<hr />';
?>

<h3>You might like these films:</h3>
<?php echo $profile->suggestFilms(); ?>

<?php include_once('../inc/footer.php'); ?>