Tweet Display Back
===============
*Tweet Display Back* is a module for Joomla! 1.5+ sites which allows users to display a user or list feed on their site.

Compatibility
===============
*Tweet Display Back* is compatible with Joomla! 1.5, 1.6, and 1.7 utilizing a common code base with native language files and manifests to each version.

Requirements
===============
*Tweet Display Back* requires the PHP cURL module be enabled in order to retrieve data from Twitter.

Support
===============
* Documentation for *Tweet Display Back* is available on my website at http://www.babdev.com/extensions/tweet-display-back.
* If you've found a bug, please report it to the Issue Tracker at https://github.com/mbabker/Tweet-Display-Back/issues.

Installation Package
===============
* Installation packages for *Tweet Display Back* are available from the downloads section of this repository.
* If you have made a checkout of the repository, you can build installation packages using Phing by running 'phing dev_head' from your interface.

Stable Master Policy
===============
The master branch will at all times remain stable.  Development for new features will occur in branches and when ready, will be pulled into the master branch.

In the event features have already been merged for the next release series and an issue arises that warrants a fix on the current release series, the developer will create a branch based off the tag created from the previous release, make the necessary changes, package a new release, and tag the new release.  If necessary, the commits made in the temporary branch will be merged into master.