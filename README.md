# Liquidus Local plugin
By enabling and configuring this plugin with 
an Analytics account, such as Appcues, Mixpanel, Keen, Segment, Google or AWS Kinesis, admin users will
be able to track different usage information from their sites.

Some of the usage information you can track is:

* User information – Encrypted, can track where the user is connecting from, with which type of device, roles, etc.
* Specific events – Such as login, course entered, activity entered, etc.
* Plugin usage – Which active plugins on your platform are being used.

Currently, the Liquidus plugin allows any admin user to configure it for getting usage info.

This plugin was contributed by the Open LMS Product Development team. Open LMS is an education technology company
dedicated to bringing excellent online teaching to institutions across the globe.  We serve colleges and universities,
schools and organizations by supporting the software that educators use to manage and deliver instructional content to
learners in virtual classrooms.

## Installation
Extract the contents of the plugin into _/wwwroot/local_ then visit `admin/upgrade.php` or use the CLI script to upgrade your site.

1. Install this as you would any Moodle plugin and configure the analytics provider data.

## Configuration - Example for Google Analytics:
1. Setup a Google analytics account and property.
2. Enable Liquidus plugin in Site *administration › Plugins › Local plugins › Liquidus.*
3. Enable the Google tracking on the same setting page
4. Copy the property ID from your Google Analytics account, so you can track the page, and Add the property ID to the local_liquidus settings form.
5. Save the form.

Check your Google Analytics dashboard to see the influx of events being sent by the plugin.

## Configuration - Example for Mixpanel:
1. Setup a Mixpanel account and create a Project.
2. Enable Liquidus plugin in *Site administration › Plugins › Local plugins › Liquidus.*
3. Enable Mixpanel tracking on the same setting page.
4. Copy the Mixpanel Project token into the `mixpaneltoken` setting in the settings page.
4. Customize the data that will be shared and the users that will be tracked in the Mixpanel settings.
5. Save the form.

Check your Mixpanel events dashboard to see the influx of events being sent by the plugin.

## Flags

### The  `local_liquidus_olms_cfg` flag.
This is a flag for internal use in Open LMS. It is used to set the plugin settings from the flags instead of the 
ones set in the database. To initialize it, we need to set the flag as the following: 
`$CFG->local_liquidus_olms_cfg = new \stdClass();`. Then we can set any of the settings for each provider in the 
following way: `$CFG->local_liquidus_olms_cfg->mixpanel_trackadmin = true`. In that example, we are enabling the 
"Tracking Admins" in Mixpanel using the flag. This setting will not be visible in the plugin settings page and will 
be used instead of the values saved in the database.

This flag is set as an object:

- `$CFG->block_conduit_adhoc_consume_capacity = new \stdClass();` to initialize the object.
- `$CFG->local_liquidus_olms_cfg-><provider_setting_name> = true` to overwrite a provider config using the flag.
- `$CFG->local_liquidus_olms_cfg: unset` the settings will be taken from the plugin settings page.


### The  `local_liquidus_disable_tracker_config` flag.
Use this flag to hide the providers settings in the plugin settings page. With this flag enabled, only the consent
checkbox will be visible. That consent checkbox will allow the providers that are defined in the flag 
`local_liquidus_trackers_require_consent` to track the events in the site. If that checkbox is not enabled, then the 
providers defined in `local_liquidus_trackers_require_consent` will not work because those require user consent to track
the events.

This flag is set as a boolean:

- `$CFG->local_liquidus_disable_tracker_config = true;` to hide the provider settings from the settings page.
- `$CFG->local_liquidus_disable_tracker_config = false;` to show the provider settings from the settings page.
- `$CFG->local_liquidus_disable_tracker_config: unset` to show the provider settings from the settings page.

### The  `local_liquidus_identifiable_share_providers` flag.
Use this flag to define which providers can share identifiable data of user (e.g. user email).

This flag is set as an array of strings:

- `$CFG->local_liquidus_identifiable_share_providers = ['appcues'];` Appcues will receive identifiable data of user.
- `$CFG->local_liquidus_identifiable_share_providers: unset` no provider will receive identifiable data of user.

