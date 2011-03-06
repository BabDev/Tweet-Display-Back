<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

// Set the variables
$imgpath 		= JURI::root()."modules/mod_tweetdisplayback/media/images";
$headerAlign	= $params->get("headerAvatarAlignment");
$tweetAlign		= $params->get("tweetAlignment");
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx	= htmlspecialchars($params->get('tweetclasssfx'));

JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/nostyle.css', false, false, false);

// Variables for the foreach
$i		= 0;
$max	= count($twitter) - 1;

foreach ($twitter as $o) {
	// On the first iteration, render the header
	if ($i == 0) {
		// Check to see if the header is set to display
		if ($params->get("headerDisplay", 0) == 1) { ?>
		<div class="TDB-header<?php echo $headerClassSfx; ?>">
			<?php if (!empty($o->header->user)) { ?>
			<div class="TDB-header-user">
				<?php echo $o->header->user; ?>
			</div>
			<?php }
			// Check to determine if the avatar is displayed in the header
			if (($params->get("headerAvatar", 1) == 1)  && (!empty($o->header->avatar))) { ?>
			<span class="TDB-header-avatar-<?php echo $headerAlign;?>">
				<?php echo $o->header->avatar; ?><br />
			</span>
			<?php }
			if (!empty($o->header->bio)) { ?>
			<div class="TDB-header-bio">
				<?php echo $o->header->bio; ?><br />
			</div>
			<?php }
			if (!empty($o->header->location)) { ?>
			<div class="TDB-header-location">
				<?php echo $o->header->location; ?><br />
			</div>
			<?php }
			if (!empty($o->header->web)) { ?>
			<div class="TDB-header-web">
				<?php echo $o->header->web; ?>
			</div>
			<?php } ?>
		<hr/>
		</div>
	<?php }
	} ?>

		<div class="TDB-tweet<?php echo $tweetClassSfx; ?>">
			<?php
			// Determine if the noavatar class is used for tweets by checking the setting and whether an avatar was returned
			if (($params->get("tweetAvatar", 1) == 1) && (!empty($o->tweet->avatar))) { ?>
			<div class="TDB-tweet-<?php echo $tweetAlign;?>">
				<span class="TDB-tweet-avatar">
				<?php echo $o->tweet->avatar; ?>
				</span>
			<?php } else { ?>
			<div class="TDB-tweet-<?php echo $tweetAlign;?>-noavatar">
			<?php }
				if (!empty($o->tweet->user)) {
					echo $o->tweet->user;
				}
				echo $o->tweet->text."<br />";
				if (!empty($o->tweet->created)) { ?>
				<p class="TDB-tweet-time"><?php echo $o->tweet->created; ?></p>
				<?php } ?>
			</div>
		</div>
	
	<?php
	// On the final iteration, render the footer
	if ($i == $max) {
		if (!empty($o->footer->follow_me)) {
			echo $o->footer->follow_me;
		}
		if (!empty($o->footer->powered_by)) {
			echo $o->footer->powered_by;
		}
	} else {
		$i++;
	}
} ?>
