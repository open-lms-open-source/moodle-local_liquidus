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
 * @copyright Copyright (c) 2021 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_liquidus\api;

defined('MOODLE_INTERNAL') || die();

/**
 * @inheritdoc
 */
class mixpanel extends analytics {
    /**
     * @inheritdoc
     */
    public static function get_tracker_info($config) {
        $res = [];
        if (empty($config->mixpaneltoken)) {
            debugging(get_string('trackernotconfigured', 'local_liquidus', 'mixpanel'));
            debugging(get_string('trackermissingfield', 'local_liquidus', 'mixpaneltoken'));
            return $res;
        }

        $token = $config->mixpaneltoken;

        if (!empty($token) && self::should_track($config)) {
            $res['trackerId'] = 'mixpanel';
            $res['token'] = $token;
            $res['staticShares'] = self::get_static_shares($config);
        }

        return $res;
    }
}
