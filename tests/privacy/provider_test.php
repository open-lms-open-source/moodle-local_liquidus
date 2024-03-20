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
 * Testcase for local_pld plugin privacy implementation.
 *
 * @package    local_liquidus
 * @author     Daniel Cifuentes
 * @copyright  Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// @group local_liquidus.

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use local_liquidus\privacy\provider;

global $CFG;


/**
 * Testcase for local liquidus privacy implementation.
 *
 * @package    local_liquidus
 * @copyright  Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_liquidus_privacy_provider_test extends provider_testcase {

    public function setUp(): void {
        $this->resetAfterTest();
    }

    public function test_get_contexts_for_userid() {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->assertEmpty(provider::get_contexts_for_userid($user->id));

        $data = new \stdClass();
        $data->userid = $user->id;
        $data->useremail = $user->email;
        $data->timemodified = time();
        $data->previousstatus = 0;
        $data->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data);
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(1, $contextlist);

        $usercontext = \context_user::instance($user->id);
        $this->assertEquals($usercontext->id, $contextlist->get_contextids()[0]);

    }

    public function test_export_user_data() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();

        $usercontext = \context_user::instance($user1->id);
        $writer = writer::with_context($usercontext);
        $this->assertFalse($writer->has_any_data());

        $data = new \stdClass();
        $data->userid = $user1->id;
        $data->useremail = $user1->email;
        $data->timemodified = time();
        $data->previousstatus = 0;
        $data->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data);

        $data2 = new \stdClass();
        $data2->userid = $user1->id;
        $data2->useremail = $user1->email;
        $data2->timemodified = time();
        $data2->previousstatus = 1;
        $data2->currentstatus = 0;
        $DB->insert_record('local_liquidus_consent_log', $data);

        $context = \context_user::instance($user1->id);
        provider::export_user_data(new approved_contextlist($user1, 'local_liquidus', [$context->id]));

        $data = $writer->get_data(['local_liquidus', 'logs']);
        $this->assertCount(2, $data->logs);
    }

    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $data = new \stdClass();
        $data->userid = $user1->id;
        $data->useremail = $user1->email;
        $data->timemodified = time();
        $data->previousstatus = 0;
        $data->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data);

        $data2 = new \stdClass();
        $data2->userid = $user2->id;
        $data2->useremail = $user2->email;
        $data2->timemodified = time();
        $data2->previousstatus = 0;
        $data2->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data2);

        $sql = "SELECT id, userid, useremail, previousstatus, currentstatus, timemodified FROM {local_liquidus_consent_log}";
        $records = $DB->get_records_sql($sql);
        $this->assertCount(2, $records);

        $context = \context_system::instance();
        provider::delete_data_for_all_users_in_context($context);

        $sql = "SELECT id, userid, useremail, previousstatus, currentstatus, timemodified FROM {local_liquidus_consent_log}";
        $records = $DB->get_records_sql($sql);
        $this->assertCount(0, $records);

    }

    public function test_delete_data_for_user() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $data = new \stdClass();
        $data->userid = $user1->id;
        $data->useremail = $user1->email;
        $data->timemodified = time();
        $data->previousstatus = 0;
        $data->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data);

        $data2 = new \stdClass();
        $data2->userid = $user2->id;
        $data2->useremail = $user2->email;
        $data2->timemodified = time();
        $data2->previousstatus = 0;
        $data2->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data2);

        $sql = "SELECT id, userid, useremail, previousstatus, currentstatus, timemodified FROM {local_liquidus_consent_log}";
        $records = $DB->get_records_sql($sql);
        $this->assertCount(2, $records);

        $context = \context_user::instance($user1->id);
        provider::delete_data_for_user(new approved_contextlist($user1, 'local_liquidus', [$context->id]));

        $sql = "SELECT id, userid, useremail, previousstatus, currentstatus, timemodified FROM {local_liquidus_consent_log}";
        $records = $DB->get_records_sql($sql);
        $this->assertCount(1, $records);
    }

    public function test_get_users_in_context() {
        global $DB;

        $systemcontext = context_system::instance();
        $userlist = new \core_privacy\local\request\userlist($systemcontext, 'local_liquidus');
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist->get_userids());

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $data = new \stdClass();
        $data->userid = $user1->id;
        $data->useremail = $user1->email;
        $data->timemodified = time();
        $data->previousstatus = 0;
        $data->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data);

        $data2 = new \stdClass();
        $data2->userid = $user2->id;
        $data2->useremail = $user2->email;
        $data2->timemodified = time();
        $data2->previousstatus = 0;
        $data2->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data2);

        $systemcontext = context_system::instance();
        $userlist = new \core_privacy\local\request\userlist($systemcontext, 'local_liquidus');
        provider::get_users_in_context($userlist);
        $this->assertCount(2, $userlist->get_userids());

    }

    public function test_delete_data_for_users() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $data = new \stdClass();
        $data->userid = $user1->id;
        $data->useremail = $user1->email;
        $data->timemodified = time();
        $data->previousstatus = 0;
        $data->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data);

        $data2 = new \stdClass();
        $data2->userid = $user2->id;
        $data2->useremail = $user2->email;
        $data2->timemodified = time();
        $data2->previousstatus = 0;
        $data2->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data2);

        $data3 = new \stdClass();
        $data3->userid = $user3->id;
        $data3->useremail = $user3->email;
        $data3->timemodified = time();
        $data3->previousstatus = 0;
        $data3->currentstatus = 1;
        $DB->insert_record('local_liquidus_consent_log', $data3);

        $sql = "SELECT id, userid, useremail, previousstatus, currentstatus, timemodified FROM {local_liquidus_consent_log}";
        $records = $DB->get_records_sql($sql);
        $this->assertCount(3, $records);

        $systemcontext = context_system::instance();
        $approveduserlist = new \core_privacy\local\request\approved_userlist
        ($systemcontext, 'local_liquidus', [$user1->id, $user2->id]);
        provider::delete_data_for_users($approveduserlist);

        $sql = "SELECT id, userid, useremail, previousstatus, currentstatus, timemodified FROM {local_liquidus_consent_log}";
        $records = $DB->get_records_sql($sql);
        $this->assertCount(1, $records);
    }

}
