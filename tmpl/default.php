<?php
/**
 * Tweet Display Back Module for Joomla!
 *
 * @copyright  Copyright (C) 2010-2016 Michael Babker. All rights reserved.
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
JHtml::_('stylesheet', 'mod_tweetdisplayback/default.css', [], true);

/** @var JDocumentHtml $document */
JFactory::getDocument()->addScript('https://platform.twitter.com/widgets.js', 'text/javascript', false, true);

// Prechecked parameters
$headerAvatar = '';

if (($params->get('headerAvatar', 1) == 0) || (empty($twitter['header']->avatar)))
{
	$headerAvatar = '-noavatar';
}

// Variables for the foreach
$i = 0;

// Check to see if the header is set to display
if ($params->get('headerDisplay', 1) == 1) : ?>
	<div class="well well-small TDB-header<?php echo $headerClassSfx . $headerAvatar; ?>">
		<?php if (!empty($twitter['header']->user)) : ?>
			<h4 class="TDB-header-user">
				<?php echo $twitter['header']->user; ?>
			</h4>
		<?php endif; ?>
		<?php // Check to determine if the avatar is displayed in the header ?>
		<?php if (($params->get('headerAvatar', 1) == 1) && (!empty($twitter['header']->avatar))) : ?>
			<span class="pull-<?php echo $headerAlign; ?> TDB-header-avatar-<?php echo $headerAlign;?>">
				<?php echo $twitter['header']->avatar; ?>
			</span>
		<?php endif; ?>
		<?php if (!empty($twitter['header']->bio)) : ?>
			<div class="TDB-header-bio">
				<?php echo $twitter['header']->bio; ?><br />
			</div>
		<?php endif ?>
		<?php if (!empty($twitter['header']->location)) : ?>
			<div class="TDB-header-location">
				<?php echo $twitter['header']->location; ?><br />
			</div>
		<?php endif; ?>
		<?php if (!empty($twitter['header']->web)) : ?>
			<div class="TDB-header-web">
				<?php echo $twitter['header']->web; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif ;?>

<?php foreach ($twitter['tweets'] as $tweet) : ?>
	<?php $tweetAvatar  = ''; ?>
	<?php if (($params->get('tweetAvatar', 1) == 1) && (!empty($tweet->avatar))) : ?>
		<?php $tweetAvatar = ' TDB-tweetavatar'; ?>
	<?php endif; ?>
    <div class="well well-small TDB-tweet<?php echo $tweetClassSfx . $tweetAvatar; echo $i == $count ? ' TDB-last-tweet' : ''; ?>">
		<div class="TDB-tweet-container TDB-tweet-align-<?php echo $tweetAlign; ?>">
			<?php if (!empty($tweet->user)) : ?>
				<h5 class="TDB-tweet-user">
					<?php echo $tweet->user; ?>
				</h5>
			<?php endif; ?>
			<?php if (($params->get('tweetAvatar', 1) == 1) && (!empty($tweet->avatar))) : ?>
				<span class="TDB-tweet-avatar-<?php echo $tweetAlign;?>">
					<?php echo $tweet->avatar; ?>
				</span>
			<?php endif; ?>
			<div class="TDB-tweet-text"><?php echo $tweet->text;?></div>
			<?php if (!empty($tweet->created)) : ?>
				<p class="small TDB-tweet-time"><?php echo $tweet->created; ?></p>
			<?php endif; ?>
			<?php if (!empty($tweet->actions)) : ?>
				<div class="TDB-tweet-actions"><?php echo $tweet->actions; ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php $i++; ?>
<?php endforeach; ?>

<?php if (!empty($twitter['footer']->follow_me)) : ?>
	<?php echo $twitter['footer']->follow_me; ?>
<?php endif;
