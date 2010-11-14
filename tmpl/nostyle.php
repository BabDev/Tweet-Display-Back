<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
* Module forked from TweetXT for Joomla!
* Original Copyright (c) 2009 joomlaxt.com, All rights reserved - http://www.joomlaxt.com
*/

// no direct access
defined('_JEXEC') or die;

$headerAlign	= $params->get("headerAvatarAlignment");
$tweetDisplay	= $params->get("tweetDisplayLocation");
JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/nostyle.css', false, false, false);

//variables foreach
$i		= 0;
$max	= count($twitter) - 1;

foreach ($twitter as $o) {
	if ($i == 0) { ?>
	
	<?php if ($params->get("showHeader", 0)==1) { ?>
		<div class="tweetheadermain">
			<div class="tweetheader<?php echo $headerAlign;?>">
				<div class="tweetheaderuser">
					<?php echo $o->header->user; ?>
				</div>
			<?php if ($params->get("showHeaderAvatar", 0)==1) { ?>
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

		<div class="tweetmain">
			<div class="tweet-<?php echo $tweetDisplay;?>">
			<?php if ($params->get("showTweetImage", 1)==1) { ?>
				<span class="tweetavatar-<?php echo $tweetDisplay;?>">
				<?php echo $o->tweet->avatar; ?>
				</span>
			<?php } ?>
				<?php echo $o->tweet->user; ?>
				<?php echo $o->tweet->text; ?><br />
				<p class="tweettime"><?php echo $o->tweet->created; ?></p>
			</div>
		</div>
	
	<?php if ($i == $max) { ?>
		<?php echo $o->footer->follow_me; ?>
		<?php echo $o->footer->powered_by; ?>
	<?php } else {
		$i++;
	}
} ?>
