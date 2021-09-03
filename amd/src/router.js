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

    const self = this;

    self.trackers = [];

    self.eventDefArray = [];

    self.initRun = false;

    self.eventsLoaded = false;

    self.registerTracker = function(tracker) {
        Log.debug(`[${tracker.trackerInfo.trackerId}] Registering tracker`);
        self.trackers[tracker.trackerInfo.trackerId] = tracker;

        // Send initial tracker events.
        tracker.identify();
        tracker.trackPage();
        if (tracker.heartBeat) {
            setInterval(tracker.heartBeat, 10000); // Heart beat.
        }
        self.addEventTracking(tracker.trackerInfo.trackerId);
    };

    self.init = function() {
        const dfd = $.Deferred();
        if (self.initRun) {
            self.whenTrue(() => {
                return self.eventsLoaded;
            }, () => {
                dfd.resolve();
            }, true);
            return dfd;
        }
        self.initRun = true;

        const promises = ajax.call([
            {
                methodname: 'local_liquidus_event_definition', args: {}
            },
        ]);

        $.when(...promises).done(function(data) {
            data.forEach((trackerDef) => {
                self.eventDefArray[trackerDef.provider] = trackerDef;
            });
            self.eventsLoaded = true;
            dfd.resolve();
        }).fail(function(ex) {
            notification.exception(ex);
            dfd.resolve();
        });

        return dfd;
    };

    self.addEventTracking = function(trackerId) {
        const trackerDef = self.eventDefArray[trackerId];
        if (typeof trackerDef === 'undefined') {
            Log.debug(`[${trackerId}] No custom events configured.`);
            return;
        }
        const tracker = self.trackers[trackerId];
        trackerDef.definition.forEach((eDef) => {
            Log.debug(`[${trackerDef.provider}] Looking for selector: ${eDef.testselector}`);
            if ($(eDef.testselector).length) {
                self.processDefinition(tracker, eDef);
            }
        });
    };

    self.processDefinition = (tracker, edef) => {
        const trackerId = tracker.trackerInfo.trackerId;
        Log.debug(`[${trackerId}] Adding event handling for custom event: ${edef.selector} -> ${edef.event}`);
        $(edef.selector).on(edef.event, function(evt) {
            evt.preventDefault();

            const parentNode = $(this);

            Log.debug(`[${trackerId}] Tracking custom event: ${edef.selector} -> ${edef.event}`);

            var data = {};

            for (let i in edef.data) {
                const ddef = edef.data[i];
                Log.debug(`[${trackerId}] Looking for '${ddef.selector}' value.`);
                const ddefNode = parentNode.find(ddef.selector);
                if (ddefNode.length > 0) {
                    Log.debug(`[${trackerId}] Selector '${ddef.selector}' value.`);
                    ddefNode.each(function(index) {
                        const dataNode = $(this);

                        let id = dataNode.attr('id');
                        if (typeof id === 'undefined') {
                            id = dataNode.attr('name');
                        }

                        if (typeof id === 'undefined') {
                            id = index;
                        } else {
                            id = index + '_' + id;
                        }

                        data[ddef.name + '_' + id] = null;

                        let value;
                        if (ddef.type === 'input') {
                            value = dataNode.val();
                        } else {
                            value = dataNode.text();
                        }
                        if (typeof value !== 'undefined' && value !== '' && value !== null) {
                            data[ddef.name + '_' + id] = value;
                        }
                    });
                }

            }

            const dfd = $.Deferred();
            tracker.processEvent(dfd, edef.name, data);

            // Forcefully resolve the promise if it hasn't been already.
            // This guarantees that the process is not stalled.
            setTimeout(() => {
                if (dfd.state() === 'pending') {
                    Log.debug(`[${trackerId}] Failed to send data for: ${edef.selector} -> ${edef.event}`);
                    dfd.resolve();
                }
            }, 1000);


            dfd.then(() => {
                Log.debug(`[${trackerId}] Processed: ${edef.selector} -> ${edef.event}, proceeding with event.`);
                $(edef.selector).off(edef.event);
                $(edef.selector).trigger(edef.event);
            });
        });
    };

    /**
     * On function evaluating true.
     *
     * @param {function} func
     * @param {function} callBack
     * @param {boolean} forceCallBack
     * @param {number} maxIterations
     * @param {number} i
     */
    self.whenTrue = (func, callBack, forceCallBack, maxIterations, i) => {
        maxIterations = !maxIterations ? 10 : maxIterations;
        i = !i ? 0 : i + 1;
        if (i > maxIterations) {
            // Error, too long waiting for function to evaluate true.
            if (forceCallBack) {
                callBack();
            }
            return;
        }
        if (func()) {
            callBack();
        } else {
            window.setTimeout(() => {
                self.whenTrue(func, callBack, forceCallBack, maxIterations, i);
            }, 200);
        }
    };

    return {
        'init': self.init,
        'registerTracker': self.registerTracker
    };
});
