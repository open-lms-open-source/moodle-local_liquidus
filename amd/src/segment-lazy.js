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
 * Loads Segment tracker.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery','core/log'],
function($, Log) {

    var self = this;

    var tracker = {};

    var options = {};

    self.addAnalyticsJS = function() {
        var dfd = $.Deferred();
        var writeKey = tracker.trackerInfo.writeKey;
        if (!writeKey) {
            Log.debug('Liquidus is misconfigured for Segment, Write Key is missing.');
            return;
        }
        /* eslint-disable */
        !function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on"];analytics.factory=function(t){return function(){var e=Array.prototype.slice.call(arguments);e.unshift(t);analytics.push(e);return analytics}};for(var t=0;t<analytics.methods.length;t++){var e=analytics.methods[t];analytics[e]=analytics.factory(e)}analytics.load=function(t,e){var n=document.createElement("script");n.type="text/javascript";n.async=!0;n.src="https://cdn.segment.com/analytics.js/v1/"+t+"/analytics.min.js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(n,a);analytics._loadOptions=e};analytics.SNIPPET_VERSION="4.1.0";
            self.analytics = analytics;
            analytics.load(writeKey);
            dfd.resolve(tracker);
        }}();
        /* eslint-enable */
        return dfd;
    };

    tracker.loadTracker = function(trackerInfo) {
        tracker.trackerInfo = trackerInfo;
        return self.addAnalyticsJS();
    };

    tracker.identify = function() {
        Log.debug('Identifying with segment');
        self.analytics.identify(tracker.trackerInfo.staticShares.userHash, tracker.trackerInfo.staticShares);
    };

    tracker.trackPage = function() {
        self.analytics.page();
    };

    tracker.processEvent = function(dfd, metricName, data) {
        self.analytics.track(metricName, {
            userHash: tracker.trackerInfo.staticShares.userHash,
            eventData: data
        }, options, function() {
            dfd.resolve(true);
            Log.debug('Segment resolved.');
        });
    };

    return tracker;
});
