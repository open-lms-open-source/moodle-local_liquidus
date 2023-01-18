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
        $unidentifiable_staticshares = join(",", analytics::UNIDENTIFIABLE_STATIC_SHARES);
        set_config("{$analyticstype}_unidentifiable_staticshares", $unidentifiable_staticshares, 'local_liquidus');

        /** @var analytics $classname */
        $classname = "\\local_liquidus\\api\\{$analyticstype}";
        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

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
        $unidentifiable_staticshares = join(",", analytics::UNIDENTIFIABLE_STATIC_SHARES);
        set_config("{$analyticstype}_unidentifiable_staticshares", $unidentifiable_staticshares, 'local_liquidus');

        /** @var analytics $classname */
        $classname = "\\local_liquidus\\api\\{$analyticstype}";
        $CFG->local_liquidus_identifiable_share_providers = ['appcues', 'google', 'keenio', 'kinesis', 'mixpanel', 'segment'];
        set_config('share_identifiable', '1', 'local_liquidus');
        set_config("{$analyticstype}_identifiable_staticshares", 'userid,useremail', 'local_liquidus');
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

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
        $unidentifiable_staticshares = join(",", analytics::UNIDENTIFIABLE_STATIC_SHARES);
        set_config("{$analyticstype}_unidentifiable_staticshares", $unidentifiable_staticshares, 'local_liquidus');

        /** @var analytics $classname */
        $classname = "\\local_liquidus\\api\\{$analyticstype}";
        $CFG->local_liquidus_identifiable_share_providers = ['appcues', 'google', 'keenio', 'kinesis', 'mixpanel', 'segment'];
        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();


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
     * Test admins and non-admins are tracked as expected
     * @dataProvider get_analytics_types
     *
     * @throws coding_exception
     */
    public function test_tracking_admin_non_admin() {
        global $PAGE;

        // Navigate to a course so we can get the page path static share.
        $course = $this->getDataGenerator()->create_course();

        // Set the page as a course.
        $urlparams = ['id' => $course->id];
        $PAGE->set_url('/course/view.php', $urlparams);
        $PAGE->set_title(get_string('coursetitle', 'moodle', ['course' => $course->fullname]));
        $PAGE->set_pagetype('course-view-' . $course->format);
        $PAGE->set_context(\context_course::instance($course->id));
        $PAGE->set_course($course);

        //Enrol student within the course
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Non-admin AND admins should not be tracked
        set_config('trackadmin', '0', 'local_liquidus');
        set_config('tracknonadmin', '0', 'local_liquidus');
        $config = get_config('local_liquidus');

        $this->setAdminUser();
        $this->assertEquals(false, analytics::should_track($config));

        $this->setUser($student);
        $this->assertEquals(false, analytics::should_track($config));

        // Only admins should be tracked
        set_config('trackadmin', '1', 'local_liquidus');
        set_config('tracknonadmin', '0', 'local_liquidus');
        $config = get_config('local_liquidus');

        $this->setAdminUser();
        $this->assertEquals(true, analytics::should_track($config));

        $this->setUser($student);
        $this->assertEquals(false, analytics::should_track($config));

        // Only non-admins should be tracked
        set_config('trackadmin', '0', 'local_liquidus');
        set_config('tracknonadmin', '1', 'local_liquidus');
        $config = get_config('local_liquidus');

        $this->setAdminUser();
        $this->assertEquals(false, analytics::should_track($config));

        $this->setUser($student);
        $this->assertEquals(true, analytics::should_track($config));

        // Non-admin AND admins should be tracked
        set_config('trackadmin', '1', 'local_liquidus');
        set_config('tracknonadmin', '1', 'local_liquidus');
        $config = get_config('local_liquidus');

        $this->setAdminUser();
        $this->assertEquals(true, analytics::should_track($config));

        $this->setUser($student);
        $this->assertEquals(true, analytics::should_track($config));

    }

    /**
     * Test that shares aren't tracked if course context doesn't exist or was deleted
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_no_tracking_if_no_context($analyticstype) {
        global $PAGE, $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

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

        //Delete the category and context of the course
        $category = core_course_category::get($course->category);
        $category->delete_full(false);

        $unidentifiable_staticshares = join(",", analytics::UNIDENTIFIABLE_STATIC_SHARES);
        set_config("{$analyticstype}_unidentifiable_staticshares", $unidentifiable_staticshares, 'local_liquidus');

        /** @var analytics $classname */
        $classname = "\\local_liquidus\\api\\{$analyticstype}";
        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS, analytics::UNIDENTIFIABLE_STATIC_SHARES);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        foreach ($sharekeys as $sharekey) {
            $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
            $this->assertStringNotContainsString($jsvarname, $injectedstaticshares);
        }

    }

    /**
     * Test that the theme used to render the content is tracked.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_current_theme($analyticstype) {
        global $PAGE, $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        $unidentifiable_staticshares = join(",", analytics::UNIDENTIFIABLE_STATIC_SHARES);
        set_config("{$analyticstype}_unidentifiable_staticshares", $unidentifiable_staticshares, 'local_liquidus');

        $themes = glob($CFG->dirroot."/theme/*", GLOB_ONLYDIR); // Get array of paths to available themes.

        array_walk($themes, function (&$theme) { // Get array of theme names only
           $theme = explode("/", $theme);
           $theme = end($theme);
        });

        foreach ($themes as $theme) {

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
            $PAGE->force_theme($theme); // Set the current theme


            /** @var analytics $classname */
            $classname = "\\local_liquidus\\api\\{$analyticstype}";
            $classname::clear_rendered_static_shares();
            $classname::build_static_shares(get_config('local_liquidus'));
            $injectedstaticshares = $classname::get_rendered_static_shares();

            $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS, analytics::UNIDENTIFIABLE_STATIC_SHARES);

            // Keys are converted to camel case.
            array_walk($sharekeys, function(&$sharekey) {
                $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
            });

            // Check that all static shares are tracked.
            foreach ($sharekeys as $sharekey) {
                $jsvarname = "localLiquidusShares.{$analyticstype}.{$sharekey}";
                $this->assertStringContainsString($jsvarname, $injectedstaticshares);
            }

            // Check that the current theme matches the static share that was injected.
            $themevarname = analytics::STATIC_THEME;
            $jsthemevarname = 'localLiquidusShares.'.$analyticstype.'.'.$themevarname.' = "'.$theme.'"';
            $this->assertStringContainsString($jsthemevarname, $injectedstaticshares);

            $PAGE->reset_theme_and_output(); //Reset theme and output so we're able to set the new theme to test.

        }

    }


    /**
     * Test that isSupportUser share is populated when there are emails or custom domains to be identified.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_is_support_user($analyticstype) {
        global $PAGE, $CFG;

        $CFG->local_liquidus_olms_cfg = new \stdClass();

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

        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        $issupportusershare = analytics::STATIC_SHARES_CAMEL_CASE[analytics::STATIC_IS_SUPPORT_USER];
        $jsvarname = "localLiquidusShares.{$analyticstype}.{$issupportusershare}";
        $this->assertStringContainsString($jsvarname, $injectedstaticshares);
        $this->assertEquals("no", analytics::identify_support_users($user->email));

        $CFG->local_liquidus_olms_cfg->support_user_domains = [$user->email]; //Add test user email to support user domain array

        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        $jsvarname = "localLiquidusShares.{$analyticstype}.{$issupportusershare}";
        $this->assertStringContainsString($jsvarname, $injectedstaticshares);
        $this->assertEquals("yes", analytics::identify_support_users($user->email));

        $emaildomainarray = explode("@", $user->email);
        $CFG->local_liquidus_olms_cfg->support_user_domains = [end($emaildomainarray)]; //Add test user email domain to support user domain array

        $classname::clear_rendered_static_shares();
        $classname::build_static_shares(get_config('local_liquidus'));
        $injectedstaticshares = $classname::get_rendered_static_shares();

        $sharekeys = array_merge(analytics::STATIC_SHARES_ALWAYS);

        // Keys are converted to camel case.
        array_walk($sharekeys, function(&$sharekey) {
            $sharekey = analytics::STATIC_SHARES_CAMEL_CASE[$sharekey];
        });

        $jsvarname = "localLiquidusShares.{$analyticstype}.{$issupportusershare}";
        $this->assertStringContainsString($jsvarname, $injectedstaticshares);
        $this->assertEquals("yes", analytics::identify_support_users($user->email));

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