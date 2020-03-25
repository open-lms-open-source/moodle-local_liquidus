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
 * @copyright Copyright (c) 2020 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_liquidus;

use local_liquidus\api\analytics;

defined('MOODLE_INTERNAL') || die();

/**
 * Class injector
 *
 * @package   local_liquidus
 * @copyright Copyright (c) 2020 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class injector {
    /** @var injector */
    private static $instance;

    /** @var bool */
    private $injected;

    /** @var null|\stdClass  */
    private $testpage;

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
        global $PAGE;

        if (!isloggedin()) {
            return;
        }

        if ($this->injected) {
            return;
        }
        $this->injected = true;

        $pluginenabled = get_config('local_liquidus', 'enabled');
        $shouldtrack = analytics::should_track();
        if (empty($pluginenabled) || !$shouldtrack) {
            return;
        }

        $analyticstypes = ['segment', 'keenio', 'kinesis'];
        $trackersinfo = [];
        $engine = null;
        foreach ($analyticstypes as $type) {
            $enabled = get_config('local_liquidus', $type);
            if ($enabled) {
                $classname = "\\local_liquidus\\api\\{$type}";
                if (!class_exists($classname, true)) {
                    debugging("Local Liquidus Module: Analytics setting '{$type}' doesn't map to a class name.");
                    return;
                }

                $engine = new $classname;
                $trackersinfo[] = $engine::get_tracker_info();
            }
        }

        if (empty($trackersinfo)) {
            return;
        }

        $page = $PAGE;
        if ($this->testpage !== null) {
            // Someone is testing a page.
            $page = $this->testpage;
        }

        $page->requires->js_call_amd('local_liquidus/main', 'init', [$trackersinfo]);
    }

    /**
     *
     */
    public function reset() {
        $this->injected = false;
        $this->testpage = null;
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
}
