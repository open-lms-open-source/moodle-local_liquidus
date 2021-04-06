# Liquidus Local plugin
By enabling and configuring this plugin with 
an Analytics account, such as Keen, Segment or Google, Admins will
be able to track different usage information from their sites.

Some of the usage information you can track is:

* User information – Encrypted, can track where the user is connecting from, with which type of device, roles, etc.
* Specific events – Such as login, course entered, activity entered, etc.
* Plugin usage – Which active plugins on your platform are being used.

Currently, the Liquidus plugin allows any admin to configure it for getting usage info.

This plugin was contributed by the Open LMS Product Development team. Open LMS is an education technology company
dedicated to bringing excellent online teaching to institutions across the globe.  We serve colleges and universities,
schools and organizations by supporting the software that educators use to manage and deliver instructional content to
learners in virtual classrooms.

## Installation
Extract the contents of the plugin into _/wwwroot/local_ then visit `admin/upgrade.php` or use the CLI script to upgrade your site.

1. You'll need to install the [local_aws_sdk](https://github.com/blackboard-open-source/moodle-local_aws_sdk)
plugin as a requirement for this plugin to work.
2. Install this as you would any Moodle plugin and configure the analytics provider data.

## Configuration - Example for Google Analytics:
1. Setup a Google analytics account and property.
2. Enable Liquidus plugin in Site *administration › Plugins › Local plugins › Liquidus.*

![liquidus](https://help.openlms.net/wp-content/uploads/2020/10/Screen-Shot-2020-10-06-at-11.12.16-AM-1-1536x306.png)

3. Enable the Google tracking on the same setting page
4. Copy the property ID from your Google Analytics account, so you can track the page, and Add the property ID to the local_liquidus settings form.

![liquidus](https://help.openlms.net/wp-content/uploads/2020/10/Screen-Shot-2020-10-06-at-11.22.17-AM.png)

5. Save the form.

Check your Google Analytics dashboard to see the influx of events being sent by the plugin.

## License
Copyright (c) 2021 Open LMS (https://www.openlms.net)

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
