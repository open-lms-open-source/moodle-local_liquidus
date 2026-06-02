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
 * @package   local_liquidus
 * @author    Jonathan Garcia Gomez <jonathan.garcia@openlms.net>
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace local_liquidus;

class hook_callbacks {

    /**
     * @param \core\hook\output\before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(\core\hook\output\before_footer_html_generation $hook): void {
        global $CFG, $SESSION;

        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        // Skip injection while MFA is pending for the session: the analytics JS
        // fires an AJAX request to local_liquidus_event_definition, which tool_mfa
        // rejects with 'redirecterrordetected' until the user has passed MFA.
        if (isloggedin() && !isguestuser()
                && empty($SESSION->tool_mfa_authenticated)
                && class_exists(\tool_mfa\manager::class)
                && \tool_mfa\manager::is_ready()) {
            return;
        }
        if (get_config('local_liquidus', 'enabled') || !empty($CFG->local_liquidus_disable_tracker_config)) {
            injector::get_instance()->inject();
        }
    }

    /**
     * Used to inject dependencies.
     */
    public static function before_standard_head_html_generation (\core\hook\output\before_standard_head_html_generation $hook): void {
        global $CFG, $PAGE, $SESSION;

        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        // Stay aligned with the footer hook: skip while MFA is pending.
        if (isloggedin() && !isguestuser()
                && empty($SESSION->tool_mfa_authenticated)
                && class_exists(\tool_mfa\manager::class)
                && \tool_mfa\manager::is_ready()) {
            return;
        }
        if (get_config('local_liquidus', 'enabled') || !empty($CFG->local_liquidus_disable_tracker_config)) {
            $PAGE->requires->jquery();
        }
    }
}
