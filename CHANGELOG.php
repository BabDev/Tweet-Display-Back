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
?>
1. Changelog
============
This is a non-exhaustive (but still near complete) changelog for Tweet Display Back,
including beta and release candidate versions.
Our thanks to all those people who've contributed bug reports and code fixes.

Legend:

* - Security Fix
# - Bug Fix
$ - Language fix or change
+ - Addition
^ - Change
- - Removed
! - Note

18-November-2010 Michael Babker
# Corrected "Follow Me" link generation

15-November-2010 Michael Babker
+ Added option to display the user's real name in the header

14-November-2010 Michael Babker
^ Restructured element calls in templates (removing some redundent code)
^ Modified CSS with template updates

13-November-2010 Michael Babker
^ Modified language installation so that language files install in site language folder
^ Added margin between bottom tweet and "Follow me" image on right aligned default template

--------------------------------------------------
Tweet Display Back 1.1.7 - Released 7-November-2010
--------------------------------------------------
7-November-2010 Michael Babker
+ Partial addition for [#22395] Ability to display retweets as being from original tweeter
^ Changed foreach argument in templates
+ Added updateservers info in XML
^ Modified template generation (thanks to Amy Stephen for the help!)
^ Changes to renderTwitter pertaining to generation of elements

5-November-2010 Michael Babker
+ Added getJSON function
+ Added description of all functions
^ Moved renderTwitter and getRelativeTime functions under tweetDisplayHelper
^ Renamed getRelativeTime to renderRelativeTime
^ Renamed numerous variables in helper
^ Converted JSON pull to be an array
^ Further defined getJSON

30-October-2010 Michael Babker
^ Added padding to the "Follow me" button on the avatar template

28-October-2010 Michael Babker
+ [#23044] Add option to display "Follow me" button versus text

--------------------------------------------------
Tweet Display Back 1.1.6 - Released 25-October-2010
--------------------------------------------------
24-October-2010 Michael Babker
# Fixed [#22872] getLimit updates
^ [#22973] Implement cURL to replace current loading procedure

21-October-2010 Michael Babker
# Code added in work on [#22872] getLimit updates
$ Changed "Unable to load" message to translatable string

14-October-2010 Michael Babker
# Fixed [#22875] Follow Me link is broken (Thanks for the report, John!)

27-September-2010 Michael Babker
# Swapped location and web positions in nostyle template

--------------------------------------------------
Tweet Display Back 1.1.5 - Released 25-September-2010
--------------------------------------------------
25-September-2010 Michael Babker
+ Partial completion of [#22494] New Template - Avatar Centered
+ Added ability to display the bio location in the user's header info

22-September-2010 Michael Babker
^ Changed height to min-height for bio with avatar

21-September-2010 Michael Babker
^ Modified preg_replace's for link display
^ Added filter to style field to exclude .html
+ Added index.html to tmpl folder

--------------------------------------------------
Tweet Display Back 1.1.4 - Released 20-September-2010
--------------------------------------------------
20-September-2010 Michael Babker
^ Corrected Powered By link
^ Renamed many parameters for better organization
# [#22522] Move parameter checks out of templates
$ Language string corrections
$ Created language strings for relative times

17-September-2010 Michael Babker
+ Add down arrow image for avatar template
$ Change description of tweet avatar alignment
^ Refactored $t->created_html creation
# Tweet source required time to be displayed

--------------------------------------------------
Tweet Display Back 1.1.3 - Released 16-September-2010
--------------------------------------------------
16-September-2010 Michael Babker
# [#22480] Remove (s) from relative time stamps

15-September-2010 Michael Babker
+ [#22454] Add support for location
^ Change from to via in source text
# [#22457] Created time & reply to line tied to "showCreated" param

12-September-2010 Michael Babker
^ [#22398] Change version in header
# [#22397] Consistent case on Powered By link
^ Tag symbol not included as part of link anymore

--------------------------------------------------
Tweet Display Back 1.1.2 - Released 10-September-2010
--------------------------------------------------
6-September-2010 Michael Babker
# Fixed error in "Follow me" link for default template
# Corrected error in getLimit JSON
^ Changed default $layout
# [#5] Add CSS for nostyle
^ Created classes for "Follow Me" and "Powered By" links
# [#4] Modify CSS for when avatar isn't displayed
+ Add class for when the header avatar is aligned left or right
^ Remove excess conditionals and call correct classes based on parameter
^ Renamed image classes to avatar
$ Changed "Header Image" to "Header Avatar" in options
# [#3] If avatar is not displayed with tweets, the tweets aren't displayed
^ Updated nostyle to match default

4-September-2010 Michael Babker
^ Changelog formatting

--------------------------------------------------
Tweet Display Back 1.1.1 - Released 3-September-2010
--------------------------------------------------
3-September-2010 Michael Babker
^ Changed "Powered by" link to direct page versus home page
^ Changed tweet avatar to use info from API pull
- Removed entities from API pull

2-September-2010 Michael Babker
+ Option to display tags as links or not
^ Changed parameter names
- Removed unused image parameter
+ Hash tags (#) and user tags (@) are now linkable

1-September-2010 Michael Babker
# Corrected "in reply to" check to not display if not directly responding to a tweet (in_reply_to_status_id)
+ Include entities in the JSON pull for future development
^ Changed tweet avatar source
^ Fixed tweet avatar size, removed inline style options
^ Removed redundant check in the helper

--------------------------------------------------
Tweet Display Back 1.1.0 - Released 28-August-2010
--------------------------------------------------
28-August-2010 Michael Babker
+ Added static text to language pack
^ Refactored install manifest for 1.6 Compliance
^ Refactored code for 1.6 Compliance
# Fixed header image source
# Fixed CSS issues in header info
+ Ability to display retweets
+ Ability to display avatar on left or right of tweet
^ Updated nostyle template to match default
^ getTweet URL updated
^ Changed getLimit verification source
^ Updated install manifest with new media locations
^ Reorganized all media into media folder
- Option to resize header avatar

--------------------------------------------------
Copied from Tweet Display Back 1.0.0 - 28-August-2010
--------------------------------------------------
