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
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_liquidus;

use local_liquidus\api\analytics;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Class injector
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class injector {
    /** @var injector */
    private static $instance;

    /** @var bool */
    private $injected;

    /** @var null|\stdClass  */
    private $testpage;

    /** @var string[] */
    private $analyticstypes;

    private function __construct() {
        $this->reset();
    }

    public static function get_instance() : injector {
        if (self::$instance === null) {
            self::$instance = new injector();
        }
        return self::$instance;
    }

    public function inject() {
        global $PAGE, $CFG;

        if (!isloggedin()) {
            return;
        }

        if ($this->injected) {
            return;
        }
        $this->injected = true;

        $configs = $this->getAvailableConfigs();
        if (empty($configs)) {
            return;
        }

        if (empty($this->analyticstypes)) {
            $this->analyticstypes = array_diff(
                scandir($CFG->dirroot . '/local/liquidus/classes/api'),
                ['..', '.', 'analytics']);
            array_walk($this->analyticstypes, function(&$item) {
                $item = basename($item, '.php');
            });
        }

        $trackersinfo = [];
        foreach ($this->analyticstypes as $type) {
            $trackersinfo = array_merge($trackersinfo, $this->retrieveTrackerInfoAllConfigs($type, $configs));
        }

        if (empty($trackersinfo)) {
            return;
        }

        $page = $PAGE;
        if ($this->testpage !== null) {
            // Someone is testing a page.
            $page = $this->testpage;
        }

        // Add script tags for trackers which require it.
        $inhead = true;
        foreach ($trackersinfo as $info) {
            if (!empty($url = $info['scripturl'])) {
                if (strpos($url, 'http') === false) {
                    $url = (is_https() ? 'https' : 'http') . "://{$url}";
                }
                $page->requires->js(new moodle_url($url), $inhead);
            }
        }

        $page->requires->js_call_amd('local_liquidus/main', 'init', [$trackersinfo]);
    }

    /**
     * Resets injection status.
     */
    public function reset() {
        $this->injected = false;
        $this->testpage = null;
        $this->analyticstypes = [];
    }

    /**
     * @param $testpage
     * @throws \coding_exception
     */
    public function set_test_page($testpage) {
        if (!defined('PHPUNIT_TEST') && !PHPUNIT_TEST) {
            throw new \coding_exception('Test page can only be set when running tests.');
        }
        $this->testpage = $testpage;
    }

    /**
     * Retrieve tracker information from all configurations, plugin and shadow.
     * @param string $type
     * @param array $configs
     * @return array
     * @throws \dml_exception
     */
    private function retrieveTrackerInfoAllConfigs($type, $configs): array {
        $result = [];
        foreach ($configs as $config) {
            $trackerinfo = $this->retrieveTrackerInfo($type, $config);
            if (!is_null($trackerinfo)) {
                $result[] = $trackerinfo;
            }
        }
        return $result;
    }

    /**
     * Build injection class and get the relevant tracker information.
     * @param $type
     * @param $config
     * @return array|null
     */
    private function retrieveTrackerInfo($type, $config): ?array {
        if (!empty($config->$type)) {
            $classname = "\\local_liquidus\\api\\{$type}";
            if (!class_exists($classname, true)) {
                debugging("Local Liquidus Module: Analytics setting '{$type}' doesn't map to a class name.");
                return null;
            }

            /** @var analytics $engine */
            $engine = new $classname;
            $trackerinfo = $engine::get_tracker_info($config);
            if (empty($trackerinfo)) {
                // Misconfigured tracker, it is enabled but missing fields.
                // Returning null ensures that the tracker is not included.
                return null;
            }

            if (!empty($scripturl = $engine::get_script_url($config))) {
                $trackerinfo['scripturl'] = $scripturl;
            }
            return $trackerinfo;
        }
        return null;
    }

    /**
     * Gets the available configurations to be used.
     * @return array
     * @throws \dml_exception
     */
    private function getAvailableConfigs() {
        global $CFG;
        $configs = [];
        // Normal Moodle config for client use.
        $configs[] = get_config('local_liquidus');
        // Shadow config for internal Open LMS use.
        if (!empty($CFG->local_liquidus_olms_cfg) && is_object($CFG->local_liquidus_olms_cfg)) {
            $configs[] = $CFG->local_liquidus_olms_cfg;
        }

        return array_filter($configs, function($config) {
            $pluginenabled = $config->enabled;
            $shouldtrack = analytics::should_track($config);
            return !empty($pluginenabled) && $shouldtrack;
        });
    }

    /**
     * Retrieves the analytics types.
     * @return array|false
     */
    public function get_analytics_types() {
        global $CFG;

        if (empty($this->analyticstypes)) {
            $this->analyticstypes = array_diff(scandir($CFG->dirroot . '/local/liquidus/classes/api'), ['..', '.', 'analytics.php']);
            array_walk($this->analyticstypes, function(&$item) {
                $item = basename($item, '.php');
            });
        }

        return $this->analyticstypes;
    }
}
