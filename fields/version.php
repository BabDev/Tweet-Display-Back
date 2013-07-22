<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @package    TweetDisplayBack
 *
 * @copyright  Copyright (C) 2010-2013 Michael Babker. All rights reserved.
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

JLoader::register('ModTweetDisplayBackHelper', dirname(__DIR__) . '/helper.php');

/**
 * Field type to display the version and check for a newer version.
 *
 * @package  TweetDisplayBack
 * @since    2.1
 */
class JFormFieldVersion extends JFormField
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
	 * @return  string  A message containing the installed version and,
	 *                  if necessary, information on a new version.
	 *
	 * @since   2.1
	 */
	protected function getLabel()
	{
		// Get the module's XML
		$xmlfile   = dirname(__DIR__) . '/mod_tweetdisplayback.xml';
		$data      = JApplicationHelper::parseXMLInstallFile($xmlfile);
		$cacheFile = JPATH_CACHE . '/tweetdisplayback_update.json';

		// The module's version
		$version = $data['version'];

		// The target to check against
		$target = 'http://www.babdev.com/updates/TDB_version_new';

		// Get the module params
		$params = static::getModuleParams($this->form->getValue('id', null, 0));

		// Get the stability level we want to show data for
		$stability = $params->get('stability', 'stable');

		// Check if we have cached data and use it if unexpired
		if (!file_exists($cacheFile) || (time() - @filemtime($cacheFile) > 86400))
		{
			// Get the data from remote
			$helper = new ModTweetDisplayBackHelper(new JRegistry);
			$data   = $helper->getJSON($target);
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


		// Message containing the version
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$message = '<div class="alert alert-info">';
			$close = '</div>';
		}
		else
		{
			$message = '<label style="max-width:100%">';
			$close = '</label>';
		}

		$message .= JText::sprintf('MOD_TWEETDISPLAYBACK_VERSION_INSTALLED', $version);

		// Make sure that the $update object actually has data
		if (!isset($update->notice))
		{
			$message .= '  ' . JText::_('MOD_TWEETDISPLAYBACK_VERSION_FAILED') . $close;
		}
		// If an update is available, and compatible with the current Joomla! version, notify the user
		elseif (version_compare($update->version, $version, 'gt') && version_compare(JVERSION, $update->jversion, 'ge'))
		{
			$message .= '  <a href="' . $update->notice . '" target="_blank">' . JText::sprintf('MOD_TWEETDISPLAYBACK_VERSION_UPDATE', $update->version) . '</a></label>';
		}
		// No updates, or the Joomla! version is not compatible, so let the user know they're using the current version
		else
		{
			$message .= '  ' . JText::_('MOD_TWEETDISPLAYBACK_VERSION_CURRENT') . $close;
		}

		return $message;
	}

	/**
	 * Method to get the module's params
	 *
	 * @param   integer  $id  The module ID
	 *
	 * @return  JRegistry
	 *
	 * @since   3.0
	 */
	protected static function getModuleParams($id)
	{
		// Get a database object
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Query the params column
		$query->select($db->quoteName('params'));
		$query->from($db->quoteName('#__modules'));
		$query->where($db->quoteName('id') . ' = ' . $id);
		$db->setQuery($query);
		$result = $db->loadResult();

		// Convert the result to a JRegistry object
		$params = new JRegistry($result);

		// Return the params
		return $params;
	}
}
