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
 * Loads AWS Kinesis tracker.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log'],
function($, Log) {

    var self = this;

    var tracker = {};

    self.addAnalyticsJS = function() {
        var dfd = $.Deferred();
        if (!tracker.trackerInfo.kinesisURL) {
            Log.debug('Liquidus is misconfigured for AWS Kinesis, Kinesis URL is missing.');
            return null;
        }

        dfd.resolve(true);

        return dfd;
    };

    tracker.loadTracker = function(trackerInfo) {
        tracker.trackerInfo = trackerInfo;
        return self.addAnalyticsJS();
    };

    tracker.identify = function() {
        $.ajax({
            url: tracker.trackerInfo.kinesisURL,
            method: 'POST',
            headers: tracker.trackerInfo.staticShares
        }).done(function() {
            Log.debug('Liquidus identified with kinesis.');
        });
    };

    tracker.trackPage = function() {
        $.ajax({
            url: tracker.trackerInfo.kinesisURL,
            method: 'POST',
            headers: {
                userHash: tracker.trackerInfo.staticShares.userHash,
                page: window.location.pathname.split("/").slice(-1),
                referrer_page: document.referrer,
                custom_metric_name: "userAgent",
                custom_metric_string_value: navigator.userAgent,
            }
        }).done(function() {
            Log.debug('Liquidus sent page to kinesis.');
        });
    };

    tracker.heartBeat = function() {
        $.ajax({
            url: tracker.trackerInfo.kinesisURL,
            method: 'POST',
            headers: {
                userHash: tracker.trackerInfo.staticShares.userHash,
                custom_metric_name: "heartBeat",
                custom_metric_string_value: 1,
            }
        }).done(function() {
            Log.debug('Liquidus sent heart beat to kinesis.');
        });
    };

    tracker.processEvent = function(dfd, metricName, data) {
        $.ajax({
            url: tracker.trackerInfo.kinesisURL,
            method: 'POST',
            headers: {
                userHash: tracker.trackerInfo.staticShares.userHash,
                custom_metric_name: metricName,
                custom_metric_string_value: JSON.stringify(data),
            }
        }).done(function() {
            dfd.resolve(true);
            Log.debug('AWS Kinesis promise resolved.');
        });
    };

    return tracker;
});
