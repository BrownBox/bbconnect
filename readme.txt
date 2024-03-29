=== Connexions - A CRM for WordPress ===
Author URI: http://brownbox.net.au
Plugin URI: http://brownbox.net.au
Contributors: havahula,brownbox,markparnell
Tags: WordPress CRM, CRM, users, contacts, contact form, search, registration, user management, user query, user profiles, custom user fields, bulk edits, e-commerce, donations, Paypal, email, email marketing, newsletter, MailChimp, admin, membership, members
Requires at least: 3.5
Tested up to: 6.3

License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Supercharge your user profiles. Simple, powerful contact relationship management.

== Description ==

Connexions is the latest in simplified, effective CRM solutions.

Built specifically for WordPress, it captures and communicates data in a way like never before - enabling you to build deeper relationships and expand your impact.

Connexions works to passively build profiles, allowing you to build up your data and capture detailed user profiles.

It has an amazing ability to connect with a wide range of additional web products, meaning it's able to grow and expand as you do.

Connexions will expand your understanding of user behaviour and allow you to better interact and communicate with your audience, to build deeper and long-lasting digital relationships.

**Need to do more?** We are releasing additional add-ons for additional functionality and workflows.

== Current Add-ons ==

* Action Notes & Work Queues
* Impersonation
* Import
* MailChimp
* Mailgun
* Personalisation
* Postie
* Relationships & Groups
* Rolling KPIs & Segment Status
* WholesaleSMS

== Installation ==

1. Activate the plugin
2. Go to **My Profile** to see the new layout
3. Go to **Users** to try out the new search utility
4. Go to **Connexions Options** to configure the options
5. Go to **Connexions Options > Manage Fields** to add fields and modify the new layout

== Changelog ==
= 2.8.23 =
* Check whether user ID is not empty in URL instead of whether parameter exists
* Updated search to allow searching for zero on number fields

= 2.8.22 =
* Fix further issues with searching on address fields

= 2.8.21 =
* Further PHP8 fixes

= 2.8.20 =
* Fix conflict with WP Migrate on PHP8

= 2.8.19 =
* Fix error that meant address fields weren't shown in field drop-down in user reports

= 2.8.18 =
* No changes - released due to error in versioning

= 2.8.17 =
* Updated fallback JS redirect to new user page to include parameter to display success message
* Various fixes to PHP8 compatibility
* Added custom action hooks before and after creating user records from Gravity Forms submissions

= 2.8.16 =
* Not released due to error in versioning

= 2.8.15 =
* Fix user pages takeover logic as previous change for 2FA compatibility broke the redirects completely

= 2.8.14 =
* Fix address field names in meta tracking
* Clean up HTML chars when comparing meta values
* Added filter to allow override of default functionality to update existing records
* Fix ability to scroll form list in quicklink
* Use new GF2.5 form markup
* Don't use rich editor in forms as it no longer works on submit form page
* Replace GF list of predefined country choices with our country list
* Updated user profile takeover logic to allow access in order to set up 2FA with WP 2FA plugin

= 2.8.13 =
* Only track meta changes for selected fields
* Don't show Saved Searches in admin menu
* Populate GF country field with Connexions list
* Treat all licences as valid since updates API is no longer available
* Code improvements

= 2.8.12 =
* Only pre-render user details into first matching field of each type
* Show user ID in submit form page
* Track user email address changes in activity log
* Bug fixes

= 2.8.11 =
* Updated US country name to United States of America
* Added ability to sort user search results by any included field
* Updated styles to better fit with WP5.3
* Bug fixes

= 2.8.10 =
* Removed hard-coded default country
* Bug fixes

= 2.8.9 =
* Added MailChimp as default option for user source
* Made submit button more obvious when submitting form for user in admin area
* Added logic to map states from full text to code when handling form submissions
* State and country matching for form submissions is now case-insensitive
* Bug fixes

= 2.8.8 =
* Added support for KPI offsets
* Added success message on new user creation
* Added support for GF2.3+
* Now treats LIKE/NOT LIKE search as SHOW if no search term passed
* Bug fixes

= 2.8.7 =
* Major performance improvements to KPI calculations
* Bug fix

= 2.8.6 =
* Various improvements to profile pages
* Added support for hidden offsets for KPIs
* Added new first transaction KPI fields
* Bug fixes

= 2.8.5 =
* Added new print mail subscribe field
* Bug fix

= 2.8.4 =
* Bug fix

