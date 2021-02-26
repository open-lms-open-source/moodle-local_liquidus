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
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_liquidus\webservice;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../../lib/externallib.php');

/**
 * Event definition web service.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_definition extends \external_api {
    /**
     * @return \external_function_parameters
     */
    public static function service_parameters() {
        return new \external_function_parameters([]);
    }

    public static function service_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                [
                    'name' => new \external_value(PARAM_TEXT, 'definition name'),
                    'testselector' => new \external_value(PARAM_TEXT, 'definition test selector'),
                    'selector' => new \external_value(PARAM_TEXT, 'definition selector'),
                    'event' => new \external_value(PARAM_TEXT, 'definition event type'),
                    'data' => new \external_multiple_structure(
                        new \external_single_structure(
                            [
                                'name' => new \external_value(PARAM_TEXT, 'data metric name'),
                                'selector' => new \external_value(PARAM_TEXT, 'data value selector'),
                                'type' => new \external_value(PARAM_TEXT, 'data html element selector'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    public static function service() {
        $eventdef = get_config('local_liquidus', 'eventdef');
        if (empty($eventdef)) {
            return [];
        }
        return json_decode($eventdef, true);
    }
}
