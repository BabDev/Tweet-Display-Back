<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

class modTweetDisplayBackHelper {

	/**
	 * Function to fetch a JSON feed
	 * 
	 * @param	string	$req	The URL of the feed to load
	 * 
	 * @return	array	$obj	The fetched JSON query
	 * @since	1.0.7
	 */
	static function getJSON($req) {
		// Create a new CURL resource
		$ch = curl_init($req);

		// Set options
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Grab URL and pass it to the browser and store it as $json
		$json = curl_exec($ch);

		// Close CURL resource
		curl_close($ch);

		// Decode the fetched JSON
		$obj = json_decode($json, true);

		return $obj;
	}

	/**
	 * Function to get the rate limit of a Twitter user
	 * 
	 * @param	string	$params	The module parameters
	 * 
	 * @return	string	$hits	The number of remaining hits on a user's rate limit
	 * @since	1.0.6
	 */
	static function getLimit($params) {
		// Load the parameters
		$uname = $params->get("twitterName","");
		$req = "http://api.twitter.com/1/account/rate_limit_status.json?screen_name=".$uname."";

		// Fetch the decoded JSON
		$obj = self::getJSON($req);

		// Get the remaining hits count
		if (isset ($obj['remaining_hits'])) {
		 	$hits = $obj['remaining_hits'];
		} else {
		 	return false;
		}
		return $hits;
	}

	/**
	 * Function to compile the data to render a formatted object displaying a Twitter feed
	 * 
	 * @param	string	$params		The module parameters
	 * 
	 * @return	object	$twitter	A formatted object with the requested tweets
	 * @since	1.6.0
	 */
	static function compileData($params) {
		// Load the parameters
		$uname		= $params->get("twitterName", "");
		$list		= $params->get("twitterList", "");
		$count		= $params->get("twitterCount", 3);
		$retweet	= $params->get("tweetRetweets", 1);

		// Convert the list name to a useable string for the JSON
		$flist		= self::toAscii($list);

		// Initialize the array
		$twitter	= array();

		// Get the user info
		$twitter	= self::prepareUser($params);

		// Check if an error was set in the user JSON; if so, end the processing
		if (isset($twitter['error'])) {
			echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');
			return $twitter;
		}

		// Determine whether the feed being returned is a user or list feed
		// 0 is user, 1 is list
		if ($params->get("twitterFeedType", 0) == 1) {
			// Get the list feed
			$req = "http://api.twitter.com/1/".$uname."/lists/".$flist."/statuses.json";
		} else {
			// Get the user feed
			// Determine whether to pull retweets or not
			if ($retweet == 1) {
				$req = "http://api.twitter.com/1/statuses/user_timeline.json?count=".$count."&include_rts=1&screen_name=".$uname."";
			} else {
				$req = "http://api.twitter.com/1/statuses/user_timeline.json?count=".$count."&screen_name=".$uname."";
			}
		}

		// Fetch the decoded JSON
		$obj = self::getJSON($req);

		// Render the JSON into a formatted array
		$twitter->tweet = self::renderFeed($obj, $params);

		return $twitter;
	}

