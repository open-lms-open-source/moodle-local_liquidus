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
 * Liquidus.
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_liquidus\api;

defined('MOODLE_INTERNAL') || die();

use context;
use core\session\manager;

/**
 * Abstract local analytics class.
 */
abstract class analytics {

    const STATIC_USER_HASH = 'userhash';
    const STATIC_USER_ROLE = 'userrole';
    const STATIC_CONTEXT_LEVEL = 'contextlevel';
    const STATIC_PAGE_TYPE = 'pagetype';
    const STATIC_PLUGINS = 'plugins';

    const STATIC_SHARES_ALWAYS = [
        self::STATIC_USER_HASH,
    ];

    const STATIC_SHARES = [
        self::STATIC_USER_ROLE,
        self::STATIC_CONTEXT_LEVEL,
        self::STATIC_PAGE_TYPE,
        self::STATIC_PLUGINS,
    ];

    const STATIC_SHARES_CAMEL_CASE = [
        self::STATIC_USER_HASH => 'userHash',
        self::STATIC_USER_ROLE => 'userRole',
        self::STATIC_CONTEXT_LEVEL => 'contextLevel',
        self::STATIC_PAGE_TYPE => 'pageType',
        self::STATIC_PLUGINS => 'plugins',
    ];

    /**
     * Encode a substring if required.
     *
     * @param string  $input  The string that might be encoded.
     * @param boolean $encode Whether to encode the URL.
     * @return string
     */
    private static function might_encode($input, $encode) {
        if (!$encode) {
            return str_replace("'", "\'", $input);
        }

        return urlencode($input);
    }

    /**
     * Get the Tracking URL for the request.
     *
     * @param bool|int $urlencode    Whether to encode URLs.
     * @param bool|int $leadingslash Whether to add a leading slash to the URL.
     * @return string A URL to use for tracking.
     */
    public static function trackurl($urlencode = false, $leadingslash = false) {
        global $DB, $PAGE;
        $pageinfo = get_context_info_array($PAGE->context->id);
        $trackurl = "";

        if ($leadingslash) {
            $trackurl .= "/";
        }

        // Adds course category name.
        if (isset($pageinfo[1]->category)) {
            if ($category = $DB->get_record('course_categories', ['id' => $pageinfo[1]->category])
            ) {
                $cats = explode("/", $category->path);
                foreach (array_filter($cats) as $cat) {
                    if ($categorydepth = $DB->get_record("course_categories", ["id" => $cat])) {
                        $trackurl .= self::might_encode($categorydepth->name, $urlencode).'/';
                    }
                }
            }
        }

        // Adds course full name.
        if (isset($pageinfo[1]->fullname)) {
            if (isset($pageinfo[2]->name)) {
                $trackurl .= self::might_encode($pageinfo[1]->fullname, $urlencode).'/';
            } else {
                $trackurl .= self::might_encode($pageinfo[1]->fullname, $urlencode);
                $trackurl .= '/';
                if ($PAGE->user_is_editing()) {
                    $trackurl .= get_string('edit', 'local_liquidus');
                } else {
                    $trackurl .= get_string('view', 'local_liquidus');
                }
            }
        }

        // Adds activity name.
        if (isset($pageinfo[2]->name)) {
            $trackurl .= self::might_encode($pageinfo[2]->modname, $urlencode);
            $trackurl .= '/';
            $trackurl .= self::might_encode($pageinfo[2]->name, $urlencode);
        }

        return $trackurl;
    }

    /**
     * Whether to track this request.
     *
     * @param \stdClass $config
     * @return boolean
     *   The outcome of our deliberations.
     */
    public static function should_track($config) {
        $tracknonadmin = !empty($config->tracknonadmin);

        if ($tracknonadmin == 1 && !is_siteadmin()) {
            return true;
        }

        $trackadmin = !empty($config->trackadmin);
        return ($trackadmin == 1);
    }

    /**
     * Gets an array with all the tracker info.
     * @param \stdClass $config Config object.
     * @return array
     */
    public static abstract function get_tracker_info($config);

