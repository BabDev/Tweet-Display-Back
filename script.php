<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @copyright  Copyright (C) 2010-2015 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

/**
 * Class to handle additional work during installation routine
 *
 * @since  3.0
 */
class Mod_TweetDisplayBackInstallerScript
{
	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string            $type    The action being performed
	 * @param   JInstallerModule  $parent  The function calling this method
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   3.0
	 */
	public function preflight($type, $parent)
	{
		// Requires PHP 5.3
		if (version_compare(PHP_VERSION, '5.3', 'lt'))
		{
			JError::raiseNotice(null, JText::_('MOD_TWEETDISPLAYBACK_ERROR_INSTALL_PHPVERSION'));

			return false;
		}

		// Requires Joomla! 3.4.0
		if (version_compare(JVERSION, '3.4.0', 'lt'))
		{
			JError::raiseNotice(null, JText::_('MOD_TWEETDISPLAYBACK_ERROR_INSTALL_VERSION'));

			return false;
		}

		return true;
	}

	/**
	 * Function to perform changes during update
	 *
	 * @param   JInstallerModule  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function update($parent)
	{
		// Remove files deleted between versions
		$this->_removeFiles();
	}

	/**
	 * Function to remove files deleted between updates
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	private function _removeFiles()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Remove the connector compatibility files
		$base  = JPATH_SITE . '/modules/mod_tweetdisplayback/';
		$files = array('compat.php', 'curl.php', 'curl_25.php');

		// Remove the files
		foreach ($files as $file)
		{
			if (is_file($base . $file))
			{
				JFile::delete($base . $file);
			}
		}

		// Get the language tag for the site to delete non-English language files
		$langTag = JFactory::getLanguage()->getTag();

		$base    = JPATH_SITE . '/language/' . $langTag . '/';
		$engBase = JPATH_SITE . '/language/en-GB/';

		// The language files for pre-3.0
		$files    = array($langTag . '.mod_tweetdisplayback.ini', $langTag . '.mod_tweetdisplayback.sys.ini');
		$engFiles = array('en-GB.mod_tweetdisplayback.ini', 'en-GB.mod_tweetdisplayback.sys.ini');

		// Remove the files
		foreach ($files as $file)
		{
			if (is_file($base . $file))
			{
				JFile::delete($base . $file);
			}
		}

		// Check for and remove en-GB files
		foreach ($engFiles as $engFile)
		{
			if (is_file($engBase . $engFile))
			{
				JFile::delete($engBase . $engFile);
			}
		}

		// Remove the module's media folder if it exists
		if (is_dir(JPATH_SITE . '/modules/mod_tweetdisplayback/media/'))
		{
			JFolder::delete(JPATH_SITE . '/modules/mod_tweetdisplayback/media/');
		}

		// The widget template files to remove
		$base  = JPATH_SITE . '/modules/mod_tweetdisplayback/tmpl/';
		$files = array('w_list.php', 'w_profile.php', 'w_search.php');

		// Remove the files
		foreach ($files as $file)
		{
			if (is_file($base . $file))
			{
				JFile::delete($base . $file);
			}
		}

		// Remove the HTTP connector fork if it exists
		if (is_dir(JPATH_SITE . '/modules/mod_tweetdisplayback/libraries/http/'))
		{
			JFolder::delete(JPATH_SITE . '/modules/mod_tweetdisplayback/librarie/http/');
		}
	}
}
