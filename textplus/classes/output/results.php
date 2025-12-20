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
 * Results page renderable for TextPlus plugin.
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
 * Results page renderable class.
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class results implements renderable, templatable {

    /** @var \local_textplus\replacer Replacer instance */
    protected \local_textplus\replacer $replacer;

    /** @var array Database items */
    protected array $database_items;

    /** @var bool Whether this is scan only */
    protected bool $scan_only;

    /**
     * Constructor.
     *
     * @param \local_textplus\replacer $replacer Replacer instance
     * @param array $database_items Database items
     * @param bool $scan_only Whether this is scan only
     */
    public function __construct(\local_textplus\replacer $replacer, array $database_items, bool $scan_only) {
        $this->replacer = $replacer;
        $this->database_items = $database_items;
        $this->scan_only = $scan_only;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $stats = $this->replacer->get_stats();
        $replacement_log = $this->replacer->get_replacement_log();

        // Title.
        $data->title = get_string('resultstitle', 'local_textplus');
        $data->scan_only = $this->scan_only;

        // Preview warning.
        if ($this->scan_only) {
            $data->preview_warning = get_string('preview_mode_warning', 'local_textplus');
        }

        // Filter replacement log for successful replacements only.
        $successful_items = array_filter($replacement_log, function($entry) {
            return isset($entry['status']) && $entry['status'] === 'success';
        });

        // Statistics.
        $data->stats = [];
        if (!$this->scan_only) {
            $data->stats[] = [
                'number' => $stats['items_replaced'],
                'label' => get_string('stats_replaced', 'local_textplus')
            ];

            if ($stats['occurrences_replaced'] > 0) {
                $data->stats[] = [
                    'number' => $stats['occurrences_replaced'],
                    'label' => get_string('stats_occurrences', 'local_textplus')
                ];
            }

            if ($stats['items_failed'] > 0) {
                $data->stats[] = [
                    'number' => $stats['items_failed'],
                    'label' => get_string('stats_failed', 'local_textplus')
                ];
            }
        }

        // No items replaced message.
        $data->no_items_replaced = !$this->scan_only && empty($successful_items);
        if ($data->no_items_replaced) {
            $data->no_items_message = get_string('noitemsreplaced', 'local_textplus') . '<br>' .
                get_string('noitemsreplaced_desc', 'local_textplus');
        }

        // Database items.
        $data->database_items = [];
        if (!$this->scan_only && !empty($successful_items)) {
            $data->database_header = get_string('itemsreplaced', 'local_textplus');
            foreach ($successful_items as $entry) {
                $preview_text = '';
                $has_preview = false;
                if (isset($entry['preview']) && !empty($entry['preview'])) {
                    $preview_text = shorten_text(s($entry['preview']), 200);
                    $has_preview = true;
                }

                $message_class = ($entry['status'] === 'success') ? 'text-success' : 'text-danger';
                
                // Format location string with language string for ID
                $location_string = s($entry['table'] ?? '') . ' → ' . s($entry['field'] ?? '') . 
                    ' (' . get_string('id_label', 'local_textplus') . ': ' . (int)($entry['id'] ?? 0) . ')';

                $data->database_items[] = [
                    'location_string' => $location_string,
                    'message' => s($entry['message']),
                    'message_class' => $message_class,
                    'has_preview' => $has_preview,
                    'preview_text' => $preview_text,
                    'preview_label' => get_string('preview', 'local_textplus')
                ];
            }
        }

        // Processing output.
        $data->has_output = !$this->scan_only && !empty($this->replacer->get_output());
        if ($data->has_output) {
            $data->output_title = get_string('processingoutput', 'local_textplus');
            $data->output_lines = [];
            foreach ($this->replacer->get_output() as $msg) {
                $data->output_lines[] = [
                    'type' => $msg['type'],
                    'message' => htmlspecialchars($msg['message'])
                ];
            }
        }

        // Completion message.
        if (!$this->scan_only) {
            $completemsg = get_string('operationcomplete', 'local_textplus') . ' ';
            if ($stats['items_replaced'] > 0) {
                $completemsg .= get_string('operationcomplete_execute', 'local_textplus');
                $cachepurgeurl = new \moodle_url('/admin/purgecaches.php', ['confirm' => 1, 'sesskey' => sesskey()]);
                $completemsg .= ' ' . get_string('operationcomplete_clearcache', 'local_textplus', $cachepurgeurl->out());
                $data->completion_class = 'alert-success';
            } else {
                $data->completion_class = 'alert-info';
            }
            $data->completion_message = $completemsg;
        }

        // Donation message.
        $data->donation_message = get_string('donation_message', 'local_textplus');

        // Start over button.
        $data->startover_url = (new \moodle_url('/local/textplus/index.php', [
            'startover' => 1,
            'sesskey' => sesskey(),
        ]))->out(false);
        $data->startover_label = get_string('startover', 'local_textplus');

        return $data;
    }
}
