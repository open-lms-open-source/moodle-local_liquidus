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
 * Update internal tracking configuration (Mixpanel)
 *
 * @package    local_liquidus
 * @author     Juan Olivares <juan.olivares@openlms.net>
 * @copyright  2022 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params(
    [
        'help' => false,
        'enabled' => null,
        'masquerade_handling' => null,
        'trackadmin' => null,
        'tracknonadmin' => null,
        'cleanurl' => null,
        'share_identifiable' => null,
        'mixpanel_unidentifiable_staticshares' => null,
        'mixpanel_pagetypeevent' => null,
        'mixpanel_trackforms' => null,
        'mixpaneltoken' => null,

    ], [
        'h' => 'help',
        'e' => 'enabled',
        'mh' => 'masquerade_handling',
        'ta' => 'trackadmin',
        'tna' => 'tracknonadmin',
        'cu' => 'cleanurl',
        'si' => 'share_identifiable',
        'mus' => 'mixpanel_unidentifiable_staticshares',
        'mpt' => 'mixpanel_pagetypeevent',
        'mtf' => 'mixpanel_trackforms',
        'mt' => 'mixpaneltoken'
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || (!$options['mixpaneltoken'])) {
    $help = "Update internal tracking configuration.

Options:
-h,    --help                                   Print out this help
-e,    --enabled                                Enable the plugin and Mixpanel tracking
-mh,   --masquerade_handling                    Handle when admin impersonates a user
-ta,   --trackadmin                             Track admin roles only
-tna,  --tracknonadmin                          Track non-admin roles only
-cu,   --cleanurl                               Generate clean URL 
-si,   --share_identifiable                     Enable sharing identifiable data of user
-'mus', --mixpanel_unidentifiable_staticshares  Static shares to be shared with the analytics tracker
                                                    'all': Share all static shares
-'mpt', --mixpanel_pagetypeevent                Append page type to event name
-'mtf', --mixpanel_trackforms                   Track form submissions
-'mt',  --mixpaneltoken                         Mixpanel token (required)

Example:
Enable plugin, track non admin roles, track user role and sitelanguage only, and use token MIXPANEL_TOKEN
$ /usr/bin/php local/liquidus/cli/update_internal_config.php -e -tna -mus='userrole,sitelanguage' -mt='MIXPANEL_TOKEN'
";

    cli_write($help);
    die;
}

$pluginname = 'local_liquidus';
foreach ($options as $key => $value) {
    if ($value !== null && $key !== "help") { //Set new config only if requested in the CLI script parameters.
        if ($key == 'mixpanel_unidentifiable_staticshares' && $value =='all') {
            set_config($key, 'userrole,contextlevel,courseid,pagetype,plugins,pageurl,pagepath,siteshortname,sitelanguage', $pluginname);
            continue;
        }
        set_config($key, $value, $pluginname);
    }
}