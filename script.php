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
class Mod_TweetDisplayBackInstallerScript extends JInstallerScript
{
	/**
	 * Constructor
	 *
	 * @since   4.0
	 */
	public function __construct()
	{
		$this->minimumJoomla = '3.6';
		$this->minimumPhp    = '5.4';

		$this->deleteFiles = [
			'/language/en-GB/en-GB.mod_tweetdisplayback.ini',
			'/language/en-GB/en-GB.mod_tweetdisplayback.sys.ini',
			'/modules/mod_tweetdisplayback/compat.php',
			'/modules/mod_tweetdisplayback/curl.php',
			'/modules/mod_tweetdisplayback/curl_25.php',
			'/modules/mod_tweetdisplayback/tmpl/w_list.php',
			'/modules/mod_tweetdisplayback/tmpl/w_profile.php',
			'/modules/mod_tweetdisplayback/tmpl/w_search.php',
		];

		$this->deleteFolders = [
			'/modules/mod_tweetdisplayback/libraries/http',
			'/modules/mod_tweetdisplayback/media',
		];
	}

	/**
	 * Function to perform changes during updates
	 *
	 * @param   JInstallerAdapterModule  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function update(JInstallerAdapterModule $parent)
	{
		// Remove files deleted between versions
		$this->removeFiles();
	}
}
