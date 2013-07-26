<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @package    TweetDisplayBack
 *
 * @copyright  Copyright (C) 2010-2013 Michael Babker. All rights reserved.
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

/**
 * Helper class for Tweet Display Back
 *
 * @package  TweetDisplayBack
 * @since    1.0
 */
class ModTweetDisplayBackHelper
{
	/**
	 * OAuth bearer token for use in API requests
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $bearer;

	/**
	 * BDHttp connector
	 *
	 * @var    BDHttp
	 * @since  3.0
	 */
	protected $http;

	/**
	 * Flag to determine whether data is cached or to load fresh
	 *
	 * @var    boolean
	 * @since  3.0
	 */
	public $isCached = false;

	/**
	 * Flag to determine whether data has been fully processed
	 *
	 * @var    boolean
	 * @since  3.0
	 */
	public $isProcessed = false;

	/**
	 * ID of the currently active module
	 *
	 * @var    integer
	 * @since  3.0
	 */
	public $moduleId;

	/**
	 * Module parameters
	 *
	 * @var    JRegistry
	 * @since  3.0
	 */
	protected $params;

	/**
	 * Container for the tweet response object
	 *
	 * @var    object
	 * @since  3.0
	 */
	public static $tweets;

	/**
	 * Container for the formatted module data
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $twitter = array();

	/**
	 * Container for the user profile response object
	 *
	 * @var    array
	 * @since  3.0
	 */
	public static $user;

	/**
	 * Constructor
	 *
	 * @param   JRegistry  $params  The module parameters
	 *
	 * @since   3.0
	 */
	public function __construct($params)
	{
		// Store the module params
		$this->params = $params;

		// Start setting up the BDHttp connector
		$transport = null;

		// Set up our JRegistry object for the BDHttp connector
		$options = new JRegistry;

		// Set the user agent
		$options->set('userAgent', 'TweetDisplayBack/3.0');

		// Use a 30 second timeout
		$options->set('timeout', 30);

		// Include the BabDev library
		JLoader::registerPrefix('BD', __DIR__ . '/libraries');

		// If the user has forced a specific connector, use it, otherwise allow BDHttpFactory to decide
		$connector = $this->params->get('overrideConnector', null);

		// If the override is 'no', set to null
		if ($connector == 'no')
		{
			$connector = null;
		}

		// Instantiate our BDHttp object
		$this->connector = BDHttpFactory::getHttp($options, $connector);
	}

