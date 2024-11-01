=== Userbase Access Control ===
Contributors: Desertsnowman
Donate link: http://twitter.com/dcramer
Tags: user access, control, access, access control, member, member control, subscriber, subscriptions
Requires at least: 2.9.2
Tested up to: 3.5.2
Stable tag: 1.0

Adds user access control via admin defined user groups on a per page/posttype level

== Description ==

Adds user access control to pages

Features include:

*	Access group creation
*	User group assigning
*	Redirect un-logged in user to defined login page
*	Redirect public users to login page.
*   Redirect Logged in users to Access denied/upgrade notice page

== Installation ==

1. Upload the plugin folder 'userbase-access-control' to your `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create access groups in the 'Access Control' menu under the 'Users' menu group
4. Edit a user to assign access rights to the user
5. Check the access control groups in the access control meta box in edit/create page

== Frequently Asked Questions ==

= Does this plugin supply a login widget? =

No. There are plenty of good login widgets. just search for one- almost everyone will work.

== Changelog ==

= 1.0 =
* Simplified the plugin to handle groups and manage access cleanly.

= 0.1.4 =
* Corrected redirection system to prevent infinite loops.
* Added a setting to select access denied/upgrade account page.

= 0.1.3 =
* Added redirect to wp-admin if a login page has not been set.

= 0.1.2 =
* Fixed a redirection problem that happens when access groups are deleted.

= 0.1.1 =
* Made changes to prevent "Add New" to be selected as login page by default.

= 0.1 =
* Initial Relase.

== Screenshots ==

1. Manage groups and user access menu location
2. Access Control Metabox in edit/add page
3. Create & Manage groups

== Upgrade Notice ==

Upgrade to what?