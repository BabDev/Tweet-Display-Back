<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @copyright  Copyright (C) 2010-2015 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('ModTweetDisplayBackHelper', dirname(__DIR__) . '/helper.php');

/**
 * Field type to display the version and check for a newer version.
 *
 * @since  2.1
 */
class TweetDisplayBackFormFieldVersion extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.1
	 */
	protected $type = 'Version';

	/**
	 * Method to get the field input.
	 *
	 * @return  string
	 *
	 * @since   2.1
	 */
	protected function getInput()
	{
		return '';
	}

	/**
	 * Method to get the field label.
	 *
	 * @return  string  A message containing the installed version and, if necessary, information on a new version.
	 *
	 * @since   2.1
	 */
	protected function getLabel()
	{
		// Get the module's XML
		$xmlfile   = dirname(__DIR__) . '/mod_tweetdisplayback.xml';
		$data      = JInstaller::parseXMLInstallFile($xmlfile);
		$cacheFile = JPATH_CACHE . '/tweetdisplayback_update.json';

		// The module's version
		$version = $data['version'];

		// The target to check against
		$target = 'https://www.babdev.com/updates/TDB_version_new';

		// Get the module params
		$params = $this->getModuleParams($this->form->getValue('id', null, 0));

		// Get the stability level we want to show data for
		$stability = $params->get('stability', 'stable');

		// Check if we have cached data and use it if unexpired
		if (!file_exists($cacheFile) || (time() - @filemtime($cacheFile) > 86400))
		{
			// Get the data from remote
			$data   = (new ModTweetDisplayBackHelper(new Registry))->getJSON($target);
			$update = $data->$stability;

			// Write the cache if data exists
			if (isset($update->notice))
			{
				$cache = json_encode($data);
				file_put_contents($cacheFile, $cache);
			}
		}
		else
		{
			// Render from the cached data
			$data   = json_decode(file_get_contents($cacheFile));
			$update = $data->$stability;
		}

		$message = '<div class="alert alert-info">';
		$message .= JText::sprintf('MOD_TWEETDISPLAYBACK_VERSION_INSTALLED', $version) . '  ';

		// Make sure that the $update object actually has data
		if (!isset($update->notice))
		{
			$message .= JText::_('MOD_TWEETDISPLAYBACK_VERSION_FAILED');
		}

		// If an update is available, and compatible with the current Joomla! version, notify the user
		elseif (version_compare($update->version, $version, 'gt') && version_compare(JVERSION, $update->jversion, 'ge'))
		{
			$message .= '<a href="' . $update->notice . '" target="_blank">' . JText::sprintf('MOD_TWEETDISPLAYBACK_VERSION_UPDATE', $update->version) . '</a>';
		}

		// No updates, or the Joomla! version is not compatible, so let the user know they're using the current version
		else
		{
			$message .= JText::_('MOD_TWEETDISPLAYBACK_VERSION_CURRENT');
		}

		$message .= '</div>';

		return $message;
	}

	/**
	 * Method to get the module's params
	 *
	 * @param   integer  $id  The module ID
	 *
	 * @return  Registry
	 *
	 * @since   3.0
	 */
	protected function getModuleParams($id)
	{
		// Get a database object
		$db    = JFactory::getDbo();

		$result = $db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('params'))
				->from($db->quoteName('#__modules'))
				->where($db->quoteName('id') . ' = ' . $id)
		)->loadResult();

		// Convert the result to a Registry object
		return new Registry($result);
	}
}
