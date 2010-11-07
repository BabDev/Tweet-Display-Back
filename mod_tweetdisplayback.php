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

// Include the helper
require_once dirname(__FILE__).DS.'helper.php';

// initialize the cache
jimport('joomla.cache.cache');
$conf =& JFactory::getConfig();
$options = array(
	'defaultgroup' => 'mod_tweetdisplayback',
	'cachebase' => $conf->get('config.cache_path'),
	'lifetime' => $params->get('cachetime') * 60, // minutes to seconds
	'language' => $conf->get('config.language'),
	'storage' => 'file' );
$cache =& JCache::getInstance("callback", $options );
$cache->setCaching($params->get("cache"));

// do cache call
$twitter = $cache->call(array('tweetDisplayHelper', 'getTweets'), $params);
if (!$twitter) {
	echo JText::_('MOD_TWEETDISPLAYBACK_UNABLE_TO_LOAD');
	return;
}
$layout = $params->get("layout", "default");
require(JModuleHelper::getLayoutPath('mod_tweetdisplayback', $layout));
