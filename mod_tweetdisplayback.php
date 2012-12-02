<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @package    TweetDisplayBack
 *
 * @copyright  Copyright (C) 2010-2012 Michael Babker. All rights reserved.
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

// Include the helper
JLoader::register('ModTweetDisplayBackHelper', __DIR__ . '/helper.php');

/* @var JRegistry $params */

// Set the template variables
$imgpath        = JUri::root() . 'modules/mod_tweetdisplayback/media/images';
$headerAlign    = $params->get('headerAvatarAlignment');
$tweetAlign     = $params->get('tweetAlignment');
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx  = htmlspecialchars($params->get('tweetclasssfx'));
$template       = $params->get('templateLayout', 'default');
$flist          = ModTweetDisplayBackHelper::toAscii($params->get('twitterList', ''));
$count          = $params->get('twitterCount', '3') - 1;

// Don't load module CSS if loading a widget
if ($params->get('twitterFeedType') != 'widget')
{
	// If CSS3 is selected, load it's stylesheet except for nostyle or Bootstrap
	$css3 = '';

	if ($params->get('templateCSS3', 1) == 1 && $template != ('nostyle' || 'bootstrap'))
	{
		$css3 = '-css3';
	}
	JHtml::stylesheet('modules/mod_tweetdisplayback/media/css/' . $template . $css3 . '.css', false, false, false);
}

// If using a widget, don't need to perform custom module rendering
if ($params->get('twitterFeedType') != 'widget')
{
	// Instantiate the helper
	$helper = new ModTweetDisplayBackHelper($params);

	// Check if caching is enabled
	if ($params->get('cache') == 1)
	{
		// Fetch cache time from module parameters and convert to seconds
		$cacheTime = $params->get('cache_time', 15);
		$cacheTime = $cacheTime * 60;

		// The files that the data is cached to
		$cacheTweets = JPATH_CACHE . '/tweetdisplayback_tweets.json';
		$cacheUser   = JPATH_CACHE . '/tweetdisplayback_user.json';

		// Cache files expired?
		if ((!file_exists($cacheTweets) && !file_exists($cacheUser)) || (time() - @filemtime($cacheTweets) > $cacheTime && time() - @filemtime($cacheUser) > $cacheTime))
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
		if (!$helper->isProcessed && isset($twitter['hits']))
		{
			echo '<div class="well well-small TDB-tweet' . $tweetClassSfx . '">'
				. '<div class="TDB-tweet-container TDB-tweet-align-' . $tweetAlign . ' TDB-error">'
				. '<div class="TDB-tweet-text">' . JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOHITS') . '</div>'
				. '</div></div>';

			return;
		}
		// No data object and no other error was set
		elseif (!$helper->isProcessed && (!$twitter) || (isset($twitter['error'])))
		{
			echo '<div class="well well-small TDB-tweet' . $tweetClassSfx . '">'
				. '<div class="TDB-tweet-container TDB-tweet-align-' . $tweetAlign . ' TDB-error">'
				. '<div class="TDB-tweet-text">' . JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD') . '</div>'
				. '</div></div>';

			return;
		}
	}
}

// Add the Twitter Web Intents script if something else already hasn't
$scheme = JUri::getInstance()->getScheme() . '://';

/* @var JDocumentHtml $document */
$document = JFactory::getDocument();

if (!in_array('<script type="text/javascript" src="' . $scheme . 'platform.twitter.com/widgets.js"></script>', $document->_custom))
{
	$document->addCustomTag('<script type="text/javascript" src="' . $scheme . 'platform.twitter.com/widgets.js"></script>');
}

// Add the Widgets script if needed
if ($params->get('twitterFeedType') == 'widget')
{
	if (!in_array('<script type="text/javascript" src="' . $scheme . 'widgets.twimg.com/j/2/widget.js"></script>', $document->_custom))
	{
		$document->addCustomTag('<script type="text/javascript" src="' . $scheme . 'widgets.twimg.com/j/2/widget.js"></script>');
	}
}

// Set the template to the correct option
if ($params->get('twitterFeedType') == 'widget')
{
	$output = 'w_' . $params->get('templateWidget');
}
else
{
	$output = $template;
}

// Build the output
require JModuleHelper::getLayoutPath('mod_tweetdisplayback', $output);
