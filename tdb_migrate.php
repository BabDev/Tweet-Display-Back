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

/**
 * Tweet Display Back migration class from Joomla 1.5 to Joomla 1.6/1.7
 *
 * @package  TweetDisplayBack
 * @since    2.0
 */
class jUpgradeModuleTDB extends jUpgrade
{
	/**
	 * Check if extension migration is supported.
	 *
	 * @return  boolean  True if extension exists
	 *
	 * @since   2.0
	 */
	protected function detectExtension()
	{
		// For whatever reason, the module's dispatcher isn't there; proceed no further
		if (!file_exists(JPATH_ROOT . '/modules/mod_tweetdisplayback/mod_tweetdisplayback.php'))
		{
			return false;
		}
		return true;
	}

	/**
	 * Get update site information
	 *
	 * @return  array  List of tables without prefix
	 *
	 * @since   2.0
	 */
	protected function getUpdateSite()
	{
		return parent::getUpdateSite();
	}

	/**
	 * Get folders to be migrated.
	 *
	 * @return  array  List of folders relative to JPATH_ROOT
	 *
	 * @since   2.0
	 */
	protected function getCopyFolders()
	{
		return parent::getCopyFolders();
	}

	/**
	 * Migrate the folders.
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	protected function migrateExtensionFolders()
	{
		return parent::migrateExtensionFolders();
	}
}
