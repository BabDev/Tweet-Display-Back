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

// Set the template variables
$imgpath = JURI::root() . 'modules/mod_tweetdisplayback/media/images';
$headerAlign = $params->get('headerAvatarAlignment');
$tweetAlign = $params->get('tweetAlignment');
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx = htmlspecialchars($params->get('tweetclasssfx'));
$template = $params->get('templateLayout', 'default');
$flist = ModTweetDisplayBackHelper::toAscii($params->get('twitterList', ''));
$count = $params->get('twitterCount', '3') - 1;

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
	// Check if caching is enabled
	if ($params->get('cache') == 1)
	{
		// Set the cache parameters
		$options = array('defaultgroup' => 'mod_tweetdisplayback');
		$cache = JCache::getInstance('callback', $options);
		$cacheTime = $params->get('cache_time');
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
	if (isset($twitter->hits))
	{
		echo '<div class="well TDB-tweet' . $tweetClassSfx . '">'
			. '<div class="TDB-tweet-container TDB-tweet-align-' . $tweetAlign . ' TDB-error">'
			. '<div class="TDB-tweet-text">' . JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOHITS') . '</div>'
			. '</div></div>';
		return;
	}
	// No data object and no other error was set
	elseif ((!$twitter) || (isset($twitter->error)))
	{
		echo '<div class="well TDB-tweet' . $tweetClassSfx . '">'
			. '<div class="TDB-tweet-container TDB-tweet-align-' . $tweetAlign . ' TDB-error">'
			. '<div class="TDB-tweet-text">' . JText::_('MOD_TWEETDISPLAYBACK_ERROR_UNABLETOLOAD') . '</div>'
			. '</div></div>';
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
	if (!in_array('<script type="text/javascript" src="http://widgets.twimg.com/j/2/widget.js"></script>', $document->_custom))
	{
		$document->addCustomTag('<script type="text/javascript" src="http://widgets.twimg.com/j/2/widget.js"></script>');
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