	/**
	 * Function to fetch the user JSON and render it
	 * 
	 * @param	string	$param		The module parameters
	 * 
	 * @return	array	$twitter	The formatted object for display
	 * @since	1.6.0
	 */
	static function prepareUser($params) {
		// Load the parameters
		$uname		= $params->get("twitterName", "");
		$list		= $params->get("twitterList", "");

		// Initialize new object containers
		$twitter			= new stdClass();
		$twitter->header	= new stdClass();
		$twitter->footer	= new stdClass();

		// Convert the list name to a useable string for the URL
		$flist		= self::toAscii($list);

		// Get the user JSON
		$req	= "http://api.twitter.com/1/users/show.json?screen_name=".$uname;

		// Decode the fetched JSON
		$obj	= self::getJSON($req);

		// Check if an error was returned in the JSON and end processing if so
		if (isset($obj['error'])) {
			return $obj;
		}

		// Header info
		if ($params->get("showHeaderUser", 1)==1) {
			// Show the real name or the username
			if ($params->get("showHeaderName", 1)==1) {
				$twitter->header->user = "<a href=\"http://twitter.com/intent/user?screen_name=".$uname."\">".$obj['name']."</a>";
			} else {
				$twitter->header->user = "<a href=\"http://twitter.com/intent/user?screen_name=".$uname."\">".$uname."</a>";
			}
			// Append the list name if being pulled
			if ($params->get("twitterFeedType", 0) == 1) {
				$twitter->header->user .= " - <a href=\"http://twitter.com/".$uname."/".$flist."\">".$list." list</a>";
			}
		}
		if ($params->get("showHeaderBio", 1)==1) {
			$twitter->header->bio = $obj['description'];
		}
		if ($params->get("showHeaderLocation", 1)==1) {
			$twitter->header->location = $obj['location'];
		}
		if ($params->get("showHeaderWeb", 1)==1) {
			$twitter->header->web = "<a href=\"".$obj['url']."\">".$obj['url']."</a>";
		}
		$twitter->header->avatar = "<img src=\"http://api.twitter.com/1/users/profile_image/twitter.json?screen_name=".$uname."&size=bigger\" width=\"73px\" alt=\"".$uname."\" />";

		// Footer info

		// If a "Follow me" link is displayed, determine whether to display a button or text
		// followType 1 is image, 0 is text
		if ($params->get("footerFollowLink", 1) == 1) {
			if ($params->get("footerFollowType", 1) == 1) {
				// Determine whether a list or user feed is being generated
				if ($params->get("twitterFeedType", 0) == 1) {
					$twitter->footer->follow_me = "<div class=\"TDB-footer-follow-img\"><b><a href=\"http://twitter.com/".$uname."/".$flist."\" rel=\"nofollow\"><img src=\"http://twitter-badges.s3.amazonaws.com/".$params->get('footerFollowImgMeUs')."-".$params->get('footerFollowImg').".png\" alt=\"Follow ".$uname."'s ".$list." list on Twitter\" align=\"center\" /></a></b></div>";
				} else {
					$twitter->footer->follow_me = "<div class=\"TDB-footer-follow-img\"><b><a href=\"http://twitter.com/intent/user?screen_name=".$uname."\" rel=\"nofollow\"><img src=\"http://twitter-badges.s3.amazonaws.com/".$params->get('footerFollowImgMeUs')."-".$params->get('footerFollowImg').".png\" alt=\"Follow ".$uname." on Twitter\" align=\"center\" /></a></b></div>";
				}
			} else {
				// Determine whether a list or user feed is being generated
				if ($params->get("twitterFeedType", 0) == 1) {
					$twitter->footer->follow_me = "<hr /><div class=\"TDB-footer-follow-link\"><b><a href=\"http://twitter.com/".$uname."/".$flist."\" rel=\"nofollow\">".$params->get('footerFollowText', 'Follow me on Twitter')."</a></b></div>";
				} else {
					$twitter->footer->follow_me = "<hr /><div class=\"TDB-footer-follow-link\"><b><a href=\"http://twitter.com/intent/user?screen_name=".$uname."\" rel=\"nofollow\">".$params->get('footerFollowText', 'Follow me on Twitter')."</a></b></div>";
				}
			}
		}
		if ($params->get("footerPoweredBy", 1) == 1) {
			//Check the type of link to determine the appropriate opening tags
			if ($params->get("footerFollowType", 1) == 1) {
				$twitter->footer->powered_by = "<div class=\"TDB-footer-powered-img\">";
			} else {
				$twitter->footer->powered_by = "<hr /><div class=\"TDB-footer-powered-text\">";
			}
			$twitter->footer->powered_by .= "Powered by <a href=\"http://www.flbab.com/extensions/tweet-display-back/13-info\">Tweet Display Back</a></div>";
		}

		return $twitter;
	}

