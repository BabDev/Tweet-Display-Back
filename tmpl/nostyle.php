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
		<div class="tweetheadermain<?php echo $headerClassSfx; ?>">
			<div class="tweetheader<?php echo $headerAlign;?>">
				<div class="tweetheaderuser">
					<?php echo $o->header->user; ?>
				</div>
			<?php if ($params->get("headerAvatar", 0)==1) { ?>
				<span class="tweetheaderavatar<?php echo $headerAlign;?>">
					<?php echo $o->header->avatar; ?><br/>
				</span>
			<?php } ?>
				<div class="tweetheaderbio">
					<?php echo $o->header->bio; ?>
				</div>
				<div class="tweetheaderlocation">
					<?php echo $o->header->location; ?>
				</div>
				<div class="tweetheaderweb">
					<?php echo $o->header->web; ?>
				</div>
			<hr/>
			</div>
		</div>
	<?php }
	} ?>

		<div class="tweetmain<?php echo $tweetClassSfx; ?>">
			<div class="tweet-<?php echo $tweetAlign;?>">
			<?php if ($params->get("tweetAvatar", 1)==1) { ?>
				<span class="tweetavatar-<?php echo $tweetAlign;?>">
				<?php echo $o->tweet->avatar; ?>
				</span>
			<?php }
				echo $o->tweet->user;
				echo $o->tweet->text; ?><br />
				<p class="tweettime"><?php echo $o->tweet->created; ?></p>
			</div>
		</div>
	
	<?php if ($i == $max) {
		echo $o->footer->follow_me;
		echo $o->footer->powered_by;
	} else {
		$i++;
	}
} ?>
