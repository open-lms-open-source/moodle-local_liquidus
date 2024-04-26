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
 * Event definition.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2024 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_liquidus\webservice;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

/**
 * Add update appcues user properties web service.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2024 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_update_appcues_user_properties extends external_api {
    /**
     * @return external_function_parameters
     */
    public static function service_parameters() {
        return new external_function_parameters([
            'appcues_property_name'        => new external_value(PARAM_TEXT, 'Appcues property name'),
            'appcues_property_value'        => new external_value(PARAM_BOOL, 'Appcues property value'),
        ]);
    }

    public static function service_returns() {
        return new external_single_structure([
            'success'    => new external_value(PARAM_BOOL, 'Property updated successfully!')
        ]);
    }

    /**
     * @return bool
     */
    public static function service($propertyname, $propertyvalue) {

        $params = self::validate_parameters(self::service_parameters(), [
            'appcues_property_name' => $propertyname,
            'appcues_property_value' => $propertyvalue,
        ]);

        // Check permissions.
        if (!has_capability('moodle/site:config', \context_system::instance())) {
            return ['success' => false];
        }

        $propertyname = $params['appcues_property_name'];
        $propertyvalue = $params['appcues_property_value'];

        $configname = 'deck36websvc'.str_replace(' ', '', $propertyname);
        set_config($configname, $propertyvalue, 'local_liquidus');

        return ['success' => true];
    }
}
