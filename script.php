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
	 * Minimum supported Joomla! version
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $minimumJoomlaVersion = '3.4';

	/**
	 * Minimum supported PHP version
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $minimumPHPVersion = '5.4';

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
		// PHP Version Check
		if (version_compare(PHP_VERSION, $this->minimumPHPVersion, 'lt'))
		{
			JError::raiseNotice(
				null, JText::sprintf('MOD_TWEETDISPLAYBACK_ERROR_INSTALL_PHPVERSION', $this->minimumPHPVersion)
			);

			return false;
		}

		// Joomla! Version Check
		if (version_compare(JVERSION, $this->minimumJoomlaVersion, 'lt'))
		{
			JError::raiseNotice(
				null, JText::sprintf('MOD_TWEETDISPLAYBACK_ERROR_INSTALL_VERSION', $this->minimumJoomlaVersion)
			);

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
		$this->removeFiles();
	}

	/**
	 * Function to remove files deleted between updates
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	private function removeFiles()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Remove the connector compatibility files
		$base  = JPATH_SITE . '/modules/mod_tweetdisplayback/';
		$files = ['compat.php', 'curl.php', 'curl_25.php'];

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
		$files    = [$langTag . '.mod_tweetdisplayback.ini', $langTag . '.mod_tweetdisplayback.sys.ini'];
		$engFiles = ['en-GB.mod_tweetdisplayback.ini', 'en-GB.mod_tweetdisplayback.sys.ini'];

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
		$files = ['w_list.php', 'w_profile.php', 'w_search.php'];

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
