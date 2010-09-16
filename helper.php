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

	function getLimit($params) {
		$uname = $params->get("twitterName","");
		$count = $params->get("twitterCount",3);
		$url = "api.twitter.com";
		$req = "1/account/rate_limit_status.json?screen_name=".$uname."";
		$fp = fsockopen ($url, 80, $errno, $errstr, 30);
		
		if (!$fp || $errno) return $errstr;
        
		@fputs($fp, "GET ".$req." HTTP/1.1\r\n");
        @fputs($fp, "HOST: ".$url."\r\n");
        @fputs($fp, "Connection: close\r\n\r\n");
		
        // read the body data
		$res = '';
		$headerdone = false;
		while (!feof($fp))
		{
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0)
			{
				// read the header
				$headerdone = true;
			}
			else if ($headerdone)
			{
				// header has been read. now read the contents
				$res .= $line;
			}
		}
		fclose ($fp);
		$obj = json_decode($res);
		return $obj;
	}

	function getTweets($params) {
		$uname = $params->get("twitterName","");
		$count = $params->get("twitterCount",3);
		$retweet = $params->get("showRetweets",1);
		$url = "api.twitter.com";
		
		if ($retweet == 1)
		{
			$req = "/1/statuses/user_timeline.json?count=".$count."&include_rts=1&screen_name=".$uname."";
		}
		else
		{
			$req = "/1/statuses/user_timeline.json?count=".$count."&screen_name=".$uname."";
		}
		$fp = fsockopen ($url, 80, $errno, $errstr, 30);
		if (!$fp || $errno) return $errstr;
        @fputs($fp, "GET ".$req." HTTP/1.1\r\n");
        @fputs($fp, "HOST: ".$url."\r\n");
        @fputs($fp, "Connection: close\r\n\r\n");
        
		// read the body data
		$res = '';
		$headerdone = false;
		while (!feof($fp))
		{
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0)
			{
				// read the header
				$headerdone = true;
			}
			else if ($headerdone)
			{
				// header has been read. now read the contents
				$res .= $line;
			}
		}
		fclose ($fp);
		$obj = json_decode($res);
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
}

function renderTwitter($twitter, $params) {
	// header
	$twitter->user->header_user = "<a href=\"http://twitter.com/".$twitter->user->screen_name."\">".$twitter->user->screen_name."</a>";
	$twitter->user->header_avatar = "<img src=\"http://api.twitter.com/1/users/profile_image/twitter.json?screen_name=".$twitter->user->screen_name."&size=bigger\" align=\"".$params->get("headerAvatarAlignment")."\" width=\"73px\" alt=\"".$twitter->user->screen_name."\" />";
	$twitter->user->header_web = "<a href=\"".$twitter->user->url."\">".$twitter->user->url."</a>";
	// user
	$twitter->user->tweet_user = "<a href=\"http://twitter.com/".$twitter->user->screen_name."\">".$twitter->user->screen_name."</a>";
	// tweets
	foreach ($twitter->tweets as $t) {
		if ($params->get("relativeTime", 1) == 1) {
			$t->created_html = "<a href=\"http://twitter.com/".$twitter->user->screen_name."/status/".$t->id."\">".getRelativeTime($t->created_at)."</a>";
		}
		else {
			$t->created_html = "<a href=\"http://twitter.com/".$twitter->user->screen_name."/status/".$t->id."\">".JHTML::date($t->created_at)."</a>";
		}
		if (($params->get("showSource", 1) == 1)) {
			$t->created_html .= " via ".$t->source;
		}
		$t->reply_html = "in reply to <a href=\"http://twitter.com/".$t->in_reply_to_screen_name."/status/".$t->in_reply_to_status_id."\">".$t->in_reply_to_screen_name."</a>";
		$t->location_html = "from <a href=\"http://maps.google.com/maps?q=".$t->place->full_name."\" target=\"_blank\">".$t->place->full_name."</a>";
		$t->tweet_avatar = "<img align=\"".$params->get("tweetDisplayLocation")."\" alt=\"".$twitter->user->screen_name."\" src=\"".$twitter->user->profile_image_url."\" width=\"32px\"/>";
		$t->text_html = preg_replace("/(http:\/\/[^\s]+)/", "<a href=\"$1\">$1</a>", $t->text);
		if ($params->get("showLinks", 1) == 1) {
			$t->text_html = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $t->text_html);
			$t->text_html = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $t->text_html);
			$t->text_html = preg_replace("/@(\w+)/", "@<a href=\"http://twitter.com/\\1\" target=\"_blank\">\\1</a>", $t->text_html);
			$t->text_html = preg_replace("/#(\w+)/", "#<a href=\"http://twitter.com/search?q=\\1\" target=\"_blank\">\\1</a>", $t->text_html);
		}
	}
	return $twitter;
}

function getRelativeTime($date) {
	$diff = time() - strtotime($date);
	if ($diff < 60) {
		return JText::_("Less than a minute ago");
	}
	$diff = round($diff/60);
	if ($diff < 2) {
		return $diff . JText::_(" minute ago");
	}
	if ($diff < 60) {
		return $diff . JText::_(" minutes ago");
	}
	$diff = round($diff/60);
	if ($diff < 2) {
		return $diff . JText::_(" hour ago");
	}
	if ($diff < 24) {
		return $diff . JText::_(" hours ago");
	}
	$diff = round($diff/24);
	if ($diff < 2) {
		return $diff . JText::_(" day ago");
	}
	if ($diff < 7) {
		return $diff . JText::_(" days ago");
	}
	$diff = round($diff/7);
	if ($diff < 2) {
		return $diff . JText::_(" week ago");
	}
	if ($diff < 4) {
		return $diff . JText::_(" weeks ago");
	}
	return JHTML::date($date);	
}
