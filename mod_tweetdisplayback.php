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

/* @type  \Joomla\Registry\Registry  $params */
/* @type  object                     $module */

// Set the template variables
$headerAlign    = $params->get('headerAvatarAlignment');
$tweetAlign     = $params->get('tweetAlignment');
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx  = htmlspecialchars($params->get('tweetclasssfx'));
$templateLayout = $params->get('layout', 'default');
$flist          = ModTweetDisplayBackHelper::toAscii($params->get('twitterList', ''));
$count          = $params->get('twitterCount', '3') - 1;

// Load module CSS
JHtml::_('stylesheet', 'mod_tweetdisplayback/' . $templateLayout . '.css', false, true, false);

try
{
	$helper = new ModTweetDisplayBackHelper($params);
}
catch (RuntimeException $e)
{
	// No HTTP adapters are available on this system
	echo '<div class="well well-small TDB-tweet' . $tweetClassSfx . '">'
		. '<div class="TDB-tweet-container TDB-tweet-align-' . $tweetAlign . ' TDB-error">'
		. '<div class="TDB-tweet-text">' . JText::_('MOD_TWEETDISPLAYBACK_ERROR_NO_CONNECTORS') . '</div>'
		. '</div></div>';

	return;
}

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
		$message = '<div class="well well-small TDB-tweet' . $tweetClassSfx . '"><div class="TDB-tweet-container TDB-tweet-align-' . $tweetAlign . ' TDB-error">'
			. '<div class="TDB-tweet-text">' . JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD');

		if (isset($twitter['error']['messages']) && count($twitter['error']['messages']))
		{
			foreach ($twitter['error']['messages'] as $message)
			{
				$message .= "<br />$message";
			}
		}

		$message .= '</div></div></div>';

		echo $message;

		return;
	}
}

/* @type JDocumentHtml $document */
JFactory::getDocument()->addScript('https://platform.twitter.com/widgets.js', 'text/javascript', false, true);

// Build the output
require JModuleHelper::getLayoutPath('mod_tweetdisplayback', $templateLayout);
