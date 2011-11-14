<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @package	  TweetDisplayBack
 *
 * @copyright  Copyright (C) 2010-2011 Michael Babker. All rights reserved.
 * @license	   GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

// Include the helper
require_once dirname(__FILE__).'/helper.php';

// Set the template variables
$imgpath 		= JURI::root().'modules/mod_tweetdisplayback/media/images';
$headerAlign	= $params->get('headerAvatarAlignment');
$tweetAlign		= $params->get('tweetAlignment');
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx	= htmlspecialchars($params->get('tweetclasssfx'));
$template		= $params->get('templateLayout', 'default');
$flist			= ModTweetDisplayBackHelper::toAscii($params->get('twitterList', ''));
$count			= $params->get('twitterCount', '3') - 1;

// Don't load module CSS if loading a widget
if ($params->get('twitterFeedType') != 'widget')
{
	// If CSS3 is selected, load it's stylesheet except for nostyle
	$css3	= '';
	if ($params->get('templateCSS3', 1) == 1 && $template != 'nostyle')
	{
		// If CSS3 is selected, load it's stylesheet except for nostyle
		$css3	= '';
		if ($params->get('templateCSS3', 1) == 1 && $template != 'nostyle')
		{
			$css3	= '-css3';
		}
	}
	JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/'.$template.$css3.'.css', false, false, false);
}

// Check if cURL is loaded; if not, proceed no further
if (!extension_loaded('curl'))
{
	echo '<div class="TDB-tweet'.$tweetClassSfx.$tweetAvatar.'"><div class="TDB-tweet-container TDB-tweet-align-'.$tweetAlign.' TDB-error"><div class="TDB-tweet-text">'.JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOCURL').'</div></div></div>';
	return;
}

// If using a widget, don't need to perform custom module rendering
if ($params->get('twitterFeedType') != 'widget')
{
	// Check if caching is enabled
	if ($params->get('cache') == 1)
	{
		// Set the cache parameters
		$options = array(
			'defaultgroup' => 'mod_tweetdisplayback');
		$cache		= JCache::getInstance('callback', $options);
		$cacheTime	= $params->get('cache_time');
		// J! 1.5 and 1.6 cache is set in seconds, 1.7 caches in minutes
		if (version_compare(JVERSION, '1.7.0', 'ge'))
		{
			$cacheTime	= round($cacheTime / 60);
		}
		$cache->setLifeTime($cacheTime);
		$cache->setCaching(true);

		// Call the cache; if expired, pull new data
		$twitter = $cache->call(array('ModTweetDisplayBackHelper', 'compileData'), $params);
	}
	else
	{
		// Pull new data
		$twitter = ModTweetDisplayBackHelper::compileData($params);
	}

	// No hits remaining
	if (isset($twitter['hits']))
	{
		echo '<div class="TDB-tweet'.$tweetClassSfx.$tweetAvatar.'"><div class="TDB-tweet-container TDB-tweet-align-'.$tweetAlign.' TDB-error"><div class="TDB-tweet-text">'.JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOHITS').'</div></div></div>';
		return;
	}
	// No data object and no other error was set
	elseif ((!$twitter) || (isset($twitter['error'])))
	{
		echo '<div class="TDB-tweet'.$tweetClassSfx.$tweetAvatar.'"><div class="TDB-tweet-container TDB-tweet-align-'.$tweetAlign.' TDB-error"><div class="TDB-tweet-text">'.JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD').'</div></div></div>';
		return;
	}
}

// Add the Twitter Web Intents script if something else already hasn't
$document = JFactory::getDocument();
if (!in_array('<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>', $document->_custom))
{
	$document->addCustomTag('<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>');
}

// Add the Widgets script if needed
if ($params->get('twitterFeedType') == 'widget')
{
	if (!in_array('<script src="http://widgets.twimg.com/j/2/widget.js"></script>', $document->_custom))
	{
		$document->addCustomTag('<script src="http://widgets.twimg.com/j/2/widget.js"></script>');
	}
}

// Set the template to the correct option
if ($params->get('twitterFeedType') == 'widget')
{
	$output = 'w_'.$params->get('templateWidget');
}
else
{
	$output = $template;
}

// Build the output
require JModuleHelper::getLayoutPath('mod_tweetdisplayback', $output);
