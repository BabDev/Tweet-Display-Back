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
  type: 'list',
  rpp: <?php echo $params->get('twitterCount', '3'); ?>,
  interval: <?php echo $params->get('widgetInterval', '6'); ?>000,
  title: 'Michael\'s',
  subject: 'J Sample List',
  width: <?php echo $params->get('widgetWidth', '250'); ?>,
  height: <?php echo $params->get('widgetHeight', '300'); ?>,
  theme: {
    shell: {
      background: '#ff96e7',
      color: '#ffffff'
    },
    tweets: {
      background: '#ffffff',
      color: '#444444',
      links: '#b740c2'
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
}).render().setList('<?php echo $params->get('twitterName', ''); ?>', '<?php echo $flist; ?>').start();
</script>
