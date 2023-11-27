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
use local_liquidus\api\analytics;

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

    if ($oldversion < 2023032300) {
        // Tracking depending on user role can be configured
        $pluginname = 'local_liquidus';

        // Add trackroles config
        $allrolesshortname = analytics::get_allrolesshortname();
        $configsetting = implode(',', $allrolesshortname);
        set_config("trackroles", $configsetting, $pluginname);

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2023032300, 'local', 'liquidus');
    }

    if ($oldversion < 2024012901) {
        // Global settings now live under each provider.
        // We need to move global settings to each individual provider.
        $providers = injector::get_instance()->get_analytics_types();
        $pluginname = 'local_liquidus';

        // Global setting list to move.
        $list = ['masquerade_handling', 'trackadmin', 'tracknonadmin', 'cleanurl', 'share_identifiable', 'trackroles'];
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
        upgrade_plugin_savepoint(true, 2024012901, 'local', 'liquidus');
    }

    if ($oldversion < 2024012902) {

        // Define table local_liquidus_consent_log to be created.
        $table = new xmldb_table('local_liquidus_consent_log');

        // Adding fields to table local_liquidus_consent_log.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('useremail', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('previousstatus', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('currentstatus', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_liquidus_consent_log.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_liquidus_consent_log.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Liquidus savepoint reached.
        upgrade_plugin_savepoint(true, 2024012902, 'local', 'liquidus');
    }

    return true;
}
