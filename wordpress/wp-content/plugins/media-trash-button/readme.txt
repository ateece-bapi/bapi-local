=== Media Trash Button ===
Contributors: Katsushi Kawamori
Donate link: https://shop.riverforest-wp.info/donate/
Tags: media library, trash
Requires at least: 4.7
Requires PHP: 8.0
Tested up to: 6.8
Stable tag: 1.04
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add trash button for Media Library.

== Description ==

Add trash button for Media Library.

= Add button =
* List view only.

= Button =
* All media items
* View Trash

= In wp-config.php, Add the following one line. =
~~~
define( 'MEDIA_TRASH', true );
~~~

== Installation ==

1. Upload `media-trash-button` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

none

== Screenshots ==

1. Media Library
2. Trash

== Changelog ==

= [1.04] 2025/04/18 =
* Fix - Loading the management screen.

= 1.03 =
Supported WordPress 6.4.
PHP 8.0 is now required.

= 1.02 =
Supported WordPress 5.6.

= 1.01 =
Made it list view only.

= 1.00 =
Initial release.

== Upgrade Notice ==

= 1.00 =
Initial release.
