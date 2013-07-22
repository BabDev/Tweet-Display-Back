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

// Include the helper
JLoader::register('ModTweetDisplayBackHelper', __DIR__ . '/helper.php');

/* @type JRegistry $params */
/* @type object $module */

// Set the template variables
$headerAlign    = $params->get('headerAvatarAlignment');
$tweetAlign     = $params->get('tweetAlignment');
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx  = htmlspecialchars($params->get('tweetclasssfx'));
$template       = $params->get('templateLayout', 'default');
$flist          = ModTweetDisplayBackHelper::toAscii($params->get('twitterList', ''));
$count          = $params->get('twitterCount', '3') - 1;

// Load module CSS
JHtml::stylesheet('mod_tweetdisplayback/' . $template . '.css', false, true, false);

$helper = new ModTweetDisplayBackHelper($params);
$helper->moduleId = $module->id;

// The files that the data is cached to
$cacheTweets = JPATH_CACHE . '/tweetdisplayback_tweets-' . $helper->moduleId . '.json';
$cacheUser   = JPATH_CACHE . '/tweetdisplayback_user-' . $helper->moduleId . '.json';

// Check if caching is enabled
if ($params->get('cache') == 1)
{
	// Fetch cache time from module parameters and convert to seconds
	$cacheTime = $params->get('cache_time', 15);
	$cacheTime = $cacheTime * 60;

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
		echo '<div class="well well-small TDB-tweet' . $tweetClassSfx . '">'
			. '<div class="TDB-tweet-container TDB-tweet-align-' . $tweetAlign . ' TDB-error">'
			. '<div class="TDB-tweet-text">' . JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD') . '</div>'
			. '</div></div>';

		return;
	}
}

// Add the Twitter Web Intents script if something else already hasn't
$scheme = JUri::getInstance()->getScheme() . '://';

/* @type JDocumentHtml $document */
$document = JFactory::getDocument();

if (!in_array('<script type="text/javascript" src="' . $scheme . 'platform.twitter.com/widgets.js" async="true"></script>', $document->_custom))
{
	$document->addCustomTag('<script type="text/javascript" src="' . $scheme . 'platform.twitter.com/widgets.js" async="true"></script>');
}

// Build the output
require JModuleHelper::getLayoutPath('mod_tweetdisplayback', $template);
