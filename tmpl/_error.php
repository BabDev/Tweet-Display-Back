<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @copyright  Copyright (C) 2010-2015 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * -----------------
 * @var   object                     $module    A module object
 * @var   array                      $attribs   An array of attributes for the module (probably from the XML)
 * @var   array                      $chrome    The loaded module chrome files
 * @var   JApplicationCms            $app       The active application singleton
 * @var   string                     $scope     The application scope before the module was included
 * @var   \Joomla\Registry\Registry  $params    Module parameters
 * @var   string                     $template  The active template
 * @var   string                     $path      The path to this module file
 * @var   JLanguage                  $lang      The active JLanguage singleton
 * @var   string                     $content   Module output content
 *
 * @var   string  $headerAlign     The header section alignment
 * @var   string  $tweetAlign      The tweet section alignment
 * @var   string  $headerClassSfx  The class suffix for the header section
 * @var   string  $tweetClassSfx   The class suffix for the tweet section
 * @var   string  $flist           The filtered list name if defined
 * @var   string  $count           The number of tweets to display as defined in the parameters
 * @var   string  $errorMsg        The error message generated during the module's processing
 * @var   object  $twitter         The Twitter data object if defined
 */

// Load module CSS
JHtml::_('stylesheet', 'mod_tweetdisplayback/bootstrap.css', [], true);

if (isset($twitter) && !empty($twitter['error']['messages']))
{
	foreach ($twitter['error']['messages'] as $message)
	{
		$errorMsg .= "<br />$message";
	}
}

?>

<div class="well well-small TDB-tweet<?php echo $tweetClassSfx; ?>">
	<div class="TDB-tweet-container TDB-tweet-align-<?php echo $tweetAlign;?> TDB-error">
		<div class="TDB-tweet-text"><?php echo $errorMsg; ?></div>
	</div>
</div>