    /**
     * Gets an array with all the static info.
     * @param \stdClass $config
     * @return array
     */
    public static function get_static_shares($config) {
        global $USER, $PAGE;

        $res = [];
        if (!isloggedin()) {
            return $res;
        }

        $user = $USER;
        $ismasquerading = manager::is_loggedinas();

        if ($ismasquerading) {
            $usereal = $config->masquerade_handling;
            if ($usereal) {
                $user = manager::get_realuser();
            }
        }

        $staticshares = $config->staticshares;
        if (!empty($staticshares)) {
            $staticshares = explode(',', $staticshares);
        } else {
            $staticshares = [];
        }

        // Add static shares which must always be used to the static shares array.
        $staticshares = array_merge(self::STATIC_SHARES_ALWAYS, $staticshares);

        $shares = [];
        foreach ($staticshares as $staticshare) {
            $value = '';
            switch ($staticshare) {
                case self::STATIC_USER_HASH:
                    $value = sha1($user->id . '-' . $user->username);
                    break;
                case self::STATIC_USER_ROLE:
                    $value = self::add_user_roles_to_footer($PAGE->context, $user->id);
                    break;
                case self::STATIC_CONTEXT_LEVEL:
                    $value = $PAGE->context->contextlevel;
                    break;
                case self::STATIC_PAGE_TYPE:
                    $value = $PAGE->pagetype;
                    break;
                case self::STATIC_PLUGINS:
                    $value = self::add_current_plugins_called_to_footer();
                    break;
            }

            if (!empty($value)) {
                $shares[self::STATIC_SHARES_CAMEL_CASE[$staticshare]] = $value;
            }
        }

        return $shares;
    }

    /**
     * @return boolean
     */
    private static function add_current_plugins_called_to_footer() {
        $currfile = __FILE__;
        $files = get_included_files();

        $validplugintypes = ['mod', 'block', 'local', 'filter', 'tool', 'theme', 'report', 'auth'];
        array_splice($files, array_search($currfile, $files), 1);
        $plugins = [];
        array_walk($files, function (&$item) use ($validplugintypes, &$plugins) {
            global $CFG;
            $bareitem = str_replace($CFG->dirroot . '/', '', $item);
            $exploded = explode('/', $bareitem);
            if (count($exploded) >= 2) {
                $type = $exploded[0];

                if ($type === 'admin' && count($exploded) >= 3) {
                    $type = $exploded[1];
                    $id = $exploded[2];
                } else {
                    if ($type === 'blocks') {
                        $type = 'block';
                    }
                    $id = $exploded[1];
                }

                if (in_array($type, $validplugintypes)) {
                    if (!isset($plugins[$type])) {
                        $plugins[$type] = [];
                    }

                    if (!in_array($id, $plugins[$type])) {
                        $plugins[$type][] = $id;
                    }
                }
            }
        });

        // Adding plugin list straight to footer, explanation: @see \local_liquidus\api\analytics::add_json_to_footer.
        $json = json_encode($plugins);
        self::add_json_to_footer('localLiquidusCurrentPlugins', $json);

        return true;
    }

    /**
     * @return boolean
     */
    private static function add_user_roles_to_footer(context $context, int $userid) {
        $roles = get_user_roles($context, $userid);

        $rolenames = [];
        foreach ($roles as $role) {
            $rolenames[] = $role->shortname;
        }

        // Adding user roles straight to footer, explanation: @see \local_liquidus\api\analytics::add_json_to_footer.
        $json = json_encode($rolenames);
        self::add_json_to_footer('localLiquidusUserRole', $json);

        return true;
    }

    /**
     * @param $varname
     * @param $json
     */
    private static function add_json_to_footer($varname, $json) {
        global $CFG;

        $script = <<<HTML
            <script>
                var $varname = $json;
            </script>
HTML;

        if (!isset($CFG->additionalhtmlfooter)) {
            $CFG->additionalhtmlfooter = '';
        }
        // Note, we have to put the plugin list into the footer instead of passing them into the amd module as an
        // argument. If you pass large amounts of data into the amd arguments then it throws a debug error.
        $CFG->additionalhtmlfooter .= $script;
    }
}
