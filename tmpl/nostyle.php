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
?>

<?php if ($params->get("showHeader", 1)==1) : ?>
	<div class="tweetheadermain">
		<div class="tweetheader<?php echo $headerAlign;?>">
			<div class="tweetheaderuser">
				<?php echo $twitter->header->user; ?>
			</div>
			<div class="tweetheaderavatar">
				<?php echo $twitter->header->avatar; ?><br/>
			</div>
			<div class="tweetheaderbio">
				<?php echo $twitter->header->bio; ?>
			</div>
			<div class="tweetheaderweb">
				<?php echo $twitter->header->location; ?>
			</div>
			<div class="tweetheaderlocation">
				<?php echo $twitter->header->web; ?>
			</div>
		</div>
	</div>
	<hr/>
<?php endif; ?>

<?php foreach ($twitter as $o) { ?>
	<div class="tweetmain">
		<?php if ($params->get("showTweetImage", 1)==1) : ?>
		<div class="tweetavatar"><?php echo $twitter->tweet->avatar; ?></div>
		<div class="tweet-<?php echo $tweetDisplay;?>">
		<?php else : ?>
		<div class="tweet-<?php echo $tweetDisplay;?>-noavatar">
		<?php endif; ?>
			<?php echo $twitter->tweet->user; ?>
			<?php echo $twitter->tweet->text; ?>
			<p class="tweettime"><?php echo $twitter->tweet->created; ?></p>
		</div>
	</div>
<?php } ?>

<?php echo $twitter->footer->follow_me; ?>
<?php echo $twitter->footer->powered_by; ?>
