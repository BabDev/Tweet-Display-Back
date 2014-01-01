<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @copyright  Copyright (C) 2010-2014 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/**
 * Field type to display information about setting up authentication
 *
 * @since  3.1
 */
class JFormFieldAuthenticationinfo extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $type = 'Authenticationinfo';

	/**
	 * Method to get the field input.
	 *
	 * @return  string
	 *
	 * @since   3.1
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
	 * @since   3.1
	 */
	protected function getLabel()
	{
		return '<p>' . JText::_('MOD_TWEETDISPLAYBACK_AUTHENTICATION_SETUP_INSTRUCTIONS') . '</p>';
	}
}
