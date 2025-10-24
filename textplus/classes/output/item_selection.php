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
 * Item selection renderable for TextPlus plugin.
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_textplus\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Item selection renderable class.
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_selection implements renderable, templatable {

    /** @var array Database items */
    protected $database_items;

    /** @var string Search term */
    protected $search_term;

    /** @var int Total occurrences */
    protected $total_occurrences;

    /**
     * Constructor.
     *
     * @param array $database_items Database items
     * @param string $search_term Search term
     */
    public function __construct($database_items, $search_term = '') {
        $this->database_items = $database_items;
        $this->search_term = $search_term;
        $this->calculate_total_occurrences();
    }

    /**
     * Calculate total occurrences across all items.
     */
    protected function calculate_total_occurrences() {
        $this->total_occurrences = 0;
        foreach ($this->database_items as $item) {
            if (isset($item->occurrence_count)) {
                $this->total_occurrences += $item->occurrence_count;
            }
        }
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $data = new stdClass();

        $data->form_action = $PAGE->url->out(false);
        $data->sesskey = sesskey();
        $data->select_items_message = get_string('selectitemstoreplace', 'local_textplus');
        $data->total_occurrences = $this->total_occurrences;

        // Database items.
        $data->has_items = !empty($this->database_items);
        if ($data->has_items) {
            $data->items = [];
            foreach ($this->database_items as $item) {
                $preview_text = '';
                if (isset($item->preview) && !empty($item->preview)) {
                    $preview_text = shorten_text(s($item->preview), 200);
                }

                $occurrence_count = isset($item->occurrence_count) ? (int)$item->occurrence_count : 0;

                $data->items[] = [
                    'item_id' => $item->id ?? 0,
                    'checkbox_id' => 'item_' . ($item->id ?? 0),
                    'table_name' => s($item->table_name ?? ''),
                    'field_name' => s($item->field_name ?? ''),
                    'occurrence_count' => $occurrence_count,
                    'preview_text' => $preview_text,
                    'has_occurrences' => $occurrence_count > 0
                ];
            }
        }

        // Labels.
        $data->select_all_label = get_string('selectall', 'local_textplus');
        $data->deselect_all_label = get_string('deselectall', 'local_textplus');
        $data->warning_text = get_string('warning_selectall', 'local_textplus');
        $data->back_label = get_string('back', 'local_textplus');
        $data->next_label = get_string('next', 'local_textplus');

        return $data;
    }
}
