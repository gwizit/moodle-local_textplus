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
 * Text replacer renderer
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer for Text replacer plugin
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_textplus_renderer extends plugin_renderer_base {

    /**
     * Render results page using the output API and templates.
     *
     * @param \local_textplus\replacer $replacer Replacer instance
     * @param array $database_items Database items
     * @param bool $scan_only Whether this is scan only
     * @return string HTML output
     */
    public function render_results($replacer, $database_items, $scan_only) {
        // Use the new output renderable and template.
        $results = new \local_textplus\output\results($replacer, $database_items, $scan_only);
        return $this->render_from_template('local_textplus/results', $results->export_for_template($this));
    }

    /**
     * Render item selection page using the output API and templates.
     *
     * @param array $database_items Database items
     * @param string $search_term Search term
     * @return string HTML output
     */
    public function render_item_selection($database_items, $search_term = '') {
        $itemselection = new \local_textplus\output\item_selection($database_items, $search_term);
        return $this->render_from_template('local_textplus/item_selection', $itemselection->export_for_template($this));
    }

    /**
     * Render step indicator using the output API and templates.
     *
     * @param int $current_step Current step number
     * @return string HTML output
     */
    public function render_step_indicator($current_step) {
        $stepindicator = new \local_textplus\output\step_indicator($current_step);
        return $this->render_from_template('local_textplus/step_indicator', $stepindicator->export_for_template($this));
    }

    /**
     * Render no items found message using the output API and templates.
     *
     * @param string $search_term Search term
     * @return string HTML output
     */
    public function render_no_items_found($search_term) {
        $noitemsfound = new \local_textplus\output\no_items_found($search_term);
        return $this->render_from_template('local_textplus/no_items_found', $noitemsfound->export_for_template($this));
    }
}
