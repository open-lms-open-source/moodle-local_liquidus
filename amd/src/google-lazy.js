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
define(['jquery','core/log', 'core/templates'],
    function($, Log, Templates) {

        var self = this;

        var tracker = {};

        self.addAnalyticsJS = function() {
            var dfd = $.Deferred();
            var siteid = tracker.trackerInfo.siteid;
            if (!siteid) {
                Log.debug('Liquidus is misconfigured for Google, Site ID is missing.');
                return;
            }
            var context = [];
            context['siteid'] = siteid;
            Templates.render('local_liquidus/gtag', context).then(function(html) {
                $('body').append(html);
                if (typeof gtag === 'undefined') {
                    dfd.fail();
                    return;
                }
                /* global gtag */
                self.gtag = gtag;
                dfd.resolve();
            });

            return dfd;
        };

        tracker.loadTracker = function(trackerInfo) {
            tracker.trackerInfo = trackerInfo;
            return self.addAnalyticsJS();
        };

        tracker.identify = function() {}; // User hash is already sent in trackPage.

        tracker.trackPage = function() {
            if (tracker.trackerInfo.staticShares.plugins) {
                const plugins = tracker.trackerInfo.staticShares.plugins;
                Object.entries(plugins).forEach(
                    ([type, value]) => {
                        value.forEach(pluginId => {
                            const eventId = type + '_' + pluginId;
                            const eventCategory = 'pluginUsed';
                            self.gtag('event', eventId, {
                                event_category: eventCategory
                            });
                        });
                    });
                delete tracker.trackerInfo.staticShares.plugins;
            }

            if (tracker.trackerInfo.staticShares.userRole) {
                const userRole = tracker.trackerInfo.staticShares.userRole;
                self.gtag('event', userRole.join(','), {
                    event_category: 'userRole'
                });
                delete tracker.trackerInfo.staticShares.userRole;
            }

            Object.entries(tracker.trackerInfo.staticShares).forEach(
                ([eventCategory, eventId]) => {
                    self.gtag('event', eventId, {
                        event_category: eventCategory
                    });
                }
            );
        };

        tracker.processEvent = function(dfd, metricName, data) {
            self.gtag('event', metricName, {
                'userHash': tracker.trackerInfo.staticShares.userHash,
                'eventData': data,
                'event_callback': function() {
                    dfd.resolve();
                }
            });
        };

        return tracker;
    });
