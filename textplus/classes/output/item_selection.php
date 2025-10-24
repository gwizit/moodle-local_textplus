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

    /**
     * Constructor.
     *
     * @param array $database_items Database items
     * @param string $search_term Search term
     */
    public function __construct($database_items, $search_term = '') {
        $this->database_items = $database_items;
        $this->search_term = $search_term;
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
        $data->database_header = get_string('databaseitems', 'local_textplus');

        // Database items.
        $data->has_items = !empty($this->database_items);
        if ($data->has_items) {
            $data->items = [];
            foreach ($this->database_items as $item_index => $item) {
                // Handle both object and array formats (session serialization may vary).
                $table = is_object($item) ? $item->table : $item['table'];
                $field = is_object($item) ? $item->field : $item['field'];
                $id = is_object($item) ? $item->id : $item['id'];
                $location = is_object($item) ? $item->location : $item['location'];
                $url = is_object($item) ? 
                    (isset($item->url) ? $item->url : null) : 
                    (isset($item['url']) ? $item['url'] : null);
                $occurrences = is_object($item) ? 
                    (isset($item->occurrences) ? $item->occurrences : []) : 
                    (isset($item['occurrences']) ? $item['occurrences'] : []);
                
                // Create unique item key for checkbox value (format: table|id|field).
                $item_key = s($table) . '|' . (int)$id . '|' . s($field);
                $checkbox_id = 'item_' . md5($item_key);
                
                // Prepare occurrence data.
                $occurrence_data = [];
                if (!empty($occurrences) && is_array($occurrences)) {
                    foreach ($occurrences as $occ_index => $occurrence) {
                        $context_data = is_array($occurrence) ? 
                            (isset($occurrence['context']) ? $occurrence['context'] : '') :
                            (isset($occurrence->context) ? $occurrence->context : '');
                        $match_data = is_array($occurrence) ? 
                            (isset($occurrence['match']) ? $occurrence['match'] : '') :
                            (isset($occurrence->match) ? $occurrence->match : '');
                        
                        // Skip empty contexts.
                        if (empty($context_data)) {
                            continue;
                        }
                        
                        $occurrence_data[] = [
                            'number' => $occ_index + 1,
                            'context' => base64_encode($context_data),
                            'match' => base64_encode($match_data),
                            'location' => base64_encode($location)
                        ];
                    }
                }
                
                $data->items[] = [
                    'item_key' => $item_key,
                    'checkbox_id' => $checkbox_id,
                    'location' => s($location),
                    'url' => $url,
                    'has_url' => !empty($url),
                    'table_info' => s($table) . '.' . s($field) . ' (ID: ' . (int)$id . ')',
                    'has_occurrences' => !empty($occurrence_data),
                    'occurrence_count' => count($occurrence_data),
                    'occurrence_plural' => count($occurrence_data) > 1 ? 's' : '',
                    'occurrences' => $occurrence_data
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
