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
defined('MOODLE_INTERNAL') || die();

use local_liquidus\injector;

require_once(__DIR__.'/../../config.php');

/**
 * Used since Moodle 29.
 */
function local_liquidus_extend_navigation() {
    injector::get_instance()->inject();
}

/**
 * Used since Moodle 29.
 */
function local_liquidus_extend_settings_navigation() {
    injector::get_instance()->inject();
}

/**
 * Used in Moodle 30+ when a user is logged on.
 */
function local_liquidus_extend_navigation_user_settings() {
    injector::get_instance()->inject();
}

/**
 * Used in Moodle 30+ on the frontpage.
 */
function local_liquidus_extend_navigation_frontpage() {
    injector::get_instance()->inject();
}

/**
 * Used in Moodle 31+ when a user is logged on.
 */
function local_liquidus_extend_navigation_user() {
    injector::get_instance()->inject();
}
