<?php
/**
* Tweet Display Back Module for Joomla!
*
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* @package		mod_tweetdisplayback
*/

// No direct access
defined('_JEXEC') or die;

/**
 * Tweet Display Back migration class from Joomla 1.5 to Joomla 1.6/1.7
 *
 * @package	mod_tweetdisplayback
 * @since	2.0.1
 */
class jUpgradeModuleTDB extends jUpgrade {
	/**
	 * Check if extension migration is supported.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	protected function detectExtension() {
		if (!file_exists(JPATH_ROOT.'/modules/mod_tweetdisplayback/mod_tweetdisplayback.php')) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get update site information
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.1.0
	 */
	protected function getUpdateSite() {
		return parent::getUpdateSite();
	}

	/**
	 * Get folders to be migrated.
	 *
	 * @return	array	List of folders relative to JPATH_ROOT
	 * @since	1.1.0
	 */
	protected function getCopyFolders() {
		return parent::getCopyFolders();
	}

	/**
	 * Migrate the folders.
	 *
	 * This function gets called after tables have been copied.
	 *
	 * If you want to split this task into even smaller chunks,
	 * please store your custom state variables into $this->state and return false.
	 * Returning false will force jUpgrade to call this function again,
	 * which allows you to continue import by reading $this->state before continuing.
	 *
	 * @return	boolean Ready (true/false)
	 * @since	1.1.0
	 */
	protected function migrateExtensionFolders()
	{
		return parent::migrateExtensionFolders();
	}
}
