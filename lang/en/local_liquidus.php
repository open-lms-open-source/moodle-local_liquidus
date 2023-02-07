<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Analytics
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Liquidus';
$string['segment'] = 'Segment';
$string['segment_desc'] = 'Segment Analytics tracking';
$string['segmentwritekey'] = 'Segment Write Key';
$string['segmentwritekey_desc'] = 'You can get your write key from your segment workspace.';
$string['keenio'] = 'Keen.IO';
$string['keenio_desc'] = 'Keen.IO Analytics tracking';
$string['keeniowritekey'] = 'Keen.IO Write Key';
$string['keeniowritekey_desc'] = 'You can get your write key from your Keen.IO workspace.';
$string['keenioprojectid'] = 'Keen.IO Project ID';
$string['keenioprojectid_desc'] = 'You can get your Project ID from your Keen.IO workspace.';
$string['masquerade_handling'] = 'Handle masquerading';
$string['masquerade_handling_desc'] = 'Handle when admin impersonates a user.';
$string['enabled'] = 'Enabled';
$string['enabled_olms_desc'] = 'Share anonymous usage information of admin users with Open LMS to help improve the product. (Non-admin roles, such as instructor, student, etc., are not tracked). You can learn more about this data telemetry initiative, <a href="https://support.openlms.net/hc/en-us/articles/5000851712925-Data-telemetry-FAQ" target="_blank">here</a>.';
$string['enabled_desc'] = 'Enable Analytics for Moodle';
$string['cleanurl'] = 'Clean URLs';
$string['cleanurl_desc'] = 'Generate clean URL for in advanced tracking';
$string['shareidentifiable'] = 'Share identifiable data of user';
$string['shareidentifiable_desc'] = 'Enable sharing identifiable data of user (e.g. user email)';
$string['trackadmin'] = 'Tracking Admins';
$string['trackadmin_desc'] = 'Enable tracking of Admin users';
$string['batchsize'] = 'Batch size';
$string['batchsize_desc'] = 'How big the batch size for records stream is.';
$string['extanalytics'] = 'External analytics';
$string['extanalyticss_help'] = 'External analytics services to push data to';
$string['liquidusaws'] = 'Liquidus AWS config';
$string['liquidusaws_help'] = 'Configuration for Liquidus analytics and data extraction';
$string['unidentifiable_staticshares'] = 'Unidentifiable static shares';
$string['unidentifiable_staticshares_desc'] = 'Unidentifiable data shared statically when the page is rendered';
$string['identifiable_staticshares'] = 'Identifiable static shares';
$string['identifiable_staticshares_desc'] = 'Identifiable data shared statically when the page is rendered';
$string['staticshares_userhash'] = 'User hash (Unidentifiable identifier)';
$string['staticshares_userrole'] = 'User role in page context';
$string['staticshares_contextlevel'] = 'Context level';
$string['staticshares_pagetype'] = 'Page type';
$string['staticshares_plugins'] = 'Plugins used when rendering the page';
$string['staticshares_courseid'] = 'Course ID';
$string['staticshares_pageurl'] = 'Page URL';
$string['staticshares_pagepath'] = 'Page navigation path';
$string['staticshares_userid'] = 'User ID';
$string['staticshares_useremail'] = 'User email';
$string['staticshares_siteshortname'] = 'Site short name';
$string['staticshares_sitelanguage'] = 'Site language';
$string['staticshares_sitehash'] = 'Site hash (Unidentifiable identifier)';
$string['staticshares_mroomsversion'] = 'Moodle Rooms version';
$string['staticshares_moodleversion'] = 'Moodle version';
$string['staticshares_theme'] = 'Theme used when rendering the page';
$string['kinesis'] = 'AWS Kinesis';
$string['kinesis_desc'] = 'Amazon Web Services Kinesis tracker';
$string['kinesisurl'] = 'AWS Kinesis URL';
$string['kinesisurl_desc'] = 'Amazon Web Services Kinesis tracker URL';
$string['eventdef'] = 'Custom Event Definition';
$string['eventdef_desc'] = 'JSON text that defines additional events to handle';
$string['privacy:metadata'] = 'The Liquidus plugin does not store any personal data but may share some data with third party providers.';
$string['privacy:metadata:liquidus'] = 'If you have accepted, liquidus can send some information to third parties as you configured it in the settings page.';
$string['privacy:metadata:liquidus:userhash'] = 'The userhash is created from siteshortname, userid, username and then is hashed (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:userrole'] = 'The userrole is obtained from the context and the user id (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:contextlevel'] = 'The context level is obtained from the context (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:pagetype'] = "The user's page type (It is only shared with third parties if it is configured in the settings page).";
$string['privacy:metadata:liquidus:plugins'] = 'The list of plugins that have been loaded (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:courseid'] = 'The course ID (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:pageurl'] = 'The page URL (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:pagepath'] = 'The path of the page (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:siteshortname'] = 'The short name of the site (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:sitelanguage'] = 'The language being used on the page (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:userid'] = 'A concatenation between the site id and the user id (It is only shared with third parties if it is configured in the settings page).';
$string['privacy:metadata:liquidus:useremail'] = "The user's email (It is only shared with third parties if it is configured in the settings page).";
$string['privacy:metadata:liquidus:sitehash'] = 'The sitehash is created from the root url of the page then is hashed (It is only shared with third parties if it is configured in the settings page).';
$string['google'] = 'Google';
$string['google_desc'] = 'Google tracking using gtag (Google Analytics, Google Ads, and Google Marketing Platform)';
$string['googlesiteid'] = 'Google Analytics Measurement ID';
$string['googlesiteid_desc'] = 'You can get your Google Analytics Measurement ID in your Google Analytics account administration. 
You can set up to 5 Measurement IDs (separated by commas).';
$string['trackernotconfigured'] = 'Liquidus tracker {$a} not properly configured.';
$string['trackermissingfield'] = 'Liquidus tracker missing field: {$a}';
$string['mixpanel'] = 'Mixpanel';
$string['mixpanel_desc'] = 'Mixpanel Analytics tracking';
$string['mixpaneltoken'] = 'Mixpanel Token';
$string['mixpaneltoken_desc'] = 'You can get your project token under project settings in Mixpanel.';
$string['tracknonadmin'] = 'Tracking Non-Admins';
$string['tracknonadmin_desc'] = 'Enable tracking of Non-Admin users';
$string['appcues'] = 'Appcues';
$string['appcues_desc'] = 'Appcues tracking';
$string['appcuesaccountid'] = 'Appcues Account ID';
$string['appcuesaccountid_desc'] = 'You can get your Appcues Analytics Account ID in your Appcues Analytics account administration.';
$string['general'] = 'General';
$string['general_help'] = 'Data telemetry tracking configuration';
$string['pagetypeevent'] = 'Append page type to page event';
$string['pagetypeevent_desc'] = 'When sending the page event to the analytics tracker, the "page type" identifier will be appended to the event identifier instead of sending it as a static share.';
$string['view'] = 'View';
$string['edit'] = 'Edit';
$string['excedlimitfield'] = 'The limit of 5 Measurement ID has been exceeded, {$a} were set.';
$string['trackforms'] = 'Track form submissions';
$string['trackforms_desc'] = 'Enables analytics provider ability to track form submissions';
