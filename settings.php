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
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_liquidus\api\analytics;
use local_liquidus\injector;

defined('MOODLE_INTERNAL') || die;

global $ADMIN, $CFG, $PAGE;
/** @var $hassiteconfig */
if ($hassiteconfig) {
    $pluginname = 'local_liquidus';

    // Var to hold the 'notchecked' hide-if condition for ease of use.
    $notcheckedcondition = 'notchecked';

    $settings = new admin_settingpage($pluginname, get_string('pluginname', $pluginname));
    $ADMIN->add('localplugins', $settings);

    $name = new lang_string('general', $pluginname);
    $description = new lang_string('general_help', $pluginname);
    $settings->add(new admin_setting_heading('general', $name, $description));

    $name = "{$pluginname}/enabled";
    $title = new lang_string('enabled', $pluginname);
    $description = empty($CFG->local_liquidus_disable_tracker_config) ? new lang_string('enabled_desc', $pluginname) :
        new lang_string('enabled_olms_desc', $pluginname);
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    if (empty($CFG->local_liquidus_disable_tracker_config)) { // Flag to disable plugin config (for internal Open LMS use.)

        $name = "{$pluginname}/masquerade_handling";
        $title = new lang_string('masquerade_handling', $pluginname);
        $description = new lang_string('masquerade_handling_desc', $pluginname);
        $default = false;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
        $settings->add($setting);

        $name = "{$pluginname}/trackadmin";
        $title = new lang_string('trackadmin', $pluginname);
        $description = new lang_string('trackadmin_desc', $pluginname);
        $default = false;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
        $settings->add($setting);

        $name = "{$pluginname}/tracknonadmin";
        $title = new lang_string('tracknonadmin', $pluginname);
        $description = new lang_string('tracknonadmin_desc', $pluginname);
        $default = true;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
        $settings->add($setting);

        $name = "{$pluginname}/cleanurl";
        $title = new lang_string('cleanurl', $pluginname);
        $description = new lang_string('cleanurl_desc', $pluginname);
        $default = true;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
        $settings->add($setting);

        // TODO: Enable and show this setting only if user has accepted the privacy agreement.
        $name = "{$pluginname}/share_identifiable";
        $title = new lang_string('shareidentifiable', $pluginname);
        $description = new lang_string('shareidentifiable_desc', $pluginname);
        $default = false;
        $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
        $settings->add($setting);

        $types = injector::get_instance()->get_analytics_types();
        foreach ($types as $type) {
            $name = new lang_string($type, $pluginname);
            $description = new lang_string("{$type}_desc", $pluginname);
            $settings->add(new admin_setting_heading($type, $name, $description));

            $prefix = "{$pluginname}/{$type}";

            $name = $prefix;
            $title = new lang_string($type, $pluginname);
            $description = new lang_string("{$type}_desc", $pluginname);
            $default = false;
            $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
            $settings->add($setting);

            if (!empty($CFG->local_liquidus_enable_eventdef)) {
                $name = "{$prefix}_eventdef";
                $title = new lang_string('eventdef', $pluginname);
                $description = new lang_string('eventdef_desc', $pluginname);
                $default = '';
                $setting = new admin_setting_configtextarea($name, $title, $description, $default);
                $settings->add($setting);
            }

            $name = "{$prefix}_unidentifiable_staticshares";
            $title = new lang_string('unidentifiable_staticshares', $pluginname);
            $description = new lang_string('unidentifiable_staticshares_desc', $pluginname);
            $staticshares = $default = [];
            foreach (\local_liquidus\api\analytics::UNIDENTIFIABLE_STATIC_SHARES as $share) {
                $staticshares[$share] = get_string('staticshares_' . $share, 'local_liquidus');
                $default[] = $share;
            }
            $setting = new admin_setting_configmultiselect($name, $title, $description, $default, $staticshares);
            $settings->add($setting);

            if (!empty($CFG->local_liquidus_identifiable_share_providers) && in_array($type, $CFG->local_liquidus_identifiable_share_providers)) {
                $name = "{$prefix}_identifiable_staticshares";
                $title = new lang_string('identifiable_staticshares', $pluginname);
                $description = new lang_string('identifiable_staticshares_desc', $pluginname);
                $staticshares = $default = [];
                foreach (\local_liquidus\api\analytics::IDENTIFIABLE_STATIC_SHARES as $share) {
                    $staticshares[$share] = get_string('staticshares_' . $share, 'local_liquidus');
                    $default[] = $share;
                }
                $setting = new admin_setting_configmultiselect($name, $title, $description, $default, $staticshares);
                $settings->add($setting);
            }

            // Additional settings specific to providers.
            foreach (injector::SETTING_PROVIDER_MAPPING as $setting => $providers) {
                if (isset($providers[$type])) {
                    $name = "{$prefix}_{$setting}";
                    $title = new lang_string($setting, $pluginname);
                    $description = new lang_string("{$setting}_desc", $pluginname);
                    $default = false;
                    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
                    $settings->add($setting);
                }
            }

            $classname = "\\local_liquidus\\api\\{$type}";
            if (!class_exists($classname, true)) {
                debugging("Local Liquidus Module: Analytics setting '{$type}' doesn't map to a class name.");
            }

            /** @var analytics $engine */
            $engine = new $classname;
            $configsettings = $engine::get_config_settings();
            foreach ($configsettings as $configsetting) {
                $name = "{$pluginname}/{$configsetting}";
                $title = new lang_string($configsetting, $pluginname);
                $description = new lang_string("{$configsetting}_desc", $pluginname);
                $default = '';
                $setting = new admin_setting_configtext($name, $title, $description, $default);
                $settings->add($setting);
            }
        }

        // AMD that moves settings into tabs.
        $PAGE->requires->js_call_amd('local_liquidus/settings-handler-lazy', 'init', ['types' => $types]);
    }
}