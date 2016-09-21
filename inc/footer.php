</div><!-- end #main -->
</div><!-- end #page -->
</div><!-- end #outer -->

<footer>
	<div id="footer_inner" class="cf">
		<div class="column col1">
			<h4>Sitemap</h4>
			<ul>
				<li><a href="/">Home</a></li>
				<li><a href="/film/">Films</a></li>
				<li><a href="/actors/">Actors</a></li>
				<li><a href="/awards/">Awards</a></li>
				<li><a href="/search/">Search</a></li>
				<li><a href="/about/">About</a></li>
				<li><a href="/contact/">Contact</a></li>
				<?php if(!User::isLogged()) echo '<li><a href="/register/:SSL">Register</a></li>'; ?>
				<?php if(!User::isLogged()) echo '<li><a href="/login/":SSL>Login</a></li>'; ?>
				<?php if(User::isLogged()) echo '<li><a href="/profile/">Profile</a></li>'; ?>
				<?php if(User::isLogged()) echo '<li><a href="/logout/">Logout</a></li>'; ?>
			</ul>
		</div>
		<div class="column col2">
			<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
			<p class="copyright">Copyright &copy; <?php echo date('Y'); ?></p>
		</div>
		<div class="column credits">
		</div>
	</div>
</footer>

<script src="/js/jquery.rateit.min.js"></script>

<script type="text/javascript">
	<?php
	// If our URL lacks a permalink, append one with HTML5-js:
	if ($page == 'film' || $page == 'person') {
		$url = $_SERVER['REQUEST_URI'];		// MODERNIZR ERROR IF TRAILING # RETAINED?
		FB::log($url, 'url');
		if (preg_match('|/[0-9]+/?$|', $url)) {
			// Lop trailing slash:
			$url = preg_replace('|/$|','', $url);
			// Modify URL:
			echo "if (Modernizr.history) {";
			echo "window.history.pushState('', 'Permalink', '$url/'+Osc.permalink+'/');";
			echo "}";
			echo "console.log('perma-append:', Osc.permalink);";
		}
	}
	?>
</script>

</body>
</html>
<?php
// Cache the page, if turned on earlier:
if (isset($cache)) $cache->create();

// Debug messages:
if (DEBUG_MODE) {
	//FB::log($_SERVER, '$_SERVER');
	//FB::log($_SESSION, '$_SESSION');
	//FB::log($_COOKIE, '$_COOKIE');
	//if (isset($_SESSION['user'])) FB::log($_SESSION['user'], '$_SESSION[user]');

}