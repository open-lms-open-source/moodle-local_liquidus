<?php
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
 * Liquidus
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $ADMIN;

if (is_siteadmin()) {
    $pluginname = 'local_liquidus';

    $settings = new admin_settingpage($pluginname, get_string('pluginname', $pluginname));
    $ADMIN->add('localplugins', $settings);

    $name = "{$pluginname}/enabled";
    $title = get_string('enabled', $pluginname);
    $description = get_string('enabled_desc', $pluginname);
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = "{$pluginname}/masquerade_handling";
    $title = get_string('masquerade_handling', $pluginname);
    $description = get_string('masquerade_handling_desc', $pluginname);
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $settings->add($setting);

    $name = "{$pluginname}/trackadmin";
    $title = get_string('trackadmin', $pluginname);
    $description = get_string('trackadmin_desc', $pluginname);
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = "{$pluginname}/cleanurl";
    $title = get_string('cleanurl', $pluginname);
    $description = get_string('cleanurl_desc', $pluginname);
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = new lang_string('eventhandling', $pluginname);
    $description = new lang_string('eventhandling_help', $pluginname);
    $settings->add(new admin_setting_heading('eventhandling', $name, $description));

    $name = "{$pluginname}/eventdef";
    $title = get_string('eventdef', $pluginname);
    $description = get_string('eventdef_desc', $pluginname);
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $settings->add($setting);

    $name = "{$pluginname}/staticshares";
    $title = get_string('staticshares', $pluginname);
    $description = get_string('staticshares_desc', $pluginname);
    $staticshares = [];
    $default = \local_liquidus\api\analytics::STATIC_SHARES;
    foreach ($default as $share) {
        $staticshares[$share] = get_string('staticshares_' . $share, 'local_liquidus');
        $default[] = $share;
    }
    $setting = new admin_setting_configmultiselect($name, $title, $description, $default, $staticshares);
    $settings->add($setting);

    $name = new lang_string('liquidusaws', $pluginname);
    $description = new lang_string('liquidusaws_help', $pluginname);
    $settings->add(new admin_setting_heading('liquidusaws', $name, $description));

    $name = "{$pluginname}/beacon";
    $title = get_string('beacon', $pluginname);
    $description = get_string('beacon_desc', $pluginname);
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = "{$pluginname}/beaconurl";
    $title = get_string('beaconurl', $pluginname);
    $description = get_string('beaconurl_desc', $pluginname);
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = new lang_string('extanalytics', $pluginname);
    $description = new lang_string('extanalyticss_help', $pluginname);
    $settings->add(new admin_setting_heading('extanalytics', $name, $description));

    $name = "{$pluginname}/segment";
    $title = get_string('segment', $pluginname);
    $description = get_string('segment_desc', $pluginname);
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = "{$pluginname}/segmentwritekey";
    $title = get_string('segmentwritekey', $pluginname);
    $description = get_string('segmentwritekey_desc', $pluginname);
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = "{$pluginname}/keenio";
    $title = get_string('keenio', $pluginname);
    $description = get_string('keenio_desc', $pluginname);
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = "{$pluginname}/keeniowritekey";
    $title = get_string('keeniowritekey', $pluginname);
    $description = get_string('keeniowritekey_desc', $pluginname);
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = "{$pluginname}/keenioprojectid";
    $title = get_string('keenioprojectid', $pluginname);
    $description = get_string('keenioprojectid_desc', $pluginname);
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

}