	/**
	 * Function to compile the data to render a formatted object displaying a Twitter feed
	 *
	 * @return  object  An object with the formatted tweets
	 *
	 * @since   1.5
	 */
	public function compileData()
	{
		// Load the parameters
		$uname   = $this->params->get('twitterName', '');
		$list    = $this->params->get('twitterList', '');
		$count   = $this->params->get('twitterCount', 3);
		$retweet = $this->params->get('tweetRetweets', 1);
		$feed    = $this->params->get('twitterFeedType', 'user');

		// Convert the list name to a usable string for the JSON
		if ($list)
		{
			$flist = static::toAscii($list);
		}

		// Get the user info
		$this->prepareUser();

		// Check to see if we have an error
		if (isset($this->twitter['error']))
		{
			return $this->twitter;
		}

		// Set the include RT's string
		$incRT = '';

		if ($retweet == 1)
		{
			$incRT = '&include_rts=1';
		}

		// Count the number of active filters
		$activeFilters = 0;

		// Mentions
		if ($this->params->get('showMentions', 0) == 0)
		{
			$activeFilters++;
		}

		// Replies
		if ($this->params->get('showReplies', 0) == 0)
		{
			$activeFilters++;
		}

		// Retweets
		if ($retweet == 0)
		{
			$activeFilters++;
		}

		// Determine whether the feed being returned is a user, favorites, or list feed
		if ($feed == 'list')
		{
			// Get the list feed
			$req = 'https://api.twitter.com/1.1/lists/statuses.json?slug=' . $flist . '&owner_screen_name=' . $uname . $incRT . '&include_entities=1';
		}
		elseif ($feed == 'favorites')
		{
			// Get the favorites feed
			$req = 'https://api.twitter.com/1.1/favorites/list.json?count=' . $count . '&screen_name=' . $uname . '&include_entities=1';
		}
		else
		{
			/*
			 * Get the user feed, we have to manually filter mentions, RTs and replies,
			 * so get additional tweets by multiplying $count based on the number
			 * of active filters
			 */
			if ($activeFilters == 1)
			{
				$count = $count * 3;
			}
			elseif ($activeFilters == 2)
			{
				$count = $count * 4;
			}
			elseif ($activeFilters == 3)
			{
				$count = $count * 5;
			}

			/*
			 * Determine whether the user has overridden the count parameter with a
			 * manual number of tweets to retrieve.  Override the $count variable
			 * if this is the case
			 */
			if ($this->params->get('overrideCount', 1) == 1)
			{
				$count = $this->params->get('tweetsToScan', 3);
			}

			$req = 'https://api.twitter.com/1.1/statuses/user_timeline.json?count=' . $count . '&screen_name=' . $uname . '&include_entities=1';
		}

		// Fetch the decoded JSON
		try
		{
			$obj = $this->getJSON($req);
		}
		catch (RuntimeException $e)
		{
			$this->twitter['error'] = '';

			return $this->twitter;
		}

		// Check if we've reached an error
		if (isset($obj->errors))
		{
			$this->twitter['error'] = array();
			$this->twitter['error']['messages'] = array();

			foreach ($obj->errors as $error)
			{
				$this->twitter['error']['messages'][] = $error->message;
			}

			return $this->twitter;
		}
		// Make sure we've got an array of data
		elseif (is_array($obj))
		{
			// Store the twitter stream response object
			static::$tweets = $obj;

			// Check if $obj has data; if not, return an error
			if (is_null($obj))
			{
				// Set an error
				$this->twitter[0]->tweet->text = JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');
			}
			else
			{
				// If caching is enabled and we aren't using cached data, json_encode the object and write it to file
				if ($this->params->get('cache') == 1)
				{
					$data = json_encode($obj);
					file_put_contents(JPATH_CACHE . '/tweetdisplayback_tweets-' . $this->moduleId . '.json', $data);
				}

				// Process the filtering options and render the feed
				$this->processFiltering();

				// Flag that processing was successful
				$this->isProcessed = true;
			}
		}
		else
		{
			$this->twitter['error'] = '';
		}

		return $this->twitter;
	}

	/**
	 * Function to compile the data from cache and format the object
	 *
	 * @return  object  An object with the formatted tweets
	 *
	 * @since   1.5
	 */
	public function compileFromCache()
	{
		// Reset the $twitter object in case we errored out previously
		$this->twitter = array();

		// Get the user info
		$this->prepareUser();

		// Check to see if we have an error or are out of hits
		if (isset($this->twitter['error']) || isset($this->twitter['hits']))
		{
			return $this->twitter;
		}

		// Retrieve the cached data and decode it
		$obj = file_get_contents(JPATH_CACHE . '/tweetdisplayback_tweets-' . $this->moduleId . '.json');
		$obj = json_decode($obj);

		// Check if we've reached an error
		if (isset($obj->errors))
		{
			$this->twitter['error'] = array();
			$this->twitter['error']['messages'] = array();

			foreach ($obj->errors as $error)
			{
				$this->twitter['error']['messages'][] = $error->message;
			}
		}
		// Make sure we've got an array of data
		elseif (is_array($obj))
		{
			// Store the twitter stream response object
			static::$tweets = $obj;

			// Check if $obj has data; if not, return an error
			if (is_null($obj))
			{
				// Set an error
				$this->twitter[0]->tweet->text = JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');
			}
			else
			{
				// Process the filtering options and render the feed
				$this->processFiltering();

				// Flag that processing was successful
				$this->isProcessed = true;
			}
		}
		else
		{
			$this->twitter['error'] = '';
		}

		return $this->twitter;
	}

