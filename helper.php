<?php
/**
* Tweet Display Back Module for Joomla!
*
* @package    TweetDisplayBack
*
* @copyright  Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;

/**
 * Helper class for Tweet Display Back
 *
 * @package  TweetDisplayBack
 * @since    1.0.0
 */
class ModTweetDisplayBackHelper
{
	/**
	 * Function to compile the data to render a formatted object displaying a Twitter feed
	 *
	 * @param   object  $params  The module parameters
	 *
	 * @return  object  $twitter  A formatted object with the requested tweets
	 *
	 * @since   1.6.0
	 */
	static function compileData($params)
	{
		// Check the number of hits available
		$hits = self::getLimit($params);
		if ($hits == 0)
		{
			$twitter->hits	= '';
			return $twitter;
		}

		// Load the parameters
		$uname		= $params->get('twitterName', '');
		$list		= $params->get('twitterList', '');
		$count		= $params->get('twitterCount', 3);
		$retweet	= $params->get('tweetRetweets', 1);

		// Convert the list name to a useable string for the JSON
		if ($list)
		{
			$flist = self::toAscii($list);
		}

		// Initialize the array
		$twitter = array();

		// Get the user info
		$twitter = self::prepareUser($params);

		// Check to see if we have an error
		if (isset ($twitter->error))
		{
			return $twitter;
		}

		// Set the include RT's string
		$incRT	= '';
		if ($retweet == 1)
		{
			$incRT = '&include_rts=1';
		}

		// Count the number of active filters
		$activeFilters = 0;
		// Mentions
		if ($params->get('showMentions', 0) == 0)
		{
			$activeFilters++;
		}
		// Replies
		if ($params->get('showReplies', 0) == 0)
		{
			$activeFilters++;
		}
		// Retweets
		if ($retweet == 0)
		{
			$activeFilters++;
		}

		// Determine whether the feed being returned is a user or list feed
		if ($params->get('twitterFeedType', 0) == 1)
		{
			// Get the list feed
			$req = 'http://api.twitter.com/1/lists/statuses.json?slug='.$flist.'&owner_screen_name='.$uname.$incRT.'&include_entities=1';
		}
		else
		{
			/* Get the user feed, we have to manually filter mentions and replies,
			 * & Twitter doesn't send additional tweets when RTs are not included
			 * So get additional tweets by multiplying $count based on the number
			 * of active filters
			 */
			if ($activeFilters == 1)
			{
				$count = $count * 3;
			}
			else if ($activeFilters == 2)
			{
				$count = $count * 4;
			}
			else if ($activeFilters == 3)
			{
				$count = $count * 5;
			}
			/* Determine whether the user has overridden the count parameter with a
			 * manual number of tweets to retrieve.  Override the $count variable
			 * if this is the case
			 */
			if ($params->get('overrideCount', 1) == 1)
			{
				$count = $params->get('tweetsToScan', 3);
			}
			$req = 'http://api.twitter.com/1/statuses/user_timeline.json?count='.$count.'&screen_name='.$uname.$incRT.'&include_entities=1';
		}

		// Fetch the decoded JSON
		$obj = self::getJSON($req);

		// Process the filtering options and render the feed
		$twitter->tweet = self::processFiltering($obj, $params);

		return $twitter;
	}

	/**
	 * Function to fetch a JSON feed
	 *
	 * @param   string  $req  The URL of the feed to load
	 *
	 * @return  array  The fetched JSON query
	 *
	 * @since   1.0.7
	 */
	static function getJSON($req)
	{
		// Create a new cURL resource
		$ch = curl_init($req);

		// Set options
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Grab the URL, pass it to the browser and store it as $json
		$json = curl_exec($ch);

		// Close cURL resource
		curl_close($ch);

		// Decode the fetched JSON
		$obj = json_decode($json, true);

		return $obj;
	}

	/**
	 * Function to get the rate limit of a Twitter user
	 *
	 * @param   object  $params  The module parameters
	 *
	 * @return  string  The number of remaining hits on a user's rate limit
	 *
	 * @since   1.0.6
	 */
	static function getLimit($params)
	{
		// Load the parameters
		$uname = $params->get('twitterName', '');
		$req = 'http://api.twitter.com/1/account/rate_limit_status.json?screen_name='.$uname;

		// Fetch the decoded JSON
		$obj = self::getJSON($req);

		// Get the remaining hits count
		if (isset ($obj['remaining_hits']))
		{
			$hits = $obj['remaining_hits'];
		}
		else
		{
			$hits = '';
		}

		return $hits;
	}

