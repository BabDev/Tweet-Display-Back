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

/* @type JRegistry $params */

// Prechecked parameters
$headerAvatar = '';
$tweetAvatar  = '';
if (($params->get('headerAvatar', 1) == 0) || (empty($twitter['header']->avatar))) {
	$headerAvatar = '-noavatar';
}

// Variables for the foreach
$i = 0;

// Check to see if the header is set to display
if ($params->get('headerDisplay', 1) == 1) { ?>
	<div class="well well-small TDB-header<?php echo $headerClassSfx . $headerAvatar; ?>">
	<?php if (!empty($twitter['header']->user)) { ?>
		<h4 class="TDB-header-user">
			<?php echo $twitter['header']->user; ?>
		</h4>
	<?php }
	// Check to determine if the avatar is displayed in the header
	if (($params->get('headerAvatar', 1) == 1) && (!empty($twitter['header']->avatar))) { ?>
		<span class="pull-<?php echo $headerAlign;?> TDB-header-avatar-<?php echo $headerAlign;?>">
			<?php echo $twitter['header']->avatar; ?>
		</span>
		<?php }
		if (!empty($twitter['header']->bio)) { ?>
		<div class="TDB-header-bio">
			<?php echo $twitter['header']->bio; ?><br />
			</div>
		<?php }
		if (!empty($twitter['header']->location)) { ?>
		<div class="TDB-header-location">
			<?php echo $twitter['header']->location; ?><br />
		</div>
		<?php }
		if (!empty($twitter['header']->web)) { ?>
		<div class="TDB-header-web">
			<?php echo $twitter['header']->web; ?>
		</div>
		<?php } ?>
	</div>
<?php }

foreach ($twitter['tweets'] as $tweet) {
if (($params->get('tweetAvatar', 1) == 1) && (!empty($tweet->avatar))) {
	$tweetAvatar = ' TDB-tweetavatar';
}
	?>
    <div class="well well-small TDB-tweet<?php echo $tweetClassSfx . $tweetAvatar; if ($i == $count) {echo ' TDB-last-tweet';} ?>">
		<div class="TDB-tweet-container TDB-tweet-align-<?php echo $tweetAlign;?>">
		<?php if (!empty($tweet->user)) { ?>
			<h5 class="TDB-tweet-user">
				<?php echo $tweet->user; ?>
			</h5>
		<?php }
		if (($params->get('tweetAvatar', 1) == 1) && (!empty($tweet->avatar))) { ?>
			<span class="TDB-tweet-avatar-<?php echo $tweetAlign;?>">
				<?php echo $tweet->avatar; ?>
			</span>
		<?php } ?>
		<div class='TDB-tweet-text'><?php echo $tweet->text;?></div>
		<?php if (!empty($tweet->created)) { ?>
			<p class="small TDB-tweet-time"><?php echo $tweet->created; ?></p>
		<?php }
		if (!empty($tweet->actions)) { ?>
			<div class="TDB-tweet-actions"><?php echo $tweet->actions; ?></div>
		<?php } ?>
		</div>
	</div>
	<div class="clr"></div>
	<?php $i++;
}

if (!empty($twitter['footer']->follow_me)) {
	echo $twitter['footer']->follow_me;
}
if (!empty($twitter['footer']->powered_by)) {
	echo $twitter['footer']->powered_by;
}
?>
<div id="pixel">&nbsp;</div>
