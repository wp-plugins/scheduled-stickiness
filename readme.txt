=== Scheduled stickiness ===

Contributors: magnus_kson
Tags: scheduled stickiness set sticky unsticky automatic
Requires at least: 2.7
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin sets and unsets stickiness on specified dates for a certain post based on meta fields.

== Description ==

Sometimes you like a post to be sticky during a time interval in the future and not immediately when publishing. This plugin lets you specify when the stickiness should begin and end. 
Once you have set the start and end dates, you don't have to change the stickiness manually, the plugin uses Wordpress's cron job to set and unset stickiness.

* When writing a post that needs scheduled stickiness, enter the dates for start and stop (inclusive) and update your post.
* The date must be entered in the form yyyy-mm-dd (i.e 2015-05-27 for 27th of May 2015).
* The stickiness is updated hourly so changes may need up to one hour to take effect.

== Installation ==

1. Upload `scheduled-stickiness.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. There are no settings for this plugin.
		
== Screenshots ==

1. The fields for entering dates are found in the side column to each post.

== Changelog ==

= 0.1 = First release 2015-06-01
