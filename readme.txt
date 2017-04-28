=== BB Connect - A CRM for WordPress ===
Author URI: http://brownbox.net.au
Plugin URI: http://brownbox.net.au
Contributors: havahula,brownbox
Tags: WordPress CRM, CRM, users, contacts, contact form, search, registration, user management, user query, user profiles, custom user fields, bulk edits, e-commerce, donations, Paypal, email, email marketing, newsletter, MailChimp, admin, membership, members
Requires at least: 3.5
Tested up to: 4.6.3

License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Supercharge your user profiles. Simple, powerful contact relationship management.

== Description ==

BB Connect brings core CRM functionality to WordPress that is infinitely extensible. Leveraging a flexible user profile, an extensible user history and a searchable user database, you can create virtually any application that involves users and your interactions with them.

BB Connect started as a fork of PauPress, but is rapidly evolving into something that is not only more powerful, but provides a better user experience.

Upon installation you can build user profiles by adding and arranging as many custom fields as you need and then search those fields to find commonalities or differences. Each user profile has a history tab where you can track detailed user activity using a built-in note system and which can be extended easily to include custom post-types attributable to each user. Lastly, you can create contact forms from your existing user fields that automatically add new contacts to your database and append the details to the user account even if the contact already exists. 

These are the basics of any CRM (contact relationship management) application and BB Connect does it all using only the core WordPress APIs.

**Need to do more?** We are releasing additional add-ons for additional functionality and workflows.

**Features of the plugin include:**

* Build advanced profiles with custom fields using drag-and-drop
* Easily track and annotate user history
* Create and use custom contact forms anywhere on your site
* Search and segment your user database with an intuitive search engine
* Export user data
* Saved searches

**Current Add-ons:**

* Work Queues
* MailChimp
* KPIs
* Quicklinks

**Languages**
* German
* Italian
* Russian
* Slovenian

Would you like to help translate the plugin into more languages? [Get in touch!](http://brownbox.net.au/contact/).

== Installation ==

1. Activate the plugin
2. Go to **My Profile** to see the new layout
3. Go to **Users** to try out the new search utility
4. Go to **BB Connect Options** to configure the options
5. Go to **BB Connect Options > Manage Fields** to add fields and modify the new layout

== Frequently Asked Questions ==

= What's this all about? =

Since 2010 we've built out BB Connect to meet the needs of clients in both the for-profit and non-profit sectors who were managing thousands of user profiles and financial transactions. Most of our clients wanted an integrated approach to relationship management that simply couldn't be found with hosted solutions and the constant juggling between several different applications was too cumbersome. In 2012 we decided to release BB Connect as a plugin everyone could use but we took a year to ensure that we had a mature and stable product to offer before releasing it. We hope you find it useful.

== Changelog ==
= 2.3.0 =
* Added support for automatic updates

= 2.2.2 =
* Added cron to update days since last transaction KPI each day
* Auto-calculate current KPI figures when update is loaded

= 2.2.1 =
* Added 4 KPI fields to default user meta, auto-populated from BB Cart
* Better handling of date and time
* Removed user categories as they add unnecessary complexity

= 2.2.0 =
* Major Gravity Forms integration - any form containing an email address will now automatically locate/create a user and record the submission as a note. Also supports custom mapping of form fields to user meta
* Set default nickname if not specified since WP now requires it
* Added support for Multiple Roles plugin
* Bug fixes

= 2.1.1 =
* Fixes for WP 4.4

= 2.1.0 =
* User segments and categories

= 2.0.0 =

* Added Saved Searches
* Started work on several new add-ons
* Rework of user actions with a completely new, extensible architecture 

== PauPress Changelog (pre-fork) ==

= 1.5.7 - June 24, 2014 =

* Minor bug fixes
* jQuery updates for current version
* updated chosen to 1.10
* fixed login permission issue

= 1.5.6 - May 9, 2014 =

* Minor bug fixes
* Added German Translation (thanks Stefan!)

= 1.5.4 - April 17, 2014 =

* Added options for customizing form notifications
* API additions
* Minor bug fixes

= 1.5.3 - March 13, 2014 =

* Minor bug fixes
* Embedded forms
* Customized random username prefixes
* Improved form styling and direct form links

= 1.5.2 - January 24, 2014 =

* Minor bug fixes
* API additions

= 1.5 - December 12, 2013 =

* Updated BB Connect Admin UI to take advantage of the 3.8 upgrades
* Moved help documentation online
* Large feature release for Pro version

= 1.4.4 - November 24, 2013 =

* Fixed bug with multitext (repeater) fields where multiple additions were not saving properly
* Added clarification on field labels to be "Reports & Forms" to make fields selectable for forms
* Improved search result displays for multitext
* Fixed bug with export default fields not showing on all fields export
* Added Russian translation (credit: Yulia – she requested that we make it known that she is not a professional translator!)

= 1.4.3 - November 7, 2013 =

* Contact forms now log all field data
* Contact forms no longer require a message
* Option to set ranges for datepicker
* Extensive updates to actions and filters
* Large feature release for Pro version

= 1.3 - October 7, 2013 =

* Enhanced Panel system compatibility
* Fix for non-default table prefix bug
* Allow re-use of options API on CPTs
* Fix for taxonomy field display

= 1.2 - September 28, 2013 =

* Added icons to activity viewer
* exposed actions to directory functionality
* fixed search selects not showing selected
* taxonomies now show hierarchy

= 1.1 - September 17, 2013 =

* fixed 404 errors
* prevent deletion of sections if it contains fields
* fixed all-fields export bug
* fixed multitext display bug
* fixed Microsoft Excel bug for exports

= 1.0.9 - September 4, 2013 =

* added support for smarter notifications
* fixed in-page profile history
* extended field API
* added support for file uploads
* closed redundancy for nonce calls
* added style sheets for simple printing
* fixed Firefox field bug

= 1.0.8 - August 11, 2013 =

* minor bug fixes

= 1.0.7 - August 8, 2013 =

* 3.6 compatibility upgrades

= 1.0.6 - July 4, 2013 =

* Added Italian translation
* Added support for Add Local Avatars plugin
* Miscellaneous Pro support updates

= 1.0.4 - June 15, 2013 =

* Small bug fixes

= 1.0.3 - June 10, 2013 =

* WordPress.org public release!

== Upgrade Notice ==

Nothing just yet!