	/**
	 * Function to fetch a JSON feed
	 *
	 * @param   string  $req  The URL of the feed to load
	 *
	 * @return  object  The fetched JSON query
	 *
	 * @since   1.0
	 * @throws  RuntimeException
	 */
	public function getJSON($req)
	{
		// Get the data
		try
		{
			// If we haven't retrieved the bearer yet, get it if in the site application
			if (($this->bearer == null) && JFactory::getApplication()->isSite())
			{
				$cacheFile = JPATH_CACHE . '/tweetdisplayback_bearer.json';

				// Check if we have cached data and use it if unexpired
				if (!file_exists($cacheFile) || (time() - @filemtime($cacheFile) > 86400))
				{
					$response = $this->connector->get('http://tdbtoken.gopagoda.com/tokenRequest.php');

					if ($response->code == 200)
					{
						$this->bearer = base64_decode($response->body);
					}
					else
					{
						throw new RuntimeException('Could not retrieve bearer token');
					}

					// Write the cache
					file_put_contents($cacheFile, $this->bearer);
				}
				else
				{
					// Render from the cached data
					$this->bearer = file_get_contents($cacheFile);
				}

			}

			$headers = array(
				'Authorization' => $this->bearer
			);

			$response = $this->connector->get($req, $headers);
		}
		catch (Exception $e)
		{
			return null;
		}

		// Return the decoded response body
		return json_decode($response->body);
	}

	/**
	 * Function to fetch the user JSON and render it
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	protected function prepareUser()
	{
		$scheme = JUri::getInstance()->getScheme() . '://';

		// Load the parameters
		$uname = $this->params->get('twitterName', '');
		$list  = $this->params->get('twitterList', '');
		$feed  = $this->params->get('twitterFeedType', 'user');

		// Initialize object containers
		$this->twitter['header'] = new stdClass;
		$this->twitter['footer'] = new stdClass;
		$this->twitter['tweets'] = new stdClass;

		// Convert the list name to a usable string for the URL
		if ($list)
		{
			$flist = static::toAscii($list);
		}

		// Retrieve data from Twitter if the header is enabled
		if ($this->params->get('headerDisplay', 1) == 1)
		{
			// Sanity check on user file cache
			$cacheFile = JPATH_CACHE . '/tweetdisplayback_user-' . $this->moduleId . '.json';
			$cacheTime = $this->params->get('cache_time', 15);
			$cacheTime = $cacheTime * 60;

			// Get the data
			if ($this->isCached && (!file_exists($cacheFile) || time() - @filemtime($cacheFile) > $cacheTime))
			{
				// Fetch from cache
				$obj = file_get_contents(JPATH_CACHE . '/tweetdisplayback_user-' . $this->moduleId . '.json');
				$obj = json_decode($obj);
			}
			else
			{
				$req = 'https://api.twitter.com/1.1/users/show.json?screen_name=' . $uname;

				try
				{
					$obj = $this->getJSON($req);
				}
				catch (RuntimeException $e)
				{
					$this->twitter['error'] = '';

					return;
				}

				// Check if we've reached an error
				if (isset($obj->errors))
				{
					$this->twitter['error'] = array();
					$this->twitter['error']['messages'] = array();

					foreach ($obj->errors as $error)
					{
						$this->twitter['error']['messages'][] = $error->message;
					}

					return;
				}
				// Check that we have the JSON, otherwise set an error
				elseif (!$obj)
				{
					$this->twitter['error'] = '';

					return;
				}

				// Store the user profile response object so it can be accessed (for advanced use)
				static::$user = $obj;

				// If caching is enabled and we aren't using cached data, json_encode the object and write it to file
				if ($this->params->get('cache') == 1 && !$this->isCached)
				{
					$data = json_encode($obj);
					file_put_contents(JPATH_CACHE . '/tweetdisplayback_user-' . $this->moduleId . '.json', $data);
				}
			}
		}

		/*
		 * Header info
		 */
		if ($this->params->get('headerDisplay', 1) == 1)
		{
			if ($this->params->get('headerUser', 1) == 1)
			{
				// Check if the Intents action is bypassed
				if ($this->params->get('bypassIntent', '0') == 1)
				{
					$this->twitter['header']->user = '<a href="' . $scheme . 'twitter.com/' . $uname . '" rel="nofollow" target="_blank">';
				}
				else
				{
					$this->twitter['header']->user = '<a href="' . $scheme . 'twitter.com/intent/user?screen_name=' . $uname . '" rel="nofollow">';
				}

				// Show the real name or the username
				if ($this->params->get('headerName', 1) == 1)
				{
					$this->twitter['header']->user .= $obj->name . '</a>';
				}
				else
				{
					$this->twitter['header']->user .= $uname . '</a>';
				}

				// Append the list name if being pulled
				if ($feed == 'list')
				{
					$this->twitter['header']->user .= ' - <a href="' . $scheme .'twitter.com/' . $uname . '/' . $flist . '" rel="nofollow">' . $list . ' list</a>';
				}
			}

			// Show the bio
			if ($this->params->get('headerBio', 1) == 1)
			{
				$this->twitter['header']->bio = $obj->description;
			}

			// Show the location
			if ($this->params->get('headerLocation', 1) == 1)
			{
				$this->twitter['header']->location = $obj->location;
			}

			// Show the user's URL
			if ($this->params->get('headerWeb', 1) == 1)
			{
				$this->twitter['header']->web = '<a href="' . $obj->url . '" rel="nofollow" target="_blank">' . $obj->url . '</a>';
			}

			// Get the profile image URL from the object
			$avatar = $obj->profile_image_url;

			// Switch from the normal size avatar (48px) to the large one (73px)
			$avatar = str_replace('normal.jpg', 'bigger.jpg', $avatar);

			$this->twitter['header']->avatar = '<img src="' . $avatar . '" alt="' . $uname . '" />';
		}

