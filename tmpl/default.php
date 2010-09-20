<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
* @copyright	Copyright (C) 2010 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
* Module forked from TweetXT for Joomla!
* Original Copyright ((c) 2009 joomlaxt.com, All rights reserved - http://www.joomlaxt.com
*/

// no direct access
defined('_JEXEC') or die;

$imgpath 		= JURI::root()."modules/mod_tweetdisplayback/media/images";
$headerAlign	= $params->get("headerAvatarAlignment");
$tweetDisplay	= $params->get("tweetDisplayLocation");
JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/default.css', false, false, false);
?>

<?php if ($params->get("showHeader", 1)==1) : ?>
	<div class="tweetheadermain">
		<div class="tweetheader<?php echo $headerAlign;?>">
			<div class="tweetheaderuser">
				<?php echo $twitter->header->user; ?>
			</div>
		
		<?php if ($params->get("showHeaderAvatar", 1)==1) : ?>
			<div class="tweetheaderavatar">
			<?php echo $twitter->header->avatar; ?>
				<div class="tweetheaderbio">
					<?php echo $twitter->header->bio; ?><br />
				</div>
				<div class="tweetheaderweb">
					<?php echo $twitter->header->web; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="tweetheaderbio">
				<?php echo $twitter->header->bio; ?><br />
			</div>
			<div class="tweetheaderweb">
				<?php echo $twitter->header->web; ?>
			</div>
		<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php foreach ($twitter->tweets as $t) : ?>
	<div class="tweetmain">
		<?php if ($params->get("showTweetImage", 1)==1) : ?>
		<div class="tweetavatar"><?php echo $t->tweet->avatar; ?></div>
		<div class="tweet<?php echo $tweetDisplay;?>arrow">
			<img src="<?php echo $imgpath; ?>/arr_<?php echo $tweetDisplay;?>.png" alt="" />
		</div>
		<div class="tweet-<?php echo $tweetDisplay;?>">
		<?php else : ?>
		<div class="tweet-<?php echo $tweetDisplay;?>-noavatar">
		<?php endif; ?>
			<?php echo $t->tweet->user; ?>
			<?php echo $t->tweet->text; ?>
			<p class="tweettime"><?php echo $t->tweet->created; ?></p>
		</div>
	</div>
	<div class="clr"></div>
<?php endforeach; ?>

<?php echo $twitter->footer->follow_me; ?>
<?php echo $twitter->footer->powered_by; ?>
<div id="pixel">&nbsp;</div>
