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
$string['enabled_desc'] = 'Enable Analytics for Moodle';
$string['cleanurl'] = 'Clean URLs';
$string['cleanurl_desc'] = 'Generate clean URL for in advanced tracking';
$string['trackadmin'] = 'Tracking Admins';
$string['trackadmin_desc'] = 'Enable tracking of Admin users (not recommended)';
$string['batchsize'] = 'Batch size';
$string['batchsize_desc'] = 'How big the batch size for records stream is.';
$string['extanalytics'] = 'External analytics';
$string['extanalyticss_help'] = 'External analytics services to push data to';
$string['liquidusaws'] = 'Liquidus AWS config';
$string['liquidusaws_help'] = 'Configuration for Liquidus analytics and data extraction';
$string['staticshares'] = 'Static shares';
$string['staticshares_desc'] = 'Data shared statically when the page is rendered';
$string['staticshares_userhash'] = 'User hash (Anonymous identifier)';
$string['staticshares_userrole'] = 'User role in page context';
$string['staticshares_contextlevel'] = 'Context level';
$string['staticshares_pagetype'] = 'Page type';
$string['staticshares_plugins'] = 'Plugins used when rendering the page';
$string['kinesis'] = 'AWS Kinesis';
$string['kinesis_desc'] = 'Amazon Web Services Kinesis tracker';
$string['kinesisurl'] = 'AWS Kinesis URL';
$string['kinesisurl_desc'] = 'Amazon Web Services Kinesis tracker URL';
$string['eventhandling'] = 'Event handling';
$string['eventhandling_help'] = 'Event stream handling management';
$string['eventdef'] = 'Definition';
$string['eventdef_desc'] = 'Event handle definition';
$string['privacy:metadata'] = 'The Liquidus plugin does not store any personal data.';
$string['google'] = 'Google';
$string['google_desc'] = 'Google tracking using gtag (Google Analytics, Google Ads, and Google Marketing Platform)';
$string['googlesiteid'] = 'Google Analytics property ID';
$string['googlesiteid_desc'] = 'You can get your Google Analytics property ID yout Google Analytics account administration.';
$string['trackernotconfigured'] = 'Liquidus tracker {$a} not properly configured.';
$string['trackermissingfield'] = 'Liquidus tracker missing field: {$a}';
$string['mixpanel'] = 'Mixpanel';
$string['mixpanel_desc'] = 'Mixpanel Analytics tracking';
$string['mixpaneltoken'] = 'Mixpanel Token';
$string['mixpaneltoken_desc'] = 'You can get your project token under project settings in Mixpanel.';
$string['tracknonadmin'] = 'Tracking Non-Admins';
$string['tracknonadmin_desc'] = 'Enable tracking of Non-Admin users (You should use it if you only want to track admins)';