		/*
		 * Footer info
		 */

		// Display the Follow button
		if ($this->params->get('footerFollowLink', 1) == 1)
		{
			// Don't display for a list feed
			if ($feed != 'list')
			{
				$followParams  = 'screen_name=' . $uname;
				$followParams .= '&lang=' . substr(JFactory::getLanguage()->getTag(), 0, 2);

				if ($this->params->get('footerFollowCount', '1') == '1')
				{
					$followParams .= '&show_count=true';
				}
				else
				{
					$followParams .= '&show_count=false';
				}

				$followParams .= '&show_screen_name=' . (bool) $this->params->get('footerFollowUser', 1);

				$iframe = '<iframe allowtransparency="true" frameborder="0" scrolling="no" src="' . $scheme . 'platform.twitter.com/widgets/follow_button.html?' . $followParams . '" style="width: 300px; height: 20px;"></iframe>';

				$this->twitter['footer']->follow_me = '<div class="TDB-footer-follow-link">' . $iframe . '</div>';
			}
		}

		if ($this->params->get('footerPoweredBy', 1) == 1)
		{
			$site = '<a href="http://www.babdev.com/extensions/tweet-display-back" rel="nofollow" target="_blank">' . JText::_('MOD_TWEETDISPLAYBACK') . '</a>';
			$this->twitter['footer']->powered_by = '<hr /><div class="TDB-footer-powered-text">' . JText::sprintf('MOD_TWEETDISPLAYBACK_POWERED_BY', $site) . '</div>';
		}
	}

	/**
	 * Function to render the Twitter feed into a formatted object
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function processFiltering()
	{
		// Initialize
		$count          = $this->params->get('twitterCount', 3);
		$showMentions   = $this->params->get('showMentions', 0);
		$showReplies    = $this->params->get('showReplies', 0);
		$showRetweets   = $this->params->get('tweetRetweets', 1);
		$numberOfTweets = $this->params->get('twitterCount', 3);
		$feedType       = $this->params->get('twitterFeedType', 'user');
		$obj            = static::$tweets;
		$i              = 0;

		// Process the feed
		foreach ($obj as $o)
		{
			if ($count > 0)
			{
				// Check if we have all of the items we want
				if ($i < $numberOfTweets)
				{
					// If we aren't filtering, just render the item
					if (($showMentions == 1 && $showReplies == 1 && $showRetweets == 1) || ($feedType == 'list' || $feedType == 'favorites'))
					{
						$this->processItem($o, $i);

						// Modify counts
						$count--;
						$i++;
					}

					// We're filtering, the fun starts here
					else
					{
						// Set variables
						// Tweets which contains a @reply
						$tweetContainsReply = $o->in_reply_to_user_id != null;

						// Tweets which contains a @mention and/or @reply
						$tweetContainsMentionAndOrReply = $o->entities->user_mentions != null;

						// Tweets which are a retweet
						$tweetIsRetweet = isset($o->retweeted_status);

						/*
						 * Check if a reply tweet contains mentions
						 * NOTE: Works only for tweets where there is also a reply, since reply is at
						 * the position ['0'] and mentions begin at ['1'].
						 */
						if (isset($o->entities->user_mentions['1']))
						{
							$replyTweetContainsMention = $o->entities->user_mentions['1'];
						}
						else
						{
							$replyTweetContainsMention = '0';
						}

						// Tweets with only @reply
						$tweetOnlyReply = $tweetContainsReply && $replyTweetContainsMention == '0';

						// Tweets which contains @mentions or @mentions+@reply
						$tweetContainsMention = $tweetContainsMentionAndOrReply && !$tweetOnlyReply;

						// Filter retweets
						if ($showRetweets == 0)
						{
							if (!$tweetIsRetweet)
							{
								$this->processItem($o, $i);

								// Modify counts
								$count--;
								$i++;
							}
						}

						// Filter @mentions and @replies, leaving retweets unchanged
						if ($showMentions == 0 && $showReplies == 0)
						{
							if (!$tweetContainsMentionAndOrReply || isset($o->retweeted_status))
							{
								$this->processItem($o, $i);

								// Modify counts
								$count--;
								$i++;
							}
						}

						// Filtering only @mentions or @replies
						else
						{
							// Filter @mentions only leaving @replies and retweets unchanged
							if ($showMentions == 0)
							{
								if (!$tweetContainsMention || isset($o->retweeted_status))
								{
									$this->processItem($o, $i);

									// Modify counts
									$count--;
									$i++;
								}
							}

							// Filter @replies only (including @replies with @mentions) leaving retweets unchanged
							if ($showReplies == 0)
							{
								if (!$tweetContainsReply)
								{
									$this->processItem($o, $i);

									// Modify counts
									$count--;
									$i++;
								}
							}

							// Somehow, we got this far; process the tweet
							if ($showMentions == 1 && $showReplies == 1 && $showRetweets == 1)
							{
								// No filtering required
								$this->processItem($o, $i);

								// Modify counts
								$count--;
								$i++;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Function to process the Twitter feed into a formatted object
	 *
	 * @param   object   $o  The item within the JSON feed
	 * @param   integer  $i  Iteration of processFiltering
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function processItem($o, $i)
	{
		$scheme = JUri::getInstance()->getScheme() . '://';

		// Set variables
		$tweetName    = $this->params->get('tweetName', 1);
		$tweetReply   = $this->params->get('tweetReply', 1);
		$tweetRTCount = $this->params->get('tweetRetweetCount', 1);

		// Initialize a new object
		$this->twitter['tweets']->$i = new stdClass;

		// Check if the item is a retweet, and if so gather data from the retweeted_status datapoint
		if (isset($o->retweeted_status))
		{
			// Retweeted user
			$tweetedBy = $o->retweeted_status->user->screen_name;
			$avatar    = $o->retweeted_status->user->profile_image_url;
			$text      = $o->retweeted_status->text;
			$urls      = $o->retweeted_status->entities->urls;
			$RTs       = $o->retweeted_status->retweet_count;
			$this->twitter['tweets']->$i->created = JText::_('MOD_TWEETDISPLAYBACK_RETWEETED');

			if (isset($o->retweeted_status->entities->media))
			{
				$media = $o->retweeted_status->entities->media;
			}
		}
		else
		{
			// User
			$tweetedBy = $o->user->screen_name;
			$avatar    = $o->user->profile_image_url;
			$text      = $o->text;
			$urls      = $o->entities->urls;
			$RTs       = $o->retweet_count;

			if (isset($o->entities->media))
			{
				$media = $o->entities->media;
			}
		}

		// Generate the object with the user data
		if ($tweetName == 1)
		{
			// Check if the Intents action is bypassed
			if ($this->params->get('bypassIntent', '0') == 1)
			{
				$userURL = $scheme . 'twitter.com/' . $tweetedBy;
			}
			else
			{
				$userURL = $scheme . 'twitter.com/intent/user?screen_name=' . $tweetedBy;
			}
			$this->twitter['tweets']->$i->user = '<strong><a href="' . $userURL . '" rel="nofollow">' . $tweetedBy . '</a>' . $this->params->get('tweetUserSeparator') . '</strong>';
		}

		$this->twitter['tweets']->$i->avatar = '<img alt="' . $tweetedBy . '" src="' . $avatar . '" width="32" />';
		$this->twitter['tweets']->$i->text = $text;

		// Make regular URLs in tweets a link
		foreach ($urls as $url)
		{
			if (isset($url->display_url))
			{
				$d_url = $url->display_url;
			}
			else
			{
				$d_url = $url->url;
			}

			// We need to check to verify that the URL has the protocol, just in case
			if (strpos($url->url, 'http') !== 0)
			{
				// Prepend http since there's no protocol
				$link = 'http://' . $url->url;
			}
			else
			{
				$link = $url->url;
			}

			$this->twitter['tweets']->$i->text = str_replace($url->url, '<a href="' . $link . '" target="_blank" rel="nofollow">' . $d_url . '</a>', $this->twitter['tweets']->$i->text);
		}

		// Make media URLs in tweets a link
		if (isset($media))
		{
			foreach ($media as $image)
			{
				if (isset($image->display_url))
				{
					$i_url = $image->display_url;
				}
				else
				{
					$i_url = $image->url;
				}

				$this->twitter['tweets']->$i->text = str_replace($image->url, '<a href="' . $image->url . '" target="_blank" rel="nofollow">' . $i_url . '</a>', $this->twitter['tweets']->$i->text);
			}
		}

		/*
		 * Info below is specific to each tweet, so it isn't checked against a retweet
		 */

		// Display the time the tweet was created
		if ($this->params->get('tweetCreated', 1) == 1)
		{
			$this->twitter['tweets']->$i->created .= '<a href="' . $scheme . 'twitter.com/' . $o->user->screen_name . '/status/' . $o->id_str . '" rel="nofollow" target="_blank">';

			// Determine whether to display the time as a relative or static time
			if ($this->params->get('tweetRelativeTime', 1) == 1)
			{
				$time = JFactory::getDate($o->created_at, 'UTC');
				$this->twitter['tweets']->$i->created .= JHtml::_('date.relative', $time, null, JFactory::getDate('now', 'UTC')) . '</a>';
			}
			else
			{
				$this->twitter['tweets']->$i->created .= JHtml::date($o->created_at) . '</a>';
			}
		}

		// Display the tweet source
		if ($this->params->get('tweetSource', 1) == 1)
		{
			$this->twitter['tweets']->$i->created .= JText::sprintf('MOD_TWEETDISPLAYBACK_VIA', $o->source);
		}

		// Display the location the tweet was made from if set
		if (($this->params->get('tweetLocation', 1) == 1) && (isset($o->place->full_name)))
		{
			$this->twitter['tweets']->$i->created .= JText::_('MOD_TWEETDISPLAYBACK_FROM') . '<a href="' . $scheme . 'maps.google.com/maps?q=' . $o->place->full_name . '" target="_blank" rel="nofollow">' . $o->place->full_name . '</a>';
		}

		// If the tweet is a reply, display a link to the tweet it's in reply to
		if ((($o->in_reply_to_screen_name) && ($o->in_reply_to_status_id_str)) && $this->params->get('tweetReplyLink', 1) == 1)
		{
			$this->twitter['tweets']->$i->created .= JText::_('MOD_TWEETDISPLAYBACK_IN_REPLY_TO') . '<a href="' . $scheme . 'twitter.com/' . $o->in_reply_to_screen_name . '/status/' . $o->in_reply_to_status_id_str . '" rel="nofollow">' . $o->in_reply_to_screen_name . '</a>';
		}

		// Display a separator bullet if there's a tweet time/source and a retweet count
		if ((($this->params->get('tweetSource', 1) == 1)
			|| (($this->params->get('tweetLocation', 1) == 1) && (isset($o->place->full_name)))
			|| ((($o->in_reply_to_screen_name) && ($o->in_reply_to_status_id_str)) && $this->params->get('tweetReplyLink', 1) == 1))
			&& (($tweetRTCount == 1) && ($RTs >= 1)))
		{
			$this->twitter['tweets']->$i->created .= ' &bull; ';
		}

		// Display the number of times the tweet has been retweeted
		if (($tweetRTCount == 1) && ($RTs >= 1))
		{
			$this->twitter['tweets']->$i->created .= JText::plural('MOD_TWEETDISPLAYBACK_RETWEETS', $RTs);
		}

		// Display Twitter Actions
		if ($tweetReply == 1)
		{
			$this->twitter['tweets']->$i->actions = '<span class="TDB-action TDB-reply"><a href="' . $scheme . 'twitter.com/intent/tweet?in_reply_to=' . $o->id_str . '" title="' . JText::_('MOD_TWEETDISPLAYBACK_INTENT_REPLY') . '" rel="nofollow"></a></span>';
			$this->twitter['tweets']->$i->actions .= '<span class="TDB-action TDB-retweet"><a href="' . $scheme . 'twitter.com/intent/retweet?tweet_id=' . $o->id_str . '" title="' . JText::_('MOD_TWEETDISPLAYBACK_INTENT_RETWEET') . '" rel="nofollow"></a></span>';
			$this->twitter['tweets']->$i->actions .= '<span class="TDB-action TDB-favorite"><a href="' . $scheme . 'twitter.com/intent/favorite?tweet_id=' . $o->id_str . '" title="' . JText::_('MOD_TWEETDISPLAYBACK_INTENT_FAVORITE') . '" rel="nofollow"></a></span>';
		}

		// If set, convert user and hash tags into links
		if ($this->params->get('tweetLinks', 1) == 1)
		{
			foreach ($o->entities->user_mentions as $mention)
			{
				// Check if the Intents action is bypassed
				if ($this->params->get('bypassIntent', '0') == 1)
				{
					$mentionURL = $scheme . 'twitter.com/' . $mention->screen_name;
				}
				else
				{
					$mentionURL = $scheme . 'twitter.com/intent/user?screen_name=' . $mention->screen_name;
				}
				$this->twitter['tweets']->$i->text = str_ireplace('@' . $mention->screen_name, '@<a class="userlink" href="' . $mentionURL . '" rel="nofollow">' . $mention->screen_name . '</a>', $this->twitter['tweets']->$i->text);
			}

			foreach ($o->entities->hashtags as $hashtag)
			{
				$this->twitter['tweets']->$i->text = str_ireplace('#' . $hashtag->text, '#<a class="hashlink" href="' . $scheme . 'twitter.com/search?q=%23' . $hashtag->text . '" target="_blank" rel="nofollow">' . $hashtag->text . '</a>', $this->twitter['tweets']->$i->text);
			}
		}
	}

	/**
	 * Function to convert a formatted list name into it's URL equivalent
	 *
	 * @param   string  $list  The user inputted list name
	 *
	 * @return  string  The list name converted
	 *
	 * @since   1.6
	 */
	public static function toAscii($list)
	{
		$clean = preg_replace("/[^a-z'A-Z0-9\/_|+ -]/", '', $list);
		$clean = strtolower(trim($clean, '-'));
		$list  = preg_replace("/[\/_|+ -']+/", '-', $clean);

		return $list;
	}
}
