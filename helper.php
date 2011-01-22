<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
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
	static function getJSON($req) {
		// check if cURL is loaded
		// TODO: Suppress MOD_TWEETDISPLAYBACK_UNABLE_TO_LOAD
		if (!extension_loaded('curl')) {
			echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOCURL');
			return;
		}
		
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
	static function getLimit($params) {
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
	static function getTweets($params) {
		// check the number of hits available; if 0, proceed no further
		// TODO: Suppress MOD_TWEETDISPLAYBACK_UNABLE_TO_LOAD
		$hits = self::getLimit($params);
		if ($hits == 0) {
			echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOHITS');
			return;
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
	 * @since	1.0.0
	 */
	static function renderTwitter($obj, $params) {
		// initialize
		$twitter = array();

		// check the first object for user info
		if (isset($obj[0])) {
			$userInfo = $obj[0];
		} else {
		 	return false;
		}
		// header info
		$headerUser = '';
		if ($params->get("showHeaderUser", 1)==1) {
			if ($params->get("showHeaderName", 1)==1) {
				$headerUser = "<a href=\"http://twitter.com/".$userInfo['user']['screen_name']."\">".$userInfo['user']['name']."</a><br />";
			}
			else {
				$headerUser = "<a href=\"http://twitter.com/".$userInfo['user']['screen_name']."\">".$userInfo['user']['screen_name']."</a><br />";
			}
		}
		$headerBio = '';
		if ($params->get("showHeaderBio", 1)==1) {
			$headerBio = $userInfo['user']['description']."<br />";
		}
		$headerLocation = '';
		if ($params->get("showHeaderLocation", 1)==1) {
			$headerLocation = $userInfo['user']['location']."<br />";
		}
		$headerWeb = '';
		if ($params->get("showHeaderWeb", 1)==1) {
			$headerWeb = "<a href=\"".$userInfo['user']['url']."\">".$userInfo['user']['url']."</a>";
		}
		$headerAvatar = "<img src=\"http://api.twitter.com/1/users/profile_image/twitter.json?screen_name=".$userInfo['user']['screen_name']."&size=bigger\" width=\"73px\" alt=\"".$userInfo['user']['screen_name']."\" />";
		
		// footer info
		
		// If a "Follow me" link is displayed, determine whether to display a button or text
		// followType 1 is image, 0 is text
		$footerFollowMe = '';
		if ($params->get("showFollowLink", 1)==1) {
			if ($params->get("followType", 1)==1) {
				$footerFollowMe = "<div class=\"followImg\"><b><a href=\"http://twitter.com/".$userInfo['user']['screen_name']."\" rel=\"nofollow\"><img src=\"http://twitter-badges.s3.amazonaws.com/".$params->get('followImgMeUs')."-".$params->get('followImg').".png\" alt=\"Follow ".$userInfo['user']['screen_name']." on Twitter\" align=\"center\" /></a></b></div>";
			} else {
				$footerFollowMe = "<hr /><div class=\"followLink\"><b><a href=\"http://twitter.com/".$userInfo['user']['screen_name']."\" rel=\"nofollow\">".$params->get('followText', 'Follow me on Twitter')."</a></b></div>";
			}
		}
		$footerShowPoweredBy = '';
		if ($params->get("showPoweredBy", 1)==1) {
			//Check the type of link to determine the appropriate opening tags
			if ($params->get("followType", 1)==1) {
				$footerShowPoweredBy = "<div class=\"poweredByImg\">";
			} else {
				$footerShowPoweredBy = "<hr /><div class=\"poweredBy\">";
			}
			$footerShowPoweredBy .= "Powered by <a href=\"http://www.flbab.com/extensions/tweet-display-back/13-info\">Tweet Display Back</a></div>";
		}
		
		// tweets
		$i = 0;
		foreach ($obj as $o) {
			// Header Information
			$twitter[$i]->header->user = $headerUser;
			$twitter[$i]->header->bio = $headerBio;
			$twitter[$i]->header->location = $headerLocation;
			$twitter[$i]->header->web = $headerWeb;
			$twitter[$i]->header->avatar = $headerAvatar;
			
			// Footer Information
			$twitter[$i]->footer->follow_me = $footerFollowMe;
			$twitter[$i]->footer->powered_by = $footerShowPoweredBy;

			// check if the item is a retweet, and if so gather data from the retweeted_status datapoint
			if(isset($o['retweeted_status'])) {
				// retweeted user
				if ($params->get("showTweetName", 1)==1) {
					$twitter[$i]->tweet->user = "<b><a href=\"http://twitter.com/".$o['retweeted_status']['user']['screen_name']."\">".$o['retweeted_status']['user']['screen_name']."</a>:</b> ";
				}
				$twitter[$i]->tweet->created = "Retweeted ";
				$twitter[$i]->tweet->avatar = "<img align=\"".$params->get("tweetDisplayLocation")."\" alt=\"".$o['retweeted_status']['user']['screen_name']."\" src=\"".$o['retweeted_status']['user']['profile_image_url']."\" width=\"32px\"/>";
				$twitter[$i]->tweet->text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $o['retweeted_status']['text']);
			} else {
				// user
				if ($params->get("showTweetName", 1)==1) {
					$twitter[$i]->tweet->user = "<b><a href=\"http://twitter.com/".$o['user']['screen_name']."\">".$o['user']['screen_name']."</a>:</b> ";
				}
				$twitter[$i]->tweet->avatar = "<img align=\"".$params->get("tweetDisplayLocation")."\" alt=\"".$o['user']['screen_name']."\" src=\"".$o['user']['profile_image_url']."\" width=\"32px\"/>";
				$twitter[$i]->tweet->text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $o['text']);
			}
			// info below is specific to the tweet, so it isn't checked against a retweet
			if ($params->get("showTweetCreated", 1)==1) {
				if ($params->get("relativeTime", 1) == 1) {
					$twitter[$i]->tweet->created .= "<a href=\"http://twitter.com/".$o['user']['screen_name']."/status/".$o['id_str']."\">".self::renderRelativeTime($o['created_at'])."</a>";
				}
				else {
					$twitter[$i]->tweet->created .= "<a href=\"http://twitter.com/".$o['user']['screen_name']."/status/".$o['id_str']."\">".JHTML::date($o['created_at'])."</a>";
				}
			}
			if (($params->get("showSource", 1) == 1)) {
				$twitter[$i]->tweet->created .= " via ".$o['source'];
			}
			if (($params->get("showLocation", 1)==1) && ($o['place']['full_name'])) {
				$twitter[$i]->tweet->created .= " from <a href=\"http://maps.google.com/maps?q=".$o['place']['full_name']."\" target=\"_blank\">".$o['place']['full_name']."</a>";
			}
			if (($o['in_reply_to_screen_name']) && ($o['in_reply_to_status_id_str'])) {
				$twitter[$i]->tweet->created .= " in reply to <a href=\"http://twitter.com/".$o['in_reply_to_screen_name']."/status/".$o['in_reply_to_status_id_str']."\">".$o['in_reply_to_screen_name']."</a>";
			}
			if (($params->get("showTweetReply", 1) == 1) || ($params->get("showRetweetCount", 1) == 1)) {
				$twitter[$i]->tweet->created .= " &bull; ";
			}
			if ($params->get("showTweetReply", 1) == 1) {
				$twitter[$i]->tweet->created .= "<a href=\"http://twitter.com/?status=@".$o['user']['screen_name']." &in_reply_to_status_id=".$o['id_str']."&in_reply_to=".$o['user']['screen_name']."\" target=\"_blank\">".JText::_('MOD_TWEETDISPLAYBACK_REPLY')."</a>";
			}
			if (($params->get("showTweetReply", 1) == 1) && ($params->get("showRetweetCount", 1) == 1)) {
				$twitter[$i]->tweet->created .= " &bull; ";
			}
			if (($params->get("showRetweetCount", 1) == 1) && ($o['retweet_count'] >= 1)) {
				$twitter[$i]->tweet->created .= JText::plural('MOD_TWEETDISPLAYBACK_RETWEETS', $o['retweet_count']);
			}
			if ($params->get("showLinks", 1) == 1) {
				$twitter[$i]->tweet->text = preg_replace("/@(\w+)/", "@<a href=\"http://twitter.com/\\1\" target=\"_blank\">\\1</a>", $twitter[$i]->tweet->text);
				$twitter[$i]->tweet->text = preg_replace("/#(\w+)/", "#<a href=\"http://twitter.com/search?q=\\1\" target=\"_blank\">\\1</a>", $twitter[$i]->tweet->text);
			}
			$i++;
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
		return JHTML::date($date);	
	}
}
