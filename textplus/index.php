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
 * Text Replacer main interface
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

/**
 * Helper function to extract form data from wizard data
 * Only includes scalar values - excludes arrays which cause form errors
 *
 * @param stdClass $wizard_data Wizard data from cache
 * @return stdClass Form data with only scalar values
 */
function get_form_data_from_wizard($wizard_data) {
    $form_data = new stdClass();
    $form_data->searchterm = $wizard_data->searchterm;
    $form_data->casesensitive = $wizard_data->casesensitive;
    $form_data->executionmode = $wizard_data->executionmode;
    $form_data->replacementtext = $wizard_data->replacementtext;
    // Note: databaseitems and selecteditems are NOT included - they stay in cache only
    return $form_data;
}

admin_externalpage_setup('local_textplus_tool');

require_login();

// Require site administrator permission.
$system_context = context_system::instance();
if (!has_capability('moodle/site:config', $system_context)) {
    // Display error page for non-administrators.
    $PAGE->set_url(new moodle_url('/local/textplus/index.php'));
    $PAGE->set_context($system_context);
    $PAGE->set_title(get_string('pluginname', 'local_textplus'));
    $PAGE->set_heading(get_string('heading', 'local_textplus'));
    
    echo $OUTPUT->header();
    echo $OUTPUT->notification(
        get_string('error_requiresiteadmin', 'local_textplus'),
        \core\output\notification::NOTIFY_ERROR
    );
    echo $OUTPUT->footer();
    exit;
}

require_capability('local/textplus:view', context_system::instance());

$PAGE->set_url(new moodle_url('/local/textplus/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_textplus'));
$PAGE->set_heading(get_string('heading', 'local_textplus'));

// Initialize cache for wizard data.
$cache = cache::make('local_textplus', 'wizarddata');

// Get default settings.
$default_search_term = get_config('local_textplus', 'defaultsearchterm');
$default_mode = get_config('local_textplus', 'defaultmode');

// Set defaults if not configured.
if ($default_search_term === false) {
    $default_search_term = '';
}
if ($default_mode === false) {
    $default_mode = 'preview';
}

// Get current step.
$step = optional_param('step', 1, PARAM_INT);
$back_btn = optional_param('backbtn', '', PARAM_RAW);
$next_btn = optional_param('nextbtn', '', PARAM_RAW);
$execute_btn = optional_param('executebtn', '', PARAM_RAW);

// Handle "Start Over" by clearing cache.
$start_over = optional_param('startover', '', PARAM_RAW);
if ($start_over) {
    $cache->delete('wizard');
    redirect($PAGE->url);
}

// Initialize or retrieve wizard data from cache.
$wizard_data = $cache->get('wizard');
if (!$wizard_data) {
    $wizard_data = new stdClass();
    $wizard_data->searchterm = $default_search_term;
    $wizard_data->casesensitive = 0;
    $wizard_data->executionmode = $default_mode;
    $wizard_data->replacementtext = '';
    $wizard_data->databaseitems = [];
    $wizard_data->selecteditems = [];
    $cache->set('wizard', $wizard_data);
}

// Prepare form custom data.
// Only pass scalar values to form - arrays cause htmlspecialchars errors
$form_data = get_form_data_from_wizard($wizard_data);

$custom_data = [
    'formdata' => $form_data,
    'step' => $step,
];

// Create form.
$mform = new \local_textplus\form\replacer_form(null, $custom_data);

