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

    public function setUp() {
        parent::setUp();
        injector::get_instance()->reset();
        $this->resetAfterTest();
    }

    /**
     * Runs an injection type on a config type.
     * @param string $type tracker type
     * @param int $configtype config type: self::CONFIG_TYPE_SETTING || self::CONFIG_TYPE_SHADOW
     * @throws coding_exception
     */
    private function run_injection_type($type, $configtype = self::CONFIG_TYPE_SETTING) {
        global $PAGE;

        // Login as someone.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $mockpage = new \stdClass;

        $pagereqs = $this->prophesize(get_class($PAGE->requires));
        $pagereqs->js_call_amd(Argument::type('string'), Argument::type('string'), Argument::type('array'))
            ->shouldBeCalledTimes(1);
        $mockpage->requires = $pagereqs->reveal();
        $mockpage->context = $PAGE->context;
        $mockpage->pagetype = $PAGE->pagetype;

        // Enable plugin and tracker type.
        $this->enable_plugin_and_tracker($type, $configtype);

        // Let's tell te injector class to use our mock page so our prophecy becomes true.
        injector::get_instance()->set_test_page($mockpage);

        injector::get_instance()->inject();

        $pagereqs->checkProphecyMethodsPredictions();
    }

    /**
     * Enables the plugin and specified tracker configuring it with dummy data.
     * @param string $type tracker type
     * @param int $configtype config type: self::CONFIG_TYPE_SETTING || self::CONFIG_TYPE_SHADOW
     */
    private function enable_plugin_and_tracker($type, $configtype = self::CONFIG_TYPE_SETTING) {
        global $CFG;
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
                }
                break;
            case self::CONFIG_TYPE_SHADOW:
                $CFG->local_liquidus_olms_cfg = new \stdClass();
                $CFG->local_liquidus_olms_cfg->enabled = true;
                $CFG->local_liquidus_olms_cfg->$type = true;
                $CFG->local_liquidus_olms_cfg->staticshares = implode(',', [
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
                }
                break;
        }
    }

    public function test_injector_segment() {
        $this->run_injection_type('segment');
    }

    public function test_injector_keenio() {
        $this->run_injection_type('keenio');
    }

    public function test_injector_kinesis() {
        $this->run_injection_type('kinesis');
    }

    public function test_injector_google() {
        $this->run_injection_type('google');
    }

    public function test_injector_segment_shadow() {
        $this->run_injection_type('segment', self::CONFIG_TYPE_SHADOW);
    }

    public function test_injector_keenio_shadow() {
        $this->run_injection_type('keenio', self::CONFIG_TYPE_SHADOW);
    }

    public function test_injector_kinesis_shadow() {
        $this->run_injection_type('kinesis', self::CONFIG_TYPE_SHADOW);
    }

    public function test_injector_google_shadow() {
        $this->run_injection_type('google', self::CONFIG_TYPE_SHADOW);
    }
}
