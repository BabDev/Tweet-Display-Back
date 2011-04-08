<?php
/**
* Tweet Display Back Module for Joomla!
*
* @version		$Id$
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
