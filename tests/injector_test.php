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
 * Liquidus Injector test.
 *
 * @package    local_liquidus
 * @copyright  Copyright (c) 2020 Open LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_liquidus\api\analytics;
use local_liquidus\injector;
use Prophecy\Argument;

defined('MOODLE_INTERNAL') || die();

/**
 * @group local_liquidus
 */
class local_liquidus_injector_testcase extends advanced_testcase {

    const CONFIG_TYPE_SETTING = 0;
    const CONFIG_TYPE_SHADOW = 1;

    public function setUp(): void {
        parent::setUp();
        injector::get_instance()->reset();
        $this->resetAfterTest();
    }

    /**
     * Runs an injection type on a config type.
     * @param string $type tracker type
     * @param int $configtype config type: self::CONFIG_TYPE_SETTING || self::CONFIG_TYPE_SHADOW
     * @param int $requirecallcount Amount of expected JS require calls
     * @throws coding_exception
     */
    private function run_injection_type($type, $configtype = self::CONFIG_TYPE_SETTING, $requirecallcount = 1) {
        global $PAGE, $CFG;

        // Login as someone.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $mockpage = new stdClass;

        $pagereqs = $this->prophesize(get_class($PAGE->requires));
        $pagereqs->js_call_amd(Argument::type('string'), Argument::type('string'), Argument::type('array'))
            ->shouldBeCalledTimes($requirecallcount);
        $mockpage->requires = $pagereqs->reveal();

        // Navigate to a course so we can get the page path static share.
        $course = $this->getDataGenerator()->create_course();

        // Set the page as a course.
        $urlparams = ['id' => $course->id];
        $PAGE->set_url('/course/view.php', $urlparams);
        $PAGE->set_title(get_string('coursetitle', 'moodle', ['course' => $course->fullname]));
        $PAGE->set_pagetype('course-view-' . $course->format);
        $PAGE->set_context(\context_course::instance($course->id));
        $PAGE->set_course($course);

        // Add properties to the page mock.
        foreach (get_object_vars($PAGE) as $key => $value) {
            $mockpage->{$key} = $value;
        }

        // Enable plugin and tracker type.
        $config = $this->enable_plugin_and_tracker($type, $configtype);


        $classname = "\\local_liquidus\\api\\{$type}";
        /** @var analytics $engine */
        $engine = new $classname;

        injector::clear_appcues_scripturl();

        // Let's tell te injector class to use our mock page so our prophecy becomes true.
        injector::get_instance()->set_test_page($mockpage);

        injector::get_instance()->inject();

        // Check for track url (appcues). If it is used by the provider, the url should be injected within the page.
        if (!empty($url = $engine::get_script_url($config)) && $type == "appcues" && $requirecallcount > 0) {
            $injectedscripturl = injector::get_appcues_scripturl();
            $this->assertStringContainsString($url, $injectedscripturl);
        }

        $pagereqs->checkProphecyMethodsPredictions();
    }

