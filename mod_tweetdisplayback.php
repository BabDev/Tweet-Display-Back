<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @copyright  Copyright (C) 2010-2015 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

// Include the helper
JLoader::register('ModTweetDisplayBackHelper', __DIR__ . '/helper.php');

/**
 * Module variables
 * -----------------
 * @var   object                     $module    A module object
 * @var   array                      $attribs   An array of attributes for the module (probably from the XML)
 * @var   array                      $chrome    The loaded module chrome files
 * @var   JApplicationCms            $app       The active application singleton
 * @var   string                     $scope     The application scope before the module was included
 * @var   \Joomla\Registry\Registry  $params    Module parameters
 * @var   string                     $template  The active template
 * @var   string                     $path      The path to this module file
 * @var   JLanguage                  $lang      The active JLanguage singleton
 * @var   string                     $content   Module output content
 */

// Set the template variables
$headerAlign    = $params->get('headerAvatarAlignment');
$tweetAlign     = $params->get('tweetAlignment');
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx  = htmlspecialchars($params->get('tweetclasssfx'));
$flist          = ModTweetDisplayBackHelper::toAscii($params->get('twitterList', ''));
$count          = $params->get('twitterCount', '3') - 1;

try
{
	$helper = new ModTweetDisplayBackHelper($params);
}
catch (RuntimeException $e)
{
	// No HTTP adapters are available on this system
	$errorMsg = JText::_('MOD_TWEETDISPLAYBACK_ERROR_NO_CONNECTORS');

	require JModuleHelper::getLayoutPath('mod_tweetdisplayback', '_error');

	return;
}

$helper->moduleId = $module->id;

// The files that the data is cached to
$cacheTweets = JPATH_CACHE . '/tweetdisplayback_tweets-' . $helper->moduleId . '.json';
$cacheUser   = JPATH_CACHE . '/tweetdisplayback_user-' . $helper->moduleId . '.json';

// Check if caching is enabled
if ($params->get('tweet_cache') == 1)
{
	// Fetch cache time from module parameters
	$cacheTime = $params->get('tweet_cache_time', 900);

	// Cache files expired?
	if (!file_exists($cacheTweets) || time() - @filemtime($cacheTweets) > $cacheTime)
	{
		// Do a request to the Twitter API for new data
		$twitter = $helper->compileData();
	}
	else
	{
		// Render from the cached data
		$helper->isCached = true;
		$twitter = $helper->compileFromCache();
	}
}
else
{
	// Do a request to the Twitter API for new data
	$twitter = $helper->compileData();
}

// Check to see if processing finished
if (!$helper->isProcessed)
{
	// If we have cache files still, try to render from them
	if (file_exists($cacheTweets) && file_exists($cacheUser))
	{
		// Render from the cached data
		$helper->isCached = true;
		$twitter = $helper->compileFromCache();
	}

	// Check for error objects if processing did not finish
	if (!$helper->isProcessed && (!$twitter) || (isset($twitter['error'])))
	{
		$errorMsg = JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');

		require JModuleHelper::getLayoutPath('mod_tweetdisplayback', '_error');

		return;
	}
}

// Build the output
require JModuleHelper::getLayoutPath('mod_tweetdisplayback', $params->get('layout', 'default'));
