<?php
/**
 * Copyright (c) 2011 SadisticAndroid
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    RottenTomatoesApi
 * @author     SadisticAndroid <sadisticandroid@gmail.com>
 * @copyright  SadisticAndroid http://sadisticandroid.com
 * @license    MIT License
 */

/**
 * Rotten Tomatoes API
 *
 * @package RottenTomatoesApi
 */
class RottenTomatoesApi
{
	/**#@+
	 * Review Types
	 * 
	 * @var string
	 */
	const REVIEW_TOP_CRITIC = 'top_critic';
	const REVIEW_ALL = 'all';
	const REVIEW_DVD = 'dvd';
	/**#@-*/

	/**
	 * API URL
	 * 
	 * @var string
	 */
	private $root = 'http://api.rottentomatoes.com/api/public/v%s/';

	/**
	 * API Key
	 * 
	 * @var string
	 */
	private $key;

	/**
	 * Timeout
	 * 
	 * @var int
	 */
	private $timeout = 10;

	/**
	 * Country
	 * 
	 * @var string
	 */
	private $country = 'us';

	/**
	 * Error
	 * 
	 * @var string
	 */
	private $error = false;

	/**
	 * Initialize API
	 * 
	 * @param string $key Your Rotten Tomatoes API key.
	 * @param int $timeout Timeout for HTTP requests.
	 * @param string $version The API version. Defaults to 1.0.
	 * @param string $country The locale, if available, as per ISO 3166-1 alpha-2.
	 * @return RottenTomatoesApi
	 */
	public function __construct($key, $timeout = 10, $version = '1.0', $country = 'us')
	{
		$this->key = $key;
		$this->root = sprintf($this->root, $version);
		$this->timeout = (int) $timeout;
		$this->country = $country;
	}

	/**
	 * Search
	 * 
	 * @param string $q The search query.
	 * @param int $limit Movies per page. Defaults to 30.
	 * @param int $page The current page.
	 * @return array
	 */
	public function search($q, $limit = 30, $page = 0)
	{
		return $this->get('movies', array(
			'q'				=> $q,
			'page_limit'	=> (int) $limit,
			'page'			=> (int) $page,
		));
	}

	/**
	 * Search Feeling Lucky
	 * 
	 * @param string $q The search query.
	 * @return array
	 */
	public function searchFeelingLucky($q)
	{
		$results = $this->search($q, 1, 1); // added 1

		if (!isset($results['movies'][0]))
		{
			return false;
		}

		return $results['movies'][0];
	}

	/**
	 * List Movies Box Office
	 * 
	 * @param int $limit The number of items. Defaults to 10.
	 * @return array
	 */
	public function listMoviesBoxOffice($limit = 10)
	{
		return $this->get('lists/movies/box_office', array('limit' => (int) $limit));
	}

	/**
	 * List Movies In Theaters
	 * 
	 * @param int $limit The number of items. Defaults to 10.
	 * @param int $page The page number.
	 * @return array
	 */
	public function listMoviesInTheaters($limit = 10, $page = 0)
	{
		return $this->get('lists/movies/in_theaters', array(
			'page_limit'	=> (int) $limit,
			'page'			=> (int) $page,
		));
	}

	/**
	 * List Movies Opening
	 * 
	 * @param int $limit The number of items. Defaults to 10.
	 * @return array
	 */
	public function listMoviesOpening($limit = 10)
	{
		return $this->get('lists/movies/opening', array('limit' => (int) $limit));
	}

	/**
	 * List Movies Upcoming
	 * 
	 * @param int $limit The number of items. Defaults to 10.
	 * @param int $page The page number.
	 * @return array
	 */
	public function listMoviesUpcoming($limit = 10, $page = 0)
	{
		return $this->get('lists/movies/upcoming', array(
			'page_limit'	=> (int) $limit,
			'page'			=> (int) $page,
		));
	}

	/**
	 * List DVDs New Releases
	 * 
	 * @param int $limit The number of items. Defaults to 10.
	 * @param int $page The page number.
	 * @return array
	 */
	public function listDvdsNewReleases($limit = 10, $page = 0)
	{
		return $this->get('lists/dvds/new_releases', array(
			'page_limit'	=> (int) $limit,
			'page'			=> (int) $page,
		));
	}

	/**
	 * Movie
	 * 
	 * @param int $id The movie id.
	 * @param string $key The key to find, if not provided all movie data is returned.
	 * @return array
	 */
	public function movie($id, $key = false)
	{
		$response = $this->get("movies/$id");

		if ($key && isset($response[$key]))
		{
			return $response[$key];
		}

		return $response;
	}

