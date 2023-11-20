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

    // Unidentifiable static shares.
    const STATIC_USER_HASH = 'userhash';
    const STATIC_USER_ROLE_CONTEXT = 'userrolecontext';
    const STATIC_ALL_USER_ROLES = "alluserroles";
    const STATIC_CONTEXT_LEVEL = 'contextlevel';
    const STATIC_PAGE_TYPE = 'pagetype';
    const STATIC_PLUGINS = 'plugins';
    const STATIC_COURSE_ID = 'courseid';
    const STATIC_PAGE_URL = 'pageurl';
    const STATIC_PAGE_PATH = 'pagepath';
    const STATIC_SITE_SHORT_NAME = 'siteshortname';
    const STATIC_LANGUAGE = 'sitelanguage';
    const STATIC_SITE_HASH = 'sitehash';
    const STATIC_MROOMS_VERSION = 'mroomsversion';
    const STATIC_MOODLE_VERSION = 'moodleversion';
    const STATIC_THEME = 'theme';
    const STATIC_IS_SUPPORT_USER = 'issupportuser';
    const STATIC_OLMS_PRODUCT = 'olmsproduct';
    const STATIC_IS_IMPERSONATED = 'isimpersonated';

    // Identifiable static shares.
    const STATIC_USER_ID = 'userid';
    const STATIC_USER_EMAIL = 'useremail';

    const STATIC_SHARES_ALWAYS = [
        self::STATIC_USER_HASH,
        self::STATIC_SITE_HASH,
        self::STATIC_IS_SUPPORT_USER,
    ];

    const UNIDENTIFIABLE_STATIC_SHARES = [
        self::STATIC_USER_ROLE_CONTEXT,
        self::STATIC_ALL_USER_ROLES,
        self::STATIC_CONTEXT_LEVEL,
        self::STATIC_PAGE_TYPE,
        self::STATIC_PLUGINS,
        self::STATIC_COURSE_ID,
        self::STATIC_PAGE_URL,
        self::STATIC_PAGE_PATH,
        self::STATIC_SITE_SHORT_NAME,
        self::STATIC_LANGUAGE,
        self::STATIC_MROOMS_VERSION,
        self::STATIC_MOODLE_VERSION,
        self::STATIC_THEME,
        self::STATIC_OLMS_PRODUCT,
        self::STATIC_IS_IMPERSONATED,
    ];

    const IDENTIFIABLE_STATIC_SHARES = [
        self::STATIC_USER_ID,
        self::STATIC_USER_EMAIL,
    ];

    const STATIC_SHARES_CAMEL_CASE = [
        self::STATIC_USER_HASH => 'userHash',
        self::STATIC_USER_ROLE_CONTEXT => 'userRoleContext',
        self::STATIC_ALL_USER_ROLES => 'allUserRoles',
        self::STATIC_USER_ID => 'userId',
        self::STATIC_USER_EMAIL => 'userEmail',
        self::STATIC_CONTEXT_LEVEL => 'contextLevel',
        self::STATIC_PAGE_TYPE => 'pageType',
        self::STATIC_PLUGINS => 'plugins',
        self::STATIC_COURSE_ID => 'courseId',
        self::STATIC_PAGE_URL => 'pageUrl',
        self::STATIC_PAGE_PATH => 'pagePath',
        self::STATIC_SITE_SHORT_NAME => 'siteShortName',
        self::STATIC_LANGUAGE => 'siteLanguage',
        self::STATIC_SITE_HASH => 'siteHash',
        self::STATIC_MROOMS_VERSION => 'mRoomsVersion',
        self::STATIC_MOODLE_VERSION => 'moodleVersion',
        self::STATIC_THEME => 'theme',
        self::STATIC_IS_SUPPORT_USER => 'isSupportUser',
        self::STATIC_OLMS_PRODUCT=> 'olmsProduct',
        self::STATIC_IS_IMPERSONATED=> 'isImpersonated'
    ];

    private static string $renderedstaticshares = '';

    /**
     * Get string of JS scripts containing the static shares.
     *
     * @return string
     */
    public static function get_rendered_static_shares(): string {
        return self::$renderedstaticshares;
    }

    /**
     * Clear string of JS scripts containing the static shares.
     */
    public static function clear_rendered_static_shares() : void {
        self::$renderedstaticshares = '';
    }

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
    public static function track_path($urlencode = false, $leadingslash = false) {
        global $DB, $PAGE;

        $id = $PAGE->context->id;
        $pageinfo = get_context_info_array($id);
        $trackurl = '';

        if ($leadingslash) {
            $trackurl .= '/';
        }

        // Adds course category name.
        if (isset($pageinfo[1]->category)) {
            if ($category = $DB->get_record('course_categories', ['id' => $pageinfo[1]->category])
            ) {
                $cats = explode('/', $category->path);
                foreach (array_filter($cats) as $cat) {
                    if ($categorydepth = $DB->get_record('course_categories', ['id' => $cat])) {
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
     * Return user object based on masquerading config
     *
     * @param \stdClass $config Config object.
     * @return user User object
     */
    public static function get_masqueradinguser($config, $provider) {
        global $USER;

        $user = $USER;
        $ismasquerading = manager::is_loggedinas();

        if ($ismasquerading) {
            $usereal = $config->{"{$provider}_masquerade_handling"};
            if ($usereal) {
                $user = manager::get_realuser();
            }
        }
        return $user;
    }

    /**
     * Retrieve an array containing the short names
     * of all roles in the whole system.
     * @return array
     */
    public static function get_allrolesshortname() {
        $allrolenames = get_all_roles();
        $rolenames = [];
        foreach ($allrolenames as $role) {
            $rolenames[] = $role->shortname;
        }

        return $rolenames;
    }

    /**
     * Retrieve an array containing the short names of all roles
     * assigned to a user in the whole system.
     *
     * @param int $userid User id.
     * @return array
     */
    private static function get_all_roles_of_user(int $userid, $issiteadmin) {
        global $DB;

        $sql = "SELECT DISTINCT r.shortname
                  FROM {role_assignments} ra, {role} r
                 WHERE ra.userid = ?
                   AND ra.roleid = r.id";

        $allroles = $DB->get_records_sql($sql, [$userid]);

        $rolenames = [];
        foreach ($allroles as $role) {
            $rolenames[] = $role->shortname;
        }

        if (empty($rolenames) && !$issiteadmin) {
            $rolenames[] = 'norole';
        }

        if ($issiteadmin) {
            $rolenames[] = 'siteadmin';
        }

        return $rolenames;

    }


    /**
     * Whether to track this request.
     *
     * @param \stdClass $config Config object.
     * @return boolean
     *   The outcome of our deliberations.
     */
    public static function should_track($config, $provider) {
        $tracknonadmin = !empty($config->{"{$provider}_tracknonadmin"});
        $checkadmin = is_siteadmin();

        $user = static::get_masqueradinguser($config, $provider);

        if ($tracknonadmin == 0 && !$checkadmin) {
            return false;
        }

        if ($tracknonadmin == 1 && !$checkadmin) {
            $trackrolesettingkey = "{$provider}_trackroles";

            $alluserroles = self::get_all_roles_of_user($user->id, $checkadmin); // Get all roles assigned to the user.

            if (empty($alluserroles)) { // Track if there's no role
                return true;
            }

            if (property_exists($config, $trackrolesettingkey)) {
                $rolesconf = $config->{$trackrolesettingkey};

                if ($rolesconf == "allroles") { // Early return if all roles are supposed to be tracked.
                    return true;
                }

                if (!empty($rolesconf)) {
                    $rolestotrack = [];
                    $rolestotrack = array_merge($rolestotrack, explode(',', $rolesconf));

                    if (count(array_intersect($rolestotrack, $alluserroles))) { // Track if there's a role in common
                        return true;
                    }
                }
            }
            return false;
        }

        $trackadmin = !empty($config->{"{$provider}_trackadmin"});
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
     * @param \stdClass $config Config object.
     */
    public static function build_static_shares($config) {
        global $PAGE, $SITE, $CFG, $DB;

        if (!isloggedin()) {
            return;
        }

        $issiteadmin = is_siteadmin();
        $id = $PAGE->context->id;

        // Early return if context is not set or was deleted
        if (!$DB->record_exists('context', ['id' => $id])) {
            return;
        }

        $provider = static::get_my_provider_name();
        $user = static::get_masqueradinguser($config, $provider);
        $staticshares = [];
        $unidentifiablestaticsharesettingkey = "{$provider}_unidentifiable_staticshares";
        if (property_exists($config, $unidentifiablestaticsharesettingkey)) {
            $unidentifiablestaticshares = $config->{$unidentifiablestaticsharesettingkey};
            if (!empty($unidentifiablestaticshares)) {
                $staticshares = array_merge($staticshares, explode(',', $unidentifiablestaticshares));
            }
        }

        if (!empty($CFG->local_liquidus_identifiable_share_providers) && in_array($provider, $CFG->local_liquidus_identifiable_share_providers)) {
            $identifiablestaticsharessettingskey = "{$provider}_identifiable_staticshares";
            if (property_exists($config, $identifiablestaticsharessettingskey)) {
                $identifiablestaticshares = $config->{$identifiablestaticsharessettingskey};
                if (!empty($identifiablestaticshares)) {
                    $staticshares = array_merge($staticshares, explode(',', $identifiablestaticshares));
                }
            }
        }

        // Add static shares which must always be used to the static shares array.
        $staticshares = array_merge(self::STATIC_SHARES_ALWAYS, $staticshares);

        foreach ($staticshares as $staticshare) {
            if (in_array($staticshare, self::IDENTIFIABLE_STATIC_SHARES) && empty($config->{"{$provider}_share_identifiable"})) {
                continue;
            }
            $value = '';
            switch ($staticshare) {
                case self::STATIC_USER_HASH:
                    $value = sha1($SITE->shortname . '-' . $user->id . '-' . $user->username);
                    break;
                case self::STATIC_USER_ROLE_CONTEXT:
                    self::add_user_roles_in_context_to_html($PAGE->context, $user->id, $issiteadmin);
                    break;
                case self::STATIC_ALL_USER_ROLES:
                    self::add_all_user_roles_of_user_to_html($user->id, $issiteadmin);
                    break;
                case self::STATIC_CONTEXT_LEVEL:
                    $value = $PAGE->context->contextlevel;
                    break;
                case self::STATIC_PAGE_TYPE:
                    $value = $PAGE->pagetype;
                    break;
                case self::STATIC_PLUGINS:
                    self::add_current_plugins_called_to_html();
                    break;
                case self::STATIC_COURSE_ID:
                    $value = $PAGE->course->id;
                    break;
                case self::STATIC_PAGE_URL:
                    $value = $PAGE->url->out(false);
                    break;
                case self::STATIC_PAGE_PATH:
                    $value = self::track_path();
                    break;
                case self::STATIC_LANGUAGE:
                    $value = current_language();
                    break;
                case self::STATIC_USER_EMAIL:
                    $value = $user->email;
                    break;
                case self::STATIC_USER_ID:
                    $value = $SITE->id . '|' . $user->id;
                    break;
                case self::STATIC_SITE_SHORT_NAME:
                    $value = $SITE->shortname;
                    break;
                case self::STATIC_SITE_HASH:
                    $value = sha1(parse_url($PAGE->url->out(false))['host']);
                    break;
                case self::STATIC_THEME:
                    $value = $PAGE->theme->name;
                    break;
                case self::STATIC_IS_SUPPORT_USER:
                    $value = self::identify_support_users($user->email);
                    break;
                case self::STATIC_MROOMS_VERSION:
                    $value = self::get_mrooms_version();
                    break;
                case self::STATIC_MOODLE_VERSION:
                    $value = self::get_moodle_version();
                    break;
                case self::STATIC_OLMS_PRODUCT:
                    $value = self::get_olms_product();
                    break;
                case self::STATIC_IS_IMPERSONATED:
                    $value = manager::is_loggedinas() ? 'true' : 'false';
                    break;
            }

            if (!empty($value)) {
                self::encode_and_add_json_to_html($staticshare, $value);
            }
        }
    }

    private static function get_mrooms_version() {
        if (!function_exists('mr_local_mrooms_version')) {
            return '-1';
        }
        mr_local_mrooms_version();
        $version = [];
        $localmroomsversion = LOCAL_MROOMS_JOULEVERSION;

        $localmroomsversiondate = explode("Build:", $localmroomsversion);
        $localmroomsversiondate = preg_replace("/[^0-9]/", "", end($localmroomsversiondate));

        $version["name"] = $localmroomsversion;
        $version["date"] = strtotime($localmroomsversiondate);

        return $version;
    }

    private static function get_moodle_version() {
        global $CFG;

        $version = [];
        $moodleversionrelease = $CFG->release;

        $moodleversiondate= explode("Build:", $moodleversionrelease);
        $moodleversiondate = preg_replace("/[^0-9]/", "", end($moodleversiondate));

        $version["name"] = $moodleversionrelease;
        $version["date"] = strtotime($moodleversiondate);

        return $version;
    }

    private static function get_olms_product() {
        $product = '';

        // Get installed local plugins list.
        $localplugins = \core_component::get_plugin_list('local');
        // Get installed authentication plugins list.
        $authplugins = \core_component::get_plugin_list('auth');

        if (array_key_exists('cloudstore', $localplugins)) {
            $product = 'Enterprise';
        } elseif (array_key_exists('cfzapi', $localplugins)) {
            $product = 'Class for zoom';
        } elseif (array_key_exists('learnbook', $localplugins)) {
            $product = 'Learnbook';
        } elseif (array_key_exists('ethink', $authplugins)) {
            $product = 'Managed Hosting';
        } elseif (array_key_exists('olms_work', $localplugins)) {
            $product = 'WORK';
        } elseif (array_key_exists('mrooms', $localplugins)) {
            $product = 'EDU';
        } else {
            $product = 'Other';
        }
        return $product;
    }

    private static function add_current_plugins_called_to_html() {
        $currfile = __FILE__;

        // This get all files that has been included among the script.
        $files = get_included_files();

        // Filter files that are related to a plugin being used.
        $files = array_filter($files, function ($file) {
            $excluded_strings = ['version.php', '/mustache/', 'access.php', 'lib.php'];
            foreach ($excluded_strings as $string) {
                if (strpos($file, $string) !== false) {
                    return false;
                }
            }
            return true;
        });

        $validplugintypes = ['mod', 'block', 'local', 'filter', 'tool', 'theme', 'report', 'auth'];
        array_splice($files, array_search($currfile, $files), 1);
        $plugins = [];

        // Use this array to keep track of how many files are included for each plugin.
        $pluginFileCounts = [];

        array_walk($files, function (&$item) use ($validplugintypes, &$plugins, &$pluginFileCounts) {
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

                if ($id === 'liquidus') { //Don't include Liquidus in the list.
                    return;
                }

                if (in_array($type, $validplugintypes)) {
                    if (!isset($plugins[$type])) {
                        $plugins[$type] = [];
                    }

                    if (!in_array($id, $plugins[$type])) {
                        $plugins[$type][] = $id;
                    }

                    // Check if the plugin is already in the count array, and increment the count.
                    if (isset($pluginFileCounts[$type][$id])) {
                        $pluginFileCounts[$type][$id]++;
                    } else {
                        $pluginFileCounts[$type][$id] = 1;
                    }
                }
            }
        });


        // Loop through the pluginFileCounts array and add plugins with multiple files to your $plugins array.
        foreach ($pluginFileCounts as $type => $pluginsOfType) {
            foreach ($pluginsOfType as $id => $count) {
                if ($count <= 1) {
                    // Remove empty arrays (plugins with only one file)
                    if (isset($plugins[$type])) {
                        $index = array_search($id, $plugins[$type]);
                        if ($index !== false) {
                            array_splice($plugins[$type], $index, 1);
                        }
                    }
                }
            }
        }

        // Adding plugin list straight to HTML.
        self::encode_and_add_json_to_html(self::STATIC_PLUGINS, $plugins);
    }

    private static function add_user_roles_in_context_to_html(context $context, int $userid, bool $issiteadmin) {
        $rolesincontext = get_user_roles($context, $userid);

        $rolenamesincontext = [];
        foreach ($rolesincontext as $role) {
            $rolenamesincontext[] = $role->shortname;
        }

        if (empty($rolenamesincontext)) {
            if ($issiteadmin) { // Check if the user has an admin role if no role can be retrieved
                $rolenamesincontext[] = 'siteadmin';
            } else {
                $rolenamesincontext[] = 'norole';
            }
        }

        // Adding user roles straight to HTML.
        self::encode_and_add_json_to_html(self::STATIC_USER_ROLE_CONTEXT, $rolenamesincontext);
    }

    private static function add_all_user_roles_of_user_to_html(int $userid, bool $issiteadmin) {
        $allrolesuser = self::get_all_roles_of_user($userid, $issiteadmin);

        // Adding user roles straight to HTML.
        self::encode_and_add_json_to_html(self::STATIC_ALL_USER_ROLES, $allrolesuser);
    }

    public static function identify_support_users(string $email) {
        global $CFG;

        $emaildomainarray = explode("@", $email);
        if (!isset($CFG->local_liquidus_olms_cfg->support_user_domains)) {
            return "no";
        }

        $supportuserdomains = $CFG->local_liquidus_olms_cfg->support_user_domains;
        if (in_array(end($emaildomainarray), $supportuserdomains) || in_array($email, $supportuserdomains)) {
            return "yes";
        }

        return "no";
    }

    /**
     * @param string $share Share identifier.
     * @param array|string $value Share value. This will be encoded as json.
     */
    private static function encode_and_add_json_to_html($share, $value) {
        global $OUTPUT;

        $jsonvalue = json_encode($value);
        $provider = static::get_my_provider_name();
        $sharecamelcase = self::STATIC_SHARES_CAMEL_CASE[$share];

        $data = [
            'provider' => $provider,
            'sharecamelcase' => $sharecamelcase,
            'jsonvalue' => $jsonvalue
        ];

        $staticsharescript = $OUTPUT->render_from_template('local_liquidus/static_shares_scripts', $data);
        self::$renderedstaticshares .= $staticsharescript;

        if (!PHPUNIT_TEST){
            echo $staticsharescript;
        }


    }

    /**
     * If this should be embedded
     * @return string
     */
    public static function get_script_url($config) {
        return '';
    }

    /**
     * @return string[]
     */
    public abstract static function get_config_settings();

    /**
     * Get this classname, it should be the same as the provider.
     * @return string
     */
    public static function get_my_provider_name() {
        $classname = static::class;
        $classparts = explode('\\', $classname);
        return end($classparts);
    }
}
