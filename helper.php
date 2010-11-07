<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
* Module forked from TweetXT for Joomla!
* Original Copyright (c) 2009 joomlaxt.com, All rights reserved - http://www.joomlaxt.com
*/

// no direct access
defined('_JEXEC') or die;

class tweetDisplayHelper {

	/**
	 * Function to fetch a JSON feed
	 * 
	 * @param	string	$req	The URL of the feed to load
	 * @return	array	$obj	The fetched JSON query
	 * @since	1.0.7
	 */
	function getJSON($req) {
		// create a new cURL resource
		$ch = curl_init($req);
		
		// set cURL options
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// grab URL and pass it to the browser and store it as $json
		$json = curl_exec($ch);
		
		// close cURL resource
		curl_close($ch);
		
		// decode the fetched JSON
		$obj = json_decode($json, true);
		
		if (isset($obj['error'])) return false;
		
		return $obj;		
	}
	
	/**
	 * Function to get the rate limit of a Twitter user
	 * 
	 * @param	string	$params
	 * @return	string	$hits	The number of remaining hits on a user's rate limit
	 * @since	1.0.6
	 */
	function getLimit($params) {
		// load the parameters
		$uname = $params->get("twitterName","");
		$req = "http://api.twitter.com/1/account/rate_limit_status.json?screen_name=".$uname."";
		
		// fetch the decoded JSON
		$obj = self::getJSON($req);
				
		// get the remaining hits count
		if (isset ($obj['remaining_hits'])) { 
		 	$hits = $obj['remaining_hits'];
		} else {
		 	return false;
		}
		return $hits;
	}

	/**
	 * Function to load a user's user_timeline Twitter feed
	 * 
	 * @param	string	$params
	 * @return	object	$twitter	A formatted object with the user's tweets
	 * @since	1.0.0
	 */
	function getTweets($params) {
		// check the number of hits available; if 0, proceed no further
		$hits = self::getLimit($params);
		if ($hits == 0) {
			return false;
		}
		
		// load the parameters
		$uname = $params->get("twitterName","");
		$count = $params->get("twitterCount",3);
		$retweet = $params->get("showRetweets",1);
		
		// determine whether to pull retweets or not
		if ($retweet == 1) {
			$req = "http://api.twitter.com/1/statuses/user_timeline.json?count=".$count."&include_rts=1&screen_name=".$uname."";
		} else {
			$req = "http://api.twitter.com/1/statuses/user_timeline.json?count=".$count."&screen_name=".$uname."";
		}
		
		// fetch the decoded JSON
		$obj = self::getJSON($req);
		
		$twitter = self::renderTwitter($obj, $params);
		return $twitter;
	}
		