	/**
	 * Function to fetch the user JSON and render it
	 *
	 * @param   object  $params  The module parameters
	 *
	 * @return  array  The formatted object for display
	 *
	 * @since   1.6.0
	 */
	static function prepareUser($params)
	{
		// Load the parameters
		$uname		= $params->get('twitterName', '');
		$list		= $params->get('twitterList', '');

		// Initialize new object containers
		$twitter			= new stdClass();
		$twitter->header	= new stdClass();
		$twitter->footer	= new stdClass();

		// Convert the list name to a useable string for the URL
		if ($list)
		{
			$flist = self::toAscii($list);
		}

		// Get the user JSON
		$req = 'http://api.twitter.com/1/users/show.json?screen_name='.$uname;

		// Decode the fetched JSON
		$obj = self::getJSON($req);

		// Check that we have the JSON, otherwise set an error
		if (!$obj)
		{
			$twitter->error	= '';
			return $twitter;
		}

		// Header info
		if ($params->get('headerUser', 1) == 1)
		{
			$twitter->header->user = '<a href="http://twitter.com/intent/user?screen_name='.$uname.'" rel="nofollow">';
			// Show the real name or the username
			if ($params->get('headerName', 1) == 1)
			{
				$twitter->header->user .= $obj['name'].'</a>';
			}
			else
			{
				$twitter->header->user .= $uname.'</a>';
			}
			// Append the list name if being pulled
			if ($params->get('twitterFeedType', 0) == 1)
			{
				$twitter->header->user .= ' - <a href="http://twitter.com/'.$uname.'/'.$flist.'" rel="nofollow">'.$list.' list</a>';
			}
		}
		// Show the bio
		if ($params->get('headerBio', 1) == 1)
		{
			$twitter->header->bio = $obj['description'];
		}
		// Show the location
		if ($params->get('headerLocation', 1) == 1)
		{
			$twitter->header->location = $obj['location'];
		}
		// Show the user's URL
		if ($params->get('headerWeb', 1) == 1)
		{
			$twitter->header->web = '<a href="'.$obj['url'].'" rel="nofollow">'.$obj['url'].'</a>';
		}

		// Get the profile image URL from the object
		$avatar	= $obj['profile_image_url'];

		// Switch from the normal size avatar (48px) to the large one (73px)
		$avatar	= str_replace('normal.jpg', 'bigger.jpg', $avatar);

		$twitter->header->avatar = '<img src="'.$avatar.'" alt="'.$uname.'" />';

		// Footer info

		// If a "Follow me" link is displayed, determine whether to display a button or text
		if ($params->get('footerFollowLink', 1) == 1)
		{
			if ($params->get('footerFollowType', 1) == 1)
			{
				$twitter->footer->follow_me = '<div class="TDB-footer-follow-img"><b>';
				// Determine whether a list or user feed is being generated
				if ($params->get('twitterFeedType', 0) == 1)
				{
					$twitter->footer->follow_me .= '<a href="http://twitter.com/'.$uname.'/'.$flist.'" rel="nofollow">';
					$alt = 'Follow '.$uname.'&#39;s '.$list.' list on Twitter';
				}
				else
				{
					$twitter->footer->follow_me .= '<a href="http://twitter.com/intent/user?screen_name='.$uname.'" rel="nofollow">';
					$alt = 'Follow '.$uname.' on Twitter';
				}
				$twitter->footer->follow_me .= '<img src="http://twitter-badges.s3.amazonaws.com/'.$params->get('footerFollowImgMeUs').'-'.$params->get('footerFollowImg').'.png" alt="'.$alt.'" align="middle" /></a></b></div>';
			}
			else
			{
				$twitter->footer->follow_me = '<hr /><div class="TDB-footer-follow-link">';
				// Determine whether a list or user feed is being generated
				if ($params->get('twitterFeedType', 0) == 1)
				{
					$twitter->footer->follow_me .= '<a href="http://twitter.com/'.$uname.'/'.$flist.'" rel="nofollow">';
				}
				else
				{
					$twitter->footer->follow_me .= '<a href="http://twitter.com/intent/user?screen_name='.$uname.'" rel="nofollow">';
				}
				$twitter->footer->follow_me .= $params->get('footerFollowText', 'Follow me on Twitter').'</a></div>';
			}
		}
		if ($params->get('footerPoweredBy', 1) == 1)
		{
			//Check the type of link to determine the appropriate opening tags
			if ($params->get('footerFollowType', 1) == 1)
			{
				$twitter->footer->powered_by = '<div class="TDB-footer-powered-img">';
			}
			else
			{
				$twitter->footer->powered_by = '<hr /><div class="TDB-footer-powered-text">';
			}
			$site = '<a href="http://www.flbab.com/extensions/tweet-display-back" rel="nofollow">Tweet Display Back</a>';
			$twitter->footer->powered_by .= JText::sprintf('MOD_TWEETDISPLAYBACK_POWERED_BY', $site).'</div>';
		}

		return $twitter;
	}

