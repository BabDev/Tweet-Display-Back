<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @package	  TweetDisplayBack
 *
 * @copyright  Copyright (C) 2010-2011 Michael Babker. All rights reserved.
 * @license	   GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Class to handle additional work during installation routine
 *
 * @package  PodcastManager
 * @since    2.2
 */
class Mod_TweetDisplayBackInstallerScript
{
	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string  $type    The action being performed
	 * @param   string  $parent  The function calling this method
	 *
	 * @return  mixed  Boolean false on failure, void otherwise
	 *
	 * @since   2.2
	 */
	function preflight($type, $parent)
	{
		// Check if the cURL extension is available
		if (!extension_loaded('curl'))
		{
			JError::raiseNotice(null, JText::_('MOD_TWEETDISPLAYBACK_ERROR_NOCURL'));
			return false;
		}

		// Requires Joomla! 2.5
		//@TODO: Change version check once 2.5 builds available
		$jversion = new JVersion;
		if (version_compare($jversion->getShortVersion(), '1.7.3', 'lt'))
		{
			JError::raiseNotice(null, JText::_('MOD_TWEETDISPLAYBACK_ERROR_INSTALL_VERSION'));
			return false;
		}

		return true;
	}
}
