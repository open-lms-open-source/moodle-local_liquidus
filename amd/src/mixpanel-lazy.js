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
            (function(c,a){if(!a.__SV){var b=window;try{var d,m,j,k=b.location,f=k.hash;d=function(a,b){return(m=a.match(RegExp(b+"=([^&]*)")))?m[1]:null};f&&d(f,"state")&&(j=JSON.parse(decodeURIComponent(d(f,"state"))),"mpeditor"===j.action&&(b.sessionStorage.setItem("_mpcehash",f),history.replaceState(j.desiredHash||"",c.title,k.pathname+k.search)))}catch(n){}var l,h;window.mixpanel=a;a._i=[];a.init=function(b,d,g){function c(b,i){var a=i.split(".");2==a.length&&(b=b[a[0]],i=a[1]);b[i]=function(){b.push([i].concat(Array.prototype.slice.call(arguments,
            0)))}}var e=a;"undefined"!==typeof g?e=a[g]=[]:g="mixpanel";e.people=e.people||[];e.toString=function(b){var a="mixpanel";"mixpanel"!==g&&(a+="."+g);b||(a+=" (stub)");return a};e.people.toString=function(){return e.toString(1)+".people (stub)"};l="disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split(" ");
            for(h=0;h<l.length;h++)c(e,l[h]);var f="set set_once union unset remove delete".split(" ");e.get_group=function(){function a(c){b[c]=function(){call2_args=arguments;call2=[c].concat(Array.prototype.slice.call(call2_args,0));e.push([d,call2])}}for(var b={},d=["get_group"].concat(Array.prototype.slice.call(arguments,0)),c=0;c<f.length;c++)a(f[c]);return b};a._i.push([b,d,g])};a.__SV=1.2;b=c.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?
            MIXPANEL_CUSTOM_LIB_URL:"file:"===c.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";d=c.getElementsByTagName("script")[0];d.parentNode.insertBefore(b,d)}})(document,window.mixpanel||[]);
            mixpanel.init(token, {batch_requests: true});
            self.analytics = mixpanel;
            /* eslint-enable */
            dfd.resolve(tracker);
            return dfd;
        };

        tracker.loadTracker = function(trackerInfo) {
            tracker.trackerInfo = trackerInfo;
            return self.addAnalyticsJS();
        };

        tracker.identify = function() {
            self.analytics.identify(tracker.trackerInfo.staticShares.userHash);
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
                    data[type] = value;
                }
            );
            self.analytics.track('Page view', data);
        };

        tracker.processEvent = function(dfd, metricName, data) {
            self.analytics.track(metricName, {
                eventData: data,
            }, {}, function() {
                dfd.resolve(true);
                Log.debug('MixPanel resolved custom event.');
            });
        };

        return tracker;
    });
