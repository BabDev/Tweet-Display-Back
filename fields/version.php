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
		$xmlfile = dirname(__DIR__) . '/mod_tweetdisplayback.xml';
		$data    = JApplicationHelper::parseXMLInstallFile($xmlfile);

		// The module's version
		$version = $data['version'];

		// The target to check against
		$target = 'http://www.babdev.com/updates/TDB_version';

		// Get the JSON data
		$update = ModTweetDisplayBackHelper::getJSON($target);

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
}
