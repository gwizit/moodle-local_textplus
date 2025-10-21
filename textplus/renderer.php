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
     * Render results page
     *
     * @param \local_textplus\replacer $replacer Replacer instance
     * @param bool $scanonly Whether this is scan only (preview mode)
     * @return string HTML output
     */
    public function render_results($replacer, $scanonly) {
        global $PAGE, $CFG;

        $output = '';

        // Add custom CSS - matching step 2 styling.
        $output .= html_writer::start_tag('style');
        $output .= '
            .stats-container { display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
            .stat-card { background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center;
                        min-width: 120px; border-left: 4px solid #3498db; }
            .stat-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
            .stat-label { color: #6c757d; font-size: 14px; }
            .file-list { background: #ffffff; border: 1px solid #dee2e6; border-radius: 6px; 
                        margin: 20px 0; max-height: 400px; overflow-y: auto; }
            .file-item { padding: 12px 15px; border-bottom: 1px solid #dee2e6; }
            .file-item:last-child { border-bottom: none; }
            .file-item:hover { background: #f8f9fa; }
            .file-link { color: #0056b3; text-decoration: none; font-family: monospace; 
                        word-break: break-all; }
            .file-link:hover { text-decoration: underline; color: #003d82; }
            .file-details { color: #666; font-size: 0.9em; margin-top: 4px; }
            .preview-warning { background: #fff3cd; border: 2px solid #ffc107; border-radius: 6px;
                             padding: 15px 20px; margin: 20px 0; color: #856404; }
            .preview-warning strong { color: #856404; }
            .output-console { background: #2d3748; color: #e2e8f0; padding: 20px; border-radius: 6px;
                            font-family: monospace; white-space: pre-wrap; max-height: 500px; overflow-y: auto;
                            margin: 20px 0; }
            .output-line { margin: 2px 0; }
            .output-info { color: #90cdf4; }
            .output-success { color: #68d391; }
            .output-warning { color: #fbd38d; }
            .output-error { color: #fc8181; }
            .section-header { background: #f8f9fa; padding: 12px 15px; border-bottom: 2px solid #dee2e6;
                            font-weight: bold; margin-top: 20px; border-radius: 6px 6px 0 0; }
            
            /* Image preview modal */
            .image-preview-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0;
                                  width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.9); }
            .image-preview-content { margin: auto; display: block; max-width: 90vw; max-height: 90vh;
                                    object-fit: contain; position: absolute; top: 50%; left: 50%;
                                    transform: translate(-50%, -50%); }
            .image-preview-close { position: absolute; top: 20px; right: 40px; color: #f1f1f1;
                                  font-size: 40px; font-weight: bold; cursor: pointer; z-index: 10000; }
            .image-preview-close:hover, .image-preview-close:focus { color: #bbb; }
            .image-preview-caption { margin: auto; display: block; width: 80%; max-width: 700px;
                                    text-align: center; color: #ccc; padding: 10px 0; position: absolute;
                                    bottom: 20px; left: 50%; transform: translateX(-50%); }
        ';
        $output .= html_writer::end_tag('style');
        
        // Add image preview modal HTML
        $output .= '<div id="imagePreviewModal" class="image-preview-modal">';
        $output .= '  <span class="image-preview-close">&times;</span>';
        $output .= '  <img class="image-preview-content" id="imagePreviewImg">';
        $output .= '  <div class="image-preview-caption" id="imagePreviewCaption"></div>';
        $output .= '</div>';
        
        // Add JavaScript for image preview
        $output .= html_writer::script("
            (function() {
                var modal = document.getElementById('imagePreviewModal');
                var modalImg = document.getElementById('imagePreviewImg');
                var captionText = document.getElementById('imagePreviewCaption');
                var closeBtn = document.querySelector('.image-preview-close');
                
                // Function to close and clear modal
                function closeModal() {
                    modal.style.display = 'none';
                    modalImg.src = '';  // Clear the image
                    captionText.innerHTML = '';
                }
                
                // Close modal when clicking X or outside image
                closeBtn.onclick = closeModal;
                modal.onclick = function(e) { 
                    if (e.target === modal || e.target === closeBtn) {
                        closeModal();
                    }
                }
                
                // Close on Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modal.style.display === 'block') {
                        closeModal();
                    }
                });
                
                // Add click handlers to all file links
                document.addEventListener('click', function(e) {
                    var target = e.target;
                    if (target.classList.contains('file-link') && target.tagName === 'A') {
                        var href = target.getAttribute('href');
                        var filename = target.textContent || target.innerText;
                        
                        // Check if it's an image file by checking both filename and href
                        if (href && (/\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(filename) || /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(href))) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Clear old image first
                            modalImg.src = '';
                            captionText.innerHTML = filename;
                            
                            // Show modal and load new image
                            modal.style.display = 'block';
                            modalImg.src = href;
                            
                            return false;
                        }
                    }
                });
            })();
        ");


        $output .= $this->heading(get_string('resultstitle', 'local_textplus'));

        $stats = $replacer->get_stats();
        $replacementlog = $replacer->get_replacement_log();

        // Preview mode warning at the top.
        if ($scanonly) {
            $output .= html_writer::div(
                get_string('preview_mode_warning', 'local_textplus'),
                'preview-warning'
            );
        }

        // Statistics.
        $output .= html_writer::start_div('stats-container');

        // Filter replacement log for successful replacements only.
        // TextPlus uses 'status' field with values: success, failed, skipped, preview
        $successfulitems = array_filter($replacementlog, function($entry) {
            return isset($entry['status']) && ($entry['status'] === 'success' || $entry['status'] === 'preview');
        });
        $faileditems = array_filter($replacementlog, function($entry) {
            return isset($entry['status']) && $entry['status'] === 'failed';
        });

        if (!$scanonly) {
            $output .= $this->render_stat_card($stats['items_replaced'],
                get_string('stats_replaced', 'local_textplus'));

            if ($stats['items_failed'] > 0) {
                $output .= $this->render_stat_card($stats['items_failed'],
                    get_string('stats_failed', 'local_textplus'));
            }
        } else {
            // Preview mode - show items found
            $output .= $this->render_stat_card($stats['items_found'],
                get_string('stats_found', 'local_textplus'));
        }

        $output .= html_writer::end_div();

        // No items replaced message.
        if (!$scanonly && empty($successfulitems)) {
            $output .= html_writer::div(
                get_string('noitemsreplaced', 'local_textplus'),
                'alert alert-warning'
            );
        }

        // Database text replacement results - show successfully replaced items.
        if (!$scanonly && !empty($successfulitems)) {
            $output .= html_writer::div(
                get_string('itemsreplaced', 'local_textplus'),
                'section-header'
            );
            
            $output .= html_writer::start_div('file-list');
            foreach ($successfulitems as $entry) {
                $table = isset($entry['table']) ? $entry['table'] : 'unknown';
                $field = isset($entry['field']) ? $entry['field'] : 'unknown';
                $id = isset($entry['id']) ? $entry['id'] : 0;
                $message = isset($entry['message']) ? $entry['message'] : '';
                
                $itemlabel = ucfirst($table) . ' (ID: ' . $id . ')';
                
                $output .= html_writer::start_div('file-item');
                $output .= html_writer::tag('strong', s($itemlabel));
                $output .= html_writer::div(
                    s($table) . '.' . s($field) . ' - ' . s($message),
                    'file-details'
                );
                $output .= html_writer::end_div();
            }
            $output .= html_writer::end_div();
        }

        // Processing output.
        if (!$scanonly && !empty($replacer->get_output())) {
            $output .= $this->heading(get_string('processingoutput', 'local_textplus'), 3);
            $output .= html_writer::start_div('output-console');
            foreach ($replacer->get_output() as $msg) {
                $class = 'output-' . $msg['type'];
                $output .= html_writer::div(htmlspecialchars($msg['message']), 'output-line ' . $class);
            }
            $output .= html_writer::end_div();
        }

        // Completion message.
        if (!$scanonly) {
            $completemsg = get_string('operationcomplete', 'local_textplus') . ' ';
            if ($stats['items_replaced'] > 0) {
                $completemsg .= get_string('operationcomplete_execute', 'local_textplus');
                // Add cache clearing link
                $cachepurgeurl = new moodle_url('/admin/purgecaches.php', ['confirm' => 1, 'sesskey' => sesskey()]);
                $completemsg .= ' ' . get_string('operationcomplete_clearcache', 'local_textplus', $cachepurgeurl->out());
                $output .= html_writer::div($completemsg, 'alert alert-success');
            } else {
                $output .= html_writer::div($completemsg, 'alert alert-info');
            }
        }
        
        // Donation message
        $output .= html_writer::div(
            get_string('donation_message', 'local_textplus'),
            'alert alert-warning text-center'
        );

        // Back button.
        $output .= html_writer::div(
            $this->single_button(new moodle_url('/local/textplus/index.php'),
                get_string('startover', 'local_textplus'), 'get'),
            'mt-3'
        );

        return $output;
    }

    /**
     * Render a stat card
     *
     * @param int $number Number to display
     * @param string $label Label for the stat
     * @return string HTML output
     */
    private function render_stat_card($number, $label) {
        $output = html_writer::start_div('stat-card');
        $output .= html_writer::div($number, 'stat-number');
        $output .= html_writer::div($label, 'stat-label');
        $output .= html_writer::end_div();
        return $output;
    }

    /**
     * Render confirmation form for selected files
     *
     * @param array $formdata Original form data
     * @return string HTML output
     */
    private function render_confirmation_form($formdata) {
        $output = '';
        
        $output .= html_writer::start_tag('form', [
            'method' => 'post',
            'action' => new moodle_url('/local/textplus/index.php'),
            'id' => 'confirm-replacement-form'
        ]);
        
        // Hidden fields to preserve form data
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'confirm']);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'searchterm', 'value' => $formdata['searchterm'] ?? '']);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'filetype', 'value' => $formdata['filetype'] ?? 'image']);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'allowimageconversion', 'value' => $formdata['allowimageconversion'] ?? 1]);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'preservepermissions', 'value' => $formdata['preservepermissions'] ?? 1]);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'draftitemid', 'value' => $formdata['draftitemid'] ?? '']);
        
        // Confirmation buttons
        $output .= html_writer::start_div('confirm-buttons');
        $output .= html_writer::tag('button', get_string('confirmreplacement', 'local_textplus'), 
            ['type' => 'submit', 'class' => 'btn btn-primary']);
        $output .= html_writer::tag('button', get_string('cancel'), 
            ['type' => 'button', 'class' => 'btn btn-secondary', 'onclick' => 'window.location.href="/local/textplus/index.php"']);
        $output .= html_writer::end_div();
        
        $output .= html_writer::end_tag('form');
        
        // Add JavaScript for select all functionality
        $output .= html_writer::start_tag('script');
        $output .= '
            function toggleAllFilesystem(checkbox) {
                var checkboxes = document.querySelectorAll(".filesystem-checkbox");
                checkboxes.forEach(function(cb) {
                    cb.checked = checkbox.checked;
                });
            }
            
            function toggleAllDatabase(checkbox) {
                var checkboxes = document.querySelectorAll(".database-checkbox");
                checkboxes.forEach(function(cb) {
                    cb.checked = checkbox.checked;
                });
            }
            
            document.getElementById("confirm-replacement-form").addEventListener("submit", function(e) {
                var fsChecked = document.querySelectorAll(".filesystem-checkbox:checked").length;
                var dbChecked = document.querySelectorAll(".database-checkbox:checked").length;
                
                if (fsChecked === 0 && dbChecked === 0) {
                    e.preventDefault();
                    alert("' . get_string('error_nofilesselected', 'local_textplus') . '");
                    return false;
                }
                
                if (!confirm("' . get_string('confirmreplacement_confirm', 'local_textplus') . ' " + (fsChecked + dbChecked) + " file(s)?")) {
                    e.preventDefault();
                    return false;
                }
            });
        ';
        $output .= html_writer::end_tag('script');
        
        return $output;
    }

    /**
     * Render replacement log table
     *
     * @param array $log Replacement log entries
     * @return string HTML output
     */
    private function render_replacement_log($log) {
        $output = '';
        
        $output .= $this->heading(get_string('replacementlog', 'local_textplus'), 3);
        
        $output .= html_writer::start_div('replacement-log');
        
        // Header
        $output .= html_writer::div(
            get_string('replacementlog_header', 'local_textplus'),
            'replacement-log-header'
        );
        
        // Log entries
        foreach ($log as $entry) {
            $output .= html_writer::start_div('replacement-log-item');
            
            // Status icon
            $statusclass = $entry['success'] ? 'log-status-success' : 'log-status-failed';
            $statusicon = $entry['success'] ? '✓' : '✗';
            $output .= html_writer::div($statusicon, 'log-status-icon ' . $statusclass);
            
            // File information
            $output .= html_writer::start_div('', ['style' => 'flex: 1;']);
            
            // Filename and type badge
            $filenamehtml = html_writer::span(htmlspecialchars($entry['filename']), 'log-filename');
            $typebadge = html_writer::span(
                strtoupper($entry['type']),
                'log-type-badge'
            );
            $output .= html_writer::div($filenamehtml . ' ' . $typebadge);
            
            // Message
            $output .= html_writer::div(htmlspecialchars($entry['message']), 'log-message');
            
            // Additional details for database files
            if ($entry['type'] === 'database' && isset($entry['component'])) {
                $details = $entry['component'] . ' / ' . $entry['filearea'];
                if (!empty($entry['filepath']) && $entry['filepath'] !== '/') {
                    $details .= ' • ' . $entry['filepath'];
                }
                $output .= html_writer::div($details, 'log-details');
            }
            
            $output .= html_writer::end_div(); // End file info div
            
            $output .= html_writer::end_div(); // End log item
        }
        
        $output .= html_writer::end_div(); // End replacement-log
        
        // Summary
        $successcount = count(array_filter($log, function($entry) { return $entry['success']; }));
        $failedcount = count($log) - $successcount;
        
        $summarytext = get_string('replacementlog_summary', 'local_textplus', [
            'total' => count($log),
            'success' => $successcount,
            'failed' => $failedcount
        ]);
        
        $alertclass = $failedcount > 0 ? 'alert alert-warning' : 'alert alert-info';
        $output .= html_writer::div($summarytext, $alertclass);
        
        return $output;
    }
}
