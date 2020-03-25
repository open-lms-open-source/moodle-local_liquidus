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
 * @copyright  Copyright (c) 2020 Blackboard Inc.
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

    public function setUp() {
        parent::setUp();
        injector::get_instance()->reset();
        $this->resetAfterTest();
    }

    private function run_injection_type($type) {
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

        // Let's tell te injector class to use our mock page so our prophecy becomes true.
        injector::get_instance()->set_test_page($mockpage);

        set_config('enabled', '1', 'local_liquidus');
        set_config($type, '1', 'local_liquidus');
        set_config('segmentwritekey', 'abc', 'local_liquidus');

        injector::get_instance()->inject();

        $pagereqs->checkProphecyMethodsPredictions();
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
}