= 2.8.3 =
* Added Merge Users quicklink
* Added basic toggle of profile fields section on click of heading
* Bug fixes

= 2.8.2 =
* Added archive type to additional email field
* Added import as option for user source field
* Added helper functions to simplify working with multitext fields
* Added Connexions styling to more pages, and added filter so addons can inherit styling

= 2.8.1 =
* Added helper function to get user country
* Bug fixes

= 2.8.0 =
* Added Last Transaction Amount KPI field
* KPIs now auto-recalculated weekly to ensure accuracy
* Added licensing support
* Bug fix

= 2.7.1 =
* Added support for passwords in form submissions
* Added option to not pre-render user details into Gravity Forms
* Added quicklink to allow editing of user profile with default WP interface
* Bug fix

= 2.7.0 =
* Major restyling of Connexions pages
* Added support for creating/updating multiple users from single form submission
* Performance improvements
* Bug fixes

= 2.6.7 =
* Allow non-admins to update user roles
* Bug fix

= 2.6.6 =
* Bug fixes

= 2.6.5 =
* More styling tweaks for submit form modal
* Close submit form modal when link clicked
* Hide History tab on user profile
* Allow pre-render of work queue field in action form
* Major rework of activity log to resolve performance issues
* Bug fixes

= 2.6.4 =
* Bug fix

= 2.6.3 =
* Added filter to GF quicklink to allow hiding forms
* Don't show send email form in quicklink
* Set default values for checkbox fields when new user created
* Styling changes for submit form quicklink
* Bug fixes

= 2.6.2 =
* Major performance improvements for activity log
* Bug fixes

= 2.6.1 =
* Show 500 users per page by default instead of 5000
* Added filter to control number of days per page in activity log

= 2.6.0 =
* Updated logo
* Track user meta changes in activity log
* Added pagination to activity log
* Added filters to activity log
* Bug fixes

= 2.5.10 =
* Major decoupling of Work Queues from core
* Better sorting of quicklinks
* Activity log now uses WP global date/time format
* Added support for additional column in activity log
* Added support for additional info on user in activity log
* Show GF entry notes in activity log
* Send follow up emails for Action Notes form
* Bug fixes

= 2.5.9 =
* New action hook in user merge
* Bug fixes

= 2.5.8 =
* Better GF integration including pre-rendering address fields
* Added new Getting to Know You function
* Bug fixes

= 2.5.7 =
* Added custom display in activity log for entries from CRM-owned forms
* Updated Plugin URI
* Update user profile logic to only include form on tabs that require it
* Submit form page now supports editing existing entry
* Bug fixes

= 2.5.6 =
* Extended Send Email form to also support SMS messages
* Added filter so that addons can add to the list of forms owned by the CRM
* Moved quicklink styles to CSS file
* Added support for modal quicklinks which do not contain forms - NOT BACKWARDS COMPATIBLE! Any classes which previously extended bb_modal_quicklink will need to be updated to extend bb_form_quicklink
* Added quicklink to submit a form on behalf of a user
* Added pre-render of user details for Gravity Forms
* Added new Action Notes form
* Bug fixes

= 2.5.5 =
* Added new filter for disabled fields on user profile
* Bug fixes

= 2.5.4 =
* Added support for tracking user who submitted a form separately from the user the form entry was about
* Show form locked message on forms list page for Connexions forms
* Added internal and external reference fields to send email form
* Bug fixes

= 2.5.3 =
* Added ability for Connexions to create and manage its own Gravity Forms
* Created first form for tracking sent emails

= 2.5.2 =
* Several bug fixes in activity log

= 2.5.1 =
* Added simple way for addons to store activities
* Include these activities in Activity Log

= 2.5.0 =
* Major overhaul of quicklinks space - functionality is now built in to core plugin with hooks to allow addons to easily add more
* Significant cleanup of options and help
* Bug fixes

= 2.4.2 =
* Form submissions in activity log now require BB Express
* Styling updates for activity log

= 2.4.1 =
* Added Gravity Forms submissions to activity log

= 2.4.0 =
* Added new Activity Log view of all notes or specific user's notes

= 2.3.2 =
* Store user source (e.g. manual, form) as meta
* Display creator name on notes
* Create default saved searches and note types on activation
* Bug fixes

= 2.3.1 =
* Add note for any BB Cart transaction/checkout
* Add note for successful Paydock recurring transaction (requires BB Cart)
* Bug fixes

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

* Updated Admin UI to take advantage of the 3.8 upgrades
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