	/**
	 * Function to render the Twitter feed into a formatted object
	 * 
	 * @param	array	$obj		The decoded JSON feed
	 * @param	string	$params		The module parameters
	 * 
	 * @return	object	$twitter	The formatted object for display
	 * @since	1.6.0
	 */
	static function renderFeed($obj, $params) {
		// Initialize
		$twitter = array();
		$i = 0;

		// Set variables
		$tweetName		= $params->get("tweetName", 1);
		$tweetAlignment	= $params->get("tweetAlignment", 'left');
		$tweetReply		= $params->get("tweetReply", 1);
		$tweetRTCount	= $params->get("tweetRetweetCount", 1);

		// Check if $obj has data; if not, return an error
		if ((is_null($obj)) || (isset($obj['error']))) {
			// Set an error
			$twitter[$i]->tweet->text = JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');
		} else {
			// Process the feed
			foreach ($obj as $o) {
				// Initialize a new object
				$twitter[$i]->tweet	= new stdClass();

				// Check if the item is a retweet, and if so gather data from the retweeted_status datapoint
				if (isset($o['retweeted_status'])) {
					// Retweeted user
					if ($tweetName == 1) {
						$twitter[$i]->tweet->user = "<b><a href=\"http://twitter.com/intent/user?screen_name=".$o['retweeted_status']['user']['screen_name']."\">".$o['retweeted_status']['user']['screen_name']."</a>".$params->get("tweetUserSeparator")."</b> ";
					}
					$twitter[$i]->tweet->created = "Retweeted ";
					$twitter[$i]->tweet->avatar = "<img align=\"".$tweetAlignment."\" alt=\"".$o['retweeted_status']['user']['screen_name']."\" src=\"".$o['retweeted_status']['user']['profile_image_url']."\" width=\"32px\"/>";
					$twitter[$i]->tweet->text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $o['retweeted_status']['text']);
				} else {
					// User
					if ($tweetName == 1) {
						$twitter[$i]->tweet->user = "<b><a href=\"http://twitter.com/intent/user?screen_name=".$o['user']['screen_name']."\">".$o['user']['screen_name']."</a>".$params->get("tweetUserSeparator")."</b> ";
					}
					$twitter[$i]->tweet->avatar = "<img align=\"".$tweetAlignment."\" alt=\"".$o['user']['screen_name']."\" src=\"".$o['user']['profile_image_url']."\" width=\"32px\"/>";
					$twitter[$i]->tweet->text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $o['text']);
				}
				// Info below is specific to each tweet, so it isn't checked against a retweet
				// Determine whether to display the time as a relative or static time
				if ($params->get("tweetCreated", 1)==1) {
					if ($params->get("tweetRelativeTime", 1) == 1) {
						$twitter[$i]->tweet->created .= "<a href=\"http://twitter.com/".$o['user']['screen_name']."/status/".$o['id_str']."\">".self::renderRelativeTime($o['created_at'])."</a>";
					} else {
						$twitter[$i]->tweet->created .= "<a href=\"http://twitter.com/".$o['user']['screen_name']."/status/".$o['id_str']."\">".JHTML::date($o['created_at'])."</a>";
					}
				}
				// Display the tweet source
				if (($params->get("tweetSource", 1) == 1)) {
					$twitter[$i]->tweet->created .= " via ".$o['source'];
				}
				// Display the location the tweet was made from
				if (($params->get("tweetLocation", 1) == 1) && ($o['place']['full_name'])) {
					$twitter[$i]->tweet->created .= " from <a href=\"http://maps.google.com/maps?q=".$o['place']['full_name']."\" target=\"_blank\">".$o['place']['full_name']."</a>";
				}
				// If the tweet is a reply, display a link to the tweet it's in reply to
				if (($o['in_reply_to_screen_name']) && ($o['in_reply_to_status_id_str'])) {
					$twitter[$i]->tweet->created .= " in reply to <a href=\"http://twitter.com/".$o['in_reply_to_screen_name']."/status/".$o['in_reply_to_status_id_str']."\">".$o['in_reply_to_screen_name']."</a>";
				}
				if (($tweetReply == 1) || (($tweetRTCount == 1) && ($o['retweet_count'] >= 1))) {
					$twitter[$i]->tweet->created .= " &bull; ";
				}
				// Display a reply link
				if ($tweetReply == 1) {
					$twitter[$i]->tweet->created .= "<a href=\"http://twitter.com/intent/tweet?in_reply_to=".$o['id_str']."\">".JText::_('MOD_TWEETDISPLAYBACK_REPLY')."</a>";
				}
				if (($tweetReply == 1) && (($tweetRTCount == 1) && ($o['retweet_count'] >= 1))) {
					$twitter[$i]->tweet->created .= " &bull; ";
				}
				// Display the number of times the tweet has been retweeted
				if (($tweetRTCount == 1) && ($o['retweet_count'] >= 1)) {
					$twitter[$i]->tweet->created .= JText::plural('MOD_TWEETDISPLAYBACK_RETWEETS', $o['retweet_count']);
				}
				// If set, convert user and hash tags into links
				if ($params->get("tweetLinks", 1) == 1) {
					$twitter[$i]->tweet->text = preg_replace("/@(\w+)/", "@<a class=\"userlink\" href=\"http://twitter.com/intent/user?screen_name=\\1\" target=\"_blank\">\\1</a>", $twitter[$i]->tweet->text);
					$twitter[$i]->tweet->text = preg_replace("/#(\w+)/", "#<a class=\"hashlink\" href=\"http://twitter.com/search?q=\\1\" target=\"_blank\">\\1</a>", $twitter[$i]->tweet->text);
				}
				$i++;
			}
		}
		return $twitter;
	}

	/**
	 * Function to convert a static time into a relative measurement
	 * 
	 * @param	string	$date	The date to convert
	 * 
	 * @return	string	$date	A text string of a relative time
	 * @since	1.0.0
	 */
	static function renderRelativeTime($date) {
		$diff = time() - strtotime($date);
		// Less than a minute
		if ($diff < 60) {
			return JText::_('MOD_TWEETDISPLAYBACK_CREATE_LESSTHANAMINUTE');
		}
		$diff = round($diff/60);
		// 1 to 59 minutes
		if ($diff < 60) {
			return JText::plural('MOD_TWEETDISPLAYBACK_CREATE_MINUTES', $diff);
		}
		$diff = round($diff/60);
		// 1 to 23 hours
		if ($diff < 24) {
			return JText::plural('MOD_TWEETDISPLAYBACK_CREATE_HOURS', $diff);
		}
		$diff = round($diff/24);
		// 1 to 6 days
		if ($diff < 7) {
			return JText::plural('MOD_TWEETDISPLAYBACK_CREATE_DAYS', $diff);
		}
		$diff = round($diff/7);
		// 1 to 3 weeks
		if ($diff < 4) {
			return JText::plural('MOD_TWEETDISPLAYBACK_CREATE_WEEKS', $diff);
		}
		// If older than 4 weeks, display a static time
		return JHTML::date($date);
	}

	/**
	 * Function to convert a formatted list name into it's URL equivilent
	 * 
	 * @param	string	$list	The user inputted list name
	 * 
	 * @return	string	$list	The list name converted
	 * @since	1.6.0
	 */
	static function toAscii($list) {
		$clean = preg_replace("/[^a-z'A-Z0-9\/_|+ -]/", '', $list);
		$clean = strtolower(trim($clean, '-'));
		$list  = preg_replace("/[\/_|+ -']+/", '-', $clean);

		return $list;
	}
}
