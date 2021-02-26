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

    self.addKeenIO = function() {
        var dfd = $.Deferred();
        var writeKey = tracker.trackerInfo.writeKey, projectId = tracker.trackerInfo.projectId;
        if (!writeKey || !projectId) {
            Log.debug('Liquidus is misconfigured for keen.io, Write Key or Project ID are missing.');
            return;
        }
        /* eslint-disable */
        (function(name,path,ctx){var latest,prev=name!=='Keen'&&window.Keen?window.Keen:false;ctx[name]=ctx[name]||{ready:function(fn){var h=document.getElementsByTagName('head')[0],s=document.createElement('script'),w=window,loaded;s.onload=s.onerror=s.onreadystatechange=function(){if((s.readyState&&!(/^c|loade/.test(s.readyState)))||loaded){return}s.onload=s.onreadystatechange=null;loaded=1;latest=w.Keen;if(prev){w.Keen=prev}else{try{delete w.Keen}catch(e){w.Keen=void 0}}ctx[name]=latest;ctx[name].ready(fn)};s.async=1;s.src=path;h.parentNode.insertBefore(s,h)}}
        })('KeenAsync','https://d26b395fwzu5fz.cloudfront.net/keen-tracking-1.4.2.min.js',this);

        KeenAsync.ready(function(){
            self.client = new KeenAsync({
                projectId: projectId,
                writeKey: writeKey
            });
            dfd.resolve(tracker);
        });
        /* eslint-enable */
        return dfd;
    };

    tracker.loadTracker = function(trackerInfo) {
        tracker.trackerInfo = trackerInfo;
        return self.addKeenIO();
    };

    tracker.identify = function() {
        self.client.recordEvent('identify', {
            userHash: tracker.trackerInfo.staticShares.userHash
        });
    };

    tracker.trackPage = function() {
        self.client.recordEvent('pageviews', tracker.trackerInfo.staticShares);
    };

    tracker.processEvent = function(dfd, metricName, data) {
        self.client.recordEvent(metricName, {
            userHash: tracker.trackerInfo.staticShares.userHash,
            eventData: data,
        }, function() {
            dfd.resolve(true);
            Log.debug('KeenIO resolved.');
        });
    };

    return tracker;
});
