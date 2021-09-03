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
 * Upgrade script.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2021 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_liquidus\injector;

/**
 * Upgrade function.
 *
 * @param int $oldversion
 */
function xmldb_local_liquidus_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021060101) {
        // Events definitions and static shares now live under each provider.
        // We need to move global settings to each individual provider.
        $providers = injector::get_instance()->get_analytics_types();
        $pluginname = 'local_liquidus';

        // Global setting list to move.
        $list = ['eventdef', 'staticshares'];
        $configsettings = [];
        // Gather existing setting values.
        foreach ($list as $key) {
            $configsettings[$key] = get_config($pluginname, $key);
        }

        // Iterate over provider settings to set new values.
        foreach ($providers as $provider) {
            foreach ($configsettings as $key => $configsetting) {
                if (empty($configsetting)) {
                    continue;
                }
                set_config("{$provider}_{$key}", $configsetting, $pluginname);
            }
        }

        // Clear old config values.
        foreach ($configsettings as $key => $configsetting) {
            unset_config($key, $pluginname);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2021060101, 'local', 'liquidus');
    }

    if ($oldversion < 2021060109) {
        // Static shares have moved to unidentifiable static shares.
        $providers = injector::get_instance()->get_analytics_types();
        $pluginname = 'local_liquidus';

        // Global setting list to move.
        $list = ['staticshares' => 'unidentifiable_staticshares'];
        // Gather existing setting values.
        foreach ($list as $oldsetting => $newsetting) {
            // Iterate over provider settings to set new values.
            foreach ($providers as $provider) {
                $configsetting = get_config($pluginname, "{$provider}_{$oldsetting}");
                set_config("{$provider}_{$newsetting}", $configsetting, $pluginname);
                unset_config("{$provider}_{$oldsetting}", $pluginname);
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2021060109, 'local', 'liquidus');
    }

    return true;
}
