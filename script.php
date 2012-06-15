<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @package    TweetDisplayBack
 *
 * @copyright  Copyright (C) 2010-2011 Michael Babker. All rights reserved.
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Class to handle additional work during installation routine
 *
 * @package  TweetDisplayBack
 * @since    2.2
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
	 * @since   2.2
	 */
	public function preflight($type, $parent)
	{
		// Requires Joomla! 2.5
		$jversion = new JVersion;
		if (version_compare($jversion->getShortVersion(), '2.5', 'lt'))
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
	 * @since   2.2
	 */
	public function update($parent)
	{
		// Get the pre-update version
		$version = $this->_getVersion();

		// If in error, throw a message about the language files
		if ($version == 'Error')
		{
			JError::raiseNotice(null, JText::_('MOD_TWEETDISPLAYBACK_ERROR_INSTALL_UPDATE'));
			return;
		}

		// If coming from 2.1 or earlier, remove language files in system language folder
		if (version_compare($version, '2.2', 'lt'))
		{
			$this->_removeLanguageFiles();
		}
	}

	/**
	 * Function to get the currently installed version from the manifest cache
	 *
	 * @return  string  The version that is installed
	 *
	 * @since   2.2
	 */
	private function _getVersion()
	{
		// Get the record from the database
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('manifest_cache'));
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('mod_tweetdisplayback'));
		$db->setQuery($query);
		if (!$db->loadObject())
		{
			JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
			$version = 'Error';
			return $version;
		}
		else
		{
			$manifest = $db->loadObject();
		}

		// Decode the JSON
		$record = json_decode($manifest->manifest_cache);

		// Get the version
		$version = $record->version;

		return $version;
	}

	/**
	 * Function to remove language files from the system language folder due to changing to
	 * module language files for 2.2
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	private function _removeLanguageFiles()
	{
		jimport('joomla.filesystem.file');

		$lang = JFactory::getLanguage();

		$langCode = $lang->getTag();

		$base = JPATH_SITE . '/language/' . $langCode . '/';
		$engBase = JPATH_SITE . '/language/en-GB/';

		// The language files for pre-2.2
		$files = array($langCode . '.mod_tweetdisplayback.ini', $langCode . '.mod_tweetdisplayback.sys.ini');
		$engFiles = array('en-GB.mod_tweetdisplayback.ini', 'en-GB.mod_tweetdisplayback.sys.ini');

		// Remove the files
		foreach ($files as $file)
		{
			if (JFile::exists($base . $file))
			{
				JFile::delete($base . $file);
			}
		}

		// Check for and remove en-GB files
		foreach ($engFiles as $engFile)
		{
			if (JFile::exists($engBase . $engFile))
			{
				JFile::delete($engBase . $engFile);
			}
		}
	}
}
