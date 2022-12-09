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
 * Liquidus definitions.
 *
 * @package   local_liquidus
 * @category  test
 * @copyright Copyright (c) 2022 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__.'/../../../../lib/behat/behat_base.php');

/**
 * Liquidus definitions.
 *
 * @package   local_liquidus
 * @category  test
 * @copyright Copyright (c) 2022 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_liquidus extends behat_base {
    /**
     * @Given /^I go to Liquidus setting page$/
     */
    public function i_go_to_settings() {
        $this->visitPath('admin/settings.php?section=local_liquidus');
    }
}