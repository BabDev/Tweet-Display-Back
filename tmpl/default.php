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
		<?php if ($params->get("showHeaderUser", 1)==1) : ?>
			<div class="tweetheaderuser">
			<?php echo $twitter->user->header_user; ?>
			</div>
		<?php endif; ?>
		
		<?php if ($params->get("showHeaderAvatar", 1)==1) : ?>
			<div class="tweetheaderavatar">
			<?php echo $twitter->user->header_avatar; ?>
			<?php if ($params->get("showHeaderBio", 1)==1) { ?>
				<div class="tweetheaderbio">
					<?php echo $twitter->user->description; ?><br />
				</div>
			<?php } ?>
			<?php if ($params->get("showHeaderWeb", 1)==1) { ?>
				<div class="tweetheaderweb">
					<?php echo $twitter->user->header_web; ?>
				</div>
			<?php } ?>
			</div>
		<?php else : ?>
			<?php if ($params->get("showHeaderBio", 1)==1) { ?>
			<div class="tweetheaderbio">
				<?php echo $twitter->user->description; ?><br />
			</div>
			<?php } ?>
			<?php if ($params->get("showHeaderWeb", 1)==1) { ?>
			<div class="tweetheaderweb">
				<?php echo $twitter->user->header_web; ?>
			</div>
			<?php } ?>
		<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php foreach ($twitter->tweets as $t) : ?>
	<div class="tweetmain">
		<?php if ($params->get("showTweetImage", 1)==1) : ?>
		<div class="tweetavatar"><?php echo $t->tweet_avatar; ?></div>
		<div class="tweet<?php echo $tweetDisplay;?>arrow">
			<img src="<?php echo $imgpath; ?>/arr_<?php echo $tweetDisplay;?>.png" alt="" />
		</div>
		<div class="tweet-<?php echo $tweetDisplay;?>">
		<?php else : ?>
		<div class="tweet-<?php echo $tweetDisplay;?>-noavatar">
		<?php endif; ?>
		<?php if ($params->get("showTweetName", 1)==1) : ?>
			<b><?php echo $twitter->user->tweet_user; ?>:</b>&nbsp;
		<?php endif; ?>
			<?php echo $t->text_html; ?>
			<p class="tweettime">
				<?php if ($params->get("showTweetCreated", 1)==1) : ?><?php echo $t->created_html; ?><?php endif; ?>
				<?php if (($t->in_reply_to_screen_name) && ($t->in_reply_to_status_id)) : ?> <?php echo $t->reply_html; ?><?php endif;?>
			</p>
		</div>
	</div>
	<div class="clr"></div>
<?php endforeach; ?>

<?php if ($params->get("showFollowLink", 1) == 1) : ?>
	<hr />
	<div class="followLink"><b><a href="http://twitter.com/<?php echo $twitter->user->screen_name ?>" rel="nofollow"><?php echo $params->get('followText', 'Follow me on Twitter') ?></a></b></div>
<?php endif; ?>

<?php if ($params->get("showPoweredBy", 1) == 1) : ?>
	<hr />
	<div class="poweredBy">Powered by <a href="http://www.flbab.com/extensions/13-tweet-display-back">Tweet Display Back</a></div>
<?php endif; ?>
<div id="pixel">&nbsp;</div>