### The  `local_liquidus_enable_eventdef` flag.
Enable the Custom Event Definition setting in each provider.

This flag is set as a boolean:

- `$CFG->local_liquidus_enable_eventdef = true;` the Custom Event Definition setting will be displayed in each provider settings page.
- `$CFG->local_liquidus_enable_eventdef = false;` the Custom Event Definition setting will not be displayed in each provider settings page.
- `$CFG->local_liquidus_enable_eventdef: unset` the Custom Event Definition setting will not be displayed in each provider settings page.

### The  `local_liquidus_appcues_user_properties_to_send` flag.
Flag to set the user properties to send to Appcues, as a comma-separated 
string of user properties, possible values are listed [here](https://github.com/open-lms-open-source/moodle-local_liquidus/blob/master/classes/api/analytics.php#L38).

This flag is set as a string separated by commas:

- `$CFG->local_liquidus_appcues_user_properties_to_send = "issupportuser,olmsproduct,isimpersonated";` this will send the properties "issupportuser", "olmsproduct" and "isimpersonated" to Appcues.
  `$CFG->local_liquidus_appcues_user_properties_to_send: unset` The properties defined in the `appcues_unidentifiable_staticshares` will be sent to Appcues.

### The  `local_liquidus_disable_support_user_domain_tracking` flag.
This flag is used to disable the support users tracking.

This flag is set as a boolean:

- `$CFG->local_liquidus_disable_support_user_domain_tracking = true;` the support users will not be tracked.
- `$CFG->local_liquidus_disable_support_user_domain_tracking = false;` the support users will be tracked.
- `$CFG->local_liquidus_disable_support_user_domain_tracking: unset` the support users will be tracked.


### The  `local_liquidus_skip_forms_track` flag.
The different forms in the site are tracked when the providers are enabled to do so using the provider settings. 
With this flag we can skip specific forms to be tracked. The given value can be the id of the body page in which the
form is rendered or the URL of the page where the form is located.

This flag is set as an array of strings:

- `$CFG->local_liquidus_skip_forms_track = ['mod-assign-grader', '/local/joulegrader/view.php'];` this skips the forms 
that are located in the page with body id of 'mod-assign-grader' and the forms that are in the page with URL 
'/local/joulegrader/view.php'.
- `$CFG->local_liquidus_skip_forms_track: unset` No forms will be skipped from tracking.


### The  `local_liquidus_site_olms_platform` flag.
This flag will define the value that will be sent in the `olmsPlatform` static share to the providers.

This flag is set as a string:

- `$CFG->local_liquidus_site_olms_platform = “Platform name example”;` The value “Platform name example” will be sent in the `olmsPlatform` static share.
- `$CFG->local_liquidus_site_olms_platform: unset` The value “Not defined” will be sent in the `olmsPlatform` static share.

### The  `local_liquidus_olms_support_user_domains` flag.
This flag is used to set the support user domain. This can be used along with the `local_liquidus_disable_support_user_domain_tracking`
flag to disable the support user tracking.

This flag is set as an array of strings:

- `$CFG->local_liquidus_olms_support_user_domains = ["support@example.net"];` the user with the domain `support@example.net` will be set as the support user.
- `$CFG->local_liquidus_olms_support_user_domains: unset` no user will be set as the support user.


### The  `local_liquidus_trackers_require_consent` flag.
This defines which providers will require the consent checkbox to be used. If a provider is declared in this flag,
the user will require to manually give consent by enabling Liquidus from the plugin settings using the 
`local_liquidus | enabled` setting. Otherwise, that provider will not track any events, even if the provier settings are
enabled.

This flag is set as an array of strings:

- `$CFG->local_liquidus_trackers_require_consent = ['mixpanel'];` Mixpanel will only work of the `local_liquidus | enabled` setting
is enabled from the plugin settings page.
- `$CFG->local_liquidus_trackers_require_consent: unset` No provider will require the `local_liquidus | enabled` setting
    to be enabled from the plugin settings page to work.




## License
Copyright (c) 2021 Open LMS (https://www.openlms.net)

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
