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

$imgpath 		= JURI::root()."modules/mod_tweetdisplayback/media/images";
$headerAlign	= $params->get("headerAvatarAlignment");
$tweetDisplay	= $params->get("tweetDisplayLocation");
JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/avatar.css', false, false, false);

//variables foreach
$i		= 0;
$max	= count($twitter) - 1;

foreach ($twitter as $o) {
	if ($i == 0) { ?>

	<div class="tweetheadermain">
		<?php if ($params->get("showHeader", 1)==1) : ?>
			<div class="tweetheaderavatar<?php echo $headerAlign;?>">
				<?php echo $o->header->avatar; ?>
			</div>
		<?php else :?>
			<div class="tweetheaderavatar<?php echo $headerAlign;?>nohead">
				<?php echo $o->header->avatar; ?>
			</div>
		<?php endif; ?>
		<?php if ($params->get("showHeader", 1)==1) { ?>
		<div class="tweet-header-<?php echo $headerAlign;?>arrow">
			<img src="<?php echo $imgpath; ?>/arr_<?php echo $headerAlign;?>.png" alt="" />
		</div>
		<div class="tweetheader<?php echo $headerAlign;?>">
			<span class="tweetheaderuser"><?php echo $o->header->user; ?></span>
			<span class="tweetheaderbio"><?php echo $o->header->bio; ?></span>
			<span class="tweetheaderlocation"><?php echo $o->header->location; ?></span>
			<span class="tweetheaderweb"><?php echo $o->header->web; ?></span>
		</div>
		<?php } ?>
	</div>

	<?php if ($params->get("showHeader", 1)==1) : ?>
	<div class="tweet-header-downarrow-<?php echo $headerAlign;?>">
		<img src="<?php echo $imgpath; ?>/arr_down.png" alt="" />
	</div>
	<?php else :?>
	<div class="tweet-header-downarrow-<?php echo $headerAlign;?>-nohead">
		<img src="<?php echo $imgpath; ?>/arr_down.png" alt="" />
	</div>
	<?php endif; ?>
<?php } ?>

	<div class="tweetmain">
		<div class="tweet-<?php echo $tweetDisplay;?>-noavatar">
			<?php echo $o->tweet->user; ?>
			<?php echo $o->tweet->text; ?>
			<p class="tweettime"><?php echo $o->tweet->created; ?></p>
		</div>
	</div>
	<div class="clr"></div>

	<?php if ($i == $max) { ?>
	<?php echo $o->footer->follow_me; ?>
	<?php echo $o->footer->powered_by; ?>
	<?php } else {
		$i++;
	}
} ?>
<div id="pixel">&nbsp;</div>
