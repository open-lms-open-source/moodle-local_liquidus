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
 * Liquidus Analytics test.
 *
 * @package    local_liquidus
 * @copyright  Copyright (c) 2020 Open LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_liquidus\api\analytics;

defined('MOODLE_INTERNAL') || die();

/**
 * @group local_liquidus
 */
class local_liquidus_analytics_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
    }

    public function test_get_static_shares_default() {
        // Login as someone.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $shares = analytics::get_static_shares(get_config('local_liquidus'));

        // All shares are enabled as default.
        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS, analytics::STATIC_SHARES);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        foreach ($sharekeys as $sharekey) {
            $this->assertArrayHasKey($sharekey, $shares);
        }
    }
}
