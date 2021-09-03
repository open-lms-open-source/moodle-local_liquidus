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
 * Liquidus settings initializer.
 *
 * @module     local_liquidus/settings-handler-lazy
 * @copyright  Copyright (c) 2021 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Templates from 'core/templates';

/**
 * Moves setting sections to tabs.
 * @param types
 */
const moveSectionsToTabs = (types) => {
    const settingsNode = $('#page-admin-setting-local_liquidus .settingsform fieldset');
    if (settingsNode.length === 0) {
        return;
    }
    Templates.render('local_liquidus/settings_tabs', {}).then((html) => {
        // Create tab structure.
        settingsNode.append(html);
        const tabNode = $('#local_liquidus-settings-nav-tab');
        const contentNode = $('#local_liquidus-settings-nav-content');
        let activeFirst = true;
        for (const i in types) {
            const type = types[i];
            // Base selector is the enabling checkbox.
            const providerEnablingNode = $(`#id_s_local_liquidus_${type}`);
            // Find formsetingheading.
            const headingNode = providerEnablingNode.parent().parent().parent().prev();
            // Find title h3.
            const titleNode = headingNode.prev();
            // Get all rows that correspond to setting inputs.
            const settingRows = $(`div[id^=admin-${type}`);
            // Move title to nav.
            Templates.render('local_liquidus/settings_tabs_nav_link', {
                id: type,
                title: titleNode.text(),
                active: activeFirst,
            }).then((navHtml) => {
                tabNode.append(navHtml);
                titleNode.remove();
            });

            // Move content to div.
            Templates.render('local_liquidus/settings_tabs_nav_content', {
                id: type,
                active: activeFirst,
            }).then((contentHtml) => {
                contentNode.append(contentHtml);
                headingNode.detach().appendTo(`#local_liquidus-nav-${type}`);
                settingRows.detach().appendTo(`#local_liquidus-nav-${type}`);
            });

            activeFirst = false;
        }
    });
};

/**
 * Initializer.
 * @param types
 */
export const init = (types) => {
    (function() {
        moveSectionsToTabs(types);
    })();
};