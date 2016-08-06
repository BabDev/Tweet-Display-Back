<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @copyright  Copyright (C) 2010-2016 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Bearer class for Tweet Display Back
 *
 * @since  3.1
 */
class BDBearer
{
	/**
	 * The time in seconds to cache the bearer token
	 *
	 * @var    integer
	 * @since  3.1
	 */
	private $cache_time = -1;

	/**
	 * JHttp connector
	 *
	 * @var    JHttp
	 * @since  3.1
	 */
	protected $connector;

	/**
	 * Module params
	 *
	 * @var    Registry
	 * @since  3.1
	 */
	protected $params;

	/**
	 * The bearer token
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $token;

	/**
	 * Constructor
	 *
	 * @param   Registry  $params     The module parameters
	 * @param   JHttp     $connector  JHttp connector
	 *
	 * @since   3.1
	 */
	public function __construct(Registry $params, JHttp $connector)
	{
		// Store the module params
		$this->params = $params;

		// Store the connector
		$this->connector = $connector;

		// Prepare the token
		$this->prepareToken();
	}

	/**
	 * Callback to fetch the bearer token
	 *
	 * @return  string
	 *
	 * @since   4.0
	 * @internal
	 */
	public function getToken()
	{
		switch ($this->params->get('token_source', 'consumer'))
		{
			case 'remote':
				return $this->callRemoteUrl();

			case 'consumer':
			default:
				return $this->callConsumer();
		}
	}

	/**
	 * Function to create the bearer authentication value for use in HTTP headers
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function prepareBearerAuth()
	{
		$ckey = rawurlencode($this->params->get('consumer_key', ''));
		$csec = rawurlencode($this->params->get('consumer_secret', ''));

		return ($ckey && $csec) ? base64_encode("{$ckey}:{$csec}") : '';
	}

	/**
	 * Function to convert the bearer_cache_time_unit and _qty into epoch seconds
	 *
	 * @return  integer  The time in seconds to cache the bearer token
	 *
	 * @since   3.1
	 */
	protected function cacheTime()
	{
		if ($this->cache_time < 0)
		{
			$this->prepareCacheTime();
		}

		return (int) $this->cache_time;
	}

	/**
	 * Function to retrieve a bearer token from Twitter's API using a consumer key
	 *
	 * @return  string
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	protected function callConsumer()
	{
		$auth = $this->prepareBearerAuth();

		if (!$auth)
		{
			throw new RuntimeException('Invalid consumer key/secret in configuration');
		}

		$url      = 'https://api.twitter.com/oauth2/token';
		$headers  = ['Authorization' => "Basic {$auth}"];
		$data     = 'grant_type=client_credentials';
		$response = $this->connector->post($url, $data, $headers);

		if ($response->code != 200)
		{
			throw new RuntimeException('Could not retrieve bearer token (consumer)');
		}

		return json_decode($response->body)->access_token;
	}

	/**
	 * Function to retrieve a bearer token from a remote URL
	 *
	 * @return  string
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	protected function callRemoteUrl()
	{
		$url = $this->params->get('remote_url', 'http://tdbtoken-v2.gopagoda.io/tokenRequest.php');

		// Call consumer or RemoteURL
		$response = $this->connector->get($url);

		if ($response->code != 200)
		{
			throw new RuntimeException('Could not retrieve bearer token (remote)');
		}

		return str_replace('Bearer ', '', base64_decode($response->body));
	}

	/**
	 * Function to convert the bearer_cache_time_unit and _qty into epoch seconds
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function prepareCacheTime()
	{
		$cacheTime = (int) $this->params->get('bearer_cache_time_qty', 1);
		$cacheUnit = $this->params->get('bearer_cache_time_unit', '');

		if (!$cacheUnit)
		{
			$cacheUnit = 'day';
			$cacheTime = 1;
		}

		switch ($cacheUnit)
		{
			case 'hour':
				$cacheTime *= 3600;
				break;

			case 'min':
				$cacheTime *= 60;
				break;

			case 'week':
				$cacheTime *= 86400 * 7;
				break;

			case 'day':
			default:
				$cacheTime *= 86400;
				break;
		}

		$this->cache_time = $cacheTime;
	}

	/**
	 * Function to obtain a bearer token, if none is cached
	 *
	 * @return  boolean  True if a bearer token is available, otherwise false
	 *
	 * @since   3.1
	 */
	protected function prepareToken()
	{
		// If we haven't retrieved the bearer yet, get it if in the site application
		if (($this->token == null) && JFactory::getApplication()->isSite())
		{
			// Get the cache controller
			/** @var JCacheControllerCallback $cache */
			$cache = JFactory::getCache('mod_tweetdisplayback', 'callback');

			// Bearer tokens are ALWAYS cached
			$cache->setCaching(true);

			// Set the lifetime to match the module params
			$cache->setLifeTime($this->cacheTime());

			// Generate a cache ID that we can reuse for all modules sharing this token source
			$source = $this->params->get('token_source', 'consumer');
			$url    = $this->params->get('remote_url', 'http://tdbtoken-v2.gopagoda.io/tokenRequest.php');

			$cacheId = md5("mod_tweetdisplayback_bearer_$source" . ($source == 'remote' ? "_$url" : ''));

			// If cache API throws a fatal error, let's work around it to try and keep the module functioning
			try
			{
				// As of 3.6 the callback controller doesn't accept Closures so we have to direct it to a public method here in the class
				$this->token = $cache->get([$this, 'getToken'], [], $cacheId);
			}
			catch (RuntimeException $e)
			{
				$this->token = $this->getToken();
			}
		}

		return !empty($this->token);
	}
}
