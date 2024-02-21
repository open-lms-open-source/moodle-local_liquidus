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
 * Privacy Subsystem implementation for local_liquidus.
 *
 * @package local_liquidus
 * @copyright Copyright (c) 2020 Open LMS (https://www.openlms.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_liquidus\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadataprovider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider as userlistprovider;
use core_privacy\local\request\plugin\provider as pluginprovider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for local_liquidus implementing null_provider.
 *
 * @copyright Copyright (c) 2020 Open LMS (https://www.openlms.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadataprovider,
    pluginprovider,
    userlistprovider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('liquidus_data', [
            'userhash' => 'privacy:metadata:liquidus:userhash',
            'userrolecontext' => 'privacy:metadata:liquidus:userrolecontext',
            'alluserroles' => 'privacy:metadata:liquidus:alluserroles',
            'contextlevel' => 'privacy:metadata:liquidus:contextlevel',
            'courseid' => 'privacy:metadata:liquidus:courseid',
            'pagetype' => 'privacy:metadata:liquidus:pagetype',
            'plugins' => 'privacy:metadata:liquidus:plugins',
            'pageurl' => 'privacy:metadata:liquidus:pageurl',
            'pagepath' => 'privacy:metadata:liquidus:pagepath',
            'siteshortname' => 'privacy:metadata:liquidus:siteshortname',
            'sitelanguage' => 'privacy:metadata:liquidus:sitelanguage',
            'theme' => 'privacy:metadata:liquidus:theme',
            'mroomsversion' => 'privacy:metadata:liquidus:mroomsversion',
            'moodleversion' => 'privacy:metadata:liquidus:moodleversion',
            'issupportuser' => 'privacy:metadata:liquidus:issupportuser',
            'userid' => 'privacy:metadata:liquidus:userid',
            'useremail' => 'privacy:metadata:liquidus:useremail',
            'sitehash' => 'privacy:metadata:liquidus:sitehash',
        ], 'privacy:metadata:liquidus');

        $collection->add_database_table(
            'local_liquidus_consent_log',
            [
                'userid' => 'privacy:metadata:liquidus:userid',
                'useremail' => 'privacy:metadata:liquidus:useremail',
                'previousstatus' => 'privacy:metadata:liquidus:previousstatus',
                'currentstatus' => 'privacy:metadata:liquidus:currentstatus',
                'timemodified' => 'privacy:metadata:liquidus:timemodified',


            ],
            'privacy:metadata:liquidus'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Get user context.
        $sql = "SELECT cx.id
                  FROM {context} cx
                  JOIN {local_liquidus_consent_log} log ON log.userid = cx.instanceid
                 WHERE cx.instanceid = :userid and cx.contextlevel = :usercontext
              GROUP BY cx.id";

        $params = [
            'userid' => $userid,
            'usercontext' => CONTEXT_USER
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $context = \context_user::instance($userid);

        $sql = "SELECT id, userid, useremail, previousstatus, currentstatus, timemodified FROM {local_liquidus_consent_log}
                 WHERE userid = :userid";
        $params = ['userid' => $userid];

        $records = $DB->get_records_sql($sql, $params);
        $data = [];
        foreach ($records as $record) {
            $data[] = (object) [
                'userid' => $userid,
                'useremail' => $record->useremail,
                'previousstatus' => $record->previousstatus,
                'currentstatus' => $record->currentstatus,
                'timemodified' => $record->timemodified
            ];
        }

        writer::with_context($context)->export_data(['local_liquidus', 'logs'], (object) ['logs' => $data]);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_system) {
            return;
        }

        // Delete local liquidus consent log records.
        $DB->delete_records('local_liquidus_consent_log');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        $DB->delete_records('local_liquidus_consent_log', ['userid' => $userid]);
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        $sql = "SELECT userid as userid FROM {local_liquidus_consent_log}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context instanceof \context_system) {
            list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            if (!empty($userinparams)) {
                $sql = "userid {$userinsql}";
                $DB->delete_records_select('local_liquidus_consent_log', $sql, $userinparams);
            }
        }
    }
}