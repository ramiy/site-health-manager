=== Site Health Manager ===
Contributors: ramiy
Tags: site health, health, security, debug, confidential data, tool, manager, data, tests
Requires at least: 5.2
Tested up to: 5.2
Stable tag: 1.1.0
Requires PHP: 5.6

Site Health Manager allows you to customize critical server data visibility and status tests availability.

== Description ==

Make sure your health score is correct by running only the tests relevant for your server configuration. And take some protective measures to keep your critical server data secure.

= Status Manager =

Site Health Status screen generates a health score based on tests it runs on the server. But some tests may not be relevant for your server setup. This may cause a low health score, unhappy site owners and complains for web hosts.

Select the test you want to disable in order to prevent displaying wrong health score in your Site Health Status screen. For example missing PHP extensions for security reasons or disabled background updates to allow version control.

= Info Manager =

Site Health Info screen displays configuration data and debugging information. Some data in this screen is confidential and sharing critical server data should be done with caution and with security in mind.

Select what information you want to disable in order to prevent your users from coping it to clipboard when sharing debug data with third parties. For example when sending data to plugin/theme developers to debug issues.

= Contribute = 

If you want to contribute, visit [Site Health Manager GitHub Repository](https://github.com/ramiy/site-health-manager) and see where you can help.

You can also help by translating the plugin to your language via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/site-health-manager/).

== Frequently Asked Questions ==

= What are the minimum requirements to run the plugin? =
* WordPress version 5.2 or greater.
* PHP version 5.6 or greater.
* MySQL version 5.0 or greater.

= Can I add new test to Site Heath Status? ==
No, you can't add new test. The plugin allows you to disable existing tests that are not relevant for your server configuration.

= Can I add new data to Site Heath Info? ==
No, you can't add new data. The plugin only lets you manage and organize existing data added by WordPress core, plugins and themes.

== Screenshots ==

1. Site Health Manager - status manager screen.
1. Site Health Manager - info manager screen.
1. Site Health Status - perfect score based on server configuration.
1. Site Health Info - debug data without the data the user disable.

== Changelog ==

= 1.1.0 =

* Status tests availability - control what tests to disable.
* Added an inner tabs navigation to separate the "Status Manager" from the "Info Manager".

= 1.0.0 =

* Initial release.
* Info data visibility - control what debug data to disable.
