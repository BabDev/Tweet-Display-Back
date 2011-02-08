<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die;

$imgpath 		= JURI::root()."modules/mod_tweetdisplayback/media/images";
$headerAlign	= $params->get("headerAvatarAlignment");
$tweetAlign	= $params->get("tweetAlignment");
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx = htmlspecialchars($params->get('tweetclasssfx'));

JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/nostyle.css', false, false, false);

//variables foreach
$i		= 0;
$max	= count($twitter) - 1;

foreach ($twitter as $o) {
	if ($i == 0) {
	
	if ($params->get("headerDisplay", 0)==1) { ?>
		<div class="TDB-header<?php echo $headerClassSfx; ?>">
			<div class="TDB-header-user">
				<?php echo $o->header->user; ?>
			</div>
		<?php if ($params->get("headerAvatar", 0)==1) { ?>
			<span class="TDB-header-avatar-<?php echo $headerAlign;?>">
				<?php echo $o->header->avatar; ?><br />
			</span>
		<?php } ?>
			<div class="TDB-header-bio">
				<?php echo $o->header->bio; ?><br />
			</div>
			<div class="TDB-header-location">
				<?php echo $o->header->location; ?><br />
			</div>
			<div class="TDB-header-web">
				<?php echo $o->header->web; ?>
			</div>
		<hr/>
		</div>
	<?php }
	} ?>

		<div class="TDB-tweet<?php echo $tweetClassSfx; ?>">
			<?php if ($params->get("tweetAvatar", 1)==1) { ?>
			<div class="TDB-tweet-<?php echo $tweetAlign;?>">
				<span class="TDB-tweet-avatar">
				<?php echo $o->tweet->avatar; ?>
				</span>
			<?php } else { ?>
			<div class="TDB-tweet-<?php echo $tweetAlign;?>-noavatar">
			<?php }
				echo $o->tweet->user;
				echo $o->tweet->text; ?><br />
				<p class="TDB-tweet-time"><?php echo $o->tweet->created; ?></p>
			</div>
		</div>
	
	<?php if ($i == $max) {
		echo $o->footer->follow_me;
		echo $o->footer->powered_by;
	} else {
		$i++;
	}
} ?>
