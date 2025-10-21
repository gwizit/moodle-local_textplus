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
 * Text replaced event
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

namespace local_textplus\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Text replaced event class
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class images_replaced extends \core\event\base {

    /**
     * Init method
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Get name
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventtextreplaced', 'local_textplus');
    }

    /**
     * Get description
     *
     * @return string
     */
    public function get_description() {
        $itemsreplaced = isset($this->other['itemsreplaced']) ? $this->other['itemsreplaced'] : 0;
        return "The user with id '$this->userid' replaced text matching term '{$this->other['searchterm']}'. " .
            "Database items replaced: {$itemsreplaced}.";
    }

    /**
     * Get URL
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/textplus/index.php');
    }
}
