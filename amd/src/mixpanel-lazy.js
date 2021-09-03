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
 * Loads Mixpanel tracker.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2021 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery','core/log'],
    function($, Log) {

        var self = this;

        var tracker = {};

        self.addAnalyticsJS = function() {
            var dfd = $.Deferred();
            var token = tracker.trackerInfo.token;
            if (!token) {
                Log.debug('Liquidus is misconfigured for Mixpanel, Token is missing.');
                return;
            }
            /* eslint-disable */
            (function(f,b){if(!b.__SV){var e,g,i,h;window.mixpanel=b;b._i=[];b.init=function(e,f,c){function g(a,d){var b=d.split(".");2==b.length&&(a=a[b[0]],d=b[1]);a[d]=function(){a.push([d].concat(Array.prototype.slice.call(arguments,0)))}}var a=b;"undefined"!==typeof c?a=b[c]=[]:c="mixpanel";a.people=a.people||[];a.toString=function(a){var d="mixpanel";"mixpanel"!==c&&(d+="."+c);a||(d+=" (stub)");return d};a.people.toString=function(){return a.toString(1)+".people (stub)"};i="disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking start_batch_senders people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(" ");
            for(h=0;h<i.length;h++)g(a,i[h]);var j="set set_once union unset remove delete".split(" ");a.get_group=function(){function b(c){d[c]=function(){call2_args=arguments;call2=[c].concat(Array.prototype.slice.call(call2_args,0));a.push([e,call2])}}for(var d={},e=["get_group"].concat(Array.prototype.slice.call(arguments,0)),c=0;c<j.length;c++)b(j[c]);return d};b._i.push([e,f,c])};b.__SV=1.2;e=f.createElement("script");e.type="text/javascript";e.async=!0;e.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?
            MIXPANEL_CUSTOM_LIB_URL:"file:"===f.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";g=f.getElementsByTagName("script")[0];g.parentNode.insertBefore(e,g)}})(document,window.mixpanel||[]);
            mixpanel.init(token);
            /* eslint-enable */

            if (typeof tracker.trackerInfo.trackforms !== 'undefined'
                && tracker.trackerInfo.trackforms) {
                trackForms();
            }

            dfd.resolve(tracker);
            return dfd;
        };

        const trackForms = () => {
            const pageType = typeof tracker.trackerInfo.staticShares.pageType != 'undefined' ?
                tracker.trackerInfo.staticShares.pageType + '_' : '';

            $('form').each(function(index) {
                const currentForm = $(this);

                let id = currentForm.attr('id');
                if (typeof id === 'undefined') {
                    id = currentForm.attr('name');
                }

                if (typeof id === 'undefined') {
                    id = index;
                } else {
                    id = index + '_' + id;
                }

                id = pageType + id;

                /* global mixpanel */
                mixpanel.track_forms(currentForm, 'Form submitted - ' + id);
            });
        };

        tracker.loadTracker = function(trackerInfo) {
            tracker.trackerInfo = trackerInfo;
            return self.addAnalyticsJS();
        };

        tracker.identify = function() {
            /* global mixpanel */
            mixpanel.identify(tracker.trackerInfo.staticShares.userHash);
        };

        tracker.trackPage = function() {
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
                    if (type === 'pageType'
                        && typeof tracker.trackerInfo.pagetypeevent != 'undefined'
                        && tracker.trackerInfo.pagetypeevent
                    ) {
                        return; // Page type will be appended to page event.
                    }
                    data[type] = value;
                }
            );
            let eventId = 'Page view';
            if (typeof tracker.trackerInfo.pagetypeevent != 'undefined'
                && tracker.trackerInfo.pagetypeevent
                && typeof tracker.trackerInfo.staticShares.pageType != 'undefined'
            ) {
                eventId += ' - ' + tracker.trackerInfo.staticShares.pageType;
            }
            /* global mixpanel */
            mixpanel.track(eventId, data);
        };

        tracker.processEvent = function(dfd, metricName, data) {
            /* global mixpanel */
            mixpanel.track(metricName, data, {send_immediately: true}, () => {
                dfd.resolve();
                Log.debug('[mixpanel] Sent custom event.');
            });
        };

        return tracker;
    });
