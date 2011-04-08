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

// Check if cURL is loaded; if not, proceed no further
if (!extension_loaded('curl')) {
	echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOCURL');
	return;
}
		
// Include the helper
require_once dirname(__FILE__).'/helper.php';

// Check the number of hits available if the cache is disabled or expired;
// If there are 0 hits remaining, then proceed no further
//TODO: Check if the cache is expired
if (($params->get('cache')) == 0) {
	$hits = modTweetDisplayBackHelper::getLimit($params);
	if ($hits == 0) {
		echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOHITS');
		return;
	}
}

//Initialize the cache
$conf = JFactory::getConfig();
$options = array(
	'defaultgroup' => 'mod_tweetdisplayback',
	'lifetime' => ($params->get('cache_time')));
$cache = JCache::getInstance('callback', $options );
if ($params->get('cache') == 1) {
	$cache->setCaching(true);
}

// Call the cache; if expired, pull new data
$twitter = $cache->call(array('modTweetDisplayBackHelper', 'compileData'), $params);
if (isset($twitter->error)) {
	// Error message already echoed
	return;
} else if (!$twitter) {
	echo JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');
	return;
}

require(JModuleHelper::getLayoutPath('mod_tweetdisplayback', $params->get('templateLayout', 'default')));
