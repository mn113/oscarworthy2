<?php
// Localisation:
setlocale(LC_ALL, 'en_US.UTF8');
date_default_timezone_set('Europe/London');

//ini_set('sendmail_from', 'system@oscarworthy.com');
ini_set('sendmail_from', 'system@oscarworthy.co.uk');

// Turn this off on live server:
define('DEBUG_MODE', true);

// Constants:
define('SITE_URL', 'http://oscarworthy.local:8888/');
define('RT_APIKEY', 'ecn57mpdtgkpqm8mrn3betcw');
define('STATIC_SALT', '4PbZ2w0K');	// Crucial info for using passwords

// Images:
define('BLANK_HEADSHOT', '/img/headshot.png');
define('BLANK_POSTER', '/img/blankposter.png');
define('TMDB_ICON', '<img src="/img/icons/tmdb.png" />');
define('CRIT_ICON', '<img src="/img/icons/crit.png" />');
define('IMDB_ICON', '<img src="/img/icons/imdb.png" />');
define('ROTT_ICON', '<img src="/img/icons/rott.png" />');

// Predefined messages:
define('MSG_ALREADY_LOGGED', 'You are already logged in.');
define('MSG_WELCOME_BACK', 'Welcome back');
define('MSG_RATE_SUCCESS', 'Got it, thanks for rating.');
define('MSG_RATE_ERROR', "Epic fail happened trying to rate that.");

// Error handler:
require_once('errorhandler/ErrorHandler.php');
define('FATAL', E_USER_ERROR);
define('ERROR', E_USER_WARNING);
define('WARNING', E_USER_NOTICE);
$errorHandler = new ErrorHandler(DEBUG_MODE);

// Composer dependencies autoload:
require_once('vendor/autoload.php');

// Includes:
require_once('db.inc.php');
include_once('inc/functions.php');
include_once('FirePHPCore/FirePHP.class.php');
include_once('FirePHPCore/fb.php');
include_once('securimage/securimage.php');
include_once('apis/RottenTomatoesApi.php');

// Class autoloader:
spl_autoload_register(function ($class_name) {
	$paths = array('/inc/classes/class.', '/inc/classes/static/staticclass.', '/inc/classes/core/class.');
	foreach ($paths as $path) {
		$file = $_SERVER{'DOCUMENT_ROOT'}.$path.$class_name.".inc.php";
		if (file_exists($file)) {
			require($file);
			return;
		}
	}
});

// Logging with Monolog:
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
// create a log channel
$logger = new Logger('all');
$logger->pushHandler(new StreamHandler(__DIR__ . '/logs/tmdb_site.log', Logger::WARNING));
$logger->pushHandler(new FirePHPHandler());
//throw new Exception($logger->name, 1);
// You can now use your logger
$logger->addInfo('My logger is now ready');
// add records to the log
//$logger->addWarning('Foo');
//$logger->addError('Bar');


// Script particularities:
$base = basename($_SERVER['SCRIPT_NAME'], '.php');
$qs = urlencode($_SERVER['QUERY_STRING']);

// Obfuscate server:
header('Server: ');			// NOT WORKING
header('X-Powered-By: ');

// Don't let client cache SSL pages:
if (isset($_SERVER['HTTPS'])) {
	$ts = gmdate("D, d M Y H:i:s") . " GMT";	// GIVES MENTAL 1981 DATE
	header("Expires: $ts");
	header("Last-Modified: $ts");
	header("Pragma: no-cache");
	header("Cache-Control: no-cache, must-revalidate");
}

// FirePHP init:
ob_start();

// Always enable session use (ajax workers need it too):
session_start();