	/**
	 * imdbMovie
	 * 
	 * @param int $id The movie's imdb id.
	 * @param string $key The key to find, if not provided all movie data is returned.
	 * @return array
	 */
	public function imdbMovie($imdb_id)
	{
		$response = $this->get("movie_alias", array(
			'id'	=> $imdb_id,
			'type'	=> 'imdb'
		));

		return $response;
	}

	/**
	 * Movie Similar
	 * 
	 * @param int $id The movie id.
	 * @param int $limit The number of items. Defaults to 5.
	 * @return array
	 */
	public function movieSimilar($id, $limit = 5)
	{
		if (!$response = $this->get("movies/$id/similar", array('limit' => $limit)))
		{
			return false;
		}

		return $response['movies'];
	}

	/**
	 * Movie Cast
	 * 
	 * @param int $id The movie id.
	 * @return array
	 */
	public function movieCast($id)
	{
		if (!$response = $this->get("movies/$id/cast"))
		{
			return false;
		}

		return $response['cast'];
	}

	/**
	 * Movie Reviews
	 * 
	 * @param int $id The movie id.
	 * @param string $type The review type.
	 * @param int $limit The number of items. Defaults to 10.
	 * @param int $page The page number.
	 * @return array
	 */
	public function movieReviews($id, $type = self::REVIEW_TOP_CRITIC, $limit = 10, $page = 0)
	{
		return $this->get("movies/$id/reviews", array(
			'review_type'	=> $type,
			'page_limit'	=> (int) $limit,
			'page'			=> (int) $page,
		));
	}

	/**
	 * Get API Resource
	 * 
	 * @param string $resource The API resouce, the part of the /api/public/v1.0/ without a .json extension.
	 * 						Ie: /api/public/v1.0/movies/770672991.json => movies/770672991
	 * @param array $data Additional data to pass to the query.
	 * @param array $headers Additional key->value pair of headers.
	 * @return array|bool Array of data or false if the call failed.
	 */
	public function get($resource, array $data = array(), array $headers = array())
	{
		// Full URL, usually from self-links in the API
		if (strpos($resource, 'http://') === false)
		{
			$root = $this->root;
		}
		else
		{
			$root = '';
		}

		$query = http_build_query($data + array('apikey' => $this->key, 'country' => $this->country));
		$response = trim($this->http("$root$resource.json?$query", $headers));

		if (!$response)
		{
			$this->error = 'No response (probably invalid API key)';

			return false;
		}

		if (!$response = json_decode($response, true))
		{
			$this->error = $this->getJsonError();

			return false;
		}

		if (isset($response['error']))
		{
			$this->error = $response['error'];

			return false;
		}

		return $response;
	}

	/**
	 * Is Error
	 * 
	 * @return bool
	 */
	public function isError()
	{
		return $this->error !== false;
	}

	/**
	 * Get Error
	 * 
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Is JSON Error
	 * 
	 * Can be usefull for determining the origin of your error
	 * or finding issues with the API.
	 * 
	 * This will return false no matter what on PHP versions before 5.3.
	 * 
	 * @return bool True if there is a JSON error.
	 */
	public function isJsonError()
	{
		return (bool) function_exists('json_last_error') ? json_last_error() : false;
	}

	/**
	 * Get JSON Error
	 * 
	 * This will return 'Unknown' no matter what on PHP versions before 5.3.
	 * 
	 * @return string The error message.
	 */
	public function getJsonError()
	{
		if (!function_exists('json_last_error'))
		{
			return 'Unknown';
		}

		switch (json_last_error())
		{
			case JSON_ERROR_NONE:
				return 'No error';

			case JSON_ERROR_DEPTH:
				return 'The maximum stack depth has been exceeded';

			case JSON_ERROR_CTRL_CHAR:
				return 'Control character error, possibly incorrectly encoded';

			case JSON_ERROR_STATE_MISMATCH:
				return 'Invalid or malformed JSON';

			case JSON_ERROR_SYNTAX:
				return 'Syntax error';
		}
	}


	/**
	 * Execute HTTP Request
	 * 
	 * @param string $url The url.
	 * @param array $headers Additional headers to send with the request.
	 * @return string|bool The response.
	 */
	private function http($url, array $headers = array())
	{
		$http = array(
			'method'		=> 'GET',
			'timeout'		=> $this->timeout,
		);

		// Set headers
		if (!empty($headers))
		{
			$http['header'] = join("\r\n", $headers);
		}

		// Create the stream context
		$context = stream_context_create(array('http' => $http));

		// Send it
		if (!$fp = @fopen($url, 'r', false, $context))
		{
			return false;
		}

		// Create the response
		return stream_get_contents($fp);
	}
}