	/**
	 * Function to render the Twitter feed into a formatted object
	 *
	 * @param   array   $obj     The decoded JSON feed
	 * @param   object  $params  The module parameters
	 *
	 * @return	object  The formatted object for display
	 *
	 * @since   2.0.0
	 */
	static function processFiltering($obj, $params)
	{
		// Initialize
		$count			= $params->get('twitterCount', 3);
		$showMentions	= $params->get('showMentions', 0);
		$showReplies	= $params->get('showReplies', 0);
		$numberOfTweets	= $params->get('twitterCount', 3);
		$twitter		= array();
		$i				= 0;

		// Check if $obj has data; if not, return an error
		if (is_null($obj))
		{
			// Set an error
			$twitter[$i]->tweet->text = JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');
		}
		else
		{
			// Process the feed
			foreach ($obj as $o)
			{
				if ($count > 0)
				{
					// Check if we have all of the items we want
					if ($i < $numberOfTweets)
					{
						// If we aren't filtering, just render the item
						if ($showMentions == 1 && $showReplies == 1)
						{
							self::processItem($twitter, $o, $i, $params);

							// Modify counts
							$count--;
							$i++;
						}
						else if ($params->get('twitterFeedType', 0) == 1)
						{
							// We can't filter list feeds, so just process them
							self::processItem($twitter, $o, $i, $params);

							// Modify counts
							$count--;
							$i++;
						}
						// We're filtering, the fun starts here
						else
						{
							// Set variables
							// Tweets which contains a @reply
							$tweetContainsReply = $o['in_reply_to_user_id'] != null;
							// Tweets which contains a @mention and/or @reply
							$tweetContainsMentionAndOrReply = $o['entities']['user_mentions'] != null;
							// Tweets which contains only @mentions
							$tweetOnlyMention = $tweetContainsMentionAndOrReply && !$tweetContainsReply;

							/* Check if a reply tweet contains mentions
							 * NOTE: Works only for tweets where there is also a reply, since reply is at
							 * the position ['0'] and mentions begin at ['1'].
							 */
							if (isset($o['entities']['user_mentions']['1']))
							{
								$replyTweetContainsMention = $o['entities']['user_mentions']['1'];
							}
							else
							{
								$replyTweetContainsMention = '0';
							}

							// Tweets with only @reply
							$tweetOnlyReply = $tweetContainsReply && $replyTweetContainsMention == '0';

							// Tweets which contains @mentions or @mentions+@reply
							$tweetContainsMention = $tweetContainsMentionAndOrReply && !$tweetOnlyReply;

							// Filter @mentions and @replies, leaving retweets unchanged
							if ($showMentions == 0 && $showReplies == 0)
							{
								if (!$tweetContainsMentionAndOrReply || isset($o['retweeted_status']))
								{
									self::processItem($twitter, $o, $i, $params);

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
									if (!$tweetContainsMention || isset($o['retweeted_status']))
									{
										self::processItem($twitter, $o, $i, $params);

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
										self::processItem($twitter, $o, $i, $params);

										// Modify counts
										$count--;
										$i++;
									}
								}
								// Somehow, we got this far; process the tweet
								if ($showMentions == 1 && $showReplies == 1)
								{
									// No filtering required
									self::processItem($twitter, $o, $i, $params);

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

		return $twitter;
	}

	/**
	 * Function to process the Twitter feed into a formatted object
	 *
	 * @param   object  &$twitter  The final output object
	 * @param   object  $o         The item within the JSON feed
	 * @param   int     $i         Iteration of processFiltering
	 * @param   object  $params    The module parameters
	 *
	 * @return  void
	 *
	 * @since	2.0.0
	 */
	static function processItem(&$twitter, $o, $i, $params)
	{
		// Set variables
		$tweetName		= $params->get('tweetName', 1);
		$tweetAlignment	= $params->get('tweetAlignment', 'left');
		$tweetReply		= $params->get('tweetReply', 1);
		$tweetRTCount	= $params->get('tweetRetweetCount', 1);

		// Initialize a new object
		$twitter[$i]->tweet	= new stdClass();

		// Check if the item is a retweet, and if so gather data from the retweeted_status datapoint
		if (isset($o['retweeted_status']))
		{
			// Retweeted user
			if ($tweetName == 1)
			{
				$tweetedBy = $o['retweeted_status']['user']['screen_name'];
			}
			$avatar = $o['retweeted_status']['user']['profile_image_url'];
			$text   = $o['retweeted_status']['text'];
			$urls   = $o['retweeted_status']['entities']['urls'];
			$twitter[$i]->tweet->created = JText::_('MOD_TWEETDISPLAYBACK_RETWEETED');
		}
		else
		{
			// User
			if ($tweetName == 1)
			{
				$tweetedBy = $o['user']['screen_name'];
			}
			$avatar = $o['user']['profile_image_url'];
			$text   = $o['text'];
			$urls   = $o['entities']['urls'];
		}
		// Generate the object with the user data
		if ($tweetName == 1)
		{
			$twitter[$i]->tweet->user = '<b><a href="http://twitter.com/intent/user?screen_name='.$tweetedBy.'" rel="nofollow">'.$tweetedBy.'</a>'.$params->get('tweetUserSeparator').'</b>';
		}
		$twitter[$i]->tweet->avatar = '<img align="'.$tweetAlignment.'" alt="'.$tweetedBy.'" src="'.$avatar.'" width="32px"/>';
		$twitter[$i]->tweet->text = $text;
		foreach ($urls as $url)
		{
			if (isset($url['display_url']))
			{
				$d_url = $url['display_url'];
			}
			else
			{
				$d_url = $url['url'];
			}
			$twitter[$i]->tweet->text = str_replace($url['url'], '<a href="'.$url['url'].'" target="_blank" rel="nofollow">'.$d_url.'</a>', $twitter[$i]->tweet->text);
		}

		// Info below is specific to each tweet, so it isn't checked against a retweet
		// Display the time the tweet was created
		if ($params->get('tweetCreated', 1) == 1)
		{
			$twitter[$i]->tweet->created .= '<a href="http://twitter.com/'.$o['user']['screen_name'].'/status/'.$o['id_str'].'" rel="nofollow">';
			// Determine whether to display the time as a relative or static time
			if ($params->get('tweetRelativeTime', 1) == 1)
			{
				$twitter[$i]->tweet->created .= self::renderRelativeTime($o['created_at']).'</a>';
			}
			else
			{
				$twitter[$i]->tweet->created .= JHTML::date($o['created_at']).'</a>';
			}
		}
		// Display the tweet source
		if (($params->get('tweetSource', 1) == 1))
		{
			$twitter[$i]->tweet->created .= JText::sprintf('MOD_TWEETDISPLAYBACK_VIA', $o['source']);
		}
		// Display the location the tweet was made from if set
		if (($params->get('tweetLocation', 1) == 1) && ($o['place']['full_name']))
		{
			$twitter[$i]->tweet->created .= JText::_('MOD_TWEETDISPLAYBACK_FROM').'<a href="http://maps.google.com/maps?q='.$o['place']['full_name'].'" target="_blank" rel="nofollow">'.$o['place']['full_name'].'</a>';
		}
		// If the tweet is a reply, display a link to the tweet it's in reply to
		if (($o['in_reply_to_screen_name']) && ($o['in_reply_to_status_id_str']))
		{
			$twitter[$i]->tweet->created .= JText::_('MOD_TWEETDISPLAYBACK_IN_REPLY_TO').'<a href="http://twitter.com/'.$o['in_reply_to_screen_name'].'/status/'.$o['in_reply_to_status_id_str'].'" rel="nofollow">'.$o['in_reply_to_screen_name'].'</a>';
		}
		// Display the number of times the tweet has been retweeted
		if ((($tweetRTCount == 1) && ($o['retweet_count'] >= 1)))
		{
			$twitter[$i]->tweet->created .= ' &bull; '.self::renderRetweetCount($o['retweet_count']);
		}
		// Display Twitter Actions
		if ($tweetReply == 1)
		{
			$twitter[$i]->tweet->actions = '<span class="TDB-action TDB-reply"><a href="http://twitter.com/intent/tweet?in_reply_to='.$o['id_str'].'" title="Reply" rel="nofollow"></a></span>';
			$twitter[$i]->tweet->actions .= '<span class="TDB-action TDB-retweet"><a href="http://twitter.com/intent/retweet?tweet_id='.$o['id_str'].'" title="Retweet" rel="nofollow"></a></span>';
			$twitter[$i]->tweet->actions .= '<span class="TDB-action TDB-favorite"><a href="http://twitter.com/intent/favorite?tweet_id='.$o['id_str'].'" title="Favorite" rel="nofollow"></a></span>';
		}
		// If set, convert user and hash tags into links
		if ($params->get('tweetLinks', 1) == 1)
		{
			foreach ($o['entities']['user_mentions'] as $mention)
			{
				$twitter[$i]->tweet->text = str_ireplace('@'.$mention['screen_name'], '@<a class="userlink" href="http://twitter.com/intent/user?screen_name='.$mention['screen_name'].'" rel="nofollow">'.$mention['screen_name'].'</a>', $twitter[$i]->tweet->text);
			}
			foreach ($o['entities']['hashtags'] as $hashtag)
			{
				$twitter[$i]->tweet->text = str_ireplace('#'.$hashtag['text'], '#<a class="hashlink" href="http://twitter.com/search?q='.$hashtag['text'].'" target="_blank" rel="nofollow">'.$hashtag['text'].'</a>', $twitter[$i]->tweet->text);
			}
		}
	}

	/**
	 * Function to convert a static time into a relative measurement
	 *
	 * @param   string  $date  The date to convert
	 *
	 * @return  string  The converted date string
	 *
	 * @since   1.0.0
	 */
	static function renderRelativeTime($date)
	{
		// Get the difference in seconds between now and the tweet time
		$diff = time() - strtotime($date);
		// Less than a minute
		if ($diff < 60)
		{
			return JText::_('MOD_TWEETDISPLAYBACK_CREATE_LESSTHANAMINUTE');
		}
		// Round to minutes
		$diff = round($diff / 60);
		// 60 to 119 seconds
		if ($diff < 2)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_MINUTE', $diff);
		}
		// 2 to 59 minutes
		if ($diff < 60)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_MINUTES', $diff);
		}
		// Round to hours
		$diff = round($diff / 60);
		// 1 hour
		if ($diff < 2)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_HOUR', $diff);
		}
		// 2 to 23 hours
		if ($diff < 24)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_HOURS', $diff);
		}
		// Round to days
		$diff = round($diff / 24);
		// 1 day
		if ($diff < 2)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_DAY', $diff);
		}
		// 2 to 6 days
		if ($diff < 7)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_DAYS', $diff);
		}
		// Round to weeks
		$diff = round($diff / 7);
		// 1 week
		if ($diff < 2)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_WEEK', $diff);
		}
		// 2 or 3 weeks
		if ($diff < 4)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_CREATE_WEEKS', $diff);
		}
		// Over a month, return the absolute time
		return JHTML::date($date);
	}

	/**
	 * Function to count the number of retweets and return the appropriate string
	 *
	 * @param   string  $count  The number of retweets
	 *
	 * @return  string  A text string of the number of retweets
	 *
	 * @since   1.6.0
	 */
	static function renderRetweetCount($count)
	{
		// 1 retweet
		if ($count = 1)
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_RETWEET', $count);
		}
		// 0 (shouldn't even be here!) or 2+ retweets
		else
		{
			return JText::sprintf('MOD_TWEETDISPLAYBACK_RETWEETS', $count);
		}
	}

	/**
	 * Function to convert a formatted list name into it's URL equivilent
	 *
	 * @param   string  $list  The user inputted list name
	 *
	 * @return  string  The list name converted
	 *
	 * @since   1.6.0
	 */
	static function toAscii($list)
	{
		$clean = preg_replace("/[^a-z'A-Z0-9\/_|+ -]/", '', $list);
		$clean = strtolower(trim($clean, '-'));
		$list  = preg_replace("/[\/_|+ -']+/", '-', $clean);

		return $list;
	}
}
