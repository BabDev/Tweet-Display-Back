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
$tweetAlign		= $params->get("tweetAlignment");

// load appropriate CSS depending on CSS3 use
if ($params->get("templateCSS3", 1)==1) {
	JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/default-css3.css', false, false, false);
} else {
	JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/default.css', false, false, false);
}

//variables foreach
$i		= 0;
$max	= count($twitter) - 1;

foreach ($twitter as $o) {
	if ($i == 0) { 
	
		if ($params->get("headerDisplay", 1)==1) { ?>
		<div class="tweetheadermain">
			<div class="tweetheader">
				<div class="tweetheaderuser">
					<?php echo $o->header->user; ?>
				</div>
			
			<?php if ($params->get("showHeaderAvatar", 1)==1) { ?>
				<span class="tweetheaderavatar<?php echo $headerAlign;?>">
				<?php echo $o->header->avatar; ?>
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
			</div>
		</div>
    	<?php }
    } ?>

    	<div class="tweetmain">
			<?php if ($params->get("tweetAvatar", 1)==1) { ?>
			<div class="tweetavatar"><?php echo $o->tweet->avatar; ?></div>
			<div class="tweet-<?php echo $tweetAlign;?>">
			<?php } else { ?>
			<div class="tweet-<?php echo $tweetAlign;?>-noavatar">
			<?php } ?>
				<?php echo $o->tweet->user;
				echo $o->tweet->text; ?>
				<p class="tweettime"><?php echo $o->tweet->created; ?></p>
			</div>
		</div>
		<div class="clr"></div>
		
	<?php if ($i == $max) {
		echo $o->footer->follow_me;
		echo $o->footer->powered_by;
	} else {
		$i++;
	}
} ?>
<div id="pixel">&nbsp;</div>
