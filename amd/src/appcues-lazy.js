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
 * Loads Appcues tracker.
 *
 * @copyright Copyright (c) 2021 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery','core/log'],
    function($, Log) {

        var self = this;

        var tracker = {};

        self.addAnalyticsJS = function() {
            const dfd = $.Deferred();
            const accountid = tracker.trackerInfo.accountid;
            if (!accountid) {
                Log.debug('Liquidus is misconfigured for Appcues, Account ID is missing.');
                return;
            }
            /* global Appcues */
            if (typeof Appcues === 'undefined') {
                dfd.fail();
                return dfd;
            }
            self.analytics = Appcues;
            dfd.resolve();
            return dfd;
        };

        tracker.loadTracker = function(trackerInfo) {
            tracker.trackerInfo = trackerInfo;
            return self.addAnalyticsJS();
        };

        tracker.identify = function() {
            let identifyData = {};

            if(typeof tracker.trackerInfo.userProperties !== 'undefined'){
                for (const property of tracker.trackerInfo.userProperties) {
                    if(typeof tracker.trackerInfo.staticShares[property] !== 'undefined'){
                        identifyData[property] = tracker.trackerInfo.staticShares[property];
                    }
                }
            } else {
                const allUserRolesString = String(tracker.trackerInfo.staticShares.allUserRoles);
                const userRoleContextString = String(tracker.trackerInfo.staticShares.userRoleContext);
                const olmsProductString = String(tracker.trackerInfo.staticShares.olmsProduct);
                const olmsPlatformString = String(tracker.trackerInfo.staticShares.olmsPlatform);
                const isImpersonatedString = String(tracker.trackerInfo.staticShares.isImpersonated);

                identifyData = {
                    allUserRoles: allUserRolesString,
                    userRoleContext: userRoleContextString,
                    olmsProduct: olmsProductString,
                    olmsPlatform: olmsPlatformString,
                    isImpersonated: isImpersonatedString,
                };
            }

            // Deck 36 configs.
            let deck36Configs = tracker.trackerInfo.deck36properties;
            if (typeof deck36Configs !== 'undefined') {
                for (const property in deck36Configs) {
                    identifyData[property] = deck36Configs[property];
                }
            }
            self.analytics.identify(tracker.trackerInfo.staticShares.userHash, identifyData);
        };

        tracker.trackPage = function() {
            self.analytics.page();
            let data = {};
            if (tracker.trackerInfo.staticShares.plugins) {
                const plugins = tracker.trackerInfo.staticShares.plugins;
                Object.entries(plugins).forEach(
                    ([type, value]) => {
                        // Dynamically adding property names.
                        data['PluginsUsed_' + type] = value;
                    });
                delete tracker.trackerInfo.staticShares.plugins;
            }
            Object.entries(tracker.trackerInfo.staticShares).forEach(
                ([type, value]) => {
                    data[type] = value;
                }
            );
            self.analytics.track('Page view', data);
        };

        tracker.processEvent = function(dfd, metricName, data) {
            self.analytics.track(metricName, data);
            // We don't resolve the dfd promise here so we have some time from the API.
        };

        return tracker;
    });
