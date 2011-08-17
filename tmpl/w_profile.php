<?php
/**
* Tweet Display Back Module for Joomla!
*
* @package    TweetDisplayBack
*
* @copyright  Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;
// TODO: Convert all items to params
?>
<script>
new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: <?php echo $params->get('twitterCount', '3'); ?>,
  interval: <?php echo $params->get('widgetInterval', '6'); ?>000,
  width: <?php echo $params->get('widgetWidth', '250'); ?>,
  height: <?php echo $params->get('widgetHeight', '300'); ?>,
  theme: {
    shell: {
      background: '#333333',
      color: '#ffffff'
    },
    tweets: {
      background: '#000000',
      color: '#ffffff',
      links: '#4aed05'
    }
  },
  features: {
    scrollbar: <?php echo $params->get('widgetScroll', 'true'); ?>,
    loop: <?php echo $params->get('widgetLoop', 'true'); ?>,
    live: <?php echo $params->get('widgetLive', 'true'); ?>,
    hashtags: <?php echo $params->get('widgetHashtag', 'true'); ?>,
    timestamp: <?php echo $params->get('widgetTimestamp', 'true'); ?>,
    avatars: <?php echo $params->get('widgetAvatar', 'true'); ?>,
    behavior: '<?php echo $params->get('widgetLoadBehavior', 'default'); ?>'
  }
}).render().setUser('<?php echo $params->get('twitterName', ''); ?>').start();
</script>
