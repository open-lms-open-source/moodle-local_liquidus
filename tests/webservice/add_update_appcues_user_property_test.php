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
 * @copyright  Copyright (c) 2024 Open LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

/**
 * @group local_liquidus
 */

class add_update_appcues_user_property_test extends advanced_testcase {

    public function test_service_parameters() {
        $params = local_liquidus\webservice\add_update_appcues_user_properties::service_parameters();
        $this->assertTrue($params instanceof \external_function_parameters);
    }

    public function test_service_returns() {
        $returns = local_liquidus\webservice\add_update_appcues_user_properties::service_returns();
        $this->assertTrue($returns instanceof \external_single_structure);
    }

    public function test_service() {

        global $DB;

        $this->resetAfterTest();

        $student = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id);

        $this->setAdminUser();
        $serviceresult = local_liquidus\webservice\add_update_appcues_user_properties::service("Name property 1", false);
        $this->assertTrue($serviceresult['success']);
        $configs[] = get_config('local_liquidus');
        $this->assertTrue(property_exists($configs[0], 'deck36websvcNameproperty1'));

        $this->setUser($student);
        $serviceresult = local_liquidus\webservice\add_update_appcues_user_properties::service("Name property 1", true);
        $this->assertFalse($serviceresult['success']);

    }
}