	/**
	 * Function to render the user timeline JSON into HTML
	 * 
	 * @param	array	$obj		The decoded JSON feed
	 * @param	string	$params
	 * @return	object	$twitter	The formatted object for display
	 * @since	1.0.6
	 */
	function renderTwitter($obj, $params) {
		// check the first object for user info
		if (isset($obj[0])) {
			$userInfo = $obj[0];
		} else {
		 	return false;
		}
		// header info
		if ($params->get("showHeaderUser", 1)==1) {
			$twitter->header->user = "<a href=\"http://twitter.com/".$userInfo['user']['screen_name']."\">".$userInfo['user']['screen_name']."</a><br />";
		}
		if ($params->get("showHeaderBio", 1)==1) {
			$twitter->header->bio = $userInfo['user']['description']."<br />";
		}
		if ($params->get("showHeaderLocation", 1)==1) {
			$twitter->header->location = $userInfo['user']['location']."<br />";
		}
		if ($params->get("showHeaderWeb", 1)==1) {
			$twitter->header->web = "<a href=\"".$userInfo['user']['url']."\">".$userInfo['user']['url']."</a>";
		}
		$twitter->header->avatar = "<img src=\"http://api.twitter.com/1/users/profile_image/twitter.json?screen_name=".$userInfo['user']['screen_name']."&size=bigger\" align=\"".$params->get("headerAvatarAlignment")."\" width=\"73px\" alt=\"".$userInfo['user']['screen_name']."\" />";
		
		// footer info
		
		// If a "Follow me" link is displayed, determine whether to display a button or text
		// followType 1 is image, 0 is text
		if ($params->get("showFollowLink", 1)==1) {
			if ($params->get("followType", 1)==1) {
				$twitter->footer->follow_me = "<div class=\"followImg\"><b><a href=\"http://twitter.com/".$userInfo->screen_name."\" rel=\"nofollow\"><img src=\"http://twitter-badges.s3.amazonaws.com/follow_me-".$params->get('followImg').".png\" alt=\"Follow ".$userInfo->screen_name." on Twitter\" align=\"center\" /></a></b></div>";
			} else {
				$twitter->footer->follow_me = "<hr /><div class=\"followLink\"><b><a href=\"http://twitter.com/".$userInfo->screen_name."\" rel=\"nofollow\">".$params->get('followText', 'Follow me on Twitter')."</a></b></div>";
			}
		}
		if ($params->get("showPoweredBy", 1)==1) {
			//Check the type of link to determine the appropriate opening tags
			if ($params->get("followType", 1)==1) {
				$twitter->footer->powered_by = "<div class=\"poweredByImg\">";
			} else {
				$twitter->footer->powered_by = "<hr /><div class=\"poweredBy\">";
			}
			$twitter->footer->powered_by .= "Powered by <a href=\"http://www.flbab.com/extensions/tweet-display-back/13-info\">Tweet Display Back</a></div>";
		}
		
		// tweets
		foreach ($obj as $t) {
			// user
			if ($params->get("showTweetName", 1)==1) {
				$twitter->tweet->user = "<b><a href=\"http://twitter.com/".$t['user']['screen_name']."\">".$t['user']['screen_name']."</a>:</b> ";
			}
			if ($params->get("showTweetCreated", 1)==1) {
				if ($params->get("relativeTime", 1) == 1) {
					$twitter->tweet->created = "<a href=\"http://twitter.com/".$t['user']['screen_name']."/status/".$t['id']."\">".self::renderRelativeTime($t['created_at'])."</a>";
				} else {
					$twitter->tweet->created = "<a href=\"http://twitter.com/".$t['user']['screen_name']."/status/".$t['id']."\">".JHTML::date($t['created_at'])."</a>";
				}
			}
			if (($params->get("showSource", 1) == 1)) {
				$twitter->tweet->created .= " via ".$t['source'];
			}
			if (($params->get("showLocation", 1)==1) && ($t->place->full_name)) {
				$twitter->tweet->created .= " from <a href=\"http://maps.google.com/maps?q=".$t['place']['full_name']."\" target=\"_blank\">".$t['place']['full_name']."</a>";
			}
			if (($t['in_reply_to_screen_name']) && ($t['in_reply_to_status_id'])) {
				$twitter->tweet->created .= " in reply to <a href=\"http://twitter.com/".$t['in_reply_to_screen_name']."/status/".$t['in_reply_to_status_id']."\">".$t['in_reply_to_screen_name']."</a>";
			}
			$twitter->tweet->avatar = "<img align=\"".$params->get("tweetDisplayLocation")."\" alt=\"".$t['user']['screen_name']."\" src=\"".$t['user']['profile_image_url']."\" width=\"32px\"/>";
			$twitter->tweet->text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $t['text']);
			if ($params->get("showLinks", 1) == 1) {
				$twitter->tweet->text = preg_replace("/@(\w+)/", "@<a href=\"http://twitter.com/\\1\" target=\"_blank\">\\1</a>", $twitter->tweet->text);
				$twitter->tweet->text = preg_replace("/#(\w+)/", "#<a href=\"http://twitter.com/search?q=\\1\" target=\"_blank\">\\1</a>", $twitter->tweet->text);
			}
		}
		return $twitter;
	}
	
	/**
	 * Function to convert a static time into a relative measurement
	 * 
	 * @param	string	$date	The date to convert
	 * @return	string	$date	A text string of a relative time
	 * @since	1.0.0
	 */
	function renderRelativeTime($date) {
		$diff = time() - strtotime($date);
		// Less than a minute
		if ($diff < 60) {
			return JText::_('MOD_TWEETDISPLAYBACK_CREATE_LESSTHANAMINUTE');
		}
		$diff = round($diff/60);
		// 60 to 119 seconds
		if ($diff < 2) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_MINUTE');
		}
		// 2 to 59 minutes
		if ($diff < 60) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_MINUTES');
		}
		$diff = round($diff/60);
		// 1 hour
		if ($diff < 2) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_HOUR');
		}
		// 2 to 23 hours
		if ($diff < 24) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_HOURS');
		}
		$diff = round($diff/24);
		// 1 day
		if ($diff < 2) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_DAY');
		}
		// 2 to 6 days
		if ($diff < 7) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_DAYS');
		}
		$diff = round($diff/7);
		// 1 week
		if ($diff < 2) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_WEEK');
		}
		// 2 or 3 weeks
		if ($diff < 4) {
			return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_WEEKS');
		}
		return JHTML::date($date);	
	}
}