    /**
     * Enables the plugin and specified tracker configuring it with dummy data.
     * @param string $type tracker type
     * @param int $configtype config type: self::CONFIG_TYPE_SETTING || self::CONFIG_TYPE_SHADOW
     * @return stdClass Config object
     */
    private function enable_plugin_and_tracker($type, $configtype = self::CONFIG_TYPE_SETTING) {
        global $CFG;
        $returncfg = null;
        switch ($configtype) {
            case self::CONFIG_TYPE_SETTING:
                set_config('enabled', '1', 'local_liquidus');
                set_config($type, '1', 'local_liquidus');
                switch ($type) {
                    case 'google':
                        set_config('googlesiteid', 'SOMESITEID', 'local_liquidus');
                        break;
                    case 'kinesis':
                        set_config('kinesisurl', 'somekinesisurl', 'local_liquidus');
                        break;
                    case 'segment':
                        set_config('segmentwritekey', 'somesegmentwritekey', 'local_liquidus');
                        break;
                    case 'keenio':
                        set_config('keeniowritekey', 'somekeeniowritekey', 'local_liquidus');
                        set_config('keenioprojectid', 'somekeenioprojectid', 'local_liquidus');
                        break;
                    case 'mixpanel':
                        set_config('mixpaneltoken', 'somemixpaneltoken', 'local_liquidus');
                        break;
                    case 'appcues':
                        set_config('appcuesaccountid', 'SOMEACCOUNTID', 'local_liquidus');
                        break;
                }
                $returncfg = get_config('local_liquidus');
                break;
            case self::CONFIG_TYPE_SHADOW:
                if (!isset($CFG->local_liquidus_olms_cfg)) {
                    $CFG->local_liquidus_olms_cfg = new stdClass();
                }
                $CFG->local_liquidus_olms_cfg->enabled = true;
                $CFG->local_liquidus_olms_cfg->{$type} = true;
                $CFG->local_liquidus_olms_cfg->{"{$type}_staticshares"} = implode(',', [
                    'userrole',
                    'contextlevel',
                    'pagetype',
                    'plugins',
                ]);
                switch ($type) {
                    case 'google':
                        $CFG->local_liquidus_olms_cfg->googlesiteid = 'SOMESITEID';
                        break;
                    case 'kinesis':
                        $CFG->local_liquidus_olms_cfg->kinesisurl = 'somekinesisurl';
                        break;
                    case 'segment':
                        $CFG->local_liquidus_olms_cfg->segmentwritekey = 'somesegmentwritekey';
                        break;
                    case 'keenio':
                        $CFG->local_liquidus_olms_cfg->keeniowritekey = 'somekeeniowritekey';
                        $CFG->local_liquidus_olms_cfg->keenioprojectid = 'somekeenioprojectid';
                        break;
                    case 'mixpanel':
                        $CFG->local_liquidus_olms_cfg->mixpaneltoken = 'somemixpaneltoken';
                        break;
                    case 'appcues':
                        $CFG->local_liquidus_olms_cfg->appcuesaccountid = 'SOMEACCOUNTID';
                        break;
                }
                $returncfg = $CFG->local_liquidus_olms_cfg;
                break;
        }
        return $returncfg;
    }

    /**
     * Tests the injector using site settings.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_injector_with_settings($analyticstype) {
        set_config('tracknonadmin', '1', 'local_liquidus');
        $this->run_injection_type($analyticstype);
    }

    /**
     * Tests the injector using config flags, A.K.A. shadow config.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_injector_shadow($analyticstype) {
        global $CFG;
        $CFG->local_liquidus_olms_cfg = new stdClass();
        $CFG->local_liquidus_olms_cfg->tracknonadmin = 1;
        $this->run_injection_type($analyticstype, self::CONFIG_TYPE_SHADOW);
    }

    /**
     * Test that users are not tracked when setting is turned off.
     * @dataProvider get_analytics_types
     *
     * @param string $analyticstype
     * @throws coding_exception
     */
    public function test_injector_no_track($analyticstype) {
        global $CFG;

        set_config('tracknonadmin', '0', 'local_liquidus');
        $this->run_injection_type($analyticstype, self::CONFIG_TYPE_SETTING, 0);

        $CFG->local_liquidus_olms_cfg = new stdClass();
        $CFG->local_liquidus_olms_cfg->tracknonadmin = 0;
        $this->run_injection_type($analyticstype, self::CONFIG_TYPE_SHADOW, 0);
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

    /**
     * Test that the analytics types we support are actually being listed.
     */
    public function test_analytics_types() {
        $supported = [
            'segment',
            'keenio',
            'kinesis',
            'google',
            'mixpanel',
            'appcues',
        ];
        $this->assertEmpty(array_diff(injector::get_instance()->get_analytics_types(), $supported));
    }
}