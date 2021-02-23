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
 * Liquidus Router module. Routes events to trackers.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'core/ajax', 'core/notification'],
function($, Log, ajax, notification) {

    var self = this;

    self.trackers = [];

    self.registerTracker = function(tracker) {
        Log.debug('Registering tracker ' + tracker.trackerInfo.trackerId);
        self.trackers.push(tracker);

        // Send initial tracker events.
        tracker.identify();
        tracker.trackPage();
        if (tracker.heartBeat) {
            setInterval(tracker.heartBeat, 10000); // Heart beat.
        }
    };

    self.init = function() {
        var promises = ajax.call([
            {
                methodname: 'local_liquidus_event_definition', args: {}
            },
        ]);

        promises[0].done(function(data) {
            self.addEventTracking(data);
        }).fail(function(ex) {
            notification.exception(ex);
        });

    };

    self.addEventTracking = function(eventDefArray) {
        if (!eventDefArray || eventDefArray.length === 0) {
            return;
        }

        Log.debug('Adding event tracking for:');
        Log.debug(eventDefArray);
        for (var e in eventDefArray) {
            var edef = eventDefArray[e];
            Log.debug('Looking for selector: ' + edef.testselector);
            if ($(edef.testselector).length) {
                self.processDefinition(edef);
            }
        }
    };

    self.processDefinition = function(edef) {
        Log.debug('Adding event handling for custom event: ' + edef.selector + ' -> ' + edef.event);
        $(edef.selector).on(edef.event, function(evt) {
            evt.preventDefault();

            Log.debug('Tracking custom event: ' + edef.selector + ' -> ' + edef.event);

            var data = {};

            for (var d in edef.data) {
                var ddef = edef.data[d];
                Log.debug('Looking for ' + ddef.selector + 'value.');
                if (ddef.type === 'input') {
                    data[ddef.name] = $(ddef.selector).val();
                }
            }
            Log.debug('Got these values:');
            Log.debug(data);

            var trackerPromises = [];
            for (var t in self.trackers) {
                var dfd = $.Deferred();
                trackerPromises.push(dfd);
                self.trackers[t].processEvent(dfd, edef.name, data);
            }

            $.when.apply($, trackerPromises).then(function() {
                Log.debug('Processed all trackers, proceeding with event.');
                $(edef.selector).off(edef.event);
                $(edef.selector).trigger(edef.event);
            });
        });
    };

    return {
        'init': self.init,
        'registerTracker': self.registerTracker
    };
});