// STEP 2: Handle content selection (uses custom HTML form, not moodleform)
if ($step == 2 && $next_btn) {
    require_sesskey();
    require_capability('moodle/site:config', context_system::instance());
    require_capability('local/textplus:manage', context_system::instance());
    
    // Get selected database items from submitted form - sanitize input.
    $selected_items = optional_param_array('database_items', [], PARAM_RAW);
    
    // Validate at least one item is selected.
    if (empty($selected_items)) {
        redirect($PAGE->url . '?step=2', get_string('error_noitemsselected', 'local_textplus'),
            null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Validate and parse selections (format: table|id|field).
    $validated_selections = [];
    foreach ($selected_items as $item_key) {
        $parts = explode('|', $item_key);
        if (count($parts) === 3) {
            $validated_selections[] = [
                'table' => clean_param($parts[0], PARAM_ALPHANUMEXT),
                'id' => clean_param($parts[1], PARAM_INT),
                'field' => clean_param($parts[2], PARAM_ALPHANUMEXT)
            ];
        }
    }
    
    // Save validated selections to cache.
    $wizard_data = $cache->get('wizard');
    $wizard_data->selecteditems = $validated_selections;
    $cache->set('wizard', $wizard_data);
    
    // Move to step 3.
    $step = 3;
    $custom_data['step'] = $step;
    $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
    
    $mform = new \local_textplus\form\replacer_form(null, $custom_data);
}

// STEP 2: Handle back button separately
if ($step == 2 && $back_btn) {
    require_sesskey();
    $step = 1;
    $wizard_data = $cache->get('wizard');
    $custom_data['step'] = $step;
    $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
    $mform = new \local_textplus\form\replacer_form(null, $custom_data);
}

// STEP 3: Handle back button separately (before form validation)
if ($step == 3 && $back_btn) {
    require_sesskey();
    $step = 2;
    $wizard_data = $cache->get('wizard');
    $custom_data['step'] = $step;
    $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
    $mform = new \local_textplus\form\replacer_form(null, $custom_data);
}

// Handle form submission (for steps 1 and 3 only - step 2 handled above)
if ($from_form = $mform->get_data()) {
    require_sesskey();
    
    // Verify site administrator permission for all form submissions.
    if (!has_capability('moodle/site:config', context_system::instance())) {
        throw new moodle_exception('error_requiresiteadmin', 'local_textplus', '', null, 
            get_string('error_requiresiteadmin_formsubmission', 'local_textplus'));
    }
    
    // STEP 1: Search for text in database
    if ($step == 1 && !$back_btn) {
        require_capability('local/textplus:manage', context_system::instance());
        
        // Save search criteria to cache (already sanitized by moodle form).
        $wizard_data = $cache->get('wizard');
        $wizard_data->searchterm = $from_form->searchterm;
        $wizard_data->casesensitive = isset($from_form->casesensitive) ? $from_form->casesensitive : 0;
        
        $config = [
            'search_term' => $from_form->searchterm,
            'case_sensitive' => (bool)$from_form->casesensitive,
            'dry_run' => true,
        ];
        
        $replacer = new \local_textplus\replacer($config);
        $database_items = $replacer->find_text_in_database();
        
        // Store found database items in cache.
        $wizard_data->databaseitems = $database_items;
        $cache->set('wizard', $wizard_data);
        
        // Move to step 2.
        $step = 2;
        $custom_data['step'] = $step;
        $custom_data['formdata'] = get_form_data_from_wizard($wizard_data);
        $mform = new \local_textplus\form\replacer_form(null, $custom_data);
        
    // STEP 3: Execute text replacement
    } else if ($step == 3 && $execute_btn) {
        // Double-check site administrator permission for text replacement.
        if (!has_capability('moodle/site:config', context_system::instance())) {
            throw new moodle_exception('error_requiresiteadmin', 'local_textplus', '', null,
                get_string('error_requiresiteadmin_formsubmission', 'local_textplus'));
        }
        
        require_capability('local/textplus:manage', context_system::instance());
        confirm_sesskey();
        
        // Verify backup confirmation.
        if (empty($from_form->backupconfirm)) {
            redirect($PAGE->url . '?step=3', get_string('backupconfirm_required', 'local_textplus'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
        
        // Get wizard data from cache.
        $wizard_data = $cache->get('wizard');
        
        // Save final options (already sanitized by form).
        $wizard_data->executionmode = $from_form->executionmode;
        $wizard_data->replacementtext = $from_form->replacementtext;
        $cache->set('wizard', $wizard_data);
        
        // Validate replacement text is provided.
        if (empty($from_form->replacementtext) && $from_form->replacementtext !== '0') {
            redirect($PAGE->url . '?step=3', get_string('error_noreplacementtext', 'local_textplus'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
        
        // Create replacer instance with final configuration.
        $config = [
            'search_term' => $wizard_data->searchterm,
            'replacement_text' => $wizard_data->replacementtext,
            'case_sensitive' => (bool)$wizard_data->casesensitive,
            'dry_run' => ($wizard_data->executionmode === 'preview'),
        ];
        
        $replacer = new \local_textplus\replacer($config);
        
        // Get selected items from cache.
        $items_to_process = $wizard_data->selecteditems;
        
        // Process text replacements.
        $replacer->process_text_replacements($items_to_process);
        
        // Log operation.
        $replacer->log_operation($USER->id);
        
        // Trigger event.
        $event = \local_textplus\event\images_replaced::create([
            'context' => context_system::instance(),
            'other' => [
                'searchterm' => $wizard_data->searchterm,
                'replacementtext' => $wizard_data->replacementtext,
                'itemsreplaced' => $replacer->get_stats()['items_replaced'],
            ],
        ]);
        $event->trigger();
        
        // Display results.
        echo $OUTPUT->header();
        $renderer = $PAGE->get_renderer('local_textplus');
        echo $renderer->render_results($replacer, $config['dry_run']);
        
        // Clear cache (Start Over button is rendered by the renderer).
        $cache->delete('wizard');
        
        echo $OUTPUT->footer();
        exit;
    }
}

// Display the form.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('heading', 'local_textplus'));

// Show description only on step 1.
if ($step == 1) {
    echo html_writer::tag('p', get_string('description', 'local_textplus'));
    
    // Credits.
    echo html_writer::tag('p', get_string('credits', 'local_textplus'), ['class' => 'alert alert-info']);
}

// Display the form only for steps 1 and 3 (step 2 uses custom HTML form).
if ($step != 2) {
    $mform->display();
}

// STEP 2: Display content selection checkboxes.
$wizard_data = $cache->get('wizard');
if ($step == 2 && !empty($wizard_data)) {
    // Verify user still has permission.
    if (!has_capability('moodle/site:config', context_system::instance())) {
        echo $OUTPUT->notification(
            get_string('error_requiresiteadmin', 'local_textplus'),
            \core\output\notification::NOTIFY_ERROR
        );
        echo $OUTPUT->footer();
        exit;
    }
    
    // Display step indicator manually for step 2.
    echo '<div class="step-indicator mb-4">';
    echo '<ol class="list-inline">';
    $steps = [
        1 => get_string('step1_name', 'local_textplus'),
        2 => get_string('step2_name', 'local_textplus'),
        3 => get_string('step3_name', 'local_textplus'),
    ];
    foreach ($steps as $num => $name) {
        $class = 'list-inline-item badge ';
        if ($num == 2) {
            $class .= 'badge-primary';
        } else if ($num < 2) {
            $class .= 'badge-success';
        } else {
            $class .= 'badge-secondary';
        }
        echo '<li class="' . $class . '">' . $num . '. ' . s($name) . '</li>';
    }
    echo '</ol>';
    echo '</div>';
    
    $database_items = $wizard_data->databaseitems;
    
    if (empty($database_items)) {
        echo $OUTPUT->notification(
            get_string('noitemsfound_desc', 'local_textplus', s($wizard_data->searchterm)),
            \core\output\notification::NOTIFY_WARNING
        );
        
        // Show Start Over button when no items found.
        echo html_writer::div(
            html_writer::link(
                new moodle_url('/local/textplus/index.php', ['startover' => 1]), 
                get_string('startover', 'local_textplus'),
                ['class' => 'btn btn-primary']
            ),
            'mt-3'
        );
    } else {
        // Add custom CSS for step 2.
        echo html_writer::start_tag('style');
        echo '
            .item-list { background: #ffffff; border: 1px solid #dee2e6; border-radius: 6px; 
                        margin: 20px 0; max-height: 500px; overflow-y: auto; }
            .item { padding: 12px 15px; border-bottom: 1px solid #dee2e6; display: flex;
                        align-items: flex-start; gap: 10px; }
            .item:last-child { border-bottom: none; }
            .item:hover { background: #f8f9fa; }
            .item input[type="checkbox"] { margin-top: 3px; flex-shrink: 0; }
            .item-info { flex: 1; }
            .item-location { color: #0056b3; font-weight: 600; margin-bottom: 4px; }
            .item-location a { color: #0056b3; text-decoration: none; }
            .item-location a:hover { text-decoration: underline; color: #003d82; }
            .item-table { color: #666; font-size: 0.9em; font-family: monospace; }
            .item-occurrences { margin-top: 8px; }
            .occurrence-link { display: inline-block; background: #e3f2fd; color: #1976d2; 
                             padding: 3px 8px; margin: 2px; border-radius: 3px; font-size: 0.85em;
                             cursor: pointer; text-decoration: none; border: 1px solid #90caf9; }
            .occurrence-link:hover { background: #bbdefb; text-decoration: none; }
            
            /* Modal for occurrence popup */
            .occurrence-modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0;
                               width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.7); }
            .occurrence-modal-content { background-color: #fefefe; margin: 5% auto; padding: 0;
                                       border: 1px solid #888; width: 90%; max-width: 1200px;
                                       border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
            .occurrence-modal-header { padding: 15px 20px; background: #1976d2; color: white;
                                      border-radius: 8px 8px 0 0; display: flex; justify-content: space-between;
                                      align-items: center; }
            .occurrence-modal-header h3 { margin: 0; font-size: 1.2em; }
            .occurrence-modal-close { color: white; font-size: 28px; font-weight: bold;
                                     cursor: pointer; background: none; border: none; }
            .occurrence-modal-close:hover { color: #ddd; }
            .occurrence-modal-body { padding: 20px; max-height: 70vh; overflow-y: auto; }
            .occurrence-code { background: #f5f5f5; padding: 15px; border-radius: 4px;
                             font-family: "Courier New", Courier, monospace; font-size: 13px;
                             white-space: pre-wrap; word-wrap: break-word;
                             line-height: 1.6; border: 1px solid #ddd; overflow-x: auto; 
                             color: #333; }
            .occurrence-code .highlight { background-color: #ffeb3b; color: #000; 
                                        font-weight: bold; padding: 1px 2px; border-radius: 2px; }
            .section-header { background: #f8f9fa; padding: 12px 15px; border-bottom: 2px solid #dee2e6;
                            font-weight: bold; margin-top: 20px; border-radius: 6px 6px 0 0; }
            .select-all-btn { margin: 10px 0; }
        ';
        echo html_writer::end_tag('style');
        
        echo html_writer::start_tag('form', [
            'method' => 'post',
            'action' => $PAGE->url->out(false),
            'id' => 'itemselectionform'
        ]);
        
        echo html_writer::tag('p', get_string('selectitemstoreplace', 'local_textplus'), 
            ['class' => 'lead']);
        
        // Database items section.
        if (!empty($database_items)) {
            echo html_writer::div(
                get_string('databaseitems', 'local_textplus'),
                'section-header'
            );
            
            echo html_writer::div(
                html_writer::link('#', get_string('selectall', 'local_textplus'), 
                    ['id' => 'select-all-items', 'class' => 'btn btn-sm btn-secondary']),
                'select-all-btn'
            );
            
            echo html_writer::start_tag('div', ['class' => 'item-list']);
            foreach ($database_items as $item_index => $item) {
                // Handle both object and array formats (session serialization may vary)
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
                
                echo html_writer::start_div('item');
                
                // Checkbox.
                echo html_writer::checkbox('database_items[]', $item_key, false, '', 
                    ['class' => 'item-checkbox', 'id' => $checkbox_id]);
                
                // Item info.
                echo html_writer::start_tag('label', ['for' => $checkbox_id, 'style' => 'flex: 1; margin: 0; cursor: pointer;']);
                
                // Location (make it clickable if URL exists)
                if ($url) {
                    $location_html = html_writer::link($url, s($location), [
                        'target' => '_blank',
                        'onclick' => 'event.stopPropagation();'
                    ]);
                    echo html_writer::div($location_html, 'item-location');
                } else {
                    echo html_writer::div(s($location), 'item-location');
                }
                
                echo html_writer::div(s($table) . '.' . s($field) . ' (ID: ' . (int)$id . ')', 'item-table');
                
                // Show occurrences as clickable links
                if (!empty($occurrences) && is_array($occurrences)) {
                    $occurrence_count = count($occurrences);
                    echo html_writer::start_div('item-occurrences');
                    echo html_writer::tag('strong', $occurrence_count . ' occurrence' . ($occurrence_count > 1 ? 's' : '') . ': ');
                    
                    foreach ($occurrences as $occ_index => $occurrence) {
                        $occ_id = 'occ_' . $item_index . '_' . $occ_index;
                        $context_data = is_array($occurrence) ? 
                            (isset($occurrence['context']) ? $occurrence['context'] : '') :
                            (isset($occurrence->context) ? $occurrence->context : '');
                        $match_data = is_array($occurrence) ? 
                            (isset($occurrence['match']) ? $occurrence['match'] : '') :
                            (isset($occurrence->match) ? $occurrence->match : '');
                        
                        // Skip empty contexts
                        if (empty($context_data)) {
                            continue;
                        }
                        
                        // Use base64 encoding to preserve exact data without any HTML entity issues
                        // This prevents double-encoding problems with database content that already has entities
                        echo html_writer::link('#', '#' . ($occ_index + 1), [
                            'class' => 'occurrence-link',
                            'data-context' => base64_encode($context_data),
                            'data-match' => base64_encode($match_data),
                            'data-location' => base64_encode($location),
                            'onclick' => 'showOccurrence(this); return false;'
                        ]);
                    }
                    echo html_writer::end_div();
                }
                
                echo html_writer::end_tag('label');
                
                echo html_writer::end_div();
            }
            echo html_writer::end_tag('div');
            
            // JavaScript for select all.
            $select_all_text = addslashes_js(get_string('selectall', 'local_textplus'));
            $deselect_all_text = 'Deselect All';
            $warning_text = addslashes_js(get_string('warning_selectall', 'local_textplus'));
            echo html_writer::script("
                document.getElementById('select-all-items').addEventListener('click', function(e) {
                    e.preventDefault();
                    var checkboxes = document.querySelectorAll('input.item-checkbox');
                    var allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    
                    if (!allChecked) {
                        // Selecting all - show warning
                        alert('{$warning_text}');
                    }
                    
                    checkboxes.forEach(function(cb) {
                        cb.checked = !allChecked;
                    });
                    this.textContent = allChecked ? '{$select_all_text}' : '{$deselect_all_text}';
                });
            ");
            
            // Add modal for occurrence popups
            echo '<div id="occurrenceModal" class="occurrence-modal">';
            echo '  <div class="occurrence-modal-content">';
            echo '    <div class="occurrence-modal-header">';
            echo '      <h3 id="occurrenceModalTitle">Code Snippet</h3>';
            echo '      <button type="button" class="occurrence-modal-close" onclick="closeOccurrenceModal(); return false;">&times;</button>';
            echo '    </div>';
            echo '    <div class="occurrence-modal-body">';
            echo '      <div id="occurrenceModalBody" class="occurrence-code"></div>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
            
            // JavaScript for occurrence modal
            $js = <<<'JAVASCRIPT'
                // UTF-8 safe base64 decode function with error handling
                // Standard atob() doesn't handle UTF-8 multi-byte characters (Japanese, Chinese, Arabic, emoji, etc.)
                function base64DecodeUnicode(str) {
                    try {
                        // Method 1: Try the standard UTF-8 decoding approach
                        return decodeURIComponent(Array.prototype.map.call(atob(str), function(c) {
                            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                        }).join(''));
                    } catch (e) {
                        // Method 2: Fallback for malformed sequences - use TextDecoder if available
                        try {
                            var binaryString = atob(str);
                            var bytes = new Uint8Array(binaryString.length);
                            for (var i = 0; i < binaryString.length; i++) {
                                bytes[i] = binaryString.charCodeAt(i);
                            }
                            // Use TextDecoder for robust UTF-8 decoding
                            if (typeof TextDecoder !== 'undefined') {
                                return new TextDecoder('utf-8').decode(bytes);
                            } else {
                                // Final fallback: return raw atob result
                                return binaryString;
                            }
                        } catch (e2) {
                            // Last resort: return error message
                            console.error('Base64 decode error:', e, e2);
                            return '[Error decoding content]';
                        }
                    }
                }
                
                function showOccurrence(link) {
                    var modal = document.getElementById('occurrenceModal');
                    var title = document.getElementById('occurrenceModalTitle');
                    var body = document.getElementById('occurrenceModalBody');
                    
                    // Get base64-encoded data from attributes and decode
                    var contextEncoded = link.getAttribute('data-context');
                    var matchEncoded = link.getAttribute('data-match');
                    var locationEncoded = link.getAttribute('data-location');
                    
                    // Decode from base64 using UTF-8 safe function
                    var context = base64DecodeUnicode(contextEncoded);
                    var match = base64DecodeUnicode(matchEncoded);
                    var location = base64DecodeUnicode(locationEncoded);
                    
                    title.textContent = location;
                    
                    // Clear the body first
                    body.textContent = '';
                    
                    // Create a text node to safely display the code without HTML interpretation
                    // This prevents any HTML/JS from executing and shows it as plain text
                    
                    // For highlighting, we need to split the context and wrap the match
                    // Use a case-insensitive search to find all matches
                    var contextLower = context.toLowerCase();
                    var matchLower = match.toLowerCase();
                    var lastIndex = 0;
                    var result = '';
                    
                    // Find all occurrences of the search term to highlight
                    while (true) {
                        var index = contextLower.indexOf(matchLower, lastIndex);
                        if (index === -1) {
                            // Add remaining text
                            result += context.substring(lastIndex);
                            break;
                        }
                        
                        // Add text before match
                        result += context.substring(lastIndex, index);
                        
                        // Add highlighted match
                        result += '<<<HIGHLIGHT_START>>>' + context.substring(index, index + match.length) + '<<<HIGHLIGHT_END>>>';
                        
                        lastIndex = index + match.length;
                    }
                    
                    // Now split by our markers and create text nodes and highlight spans
                    var parts = result.split(/<<<HIGHLIGHT_START>>>|<<<HIGHLIGHT_END>>>/);
                    for (var i = 0; i < parts.length; i++) {
                        if (i % 2 === 1) {
                            // This is a highlighted part
                            var span = document.createElement('span');
                            span.className = 'highlight';
                            span.textContent = parts[i];
                            body.appendChild(span);
                        } else {
                            // This is normal text
                            var textNode = document.createTextNode(parts[i]);
                            body.appendChild(textNode);
                        }
                    }
                    
                    modal.style.display = 'block';
                }
                
                function closeOccurrenceModal() {
                    var modal = document.getElementById('occurrenceModal');
                    modal.style.display = 'none';
                }
                
                // Close modal when clicking outside of it
                window.onclick = function(event) {
                    var modal = document.getElementById('occurrenceModal');
                    if (event.target == modal) {
                        closeOccurrenceModal();
                    }
                }
                
                // Close modal on Escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        closeOccurrenceModal();
                    }
                });
JAVASCRIPT;
            echo html_writer::script($js);
        }
        
        // Hidden fields to preserve step data.
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'step', 'value' => 2]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        
        // Buttons.
        echo html_writer::start_div('mt-3');
        echo html_writer::tag('button', get_string('back', 'local_textplus'), [
            'type' => 'submit',
            'name' => 'backbtn',
            'value' => '1',
            'class' => 'btn btn-secondary mr-2'
        ]);
        echo html_writer::tag('button', get_string('next', 'local_textplus'), [
            'type' => 'submit',
            'name' => 'nextbtn',
            'value' => '1',
            'class' => 'btn btn-primary'
        ]);
        echo html_writer::end_div();
        
        echo html_writer::end_tag('form');
    }
}

// Display directories info on step 1.
if ($step == 1) {
    echo html_writer::start_div('alert alert-info mt-3');
    echo html_writer::tag('strong', get_string('directoriesscanned', 'local_textplus'));
    echo html_writer::tag('p', get_string('directories_list', 'local_textplus'));
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
