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
