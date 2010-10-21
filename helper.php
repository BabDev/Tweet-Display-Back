<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
* Module forked from TweetXT for Joomla!
* Original Copyright ((c) 2009 joomlaxt.com, All rights reserved - http://www.joomlaxt.com
*/

// no direct access
defined('_JEXEC') or die;

class tweetDisplayHelper {

	function getTweets($params) {
		// check the number of hits available; if 0, proceed no further
		$hits = self::getLimit($params);
		
		if ($hits == '0') {
			$twitter = JText::_('MOD_TWEETDISPLAYBACK_EXCEEDED');
			return $twitter;
		}
		
		$uname = $params->get("twitterName","");
		$count = $params->get("twitterCount",3);
		$retweet = $params->get("showRetweets",1);
		
		if ($retweet == 1)
		{
			$req = "http://api.twitter.com/1/statuses/user_timeline.json?count=".$count."&include_rts=1&screen_name=".$uname."";
		}
		else
		{
			$req = "http://api.twitter.com/1/statuses/user_timeline.json?count=".$count."&screen_name=".$uname."";
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
		$obj = json_decode($json);
		
		if (isset($obj->error)) return false;
		
		// get user Info from first tweet
		if (isset ($obj[0]))
		{ 
		 	$twitter->user = $obj[0]->user;
		}
		else
		{
		 	return false;
		}
		$i = 0;
		
		// get the tweets
		foreach ($obj as $o)
		{
		 	foreach ($o as $k=>$v)
		 	{
				if ($k != "user")
				{
					$t->$k = $v;
				}
			}
			$tweet[] = clone $t;
		$i++;
		if ($i == $count) break;
		}
		$twitter->tweets = $tweet;
		$twitter = renderTwitter($twitter, $params);
		return $twitter;
	}
	
	function getLimit($params) {
		$uname = $params->get("twitterName","");
		$req = "http://api.twitter.com/1/account/rate_limit_status.json?screen_name=".$uname."";
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
		$obj = json_decode($json);
		
		if (isset($obj->error)) return false;
		
		// get the remaining hits count
		if (isset ($obj->{'remaining_hits'}))
		{ 
		 	$hits = $obj->{'remaining_hits'};
		}
		else
		{
		 	return false;
		}
		return $hits;
	}
}

function renderTwitter($twitter, $params) {
	// header
	if ($params->get("showHeaderUser", 1)==1) {
		$twitter->header->user = "<a href=\"http://twitter.com/".$twitter->user->screen_name."\">".$twitter->user->screen_name."</a><br />";
	}
	if ($params->get("showHeaderBio", 1)==1) {
		$twitter->header->bio = $twitter->user->description."<br />";
	}
	if ($params->get("showHeaderLocation", 1)==1) {
		$twitter->header->location = $twitter->user->location."<br />";
	}
	if ($params->get("showHeaderWeb", 1)==1) {
		$twitter->header->web = "<a href=\"".$twitter->user->url."\">".$twitter->user->url."</a>";
	}
	$twitter->header->avatar = "<img src=\"http://api.twitter.com/1/users/profile_image/twitter.json?screen_name=".$twitter->user->screen_name."&size=bigger\" align=\"".$params->get("headerAvatarAlignment")."\" width=\"73px\" alt=\"".$twitter->user->screen_name."\" />";
	// footer
	if ($params->get("showFollowLink", 1)==1) {
		$twitter->footer->follow_me = "<hr /><div class=\"followLink\"><b><a href=\"http://twitter.com/".$twitter->user->screen_name."\" rel=\"nofollow\">".$params->get('followText', 'Follow me on Twitter')."</a></b></div>";
	}
	if ($params->get("showPoweredBy", 1)==1) {
		$twitter->footer->powered_by = "<hr /><div class=\"poweredBy\">Powered by <a href=\"http://www.flbab.com/extensions/tweet-display-back/13-info\">Tweet Display Back</a></div>";
	}
	// tweets
	foreach ($twitter->tweets as $t) {
		// user
		if ($params->get("showTweetName", 1)==1) {
			$t->tweet->user = "<b><a href=\"http://twitter.com/".$twitter->user->screen_name."\">".$twitter->user->screen_name."</a>:</b> ";
		}
		if ($params->get("showTweetCreated", 1)==1) {
			if ($params->get("relativeTime", 1) == 1) {
				$t->tweet->created = "<a href=\"http://twitter.com/".$twitter->user->screen_name."/status/".$t->id."\">".getRelativeTime($t->created_at)."</a>";
			}
			else {
				$t->tweet->created = "<a href=\"http://twitter.com/".$twitter->user->screen_name."/status/".$t->id."\">".JHTML::date($t->created_at)."</a>";
			}
		}
		if (($params->get("showSource", 1) == 1)) {
			$t->tweet->created .= " via ".$t->source;
		}
		if (($params->get("showLocation", 1)==1) && ($t->place->full_name)) {
			$t->tweet->created .= " from <a href=\"http://maps.google.com/maps?q=".$t->place->full_name."\" target=\"_blank\">".$t->place->full_name."</a>";
		}
		if (($t->in_reply_to_screen_name) && ($t->in_reply_to_status_id)) {
			$t->tweet->created .= " in reply to <a href=\"http://twitter.com/".$t->in_reply_to_screen_name."/status/".$t->in_reply_to_status_id."\">".$t->in_reply_to_screen_name."</a>";
		}
		$t->tweet->avatar = "<img align=\"".$params->get("tweetDisplayLocation")."\" alt=\"".$twitter->user->screen_name."\" src=\"".$twitter->user->profile_image_url."\" width=\"32px\"/>";
		$t->tweet->text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $t->text);
		if ($params->get("showLinks", 1) == 1) {
			$t->tweet->text = preg_replace("/@(\w+)/", "@<a href=\"http://twitter.com/\\1\" target=\"_blank\">\\1</a>", $t->tweet->text);
			$t->tweet->text = preg_replace("/#(\w+)/", "#<a href=\"http://twitter.com/search?q=\\1\" target=\"_blank\">\\1</a>", $t->tweet->text);
		}
	}
	return $twitter;
}

function getRelativeTime($date) {
	$diff = time() - strtotime($date);
	if ($diff < 60) {
		return JText::_('MOD_TWEETDISPLAYBACK_CREATE_LESSTHANAMINUTE');
	}
	$diff = round($diff/60);
	if ($diff < 2) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_MINUTE');
	}
	if ($diff < 60) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_MINUTES');
	}
	$diff = round($diff/60);
	if ($diff < 2) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_HOUR');
	}
	if ($diff < 24) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_HOURS');
	}
	$diff = round($diff/24);
	if ($diff < 2) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_DAY');
	}
	if ($diff < 7) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_DAYS');
	}
	$diff = round($diff/7);
	if ($diff < 2) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_WEEK');
	}
	if ($diff < 4) {
		return $diff . JText::_('MOD_TWEETDISPLAYBACK_CREATE_WEEKS');
	}
	return JHTML::date($date);	
}
