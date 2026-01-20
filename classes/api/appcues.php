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
 * Liquidus Appcues tracker.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_liquidus\api;

defined('MOODLE_INTERNAL') || die();

/**
 * @inheritdoc
 */
class appcues extends analytics {
    /**
     * @inheritdoc
     */
    public static function get_tracker_info($config) {
        $res = [];
        if (empty($config->appcuesaccountid)) {
            debugging(get_string('trackernotconfigured', 'local_liquidus', 'appcues'));
            debugging(get_string('trackermissingfield', 'local_liquidus', 'appcuesaccountid'));
            return $res;
        }

        $accountid = $config->appcuesaccountid;

        if (!empty($accountid) && self::should_track($config, 'appcues')) {
            $res['trackerId'] = 'appcues';
            $res['accountid'] = $accountid;
        }

        return $res;
    }

    /**
     * @return string
     */
    public static function get_script_url($config) {
        if (empty($config->appcuesaccountid)) {
            debugging(get_string('trackernotconfigured', 'local_liquidus', 'appcues'));
            debugging(get_string('trackermissingfield', 'local_liquidus', 'appcuesaccountid'));
            return '';
        }

        return "fast.appcues.com/{$config->appcuesaccountid}.js";
    }

    /**
     * {@inheritDoc}
     */
    public static function get_config_settings() {
        return ['appcuesaccountid'];
    }

    /**
     * {@inheritDoc}
     */
    public static function get_extra_configs(\stdClass $config): array {
        global $CFG;
        $extra = [];

        if (!empty($CFG->local_liquidus_appcues_user_properties_to_send)) {
            $user_properties = explode(',', $CFG->local_liquidus_appcues_user_properties_to_send);
            $user_properties = array_map('trim', $user_properties);

            $extra['userProperties'] = array_reduce($user_properties, function ($result, $property) {
                if (isset(self::STATIC_SHARES_CAMEL_CASE[$property])) {
                    $result[] = self::STATIC_SHARES_CAMEL_CASE[$property];
                }

                return $result;
            }, []);
        }

        if (has_capability('moodle/site:config', \context_system::instance())) {
            $deck36properties = [];
            foreach ($config as $key => $value) {
                if (preg_match('/^deck36websvc/', $key)) {
                    $deck36properties[str_replace('deck36websvc', '', $key)] = $value;
                }
            }
            if (!empty($deck36properties)) {
                $extra['deck36properties'] = $deck36properties;
            }
        }

        return $extra;
    }
}
