=== WPS Hide Login ===

Contributors: tabrisrp, WPServeur, nicolaskulka
Tags: rename, login, wp-login, wp-login.php, custom login url
Requires at least: 4.1
Tested up to: 4.9
Stable tag: 1.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change wp-login.php to anything you want.

== Description ==

*WPS Hide Login* is a very light plugin that lets you easily and safely change the url of the login form page to anything you want. It doesn’t literally rename or change files in core, nor does it add rewrite rules. It simply intercepts page requests and works on any WordPress website. The wp-admin directory and wp-login.php page become inaccessible, so you should bookmark or remember the url. Deactivating this plugin brings your site back exactly to the state it was before.

= Compatibility =

Requires WordPress 4.1 or higher. All login related things such as the registration form, lost password form, login widget and expired sessions just keep working.

It’s also compatible with any plugin that hooks in the login form, including:

* BuddyPress,
* bbPress,
* Limit Login Attempts,
* and User Switching.

Obviously it doesn’t work with plugins or themes that *hardcoded* wp-login.php.

Works with multisite, but not tested with subdomains. Activating it for a network allows you to set a networkwide default. Individual sites can still rename their login page to something else.

If you’re using a **page caching plugin** other than WP Rocket, you should add the slug of the new login url to the list of pages not to cache. WP Rocket is already fully compatible with the plugin.

For W3 Total Cache and WP Super Cache this plugin will give you a message with a link to the field you should update.

= GitHub =

https://github.com/tabrisrp/wps-hide-login

== Installation ==

1. Go to Plugins › Add New.
2. Search for *WPS Hide Login*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Change your login url there.
5. You can change this option any time you want, just go back to Settings › General › WPS Hide Login.

== Screenshots ==
1. Setting on single site installation
2. Setting for network wide

== Frequently Asked Questions ==

= I forgot my login url!  =

Either go to your MySQL database and look for the value of `whl_page` in the options table, or remove the `wps-hide-login` folder from your `plugins` folder, log in through wp-login.php and reinstall the plugin.

On a multisite install the `whl_page` option will be in the sitemeta table, if there is no such option in the options table.

= I'm locked out! =
This case can come from plugins modifying your .htaccess files to add or change rules, or from an old WordPress MU configuration not updated since Multisite was added.

First step is to check your .htaccess file and compare it to a regular one, to see if the problem comes from it.

== Changelog ==

= 1.2.2 =
* Enhancement: Compatibility 4.9.x

= 1.2.1 =
* Enhancement: Prevent access to the login page by using the URL encoded version of wp-login.php

= 1.2 =
* Enhancement: Prevent redirection to login URL when accessing /wp-admin/customize.php directly
* Enhancement: Redirect to admin URL when already logged-in and accessing login URL without the action query string

= 1.1.7 =
* Fix: change fake 404 on wp-admin when not logged-in to a 403 forbidden to prevent fatal errors with various themes & plugins

= 1.1.6 =
* Fix: bug with Yoast SEO causing a Fatal Error and blank screen when loading /wp-admin/ without being logged-in

= 1.1.5 =
* Fix: Stop displaying the new login url notice everywhere when settings are updated (thanks @ kmelia on GitHub)
* Improvement: better way of retrieving the 404 template

= 1.1.4 =
* Fix: bypass the plugin when $pagenow is admin-post.php

= 1.1.3 =
* Fix: issue if no 404 template in active theme directory

= 1.1.2 =
* Modified priority on hooks to fix a problem with some configurations

= 1.1.1 =
* Check for Rename wp-login.php activation before activating WPS Hide Login to prevent conflict

= 1.1 =
* Fix : CSRF security issue when saving option value in single site and multisite mode. Thanks to @Secupress
* Improvement : changed option location from permalinks to general, because register_setting doesn't work on permalinks page.
* Improvement : notice after saving is now dismissible (compatibility with WP 4.2)
* Uninstall function is now in it's separate file uninstall.php
* Some cleaning and reordering of code

= 1.0 =

* Initial version. This is a fork of the Rename wp-login.php plugin, which is unmaintained https://wordpress.org/plugins/rename-wp-login/. All previous changelogs can be found there.
