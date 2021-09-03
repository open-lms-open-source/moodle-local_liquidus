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
 * Liquidus main module. Loads specific trackers.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'local_liquidus/router'],
function($, Log, Router) {

    const requireTracker = (trackerInfo) => {
        if (!trackerInfo.trackerId) {
            return;
        }

        // Initialize tracker's static shares.
        trackerInfo.staticShares = {};

        /* global localLiquidusShares */
        if (typeof localLiquidusShares !== 'undefined'
            && typeof localLiquidusShares[trackerInfo.trackerId] !== 'undefined'
        ) {
            Log.debug(`[${trackerInfo.trackerId}] Found shares, loading.`);
            trackerInfo.staticShares = localLiquidusShares[trackerInfo.trackerId];
        }

        require(['local_liquidus/' + trackerInfo.trackerId + '-lazy'], function(tracker) {
            Log.debug(`[${trackerInfo.trackerId}] Loaded tracker. Initializing.`);
            Log.debug(trackerInfo);
            tracker.loadTracker(trackerInfo)
                .done(() => Router.registerTracker(tracker))
            ;
        });
    };

    /**
     * Create a single instance for Liquidus to configure a single tracker.
     * @param trackerInfo
     * @constructor
     */
    const Liquidus = function(trackerInfo) {
        Log.debug(`[${trackerInfo.trackerId}] Loading Liquidus.`);
        Router.init().done(() => {
            requireTracker(trackerInfo);
        });
    };

    return {
        'init': (trackerInfo) => {
            return new Liquidus(trackerInfo);
        }
    };
});
