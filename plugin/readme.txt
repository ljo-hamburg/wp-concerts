=== WP Concerts ===
Contributors: codello
Tags: music, orchestra, concert, ljo
Requires at least: <%= composer['require-dev']['johnpbloch/wordpress'].replace(/^\^/, '') %>
Tested up to: <%= composer['require-dev']['johnpbloch/wordpress'].replace(/^\^/, '') %>
License: GNU General Public License version 3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WP Concerts is an easy way to include a listing of concerts on your WordPress website.
Styling is mostly done by the theme.

== Description ==
WP Concerts is a plugin that allows you to easily include concerts on your WordPress
site. This is similar to other event plugins, but specializes in the use case of
classical concerts. The plugin features:

- Customizable Concerts
- Extensible API to supply custom data from themes or site-specific plugins.
- Out of the box support for JSON-LD structured data
- Support for ICS-formatted events
- Gutenberg support

## ICS Support
The WP Concerts plugin comes with ICS support out of the box. By appending `?ics=1` to
a single concert page or a concert archive the respective page will be downloaded as an
ICS file. Many existing calendar applications can simply subscribe to these calendars
including automatic updates.

== Installation ==
Installing the plugin is as simple as installing any other plugin. Just upload it to
your site and click "activate".
