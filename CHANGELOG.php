<?php
/**
* Tweet Display Back Module for Joomla!
*
* @copyright	Copyright (C) 2010-2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
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

18-July-2011 Michael Babker
^ Set cache life in minutes for Joomla! 1.7
$+ Add nl-BE and nl-NL translations (translated by Jurian Even)

13-July-2011 Michael Babker
^ Optimize processFiltering by having no filtering conditions and list feeds process before filtering begins
^ Check if $list is defined before ASCII'ing it

--------------------------------------------------
Tweet Display Back 2.0 Beta - Released 13-July-2011
--------------------------------------------------
10-July-2011 Michael Babker
$ Updated pt-BR translation
$- Removed unused string

9-July-2011 Michael Babker
^$ Process count values through JText::sprintf to allow for greater international compatibility

3-July-2011 Michael Babker
+ Add logic to count the number of active filters
^ Calculate the $count multiplier based on the number of active filters

2-July-2011 Michael Babker
+ Add fields to override the number of tweets queried from Twitter
^ Add logic to compileData to override the count when building the query JSON

30-June-2011 Michael Babker
# Correct error with hashtag str_replace

29-June-2011 Michael Babker
$ Update sv-SE translation
$+ Added French translation (translated by Benjamin Ach)

28-June-2011 Michael Babker
^$ Changed "Filter ..." to "Show ..." for filter settings

27-June-2011 Jurian Even
! Merge pull from mbabker
^ Changed some comments
^ Changed variable name $tweetContainsMention to $tweetContainsMentionAndOrReply
^ Repositioned if ($count > 0) check in foreach loop, improved loadtime

27-June-2011 Michael Babker
! Merge pull request #3 from Twentronix/twentronix
- Removed commented out logic since it has been replaced
^ Made $tweetContainsMentionAndReply more generalized and reset false value

26-June-2011 Jurian Even
! Merge from mbabker
^ Comments improved
^ Fixed naming of $tweetContainsMention to $tweetContainsMentionAndOrReply
^ Changed naming of $mention to $mentionSet
# New calculation for $tweetOnlyReply, bug not yet fixed

26-June-2011 Michael Babker
! Merge pull request #1 from Twentronix/twentronix
^ Modified variable names
! Code formatting
# Added conditional to check mention to resolve undefined index error
^ Moved the check for number of tweets to execute pre-variable setup

25-June 2011 Jurian Even
# Corrected if else structure in processFiltering function bigtime. Filering both filterMentions and filterReplies did not activate both if statements.
# Corrected check for filter @mentions only, there is however a notice warning left (needs to be fixed)
^ Cleaned code
+ Added different variables
^ Restructured $count > 0

25-June-2011 Michael Babker
! Merge remote-tracking branches 'twentronix/twentronix' and 'origin/master'
# Corrected check for replies within the mentions conditional
^ Moved check for replies out of mentions conditional (this causes replies to be filtered with mentions)
^ Add check for RTs to count multiplier

24-June-2011 Jurian Even
# Corrected error with no tweets returning with filtering disabled

24-June-2011 Michael Babker
! Merge remote-tracking branches 'twentronix/twentronix' and 'origin/master'
+ Add new params to 1.7
$ Updated pt-BR translation
^ Modified Jurian's code to optimize filtering checks
^ Replaced preg_replace with str_replace using entities

23-June-2011 Jurian Even
+ Added params for filtering to 1.5 manifest
+ Initial code for filtering based on mentions and replies

23-June-2011 Michael Babker
^ Began restructuring repo for single repo version for J! 1.5 & 1.6+

--------------------------------------------------
Branched from Tweet Display Back Trunk revision 212 - 23-June-2011
--------------------------------------------------
23-June-2011 Michael Babker
+ Added TDB-last-tweet class for nostyle (thanks Jurian for the suggestion)

22-June-2011 Michael Babker
+ Added rel="nofollow" to all links (thanks Jurian for the suggestion)

--------------------------------------------------
Tweet Display Back 1.6.5 - Released 17-June-2011
--------------------------------------------------
10-June-2011 Michael Babker
^ Changed cache time to process in seconds versus minutes
^ Return an empty string when getLimit doesn't get the JSON
^ Added checks to ensure the user object pulls data

8-June-2011 Michael Babker
^ Change "Powered By" link to point to category page

2-June-2011 Michael Babker
$+ Added Swedish translation (translated by JOKR Solutions)
$+ Added Portuguese (Brazilian) translation (translated by Manoel Silva)

--------------------------------------------------
Tweet Display Back 1.6.4 - Released 30-May-2011
--------------------------------------------------
30-May-2011 Michael Babker
^ Simplified parameters check
# Corrected parameter names for header objects in helper
+ Added TDB-headavatar class for construct
^ Modified header name generation
- Remove count param from list API
+ Added TDB-tweetavatar class for construct
# Corrected parameter check for tweet avatar
^ Reformatted default template
^ Reformatted nostyle template

21-May-2011 Michael Babker
^ Changed the list feed to the new end point
^ Added a variable for the include_rts string

--------------------------------------------------
Tweet Display Back 1.6.3 - Released 19-May-2011
--------------------------------------------------
17-May-2011 Michael Babker
+ Add moduleclass_sfx
^ Change the Follow Me button to align=middle for validation
+ Add Construct template
# Fixed [#24554] Avatar URL for $headerAvatar

--------------------------------------------------
Tweet Display Back 1.6.2 - Released 18-April-2011
--------------------------------------------------
14-April-2011 Michael Babker
+ Added Web Intents for Retweet and Favorite
^ Modified templates to display intent icons

7-April-2011 Michael Babker
^ [#25578] Changed prepareUser $obj['name'] references to $uname to help with error situations
^ Restore defaultgroup cache parameter
^ [#25578] Modified setCaching param
^ [#25578] Remove the minutes to seconds conversion; locally, cache seems to be working OK
^ Reorganize checks for caching and moved getLimits check

6-April-2011 Michael Babker
^ Modified cache parameters
^ Modified header user link generation
# Removed Web Intent from list links

--------------------------------------------------
Tweet Display Back 1.6.1 - Released 4-April-2011
--------------------------------------------------
31-March-2011 Michael Babker
+ Add a link for the Twitter Web Intents script
+ Add the user intent to all user links
+ Add the reply intent
+ Added an additional error check to verify that data has been retrieved (Thanks rb for the suggestion)
# Closed the unclosed URL tag

--------------------------------------------------
Tweet Display Back 1.6.0 - Released 29-March-2011
--------------------------------------------------
28-March-2011 Michael Babker
^ Re-enable updateservers

27-March-2011 Michael Babker
^ Add safehtml filter as appropriate
^ Modified the character separator to a single space to prevent a display bug when trying to make it blank

--------------------------------------------------
Tweet Display Back 1.6 Beta 2 - Released 12-March-2011
--------------------------------------------------
11-March-2011 Michael Babker
^ Partial fix for [#24576] PHP Strict

10-March-2011 Michael Babker
+^ [#25063] Initial conversion of the helper to pull both user and list feeds, list options added
^ Renamed prepareStatic to prepareUser
^ First pass at reformatting the default template for the new rendering
^ List name is not a required field
^ Changed references that are not processed through foreach from $o to $twitter
^ Reformatted nostyle template for new rendering

8-March-2011 Michael Babker
#- [#25214] Removed debug code that accidentally made it into the release package

--------------------------------------------------
Tweet Display Back 1.6 Beta - Released 6-March-2011
--------------------------------------------------
6-March-2011 Michael Babker
^ Moved the getLimit check to (logically) be executed only when the cache is disabled or expired
- Removed the cache refactoring

11-February-2011 Michael Babker
^ Moved the check to determine if CURL is enabled to be the first code executed by the module
^ Moved the getLimits check to the module controller
^ Added !empty checks to template elements to prevent rendering empty elements
^$ Modified caching parameters to use Joomla! globals and removed specific language strings
^ Code for cache calling refactored

8-February-2011 Michael Babker
^ [#24555] Standardize CSS calls
^ Actually implement separator character parameter

4-February-2011 Michael Babker
+$ [#24090] Include "Module Class Suffix" option

2-February-2011 Michael Babker
+ Added a class for user and hash tag links (Thanks Babs for the idea)

--------------------------------------------------
Tweet Display Back 1.2 Beta - Released 29-January-2011
--------------------------------------------------
29-January-2011 Michael Babker
^ Partial solution for [#24089] - Refactored CSS for speech bubble on left-aligned default
^ Partial solution for [#24089] - Refactored CSS for speech bubble on right-aligned default
^$ [#24091] Reorganize Parameters
^$ Renamed most parameters to match reorganization
+$ Add a username separator parameter

22-January-2011 Michael Babker
- Removed avatar template
+$ Partial solution for [#24190] Better error reporting
+$ Implemented [#24551] Modify tweet footer
+$ Implemented [#24552] Refactor tweetDisplayHelper::renderRelativeTime
+$ Implemented [#24565] Follow Me/Us Option

--------------------------------------------------
Branched from Tweet Display Back 1.1.10 - 22-January-2011
--------------------------------------------------
