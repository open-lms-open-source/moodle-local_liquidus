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
use local_liquidus\injector;

defined('MOODLE_INTERNAL') || die();

/**
 * @group local_liquidus
 */
class local_liquidus_analytics_test extends advanced_testcase {

    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test that static shares are what's expected.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_get_static_shares_default($analyticstype) {
        global $PAGE;
        // Login as someone.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Navigate to a course so we can get the page path static share.
        $course = $this->getDataGenerator()->create_course();

        // Set the page as a course.
        $urlparams = ['id' => $course->id];
        $PAGE->set_url('/course/view.php', $urlparams);
        $PAGE->set_title(get_string('coursetitle', 'moodle', ['course' => $course->fullname]));
        $PAGE->set_pagetype('course-view-' . $course->format);
        $PAGE->set_context(\context_course::instance($course->id));
        $PAGE->set_course($course);

        /** @var analytics $classname */
        $classname = "\\local_liquidus\\api\\{$analyticstype}";
        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        // All shares are enabled as default.
        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS, analytics::UNIDENTIFIABLE_STATIC_SHARES);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        foreach ($sharekeys as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            $this->assertStringContainsString($jsvarname, $injectedstaticshares);
        }
    }

    /**
     * Test that static shares are what's expected including identifiable static shares.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_get_identifiable_static_shares($analyticstype) {
        global $CFG, $PAGE;
        // Login as someone.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Navigate to a course so we can get the page path static share.
        $course = $this->getDataGenerator()->create_course();

        // Set the page as a course.
        $urlparams = ['id' => $course->id];
        $PAGE->set_url('/course/view.php', $urlparams);
        $PAGE->set_title(get_string('coursetitle', 'moodle', ['course' => $course->fullname]));
        $PAGE->set_pagetype('course-view-' . $course->format);
        $PAGE->set_context(\context_course::instance($course->id));
        $PAGE->set_course($course);

        /** @var analytics $classname */
        $classname = "\\local_liquidus\\api\\{$analyticstype}";
        $CFG->local_liquidus_identifiable_share_providers = ['appcues', 'google', 'keenio', 'kinesis', 'mixpanel', 'segment'];
        set_config('share_identifiable', '1', 'local_liquidus');
        set_config("{$analyticstype}_identifiable_staticshares", 'userid,useremail', 'local_liquidus');
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        // All shares are enabled as default.
        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS, analytics::UNIDENTIFIABLE_STATIC_SHARES, analytics::IDENTIFIABLE_STATIC_SHARES);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        foreach ($sharekeys as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            $this->assertStringContainsString($jsvarname, $injectedstaticshares);
        }

        $CFG->local_liquidus_identifiable_share_providers = [];
        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        // Only unidentifiable shares are enabled.
        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS, analytics::UNIDENTIFIABLE_STATIC_SHARES);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        foreach ($sharekeys as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            $this->assertStringContainsString($jsvarname, $injectedstaticshares);
        }

        foreach (analytics::IDENTIFIABLE_STATIC_SHARES as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            $this->assertStringNotContainsString($jsvarname, $injectedstaticshares);
        }
    }


    /**
     * Test that static shares are what's expected taking into account $CFG->local_liquidus_enabled_providers.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_get_static_shares_with_specified_providers($analyticstype) {
        global $CFG, $PAGE;
        // Login as someone.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Navigate to a course so we can get the page path static share.
        $course = $this->getDataGenerator()->create_course();

        // Set the page as a course.
        $urlparams = ['id' => $course->id];
        $PAGE->set_url('/course/view.php', $urlparams);
        $PAGE->set_title(get_string('coursetitle', 'moodle', ['course' => $course->fullname]));
        $PAGE->set_pagetype('course-view-' . $course->format);
        $PAGE->set_context(\context_course::instance($course->id));
        $PAGE->set_course($course);

        /** @var analytics $classname */
        $classname = "\\local_liquidus\\api\\{$analyticstype}";
        $CFG->local_liquidus_identifiable_share_providers = ['appcues', 'google', 'keenio', 'kinesis', 'mixpanel', 'segment'];
        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        // All shares are enabled as default.
        $unidentifiablesharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS, analytics::UNIDENTIFIABLE_STATIC_SHARES);
        $identifiablesharekeys = analytics::IDENTIFIABLE_STATIC_SHARES;

        // Keys are converted to camel case.
        array_walk($unidentifiablesharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });
        array_walk($identifiablesharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        foreach ($unidentifiablesharekeys as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            $this->assertStringContainsString($jsvarname, $injectedstaticshares);
        }
        foreach ($identifiablesharekeys as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            $this->assertStringNotContainsString($jsvarname, $injectedstaticshares);
        }

        $CFG->local_liquidus_identifiable_share_providers = ['appcues', 'google'];
        $classname::clear_rendered_static_shares();
        set_config('share_identifiable', '1', 'local_liquidus');
        set_config("{$analyticstype}_identifiable_staticshares", 'userid,useremail', 'local_liquidus');
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        foreach (array_merge($unidentifiablesharekeys, $identifiablesharekeys) as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            if (!in_array($analyticstype, $CFG->local_liquidus_identifiable_share_providers) && in_array($sharekey, $identifiablesharekeys)) {
                $this->assertStringNotContainsString($jsvarname, $injectedstaticshares);
            } else {
                $this->assertStringContainsString($jsvarname, $injectedstaticshares);
            }
        }
    }

    /**
     * @return array|false|string[]
     */
    public function get_analytics_types() {
        $types = [];
        foreach (injector::get_instance()->get_analytics_types() as $type) {
            $types[$type] = [$type];
        }
        return $types;
    }
}
