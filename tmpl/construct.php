<?php
/**
* Tweet Display Back Module for Joomla!
*
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

// Set the variables
$imgpath 		= JURI::root()."modules/mod_tweetdisplayback/media/images";
$headerAlign	= $params->get("headerAvatarAlignment");
$tweetDisplay	= $params->get("tweetAlignment");
$headerClassSfx = htmlspecialchars($params->get('headerclasssfx'));
$tweetClassSfx	= htmlspecialchars($params->get('tweetclasssfx'));

// If CSS3 is selected, load it's stylesheet
if ($params->get("templateCSS3", 1) == 1) {
	JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/construct-css3.css', false, false, false);
} else {
	JHTML::stylesheet('modules/mod_tweetdisplayback/media/css/construct.css', false, false, false);
}

// Add the Twitter Web Intents script
$document = JFactory::getDocument();
$document->addCustomTag('<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>');

// Variables for the foreach
$i		= 0;
$count	= $params->get("twitterCount", 3) - 1;

// Check to see if the header is set to display
if ($params->get("headerDisplay", 1) == 1) { ?>
	<div class="TDB-header<?php echo $headerClassSfx; ?>">
	<?php if (!empty($twitter->header->user)) { ?>
		<div class="TDB-header-user">
			<?php echo $twitter->header->user; ?><br />
		</div>
	<?php }
	// Check to determine if the avatar is displayed in the header
	if (($params->get("headerAvatar", 1) == 1)  && (!empty($twitter->header->avatar))) { ?>
		<span class="TDB-header-avatar-<?php echo $headerAlign;?>">
			<?php echo $twitter->header->avatar; ?>
		</span>
		<?php }
		if (!empty($twitter->header->bio)) { ?>
		<div class="TDB-header-bio">
			<?php echo $twitter->header->bio; ?><br />
			</div>
		<?php }
		if (!empty($twitter->header->location)) { ?>
		<div class="TDB-header-location">
			<?php echo $twitter->header->location; ?><br />
		</div>
		<?php }
		if (!empty($twitter->header->web)) { ?>
		<div class="TDB-header-web">
			<?php echo $twitter->header->web; ?>
		</div>
		<?php } ?>
	</div>
<?php }

foreach ($twitter->tweet as $o) {
if ($i <= $count) { ?>
    <div class="TDB-tweet<?php echo $tweetClassSfx; ?>">
		<div class="TDB-tweet-<?php echo $tweetDisplay;?>">
		<?php if (!empty($o->tweet->user)) { ?>
			<div class="TDB-tweet-user">
				<?php echo $o->tweet->user; ?>
			</div>
		<?php }
		if ($params->get("showTweetImage", 1)==1) {
			if (!empty($o->tweet->avatar)) { ?>
				<span class="TDB-tweet-avatar-<?php echo $tweetDisplay;?>">
					<?php echo $o->tweet->avatar; ?>
				</span>
			<?php }
		}
		echo $o->tweet->text;
		if (!empty($o->tweet->created)) { ?>
			<p class="TDB-tweet-time"><?php echo $o->tweet->created; ?></p>
		<?php } 
		if (!empty($o->tweet->actions)) { ?>
			<div class="TDB-tweet-actions"><?php echo $o->tweet->actions; ?></div>
		<?php } ?>
		</div>
	</div>
	<div class="clr"></div>
	<?php $i++;
	}
}

if (!empty($twitter->footer->follow_me)) {
	echo $twitter->footer->follow_me;
}
if (!empty($twitter->footer->powered_by)) {
	echo $twitter->footer->powered_by;
}
?>
<div id="pixel">&nbsp;</div>
